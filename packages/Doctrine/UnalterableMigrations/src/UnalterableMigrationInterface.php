<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\UnalterableMigrations;

interface UnalterableMigrationInterface
{
    const REMOVE_PARENT = '-- remove parent';

    public function getParentClassName(): ?string;

    public function getParent(): ?UnalterableMigrationInterface;

    public function getUpSql(): string;

    public function getDownSql(): ?string;
}
