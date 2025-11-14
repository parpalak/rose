<?php
/**
 * @copyright 2025 Roman Parpalak
 * @license   https://opensource.org/license/mit MIT
 */

declare(strict_types=1);

namespace S2\Rose\Test\Helper;

use Codeception\Test\Unit;
use S2\Rose\Entity\Metadata\SnippetSource;
use S2\Rose\Helper\SnippetFormatter;

class SnippetFormatterTest extends Unit
{
    public function testEscapesPlainTextSnippets(): void
    {
        $result = SnippetFormatter::toOutput('<script>alert(1)</script>', SnippetSource::FORMAT_PLAIN_TEXT, true);

        $this->assertSame('&lt;script&gt;alert(1)&lt;/script&gt;', $result);
    }

    public function testKeepsInternalFormattingTags(): void
    {
        $result = SnippetFormatter::toOutput('\\iDanger\\I text', SnippetSource::FORMAT_INTERNAL, true);

        $this->assertSame('<i>Danger</i> text', $result);
    }
}
