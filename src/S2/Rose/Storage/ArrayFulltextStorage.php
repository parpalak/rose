<?php
/**
 * @copyright 2016-2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Storage;

class ArrayFulltextStorage implements FulltextProxyInterface
{
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
                $result[$id][] = $entries;
            } else {
                $entries = explode('|', $entries);
                foreach ($entries as $position) {
                    $result[$id][] = base_convert($position, 36, 10);
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
    public function addWord(string $word, int $id, int $position): void
    {
        $word = (string)$word;
        if ($word === '') {
            return;
        }

        if (isset($this->fulltextIndex[$word][$id])) {
            $value = $this->fulltextIndex[$word][$id];
            if (\is_int($value)) {
                // There was the only one position, but it's no longer the case.
                // Convert to the 36-based number system.
                $this->fulltextIndex[$word][$id] = base_convert($value, 10, 36) . '|' . base_convert($position, 10, 36);
            } else {
                // Appending
                $this->fulltextIndex[$word][$id] = $value . '|' . base_convert($position, 10, 36);
            }
        } else {
            // If there is the only one position in index, the position is stored as decimal number
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
