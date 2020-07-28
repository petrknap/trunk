<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\UnalterableMigrations\Test\UnalterableMigrationTest;

use PetrKnap\Doctrine\UnalterableMigrations\Patches;
use PetrKnap\Doctrine\UnalterableMigrations\UnalterableMigrationInterface;
use PetrKnap\Doctrine\UnalterableMigrations\UnalterableMigrationTrait;

class Alter extends MigrationStub implements UnalterableMigrationInterface
{
    use UnalterableMigrationTrait;

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
