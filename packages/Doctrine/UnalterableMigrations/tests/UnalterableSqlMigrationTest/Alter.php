<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\UnalterableMigrations\Test\UnalterableSqlMigrationTest;

use PetrKnap\Doctrine\UnalterableMigrations\Patches;
use PetrKnap\Doctrine\UnalterableMigrations\UnalterableSqlMigrationInterface;
use PetrKnap\Doctrine\UnalterableMigrations\UnalterableSqlMigrationTrait;

class Alter extends MigrationStub implements UnalterableSqlMigrationInterface
{
    use UnalterableSqlMigrationTrait;

    public function getParentClassName(): ?string
    {
        return Create::class;
    }

    public function getUpSql(): string
    {
        return Patches::on($this->getParent()->getUpSql())
            ->removeLine(3, 'a.id')
            ->insertLine(3, 'a.id,')
            ->insertLine(4, 'a.name')
            ->apply();
    }
}
