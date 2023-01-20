<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Entity\Metadata;

use S2\Rose\Helper\StringHelper;

class SentenceMap
{
    /**
     * [
     *    1 => [
     *       '/html/body/p[1]/text()' => 'One sentence.',
     *    ],
     *    2 => [
     *       '/html/body/p[2]/text()[1]' => 'Second',
     *       '/html/body/p[2]/br'        => ' ',
     *       '/html/body/p[2]/text()[2]' => 'sentence. And a third one.',
     *    ],
     * ]
     *
     * @var array[][]
     */
    private array $paragraphs = [];

    /**
     * @param int    $paragraphIndex Number of current paragraph. Must be detected outside based on formatting.
     * @param string $path Some identifier of a content node. Must be unique for the paragraph given.
     * @param string $textContent Raw text content of a node. No formatting is supported now. TODO add simple formatting?
     */
    public function add(int $paragraphIndex, string $path, string $textContent): self
    {
        if (isset($this->paragraphs[$paragraphIndex][$path])) {
            throw new \LogicException(sprintf('Map already has a content for paragraph "%s" and path "%s".', $paragraphIndex, $path));
        }
        $this->paragraphs[$paragraphIndex][$path] = $textContent;

        return $this;
    }

    public function toSentenceCollection(): SentenceCollection
    {
        $sentenceCollection = new SentenceCollection();

        foreach ($this->paragraphs as $paragraph) {
            $text      = trim(implode('', $paragraph));
            $sentences = StringHelper::sentencesFromText($text);

            foreach ($sentences as $sentence) {
                if ($sentence === '') {
                    continue;
                }
                // TODO transfer information about formatting?
                $sentenceCollection->attach($sentence);
            }
        }

        return $sentenceCollection;
    }
}
