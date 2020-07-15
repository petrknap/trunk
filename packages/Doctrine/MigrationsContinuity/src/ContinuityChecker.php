<?php declare(strict_types=1);

namespace PetrKnap\Doctrine\MigrationsContinuity;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Event\MigrationsEventArgs;
use Doctrine\Migrations\Events;
use Doctrine\Migrations\Exception\AbortMigration;
use Doctrine\Migrations\Version\Version;

class ContinuityChecker implements EventSubscriber
{
    private $checked;

    public static function init(Connection $connection): void
    {
        $connection->getEventManager()->addEventSubscriber(new self);
    }

    public function getSubscribedEvents() : array
    {
        return [
            Events::onMigrationsMigrating,
        ];
    }

    public function onMigrationsMigrating(MigrationsEventArgs $args): void
    {
        if (!$this->checked) {
            $this->check($args->getConfiguration()->getMigrations());
            $this->checked = true;
        }
    }

    /**
     * @internal public for test purpose only
     * @param Version[]
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
