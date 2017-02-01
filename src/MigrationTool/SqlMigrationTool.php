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

    const MESSAGE_COULD_NOT_CREATE_TABLE_NAME = "Could not create migration table [name='%s']";
    const MESSAGE_CREATED_MIGRATION_TABLE_NAME = "Created migration table [name='%s']";
    const MESSAGE_COULD_NOT_REGISTER_MIGRATION_ID = "Could not register migration [id='%s']";
    const MESSAGE_COULD_NOT_READ_MIGRATION_FILE_PATH = "Could not read migration file [path='%s']";
    const MESSAGE_YOU_HAVE_AN_ERROR_IN_YOUR_SQL_SYNTAX_PATH = "You have an error in your SQL syntax [path='%s']";

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
        $result = $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS {$this->migrationTableName}" .
            "(" .
            "id VARCHAR(16) NOT NULL," .
            "applied DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP," .
            "PRIMARY KEY (id)" .
            ")"
        );

        if ($result === false) {
            $message = sprintf(
                self::MESSAGE_COULD_NOT_CREATE_TABLE_NAME,
                $this->migrationTableName
            );

            if ($this->getLogger()) {
                $this->getLogger()->critical($message);
            }

            throw new DatabaseException(
                $message,
                0,
                new \Exception(
                    implode(" ", $this->pdo->errorInfo())
                )
            );
        }

        if ($result > 0 && $this->getLogger()) {
            $this->getLogger()->debug(
                sprintf(
                    self::MESSAGE_CREATED_MIGRATION_TABLE_NAME,
                    $this->migrationTableName
                )
            );
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
        $migrationId = $this->getMigrationId($pathToMigrationFile);
        if ($statement->execute(array("id" => $migrationId)) === false) {
            $message = sprintf(
                self::MESSAGE_COULD_NOT_REGISTER_MIGRATION_ID,
                $this->getMigrationId($pathToMigrationFile)
            );

            if (null != $this->getLogger()) {
                $this->getLogger()->critical($message);
            }

            throw new DatabaseException(
                $message,
                0,
                new \Exception(
                    implode(" ", $this->pdo->errorInfo())
                )
            );
        }
    }

    /**
     * @inheritdoc
     */
    protected function isMigrationApplied($pathToMigrationFile)
    {
        /** @noinspection SqlNoDataSourceInspection,SqlDialectInspection */
        $statement = $this->pdo->prepare("SELECT null FROM {$this->migrationTableName} WHERE id = :id");
        $migrationId = $this->getMigrationId($pathToMigrationFile);
        $statement->execute(array("id" => $migrationId));
        $isApplied = $statement->fetch() !== false;

        return $isApplied;
    }

    /**
     * @inheritdoc
     */
    protected function applyMigrationFile($pathToMigrationFile)
    {
        $migrationData = @file_get_contents($pathToMigrationFile);

        if ($migrationData === false) {
            $message = sprintf(
                self::MESSAGE_COULD_NOT_READ_MIGRATION_FILE_PATH,
                $pathToMigrationFile
            );

            if ($this->getLogger()) {
                $this->getLogger()->critical($message);
            }

            throw new MigrationFileException($message);
        }

        $this->pdo->beginTransaction();

        try {
            $result = $this->pdo->exec($migrationData);
        } catch (\Exception $e) {
            $result = $e;
        }

        if ($result === false || $result instanceof \Exception) {
            if (!$result/* instanceof \Exception */) {
                $result = new DatabaseException(implode(" ", $this->pdo->errorInfo()));
            }

            $this->pdo->rollBack();
            $message = sprintf(
                self::MESSAGE_YOU_HAVE_AN_ERROR_IN_YOUR_SQL_SYNTAX_PATH,
                $pathToMigrationFile
            );

            if ($this->getLogger()) {
                $this->getLogger()->critical($message);
            }

            throw new MigrationFileException(
                $message,
                $result->getCode(),
                $result
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
