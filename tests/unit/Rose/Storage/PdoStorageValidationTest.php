<?php
/**
 * @copyright 2025 Roman Parpalak
 * @license   https://opensource.org/license/mit MIT
 */

declare(strict_types=1);

namespace S2\Rose\Test\Storage;

use Codeception\Test\Unit;
use S2\Rose\Exception\InvalidArgumentException;
use S2\Rose\Storage\Database\PdoStorage;

class PdoStorageValidationTest extends Unit
{
    public function testRejectsInvalidPrefix(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PdoStorage(new class extends \PDO {
            public function __construct() {}
        }, 'bad;DROP', []);
    }

    public function testRejectsInvalidTableOverride(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PdoStorage(new class extends \PDO {
            public function __construct() {}
        }, 'ok_prefix_', ['toc' => 'toc;DROP']);
    }
}
