<?php

namespace PetrKnap\Php\MigrationTool\Test\SqlMigrationToolTest;

use PetrKnap\Php\MigrationTool\SqlMigrationTool;

class SqlMigrationToolMock extends SqlMigrationTool
{
    private $phpDataObject;
    private $nameOfMigrationTable;
    private $pathToDirectoryWithMigrationFiles;

    public function __construct()
    {
        $this->pathToDirectoryWithMigrationFiles = __DIR__ . "/migrations";
    }

    public function setPhpDataObject(\PDO $phpDataObject)
    {
        $this->phpDataObject = $phpDataObject;
    }

    protected function getPhpDataObject()
    {
        return $this->phpDataObject;
    }

    public function setNameOfMigrationTable($nameOfMigrationTable)
    {
        $this->nameOfMigrationTable = $nameOfMigrationTable;
    }

    protected function getMigrationTableName()
    {
        return $this->nameOfMigrationTable;
    }

    public function setPathToDirectoryWithMigrationFiles($pathToDirectoryWithMigrationFiles)
    {
        $this->pathToDirectoryWithMigrationFiles = $pathToDirectoryWithMigrationFiles;
    }

    protected function getPathToDirectoryWithMigrationFiles()
    {
        return $this->pathToDirectoryWithMigrationFiles;
    }
}
