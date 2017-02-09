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
        /** @noinspection SqlNoDataSourceInspection,SqlDialectInspection */
        if (false === $this->phpDataObject()->exec("SELECT null FROM {$this->migrationTableName()} LIMIT 1")) {
            /** @noinspection SqlNoDataSourceInspection,SqlDialectInspection */
            $result = $this->phpDataObject()->exec(
                "CREATE TABLE IF NOT EXISTS {$this->migrationTableName()}" .
                "(" .
                "id VARCHAR(16) NOT NULL," .
                "applied DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP," .
                "PRIMARY KEY (id)" .
                ")"
            );

            if ($result === false) {
                $context = array(
                    "table" => $this->migrationTableName()
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
                        implode(" ", $this->phpDataObject()->errorInfo())
                    )
                );
            }

            if ($this->getLogger()) {
                $this->getLogger()->debug(
                    self::MESSAGE__CREATED_MIGRATION_TABLE__TABLE,
                    array(
                        "table" => $this->migrationTableName(),
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
        $statement = $this->phpDataObject()->prepare("INSERT INTO {$this->migrationTableName()} (id) VALUES (:id)");
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
                    implode(" ", $this->phpDataObject()->errorInfo())
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
        $statement = $this->phpDataObject()->prepare("SELECT null FROM {$this->migrationTableName()} WHERE id = :id");
        $migrationId = $this->getMigrationId($pathToMigrationFile);
        if (false === $statement || false === $statement->execute(array("id" => $migrationId))) {
            $context = array(
                "table" => $this->getMigrationTableName()
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

        $this->phpDataObject()->beginTransaction();

        try {
            $result = $this->phpDataObject()->exec($migrationData);
        } catch (\Exception $e) {
            $result = $e;
        }

        if ($result === false || $result instanceof \Exception) {
            if (!$result/* instanceof \Exception */) {
                $result = new DatabaseException(implode(" ", $this->phpDataObject()->errorInfo()));
            }

            $this->phpDataObject()->rollBack();
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

        $this->phpDataObject()->commit();
    }

    /**
     * @inheritdoc
     */
    protected function getMigrationFilePattern()
    {
        return '/\.sql$/i';
    }

    /**
     * @return \PDO
     */
    abstract protected function getPhpDataObject();

    /**
     * @return string
     */
    abstract protected function getMigrationTableName();

    /**
     * @return \PDO
     */
    private function phpDataObject()
    {
        if (!$this->{__METHOD__}) {
            $this->{__METHOD__} = $this->getPhpDataObject();
        }
        return $this->{__METHOD__};
    }

    /**
     * @return string
     */
    private function migrationTableName()
    {
        if (!$this->{__METHOD__}) {
            $this->{__METHOD__} = $this->getMigrationTableName();
        }
        return $this->{__METHOD__};
    }
}
