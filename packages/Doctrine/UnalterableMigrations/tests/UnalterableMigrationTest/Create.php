<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\UnalterableMigrations\Test\UnalterableMigrationTest;

use PetrKnap\Doctrine\UnalterableMigrations\UnalterableMigrationInterface;
use PetrKnap\Doctrine\UnalterableMigrations\UnalterableMigrationTrait;

class Create extends MigrationStub implements UnalterableMigrationInterface
{
    use UnalterableMigrationTrait;

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
