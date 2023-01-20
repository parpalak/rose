<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity\Metadata;

use S2\Rose\Helper\StringHelper;

class SentenceCollection
{
    /**
     * @var string[]
     */
    private array $sentences = [];
    private ?array $cachedWords = null;
    private ?array $cachedSnippetSources = null;

    /**
     * @param string $text Raw text content of a sentence. No formatting is supported now. TODO add simple formatting?
     * @return void
     */
    public function attach(string $text): void
    {
        $this->cachedWords = null;
        $this->sentences[] = preg_replace('#\\s+#', ' ', $text);
    }

    public function getText(): string
    {
        return implode(' ', $this->sentences);
    }

    /**
     * @internal Used for tests only!
     */
    public function toArray(): array
    {
        return $this->sentences;
    }

    /**
     * @return string[]
     */
    public function getWordsArray(): array
    {
        if ($this->cachedWords === null) {
            $this->buildWordsInfo();
        }

        return $this->cachedWords;
    }

    /**
     * @return SnippetSource[]
     */
    public function getSnippetSources(): array
    {
        if ($this->cachedSnippetSources === null) {
            $this->buildWordsInfo();
        }

        return $this->cachedSnippetSources;
    }

    private function buildWordsInfo(): void
    {
        $this->cachedWords          = [];
        $this->cachedSnippetSources = [];
        $oldSize                    = 0;
        foreach ($this->sentences as $idx => $sentence) {
            // NOTE: maybe it's worth to join sentences somehow before exploding for optimization reasons
            $contentWords        = self::breakIntoWords($sentence);
            $this->cachedWords[] = $contentWords;
            $wordsInSentence     = \count($contentWords);
            if ($wordsInSentence === 0) {
                continue;
            }
            $newSize = $wordsInSentence + $oldSize;

            if ($wordsInSentence >= 3) { // Skip too short snippets
                // TODO transfer formatting data
                $this->cachedSnippetSources[$idx] = new SnippetSource($sentence, $oldSize, $newSize - 1);
            }

            $oldSize = $newSize;
        }
        $this->cachedWords = array_merge(...$this->cachedWords);
    }

    /**
     * @return string[]
     */
    private static function breakIntoWords(string $content): array
    {
        // We allow letters, digits and some punctuation: ".,-"
        $content = preg_replace('#[^\\-.,0-9\\p{L}^_]+#u', ' ', $content);
        $content = mb_strtolower($content);
        $content = str_replace([", ", ". ", "- ", 'ё'], [' ', ' ', ' ', 'е'], $content);

        // These punctuation characters are meant to be inside words and numbers.
        // Remove trailing characters when splitting the words.
        $content = rtrim($content, '-.,');

        $words = explode(' ', $content);
        StringHelper::removeLongWords($words);

        return $words;
    }
}
