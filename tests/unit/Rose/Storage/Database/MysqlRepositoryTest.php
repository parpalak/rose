<?php
/**
 * @copyright 2025 Roman Parpalak
 * @license   https://opensource.org/license/mit MIT
 */

declare(strict_types=1);

namespace S2\Rose\Test\Storage\Database;

use Codeception\Test\Unit;
use S2\Rose\Storage\Database\MysqlRepository;

class MysqlRepositoryTest extends Unit
{
    public function testInsertWordsUsesPreparedStatements(): void
    {
        $pdo  = new class extends \PDO {
            public string $capturedSql = '';
            public array $executedParams = [];

            public function __construct()
            {
            }

            public function prepare($statement, $options = null)
            {
                $this->capturedSql = $statement;

                return new class($this) extends \PDOStatement {
                    private $pdo;

                    public function __construct($pdo)
                    {
                        $this->pdo = $pdo;
                    }

                    public function execute($params = null): bool
                    {
                        $this->pdo->executedParams[] = $params ?? [];

                        return true;
                    }
                };
            }
        };

        $repository = new MysqlRepository($pdo, 'prefix_', []);
        $repository->insertWords(['test"', "danger\\word"]);

        $this->assertStringNotContainsString('test"', $pdo->capturedSql);
        $this->assertSame([['test"', "danger\\word"]], $pdo->executedParams);
    }
}
