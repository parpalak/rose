<?php
/**
 * @copyright 2025 Roman Parpalak
 * @license   https://opensource.org/license/mit MIT
 */

namespace S2\Rose\Test\Storage\Database;

use Codeception\Test\Unit;
use S2\Rose\Exception\InvalidArgumentException;
use S2\Rose\Storage\Database\MysqlRepository;

class RepositoryValidationTest extends Unit
{
    public function testRejectsInvalidPrefix(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MysqlRepository(new class extends \PDO {
            public function __construct() {}
        }, 'bad;DROP', []);
    }

    public function testRejectsInvalidTableOverride(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new MysqlRepository(new class extends \PDO {
            public function __construct() {}
        }, 'ok_prefix', ['toc' => 'toc;DROP']);
    }
}
