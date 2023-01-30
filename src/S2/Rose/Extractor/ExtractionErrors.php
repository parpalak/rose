<?php declare(strict_types=1);
/**
 * @copyright 2023 Roman Parpalak
 * @license   MIT
 */

namespace S2\Rose\Extractor;

class ExtractionErrors
{
    /**
     * @var array[]
     */
    private array $errors = [];

    public function addError(string $message, string $code, int $line, ?int $column = null): self
    {
        $this->errors[] = [
            'message' => $message,
            'code'    => $code,
            'line'    => $line,
            'column'  => $column
        ];

        return $this;
    }

    /** @noinspection PhpComposerExtensionStubsInspection */
    public function addLibXmlError(\LibXMLError $error): self
    {
        return $this->addError(trim($error->message), (string)$error->code, $error->line, $error->column);
    }

    public function hasErrors(): bool
    {
        return \count($this->errors) > 0;
    }

    /**
     * @return string[]
     */
    public function getFormattedLines(): array
    {
        return array_map(static fn(array $error) => sprintf(
            "%s:%s %s (code=%s)",
            $error['line'],
            $error['column'] ?? '?',
            $error['message'],
            $error['code']
        ), $this->errors);
    }
}
