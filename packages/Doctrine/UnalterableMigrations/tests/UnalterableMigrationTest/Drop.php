<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\UnalterableMigrations\Test\UnalterableMigrationTest;

use PetrKnap\Doctrine\UnalterableMigrations\UnalterableMigrationInterface;
use PetrKnap\Doctrine\UnalterableMigrations\UnalterableMigrationTrait;

class Drop extends MigrationStub implements UnalterableMigrationInterface
{
    use UnalterableMigrationTrait;

    public function getParentClassName(): ?string
    {
        return Rename::class;
    }

    public function getUpSql(): string
    {
        return self::DROP_PARENT;
    }
}
