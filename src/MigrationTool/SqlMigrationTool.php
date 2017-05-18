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

    const MESSAGE__COULD_NOT_CREATE_TABLE__TABLE = "Could not create migration table {table}";
    const MESSAGE__CREATED_MIGRATION_TABLE__TABLE = "Created migration table {table}";
    const MESSAGE__COULD_NOT_REGISTER_MIGRATION__ID = "Could not register migration {id}";
    const MESSAGE__COULD_NOT_READ_MIGRATION_FILE__PATH = "Could not read migration file {path}";
    const MESSAGE__COULD_NOT_READ_FROM_TABLE__TABLE = "Could not read from table {table}";
    const MESSAGE__YOU_HAVE_AN_ERROR_IN_YOUR_SQL_SYNTAX__PATH = "You have an error in your SQL syntax in {path}";

    /**
     * @inheritdoc
     */
    public function migrate()
    {
        $this->createMigrationTable();

        parent::migrate();
    }

    /**
     * @throws DatabaseException
     */
    protected function createMigrationTable()
    {
        $errmode = $this->getPhpDataObject()->getAttribute(\PDO::ATTR_ERRMODE);
        $this->getPhpDataObject()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        try {
            /** @noinspection SqlNoDataSourceInspection,SqlDialectInspection */
            $result = $this->getPhpDataObject()->query("SELECT null FROM {$this->getNameOfMigrationTable()} LIMIT 1")->fetchAll();
        } catch (\PDOException $e) {
            if ($e->getCode() === '42S02') {
               $result = false;
            } else {
                throw $e;
            }
        }
        $this->getPhpDataObject()->setAttribute(\PDO::ATTR_ERRMODE, $errmode);

        if (false === $result) {
            /** @noinspection SqlNoDataSourceInspection,SqlDialectInspection */
            $result = $this->getPhpDataObject()->exec(
                "CREATE TABLE IF NOT EXISTS {$this->getNameOfMigrationTable()}" .
                "(" .
                "id VARCHAR(16) NOT NULL," .
                "applied DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP," .
                "PRIMARY KEY (id)" .
                ")"
            );

            if ($result === false) {
                $context = array(
                    "table" => $this->getNameOfMigrationTable()
                );

                if ($this->getLogger()) {
                    $this->getLogger()->critical(
                        self::MESSAGE__COULD_NOT_CREATE_TABLE__TABLE,
                        $context
                    );
                }

                throw new DatabaseException(
                    $this->interpolate(
                        self::MESSAGE__COULD_NOT_CREATE_TABLE__TABLE,
                        $context
                    ),
                    0,
                    new \Exception(
                        implode(" ", $this->getPhpDataObject()->errorInfo())
                    )
                );
            }

            if ($this->getLogger()) {
                $this->getLogger()->debug(
                    self::MESSAGE__CREATED_MIGRATION_TABLE__TABLE,
                    array(
                        "table" => $this->getNameOfMigrationTable(),
                    )
                );
            }
        }
    }

    /**
     * @param string $pathToMigrationFile
     * @throws DatabaseException
     */
    protected function registerMigrationFile($pathToMigrationFile)
    {
        /** @noinspection SqlNoDataSourceInspection,SqlDialectInspection */
        $statement = $this->getPhpDataObject()->prepare("INSERT INTO {$this->getNameOfMigrationTable()} (id) VALUES (:id)");
        $migrationId = $this->getMigrationId($pathToMigrationFile);
        if (false === $statement || false === $statement->execute(array("id" => $migrationId))) {
            $context = array(
                "id" => $migrationId
            );

            if (null != $this->getLogger()) {
                $this->getLogger()->critical(
                    self::MESSAGE__COULD_NOT_REGISTER_MIGRATION__ID,
                    $context
                );
            }

            throw new DatabaseException(
                $this->interpolate(
                    self::MESSAGE__COULD_NOT_REGISTER_MIGRATION__ID,
                    $context
                ),
                0,
                new \Exception(
                    implode(" ", $this->getPhpDataObject()->errorInfo())
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
        $statement = $this->getPhpDataObject()->prepare("SELECT null FROM {$this->getNameOfMigrationTable()} WHERE id = :id");
        $migrationId = $this->getMigrationId($pathToMigrationFile);
        if (false === $statement || false === $statement->execute(array("id" => $migrationId))) {
            $context = array(
                "table" => $this->getNameOfMigrationTable()
            );

            if ($this->getLogger()) {
                $this->getLogger()->critical(
                    self::MESSAGE__COULD_NOT_READ_FROM_TABLE__TABLE,
                    $context
                );
            }

            throw new DatabaseException(
                $this->interpolate(
                    self::MESSAGE__COULD_NOT_READ_FROM_TABLE__TABLE,
                    $context
                )
            );
        }

        return $statement->fetch() !== false;
    }

    /**
     * @inheritdoc
     */
    protected function applyMigrationFile($pathToMigrationFile)
    {
        $migrationData = @file_get_contents($pathToMigrationFile);

        if ($migrationData === false) {
            $context = array(
                "path" => $pathToMigrationFile
            );

            if ($this->getLogger()) {
                $this->getLogger()->critical(
                    self::MESSAGE__COULD_NOT_READ_MIGRATION_FILE__PATH,
                    $context
                );
            }

            throw new MigrationFileException(
                $this->interpolate(
                    self::MESSAGE__COULD_NOT_READ_MIGRATION_FILE__PATH,
                    $context
                )
            );
        }

        $this->getPhpDataObject()->beginTransaction();

        try {
            $result = $this->getPhpDataObject()->exec($migrationData);
        } catch (\Exception $e) {
            $result = $e;
        }

        if ($result === false || $result instanceof \Exception) {
            if (!$result/* instanceof \Exception */) {
                $result = new DatabaseException(implode(" ", $this->getPhpDataObject()->errorInfo()));
            }

            $this->getPhpDataObject()->rollBack();
            $context = array(
                "path" => $pathToMigrationFile,
            );

            if ($this->getLogger()) {
                $this->getLogger()->critical(
                    self::MESSAGE__YOU_HAVE_AN_ERROR_IN_YOUR_SQL_SYNTAX__PATH,
                    $context
                );
            }

            throw new MigrationFileException(
                $this->interpolate(
                    self::MESSAGE__YOU_HAVE_AN_ERROR_IN_YOUR_SQL_SYNTAX__PATH,
                    $context
                ),
                $result->getCode(),
                $result
            );
        }

        $this->registerMigrationFile($pathToMigrationFile);

        $this->getPhpDataObject()->commit();
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
