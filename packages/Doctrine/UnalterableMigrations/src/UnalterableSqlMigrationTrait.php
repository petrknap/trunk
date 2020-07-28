<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\UnalterableMigrations;

use Doctrine\DBAL\Schema\Schema;

trait UnalterableSqlMigrationTrait
{
    public function getParentClassName(): ?string
    {
        return null;
    }

    public function getParent(): ?UnalterableSqlMigrationInterface
    {
        $parentClassName = $this->getParentClassName();

        if ($parentClassName) {
            return new $parentClassName(clone $this->version);
        }

        return null;
    }

    public function getDownSql(): ?string
    {
        return null;
    }

    public function up(Schema $schema): void
    {
        $this->parentsDown();

        if ($this->getUpSql() !== UnalterableSqlMigrationInterface::REMOVE_PARENT) {
            $this->addSql($this->getUpSql());
        }
    }

    public function down(Schema $schema): void
    {
        if ($this->getDownSql()) {
            $this->addSql($this->getDownSql());
        } else {
            $this->abortIf($this->getParent() === null, 'Can not generate down method without down SQL');
        }

        if ($this->getUpSql() !== UnalterableSqlMigrationInterface::REMOVE_PARENT) {
            $this->parentsDown();
        }

        if ($this->getParent()) {
            $this->addSql($this->getParent()->getUpSql());
        }
    }

    private function parentsDown(): void
    {
        $parent = $this->getParent();
        while ($parent) {
            if ($parent->getDownSql()) {
                $this->addSql($parent->getDownSql());
            }
            $parent = $parent->getParent();
        }
    }
}
