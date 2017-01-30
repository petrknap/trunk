<?php

namespace PetrKnap\Php\MigrationTool\Test\AbstractMigrationToolTest;

use PetrKnap\Php\MigrationTool\AbstractMigrationTool;

class AbstractMigrationToolMock extends AbstractMigrationTool
{
    const MIGRATION_FILE_PATTERN = '/\.ext/i';

    /**
     * @var array
     */
    private $appliedMigrations;

    public function __construct(array $appliedMigrations)
    {
        $this->appliedMigrations = $appliedMigrations;
    }

    /**
     * @inheritdoc
     */
    protected function isMigrationApplied($pathToMigrationFile)
    {
        return in_array($this->getMigrationId($pathToMigrationFile), $this->appliedMigrations);
    }

    /**
     * @inheritdoc
     */
    protected function applyMigrationFile($pathToMigrationFile)
    {
        $this->appliedMigrations[] = $this->getMigrationId($pathToMigrationFile);
    }

    /**
     * @inheritdoc
     */
    protected function getPathToDirectoryWithMigrationFiles()
    {
        return __DIR__ . "/migrations";
    }
}
