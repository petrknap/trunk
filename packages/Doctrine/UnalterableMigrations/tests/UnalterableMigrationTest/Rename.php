<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\UnalterableMigrations\Test\UnalterableMigrationTest;

use PetrKnap\Doctrine\UnalterableMigrations\Patches;
use PetrKnap\Doctrine\UnalterableMigrations\UnalterableMigrationInterface;
use PetrKnap\Doctrine\UnalterableMigrations\UnalterableMigrationTrait;

class Rename extends MigrationStub implements UnalterableMigrationInterface
{
    use UnalterableMigrationTrait;

    public function getParentClassName(): ?string
    {
        return Alter::class;
    }

    public function getUpSql(): string
    {
        return Patches::on($this->getParent()->getUpSql())
            ->removeLine(1, 'CREATE VIEW view_b AS (')
            ->insertLine(1, 'CREATE VIEW view_c AS (')
            ->apply();
    }

    public function getDownSql(): ?string
    {
        return 'DROP VIEW view_c';
    }
}
