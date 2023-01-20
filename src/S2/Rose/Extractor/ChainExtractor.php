<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Extractor;

use Psr\Log\LoggerAwareTrait;
use S2\Rose\Exception\LogicException;
use S2\Rose\Exception\RuntimeException;

class ChainExtractor implements ExtractorInterface
{
    use LoggerAwareTrait;

    /**
     * @var ExtractorInterface[]
     */
    private array $extractors = [];

    public function attachExtractor(ExtractorInterface $extractor): void
    {
        $this->extractors[] = $extractor;
    }

    /**
     * {@inheritdoc}
     * @throws RuntimeException
     */
    public function extract(string $text): ExtractionResult
    {
        if (\count($this->extractors) === 0) {
            throw new LogicException('No extractors were attached to the ChainExtractor.');
        }

        $e = null;
        foreach ($this->extractors as $extractor) {
            try {
                return $extractor->extract($text);
            } catch (\Exception $e) {
                if ($this->logger) {
                    $this->logger->error($e->getMessage(), ['exception' => $e]);
                }
            }
        }

        throw new RuntimeException($e->getMessage(), 0, $e);
    }
}
