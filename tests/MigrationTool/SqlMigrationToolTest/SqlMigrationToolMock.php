<?php

namespace PetrKnap\Php\MigrationTool\Test\SqlMigrationToolTest;

use PetrKnap\Php\MigrationTool\SqlMigrationTool;

class SqlMigrationToolMock extends SqlMigrationTool
{
    public $getPhpDataObjectCalls = 0;
    public $getMigrationTableNameCalls = 0;
    private $phpDataObject;
    private $migrationTableName;
    private $pathToDirectoryWithMigrationFiles;

    public function setPhpDataObject(\PDO $phpDataObject)
    {
        $this->phpDataObject = $phpDataObject;
    }

    protected function getPhpDataObject()
    {
        $this->getPhpDataObjectCalls++;
        return $this->phpDataObject;
    }

    public function setMigrationTableName($migrationTableName)
    {
        $this->migrationTableName = $migrationTableName;
    }

    protected function getMigrationTableName()
    {
        $this->getMigrationTableNameCalls++;
        return $this->migrationTableName;
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
