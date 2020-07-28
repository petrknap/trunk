<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\UnalterableMigrations\Test\UnalterableSqlMigrationTest;

use PetrKnap\Doctrine\UnalterableMigrations\UnalterableSqlMigrationInterface;
use PetrKnap\Doctrine\UnalterableMigrations\UnalterableSqlMigrationTrait;

class Remove extends MigrationStub implements UnalterableSqlMigrationInterface
{
    use UnalterableSqlMigrationTrait;

    public function getParentClassName(): ?string
    {
        return Alter::class;
    }

    public function getUpSql(): string
    {
        return self::REMOVE_PARENT;
    }
}
