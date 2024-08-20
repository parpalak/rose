<?php
/**
 * @copyright 2023-2024 Roman Parpalak
 * @license   MIT
 */

declare(strict_types=1);

namespace S2\Rose\Extractor\HtmlDom;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use S2\Rose\Entity\Metadata\SentenceMap;
use S2\Rose\Extractor\ExtractionErrors;
use S2\Rose\Extractor\ExtractionResult;
use S2\Rose\Extractor\ExtractorInterface;
use S2\Rose\Helper\StringHelper;

class DomExtractor implements ExtractorInterface
{
    private const NODE_SKIP         = 'node_skip';
    private const NODE_BLOCK        = 'node_block';
    private const NODE_BOLD         = 'node_bold';
    private const NODE_ITALIC       = 'node_italic';
    private const NODE_SUPERSCRIPT  = 'node_superscript';
    private const NODE_SUBSCRIPT    = 'node_subscript';
    private const NODE_OTHER_INLINE = 'node_inline';

    use LoggerAwareTrait;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public static function available(): bool
    {
        return class_exists(\DOMDocument::class);
    }

    public function processTextNode(\DOMText $domNode, DomState $domState, ExtractionErrors $extractionErrors, int $level): void
    {
        $textContent = $domNode->textContent;

        $this->checkContentForWarnings($level, $textContent, $extractionErrors, $domNode);

        $domState->attachContent($domNode->getNodePath(), $textContent);
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

        $dom              = static::getDomDocument($text);
        $domState         = new DomState();
        $extractionErrors = new ExtractionErrors();

        $this->walkDomNode($dom->getElementsByTagName('body')[0], $domState, $extractionErrors, 0);

        $contentWithMetadata = $domState->toContentWithMetadata();

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
                    $extractionErrors->addLibXmlError($error);
            }
        }

        if ($internalErrorsOptionValue === false) {
            libxml_use_internal_errors(false);
        }

        return new ExtractionResult($contentWithMetadata, $extractionErrors);
    }

    protected function walkDomNode(\DOMNode $domNode, DomState $domState, ExtractionErrors $extractionErrors, int $level): void
    {
        if ($domNode instanceof \DOMText) {
            $this->processTextNode($domNode, $domState, $extractionErrors, $level);

            return;
        }

        if ($domNode instanceof \DOMElement) {
            $nodeType = static::processDomElement($domNode, $domState);
            switch ($nodeType) {
                case self::NODE_SKIP:
                    return;
                case self::NODE_BLOCK:
                    $domState->startNewParagraph();
                    break;
                case self::NODE_ITALIC:
                    $domState->startFormatting(StringHelper::ITALIC);
                    break;
                case self::NODE_BOLD:
                    $domState->startFormatting(StringHelper::BOLD);
                    break;
                case self::NODE_SUPERSCRIPT:
                    $domState->startFormatting(StringHelper::SUPERSCRIPT);
                    break;
                case self::NODE_SUBSCRIPT:
                    $domState->startFormatting(StringHelper::SUBSCRIPT);
                    break;
            }
        }

        foreach ($domNode->childNodes as $childNode) {
            static::walkDomNode($childNode, $domState, $extractionErrors, $level + 1);
        }

        if ($domNode instanceof \DOMElement) {
            switch ($nodeType) {
                case self::NODE_BLOCK:
                    $domState->startNewParagraph();
                    break;
                case self::NODE_ITALIC:
                    $domState->stopFormatting(StringHelper::ITALIC);
                    break;
                case self::NODE_BOLD:
                    $domState->stopFormatting(StringHelper::BOLD);
                    break;
                case self::NODE_SUPERSCRIPT:
                    $domState->stopFormatting(StringHelper::SUPERSCRIPT);
                    break;
                case self::NODE_SUBSCRIPT:
                    $domState->stopFormatting(StringHelper::SUBSCRIPT);
                    break;
            }
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
                    html_entity_decode($domNode->getAttribute('src'), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5),
                    $domNode->getAttribute('width'),
                    $domNode->getAttribute('height'),
                    html_entity_decode($domNode->getAttribute('alt'), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5)
                );
                // TODO Add alt text?
                $domState->attachContent($domNode->getNodePath(), ' ');
                return self::NODE_SKIP;

            case 'style':
            case 'script':
                return self::NODE_SKIP;

            case 'b':
            case 'strong':
                return self::NODE_BOLD;

            case 'i':
            case 'em':
                return self::NODE_ITALIC;

            case 'sup':
                return self::NODE_SUPERSCRIPT;

            case 'sub':
                return self::NODE_SUBSCRIPT;
        }

        return self::NODE_OTHER_INLINE;
    }

    protected function checkContentForWarnings(int $level, string $textContent, ExtractionErrors $extractionErrors, \DOMText $domNode): void
    {
        if ($level <= 1 && trim($textContent) !== '') {
            try {
                $extractionErrors->addError(
                    sprintf(
                        'Found anonymous text block %s. Consider using <p> tag as a text container.',
                        json_encode(
                            mb_strlen($textContent) > 33 ? mb_substr($textContent, 0, 30) . '...' : $textContent,
                            JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                        )
                    ),
                    'anon_text',
                    $domNode->getLineNo()
                );
            } catch (\JsonException $e) {
                throw new \LogicException('Impossible exception occurred.');
            }
        }
    }
}
