<?php
/**
 * @copyright 2016-2025 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity;

use S2\Rose\Helper\StringHelper;

class Query
{
    private const MAX_WORDS = 64;

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
        $content = self::normalizeValue($this->value);
        if ($content === '') {
            return [];
        }

        $content = strip_tags($content);

        // Normalize
        $content = str_replace(['«', '»', '“', '”', '‘', '’'], '"', $content);
        $content = str_replace('−', '-', $content); // Replace minus sign to a hyphen
        $content = str_replace(['---', '–', '−'], '—', $content); // Normalize dashes
        $content = self::safePregReplace('#,\\s+,#u', ',,', $content);
        $content = self::safePregReplace('#[^\\-\\p{L}0-9^_.,()";?!…:—]+#iu', ' ', $content);
        $content = mb_strtolower($content);

        // Replace decimal separators: ',' -> '.'
        $content = self::safePregReplace('#(?<=^|\\s)(\\-?\\d+),(\\d+)(?=\\s|$)#u', '\\1.\\2', $content);

        // Separate special chars at the beginning of the word
        while (true) {
            $content = self::safePregReplace('#(?:^|\\s)\K([—^()"?:!])(?=[^\s])#u', '\\1 ', $content, -1, $count);
            if ($count === 0 || $content === '') {
                break;
            }
        }

        // Separate special chars at the end of the word
        while (true) {
            $content = self::safePregReplace('#(?<=[^\s])([—^()"?:!])(?=\\s|$)#u', ' \\1', $content, -1, $count);
            if ($count === 0 || $content === '') {
                break;
            }
        }

        // Separate groups of commas
        $content = self::safePregReplace('#(,+)#u', ' \\1 ', $content);

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

        if (\count($words) > self::MAX_WORDS) {
            $words = \array_slice($words, 0, self::MAX_WORDS);
        }

        return $words;
    }

    private static function normalizeValue($value): string
    {
        if (\is_string($value)) {
            $stringValue = $value;
        } elseif (\is_scalar($value) || (class_exists(\Stringable::class) && $value instanceof \Stringable)) {
            $stringValue = (string)$value;
        } else {
            return '';
        }

        return self::normalizeUtf8($stringValue);
    }

    private static function normalizeUtf8(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $previousSubstitute = mb_substitute_character();
        mb_substitute_character('none');
        $converted = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        mb_substitute_character($previousSubstitute);

        if ($converted === false) {
            return '';
        }

        return $converted;
    }

    private static function safePregReplace(string $pattern, string $replacement, string $subject, int $limit = -1, ?int &$count = null): string
    {
        $result = preg_replace($pattern, $replacement, $subject, $limit, $count);

        return $result ?? '';
    }
}
