<?php
/**
 * @copyright 2016-2020 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

use S2\Rose\Helper\StringHelper;

class Query
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @var int|null
     */
    protected $instanceId;

    /**
     * @var int|null
     */
    protected $limit;

    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * @param string $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return int|null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     *
     * @return self
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     *
     * @return self
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int|null
     */
    public function getInstanceId()
    {
        return $this->instanceId;
    }

    /**
     * @param int|null $instanceId
     *
     * @return self
     */
    public function setInstanceId($instanceId)
    {
        $this->instanceId = $instanceId;

        return $this;
    }

    /**
     * @return string[]
     */
    public function valueToArray()
    {
        $content = strip_tags($this->value);

        // Normalize
        $content = str_replace(['«', '»', '“', '”', '‘', '’'], '"', $content);
        $content = str_replace('−', '-', $content); // Replace minus sign to a hyphen
        $content = str_replace(['---', '–', '−'], '—', $content); // Normalize dashes
        $content = preg_replace('#,\\s+,#u', ',,', $content);
        $content = preg_replace('#[^\\-\\p{L}0-9^_.,()";?!…:—]+#iu', ' ', $content);
        $content = mb_strtolower($content);

        // Replace decimal separators: ',' -> '.'
        $content = preg_replace('#(?<=^|\\s)(\\-?\\d+),(\\d+)(?=\\s|$)#u', '\\1.\\2', $content);

        // Separate special chars at the beginning of the word
        while (true) {
            $content = preg_replace('#(?:^|\\s)\K([—^()"?:!])(?=[^\s])#u', '\\1 ', $content, -1, $count);
            if ($count === 0) {
                break;
            }
        }

        // Separate special chars at the end of the word
        while (true) {
            $content = preg_replace('#(?<=[^\s])([—^()"?:!])(?=\\s|$)#u', ' \\1', $content, -1, $count);
            if ($count === 0) {
                break;
            }
        }

        // Separate groups of commas
        $content = preg_replace('#(,+)#u', ' \\1 ', $content);

        $words = preg_split('#\\s+#', $content);
        foreach ($words as $k => &$v) {
            // Replace 'ё' inside words
            if ($v !== 'ё' && false !== strpos($v, 'ё')) {
                $v = str_replace('ё', 'е', $v);
            }
        }
        unset($v);

        $words = array_unique($words);

        StringHelper::removeLongWords($words);

        // Fix keys
        // $words = array_values($words); // <- moved to helper

        return $words;
    }
}
