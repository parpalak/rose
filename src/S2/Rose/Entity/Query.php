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
        $content = str_replace(['---', '--', '–', '−'], '—', $content);
        $content = preg_replace('#,\\s+,#u', ',,', $content);
        $content = preg_replace('#[^\\-\\p{L}0-9\\^\\.,\\(\\)";?!…:—]+#iu', ' ', $content);
        $content = preg_replace('#\\n+#', ' ', $content);
        $content = preg_replace('#\\s+#u', ' ', $content);
        $content = mb_strtolower($content);

        $content = preg_replace('#(,+)#u', '\\1 ', $content);

        $content = preg_replace('#[ |\\/]+#', ' ', $content);

        $words = explode(' ', $content);
        foreach ($words as $k => $v) {
            // Separate special chars from the letter combination
            if (strlen($v) > 1) {
                foreach (['—', '^', '(', ')', '"', ':', '?', '!'] as $specialChar) {
                    if (mb_substr($v, 0, 1) == $specialChar || mb_substr($v, -1) == $specialChar) {
                        $words[$k] = str_replace($specialChar, '', $v);
                        $words[]   = $specialChar;
                    }
                }
            }

            // Separate hyphen from the letter combination
            if (strlen($v) > 1 && (substr($v, 0, 1) == '-' || substr($v, -1) == '-')) {
                $words[$k] = str_replace('-', '', $v);
                $words[]   = '-';
            }

            // Replace 'ё' inside words
            if (false !== strpos($v, 'ё') && $v != 'ё') {
                $words[$k] = str_replace('ё', 'е', $v);
            }

            // Remove ','
            if (preg_match('#^[^,]+,$#u', $v) || preg_match('#^,[^,]+$#u', $v)) {
                $words[$k] = str_replace(',', '', $v);
                $words[]   = ',';
            }
        }

        $words = StringHelper::removeLongWords($words);

        // Fix keys
        // $words = array_values($words); // <- moved to helper

        return $words;
    }
}
