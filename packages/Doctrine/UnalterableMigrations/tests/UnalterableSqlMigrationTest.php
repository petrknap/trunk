<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\UnalterableMigrations\Test;

use Doctrine\DBAL\Schema\Schema;
use PetrKnap\Doctrine\UnalterableMigrations\Test\UnalterableSqlMigrationTest\Alter;
use PetrKnap\Doctrine\UnalterableMigrations\Test\UnalterableSqlMigrationTest\Create;
use PetrKnap\Doctrine\UnalterableMigrations\Test\UnalterableSqlMigrationTest\MigrationStub;
use PetrKnap\Doctrine\UnalterableMigrations\Test\UnalterableSqlMigrationTest\Remove;
use PHPUnit\Framework\TestCase;

class UnalterableSqlMigrationTest extends TestCase
{
    public function setUp(): void
    {
        MigrationStub::$sqls = [];
    }

    /**
     * @dataProvider dataUpWorks
     * @noinspection PhpDocSignatureInspection
     */
    public function testUpWorks(string $migrationClass, array $expectedUpSqls): void
    {
        /** @var MigrationStub $migration */
        $migration = new $migrationClass;
        $migration->up($this->getMockBuilder(Schema::class)->getMock());

        $this->assertSame($expectedUpSqls, MigrationStub::$sqls);
    }

    public function dataUpWorks(): array
    {
        $create = new Create();
        $alter = new Alter();

        return [
            [Create::class, [$create->getUpSql()]],
            [Alter::class, [$create->getDownSql(), $alter->getUpSql()]],
            [Remove::class, [$create->getDownSql()]],
        ];
    }

    /**
     * @dataProvider dataDownWorks
     * @noinspection PhpDocSignatureInspection
     */
    public function testDownWorks(string $migrationClass, array $expectedDownSqls): void
    {
        /** @var MigrationStub $migration */
        $migration = new $migrationClass;
        $migration->down($this->getMockBuilder(Schema::class)->getMock());

        $this->assertSame($expectedDownSqls, MigrationStub::$sqls);
    }

    public function dataDownWorks(): array
    {
        $create = new Create();
        $alter = new Alter();

        return [
            [Create::class, [$create->getDownSql()]],
            [Alter::class, [$create->getDownSql(), $create->getUpSql()]],
            [Remove::class, [$alter->getUpSql()]],
        ];
    }
}
