<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\UnalterableMigrations;

use Doctrine\DBAL\Schema\Schema;

trait UnalterableMigrationTrait
{
    public function up(Schema $schema): void
    {
        $this->doDown($this->getParent());

        if ($this->getUpSql() !== UnalterableMigrationInterface::DROP_PARENT) {
            $this->addSql($this->getUpSql());
        }
    }

    private function doDown(?UnalterableMigrationInterface $migration): void
    {
        while ($migration) {
            if ($migration->getDownSql()) {
                $this->addSql($migration->getDownSql());
                break;
            } else {
                $this->abortIf(
                    $this->getParent() === null,
                    'Can not generate up and down method without down SQL or parent'
                );
            }
            $migration = $migration->getParent();
        }
    }

    public function getParent(): ?UnalterableMigrationInterface
    {
        $parentClassName = $this->getParentClassName();

        if ($parentClassName) {
            return new $parentClassName(clone $this->version);
        }

        return null;
    }

    public function getParentClassName(): ?string
    {
        return null;
    }

    public function down(Schema $schema): void
    {
        if ($this->getUpSql() !== UnalterableMigrationInterface::DROP_PARENT) {
            $this->doDown($this);
        }

        if ($this->getParent()) {
            $this->addSql($this->getParent()->getUpSql());
        }
    }

    public function getDownSql(): ?string
    {
        return null;
    }
}
