<?php

namespace PetrKnap\Php\MigrationTool\Test\SqlMigrationToolTest;

use PetrKnap\Php\MigrationTool\SqlMigrationTool;

class SqlMigrationToolMock extends SqlMigrationTool
{
    private $phpDataObject;
    private $nameOfMigrationTable;
    private $pathToDirectoryWithMigrationFiles;

    public function __construct(\PDO $phpDataObject, $nameOfMigrationTable, $pathToDirectoryWithMigrationFiles = null)
    {
        if ($pathToDirectoryWithMigrationFiles === null) {
            $pathToDirectoryWithMigrationFiles = __DIR__ . "/migrations";
        }

        $this->phpDataObject = $phpDataObject;
        $this->nameOfMigrationTable = $nameOfMigrationTable;
        $this->pathToDirectoryWithMigrationFiles = $pathToDirectoryWithMigrationFiles;
    }

    /**
     * @inheritdoc
     */
    protected function getPhpDataObject()
    {
        return $this->phpDataObject;
    }

    /**
     * @inheritdoc
     */
    protected function getNameOfMigrationTable()
    {
        return $this->nameOfMigrationTable;
    }

    /**
     * @inheritdoc
     */
    protected function getPathToDirectoryWithMigrationFiles()
    {
        return $this->pathToDirectoryWithMigrationFiles;
    }
}
