<?php

namespace PetrKnap\Php\MigrationTool\Test\SqlMigrationToolTest;

use PetrKnap\Php\MigrationTool\SqlMigrationTool;

class SqlMigrationToolMock extends SqlMigrationTool
{
    private $phpDataObject;
    private $migrationTableName;
    private $pathToDirectoryWithMigrationFiles;

    public function setPhpDataObject(\PDO $phpDataObject)
    {
        $this->phpDataObject = $phpDataObject;
    }

    protected function getPDO()
    {
        return $this->phpDataObject;
    }

    public function setMigrationTableName($migrationTableName)
    {
        $this->migrationTableName = $migrationTableName;
    }

    protected function getMigrationTableName()
    {
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
