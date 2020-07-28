<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\UnalterableMigrations\Test\UnalterableSqlMigrationTest;

use PetrKnap\Doctrine\UnalterableMigrations\UnalterableSqlMigrationInterface;
use PetrKnap\Doctrine\UnalterableMigrations\UnalterableSqlMigrationTrait;

class Create extends MigrationStub implements UnalterableSqlMigrationInterface
{
    use UnalterableSqlMigrationTrait;

    public function getUpSql(): string
    {
        return '
CREATE VIEW view_b AS (
SELECT
    a.id
FROM table_a a
)';
    }

    public function getDownSql(): ?string
    {
        return 'DROP VIEW view_b';
    }
}
