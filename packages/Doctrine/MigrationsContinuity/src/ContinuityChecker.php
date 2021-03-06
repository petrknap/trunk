<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\MigrationsContinuity;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Event\MigrationsEventArgs;
use Doctrine\Migrations\Events;
use Doctrine\Migrations\Exception\AbortMigration;
use Doctrine\Migrations\Metadata\MigrationPlan;
use Doctrine\Migrations\Version\Version;

class ContinuityChecker implements EventSubscriber
{
    private $checked;

    public static function init(Connection $connection): void
    {
        $connection->getEventManager()->addEventSubscriber(new self);
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::onMigrationsMigrating,
        ];
    }

    public function onMigrationsMigrating(MigrationsEventArgs $args): void
    {
        if (!$this->checked) {
            $this->check(array_map(function (MigrationPlan $migrationPlan): Version {
                return $migrationPlan->getVersion();
            }, $args->getPlan()->getItems()));
            $this->checked = true;
        }
    }

    /**
     * @param Version[]
     * @internal public for test purpose only
     */
    public function check(array $migrations): void
    {
        $previousMigration = array_shift($migrations);
        foreach ($migrations as $migration) {
            if (!$previousMigration->isMigrated() && $migration->isMigrated()) {
                throw new AbortMigration("Detected gape before {$migration->getVersion()}");
            }
            $previousMigration = $migration;
        }
    }
}
