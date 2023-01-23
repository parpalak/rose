<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Extractor\HtmlDom;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use S2\Rose\Entity\Metadata\SentenceMap;
use S2\Rose\Extractor\ExtractionErrors;
use S2\Rose\Extractor\ExtractionResult;
use S2\Rose\Extractor\ExtractorInterface;

class DomExtractor implements ExtractorInterface
{
    private const NODE_SKIP   = 'node_skip';
    private const NODE_BLOCK  = 'node_block';
    private const NODE_INLINE = 'node_inline';

    use LoggerAwareTrait;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public static function available(): bool
    {
        return class_exists(\DOMDocument::class);
    }

    /**
     * {@inheritdoc}
     * @noinspection PhpComposerExtensionStubsInspection
     */
    public function extract(string $text): ExtractionResult
    {
        $internalErrorsOptionValue = !\function_exists('libxml_use_internal_errors') || libxml_use_internal_errors();
        if ($internalErrorsOptionValue === false) {
            libxml_use_internal_errors(true);
        }

        $dom      = static::getDomDocument($text);
        $domState = new DomState();

        static::walkDomNode($dom->getElementsByTagName('body')[0], $domState, 0);

        $contentWithMetadata = $domState->toContentWithMetadata();

        $extractorErrors = new ExtractionErrors();

        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            /**
             * Skip errors like "Tag svg invalid".
             * There are a lot of tags in SVG and HTML5 that fire this warning.
             */
            if ($error->code === 801) {
                continue;
            }

            switch ($error->level) {
                case LIBXML_ERR_FATAL:
                    if ($this->logger) {
                        $this->logger->error('Error in html', (array)$error);
                    }
                case LIBXML_ERR_WARNING:
                case LIBXML_ERR_ERROR:
                    $extractorErrors->addLibXmlError($error);
            }
        }

        if ($internalErrorsOptionValue === false) {
            libxml_use_internal_errors(false);
        }

        return new ExtractionResult($contentWithMetadata, $extractorErrors);
    }

    protected static function walkDomNode(\DOMNode $domNode, DomState $domState, int $level): void
    {
        if ($domNode instanceof \DOMText) {
            $domState->attachContent($domNode->getNodePath(), $domNode->textContent);

            return;
        }

        $newBlock = false;

        if ($domNode instanceof \DOMElement) {
            $nodeType = static::processDomElement($domNode, $domState);
            if ($nodeType === self::NODE_SKIP) {
                return;
            }
            if ($nodeType === self::NODE_BLOCK) {
                $newBlock = true;
            }
        }

        if ($newBlock) {
            $domState->startNewParagraph();
        }

        foreach ($domNode->childNodes as $childNode) {
            static::walkDomNode($childNode, $domState, $level + 1);
        }

        if ($newBlock) {
            $domState->startNewParagraph();
        }
    }

    protected static function getDomDocument(string $text): \DOMDocument
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        if (strpos($text, '</html>') === false && strpos($text, '</body>') === false) {
            /** @noinspection HtmlRequiredLangAttribute */
            /** @noinspection HtmlRequiredTitleElement */
            $text = sprintf('<!DOCTYPE html><html><head><meta charset="UTF-8"><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>%s</body></html>', $text);
        }

        // When using DOM API as is, some custom entities (especially from HTML5) remain encoded.
        // E.g. '&#43; &plus;' becomes '+ &plus;'.
        // One cannot just re-decode entities with ENT_HTML5 because in this case '&amp;plus;' also becomes '+'.
        // Seems like substituteEntities does not work.
        // $dom->substituteEntities = true;
        // Trying a workaround.
        $text = str_replace(['&', SentenceMap::LINE_SEPARATOR], ['&amp;', ''], $text);
        $dom->loadHTML($text);

        return $dom;
    }

    protected static function processDomElement(\DOMNode $domNode, DomState $domState): string
    {
        if (strpos(' ' . $domNode->getAttribute('class') . ' ', ' index-skip ') !== false) {
            return self::NODE_SKIP;
        }

        switch ($domNode->nodeName) {
            case 'p':
            case 'div':
            case 'pre':
            case 'li':
            case 'ul':
            case 'ol':
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
            case 'table':
            case 'td': // Does a new cell should be treated at least as a separate sentence? If no, remove this line
            case 'blockquote':
            case 'dd':
            case 'dl':
            case 'dt':
            case 'menu':
            case 'article':
            case 'aside':
            case 'details':
            case 'figure':
            case 'figcaption':
            case 'footer':
            case 'header':
            case 'main':
            case 'nav':
            case 'section':
                return self::NODE_BLOCK;

            case 'br':
                $domState->attachContent($domNode->getNodePath(), SentenceMap::LINE_SEPARATOR);
                return self::NODE_SKIP;

            case 'hr':
            case 'iframe':
                // Force space
                $domState->attachContent($domNode->getNodePath(), ' ');
                return self::NODE_SKIP;

            case 'svg':
                $domState->attachImg(
                    '', // TODO How to handle SVG? Save as data uri?
                    $domNode->getAttribute('width'),
                    $domNode->getAttribute('height'),
                    ''
                );

                $domState->attachContent($domNode->getNodePath(), ' ');
                return self::NODE_SKIP;

            case 'img':
                $domState->attachImg(
                    $domNode->getAttribute('src'),
                    $domNode->getAttribute('width'),
                    $domNode->getAttribute('height'),
                    $domNode->getAttribute('alt')
                );
                // TODO Add alt text?
                $domState->attachContent($domNode->getNodePath(), ' ');
                return self::NODE_SKIP;

            case 'style':
            case 'script':
                return self::NODE_SKIP;
        }

        return self::NODE_INLINE;
    }
}
