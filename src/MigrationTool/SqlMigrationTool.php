<?php

namespace PetrKnap\Php\MigrationTool;

use PetrKnap\Php\MigrationTool\Exception\DatabaseException;
use PetrKnap\Php\MigrationTool\Exception\MigrationFileException;

/**
 * SQL migration tool
 *
 * @author   Petr Knap <dev@petrknap.cz>
 * @since    2016-06-22
 * @license  https://github.com/petrknap/php-migrationtool/blob/master/LICENSE MIT
 */
abstract class SqlMigrationTool extends AbstractMigrationTool
{
    const MIGRATION_FILE_PATTERN = '/\.sql$/i';

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $migrationTableName;

    /**
     * @inheritdoc
     */
    public function migrate()
    {
        $this->pdo = $this->getPhpDataObject();
        $this->migrationTableName = $this->getNameOfMigrationTable();

        $this->createMigrationTable();

        parent::migrate();
    }

    /**
     * @throws DatabaseException
     */
    protected function createMigrationTable()
    {
        /** @noinspection SqlNoDataSourceInspection,SqlDialectInspection */
        if (
            $this->pdo->exec(
                "CREATE TABLE IF NOT EXISTS {$this->migrationTableName}" .
                "(" .
                "id VARCHAR(16) NOT NULL," .
                "applied DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP," .
                "PRIMARY KEY (id)" .
                ")"
            ) === false
        ) {
            throw new DatabaseException(sprintf(
                "Could not create table [name='%s']",
                $this->migrationTableName
            ), 0, new \Exception(implode(" ", $this->pdo->errorInfo())));
        }
    }

    /**
     * @param string $pathToMigrationFile
     * @throws DatabaseException
     */
    protected function registerMigrationFile($pathToMigrationFile)
    {
        /** @noinspection SqlNoDataSourceInspection,SqlDialectInspection */
        $statement = $this->pdo->prepare("INSERT INTO {$this->migrationTableName} (id) VALUES (:id)");
        if ($statement->execute(array("id" => $this->getMigrationId($pathToMigrationFile))) === false) {
            throw new DatabaseException(sprintf(
                "Could not register migration [id='%s']",
                $this->getMigrationId($pathToMigrationFile)
            ), 0, new \Exception(implode(" ", $this->pdo->errorInfo())));
        }
    }

    /**
     * @inheritdoc
     */
    protected function isMigrationApplied($pathToMigrationFile)
    {
        /** @noinspection SqlNoDataSourceInspection,SqlDialectInspection */
        $statement = $this->pdo->prepare("SELECT null FROM {$this->migrationTableName} WHERE id = :id");
        $statement->execute(array("id" => $this->getMigrationId($pathToMigrationFile)));

        return $statement->fetch() !== false;
    }

    /**
     * @inheritdoc
     */
    protected function applyMigrationFile($pathToMigrationFile)
    {
        $migrationData = @file_get_contents($pathToMigrationFile);

        if ($migrationData === false) {
            throw new MigrationFileException(
                sprintf(
                    "Could not read migration file [id='%s']",
                    $this->getMigrationId($pathToMigrationFile)
                )
            );
        }

        $this->pdo->beginTransaction();

        $result = $this->pdo->exec($migrationData);

        if ($result === false || $result instanceof \Exception) {
            if (!$result) {
                $result = new DatabaseException(implode(" ", $this->pdo->errorInfo()));
            }

            $this->pdo->rollBack();
            throw new MigrationFileException(
                sprintf(
                    "You have an error in your SQL syntax [id='%s']",
                    $this->getMigrationId($pathToMigrationFile),
                    $result
                )
            );
        }

        $this->registerMigrationFile($pathToMigrationFile);

        $this->pdo->commit();
    }

    /**
     * @return \PDO
     */
    abstract protected function getPhpDataObject();

    /**
     * @return string
     */
    abstract protected function getNameOfMigrationTable();
}
