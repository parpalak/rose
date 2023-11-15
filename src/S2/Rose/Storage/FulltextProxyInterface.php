<?php
/**
 * @copyright 2016-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

interface FulltextProxyInterface
{
    public const TYPE_TITLE = 1;
    public const TYPE_KEYWORD = 2;
    public const TYPE_CONTENT = 3;
    /**
     * @return array[][]
     */
    public function getByWord(string $word): array;

    public function countByWord(string $word): int;

    public function addWord(string $word, int $id, int $type, int $position): void;

    public function removeWord(string $word): void;

    /**
     * @return array|int[]
     */
    public function getFrequentWords(int $threshold): array;

    public function removeById(int $id): void;
}
