<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\UnalterableMigrations\Test\UnalterableMigrationTest;

use PetrKnap\Doctrine\UnalterableMigrations\UnalterableMigrationInterface;
use PetrKnap\Doctrine\UnalterableMigrations\UnalterableMigrationTrait;

class Remove extends MigrationStub implements UnalterableMigrationInterface
{
    use UnalterableMigrationTrait;

    public function getParentClassName(): ?string
    {
        return Alter::class;
    }

    public function getUpSql(): string
    {
        return self::REMOVE_PARENT;
    }
}
