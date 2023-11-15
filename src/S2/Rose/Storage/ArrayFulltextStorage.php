<?php
/**
 * @copyright 2016-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

class ArrayFulltextStorage implements FulltextProxyInterface
{
    public const PREFIX_KEYWORD = 'K';
    public const PREFIX_TITLE   = 'T';

    /**
     * @var array|string[][]
     */
    protected array $fulltextIndex = [];

    public function getFulltextIndex(): array
    {
        return $this->fulltextIndex;
    }

    public function setFulltextIndex(array $fulltextIndex): self
    {
        $this->fulltextIndex = $fulltextIndex;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getByWord(string $word): array
    {
        if (!isset($this->fulltextIndex[$word])) {
            return [];
        }

        $result = [];
        foreach ($this->fulltextIndex[$word] as $id => $entries) {
            if (\is_int($entries)) {
                $result[$id][self::TYPE_CONTENT][] = $entries;
            } else {
                $entries = explode('|', $entries);
                foreach ($entries as $position) {
                    if ($position[0] === self::PREFIX_TITLE) {
                        $result[$id][self::TYPE_TITLE][] = base_convert(substr($position, 1), 36, 10);
                    } elseif ($position[0] === self::PREFIX_KEYWORD) {
                        $result[$id][self::TYPE_KEYWORD][] = base_convert(substr($position, 1), 36, 10);
                    } else {
                        $result[$id][self::TYPE_CONTENT][] = base_convert($position, 36, 10);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function countByWord(string $word): int
    {
        if (!isset($this->fulltextIndex[$word])) {
            return 0;
        }

        return \count($this->fulltextIndex[$word]);
    }

    /**
     * {@inheritdoc}
     */
    public function addWord(string $word, int $id, int $type, int $position): void
    {
        if ($word === '') {
            return;
        }

        if (isset($this->fulltextIndex[$word][$id])) {
            $positionStr = base_convert($position, 10, 36);
            if ($type === self::TYPE_KEYWORD) {
                $positionStr = self::PREFIX_KEYWORD . $positionStr;
            } elseif ($type === self::TYPE_TITLE) {
                $positionStr = self::PREFIX_TITLE . $positionStr;
            }

            $value = $this->fulltextIndex[$word][$id];
            if (\is_int($value)) {
                // There was the only one content position, but it's no longer the case.
                // Convert to the 36-based number system.
                $this->fulltextIndex[$word][$id] = base_convert($value, 10, 36) . '|' . $positionStr;
            } else {
                // Appending
                $this->fulltextIndex[$word][$id] = $value . '|' . $positionStr;
            }
        } else {
            // If there is the only one content position in index, the position is stored as decimal number
            if ($type === self::TYPE_KEYWORD) {
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                $position = self::PREFIX_KEYWORD . base_convert($position, 10, 36);
            } elseif ($type === self::TYPE_TITLE) {
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                $position = self::PREFIX_TITLE . base_convert($position, 10, 36);
            }
            $this->fulltextIndex[$word][$id] = $position;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeWord(string $word): void
    {
        unset($this->fulltextIndex[$word]);
    }

    /**
     * {@inheritdoc}
     */
    public function getFrequentWords(int $threshold): array
    {
        $result = [];
        $link   = &$this->fulltextIndex; // for memory optimization
        foreach ($this->fulltextIndex as $word => $stat) {
            // Drop fulltext frequent or empty items
            $num = \count($stat);
            if ($num > $threshold) {
                $result[$word] = $num;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function removeById(int $id): void
    {
        foreach ($this->fulltextIndex as &$data) {
            if (isset($data[$id])) {
                unset($data[$id]);
            }
        }
        unset($data);
    }
}
