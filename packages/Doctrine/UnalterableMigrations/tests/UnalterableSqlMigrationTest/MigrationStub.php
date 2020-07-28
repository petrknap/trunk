<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\UnalterableMigrations\Test\UnalterableSqlMigrationTest;

use Doctrine\DBAL\Schema\Schema;

abstract class MigrationStub
{
    public static $sqls = [];

    public function __construct()
    {
        $this->version = new \stdClass();
    }

    abstract public function up(Schema $schema): void;

    abstract public function down(Schema $schema): void;

    protected function addSql(string $sql): void
    {
        self::$sqls[] = $sql;
    }

    protected function abortIf(bool $condition)
    {
        if ($condition) {
            throw new \Exception();
        }
    }
}
