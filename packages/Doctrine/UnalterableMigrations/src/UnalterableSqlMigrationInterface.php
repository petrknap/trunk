<?php declare(strict_types=1);

namespace App;

namespace PetrKnap\Doctrine\UnalterableMigrations;

interface UnalterableSqlMigrationInterface
{
    const REMOVE_PARENT = '-- remove parent';

    public function getParentClassName(): ?string;

    public function getParent(): ?UnalterableSqlMigrationInterface;

    public function getUpSql(): string;

    public function getDownSql(): ?string;
}
