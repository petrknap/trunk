<?php

namespace PetrKnap\Php\MigrationTool\Test;

use PetrKnap\Php\MigrationTool\Exception\DatabaseException;
use PetrKnap\Php\MigrationTool\Exception\MigrationException;
use PetrKnap\Php\MigrationTool\Exception\MigrationFileException;
use PetrKnap\Php\MigrationTool\SqlMigrationTool;
use PetrKnap\Php\MigrationTool\Test\SqlMigrationToolTest\SqlMigrationToolMock;

class SqlMigrationToolTest extends TestCase
{
    const TABLE_NAME = "migrations";

    private function getPDO()
    {
        return new \PDO("sqlite::memory:");
    }

    private function getTool(\PDO $pdo, $pathToDirectoryWithMigrationFiles = null)
    {
        return new SqlMigrationToolMock($pdo, self::TABLE_NAME, $pathToDirectoryWithMigrationFiles);
    }

    public function testCreateMigrationTableWorks()
    {
        $pdo = $this->getPDO();
        $tool = $this->getTool($pdo);
        @$tool->migrate();

        $this->invokeMethod($tool, "createMigrationTable");

        /** @noinspection SqlDialectInspection */
        /** @noinspection SqlNoDataSourceInspection */
        $statement = $pdo->prepare("SELECT name FROM sqlite_master WHERE name = :name");
        $statement->execute(array("name" => self::TABLE_NAME));

        $this->assertEquals(array("name" => self::TABLE_NAME), $statement->fetch(\PDO::FETCH_ASSOC));
    }

    public function testRegisterMigrationFileWorks()
    {
        $pdo = $this->getPDO();
        $tool = $this->getTool($pdo);
        @$tool->migrate();

        $this->invokeMethod($tool, "registerMigrationFile", array(
            __DIR__ . "/SqlMigrationToolTest/migrations/2016-06-22.2 - Ignored migration.ext"
        ));

        /** @noinspection SqlDialectInspection */
        /** @noinspection SqlNoDataSourceInspection */
        $statement = $pdo->prepare(sprintf("SELECT COUNT(id) AS count FROM %s", self::TABLE_NAME));
        $statement->execute();

        $this->assertEquals(array("count" => 3), $statement->fetch(\PDO::FETCH_ASSOC));

        $this->setExpectedException(get_class(new DatabaseException()));

        $this->invokeMethod($tool, "registerMigrationFile", array(
            __DIR__ . "/SqlMigrationToolTest/migrations/2016-06-22.2 - Ignored migration.ext"
        ));
    }

    /**
     * @dataProvider dataIsMigrationAppliedWorks
     * @param string $pathToMigrationFile
     * @param bool $expectedResult
     */
    public function testIsMigrationAppliedWorks($pathToMigrationFile, $expectedResult)
    {
        $pdo = $this->getPDO();
        $tool = $this->getTool($pdo);
        @$tool->migrate();

        $this->assertEquals(
            $expectedResult,
            $this->invokeMethod($tool, "isMigrationApplied", array($pathToMigrationFile))
        );
    }

    public function dataIsMigrationAppliedWorks()
    {
        return array(
            array(__DIR__ . "/SqlMigrationToolTest/migrations/2016-06-22.1 - First migration.sql", true),
            array(__DIR__ . "/SqlMigrationToolTest/migrations/2016-06-22.2 - Ignored migration.ext", false),
            array(__DIR__ . "/SqlMigrationToolTest/migrations/2016-06-22.3 - Second migration.sql", true)
        );
    }

    /**
     * @dataProvider dataApplyMigrationFileWorks
     * @param string $pathToMigrationFile
     * @param \Exception $expectedException
     */
    public function testApplyMigrationFileWorks($pathToMigrationFile, $expectedException = null)
    {
        $pdo = $this->getPDO();
        $tool = $this->getTool($pdo);
        @$tool->migrate();

        $this->invokeMethod($tool, "applyMigrationFile", array(
            __DIR__ . "/SqlMigrationToolTest/SQLs/create_table.sql"
        ));

        if ($expectedException) {
            $this->setExpectedException(get_class($expectedException));
        }

        $this->invokeMethod($tool, "applyMigrationFile", array($pathToMigrationFile));
    }

    public function dataApplyMigrationFileWorks()
    {
        return array(
            array(__DIR__ . "/SqlMigrationToolTest/SQLs/single_query.sql", null),
            array(__DIR__ . "/SqlMigrationToolTest/SQLs/single_query_with_error.sql", new MigrationFileException()),
            array(__DIR__ . "/SqlMigrationToolTest/SQLs/multi_query.sql", null),
            array(__DIR__ . "/SqlMigrationToolTest/SQLs/multi_query_with_error.sql", new MigrationFileException())
        );
    }

    public function testGetMigrationFilesAcceptsOnlySqlFiles()
    {
        $tool = $this->getTool($this->getPDO());

        $this->assertEquals(
            array(
                __DIR__ . "/SqlMigrationToolTest/migrations/2016-06-22.1 - First migration.sql",
                __DIR__ . "/SqlMigrationToolTest/migrations/2016-06-22.3 - Second migration.sql"
            ),
            @$this->invokeMethod($tool, "getMigrationFiles")
        );
    }

    public function testMigrateStopsAtFirstException()
    {
        $pdo = $this->getPDO();
        $tool = $this->getTool($pdo, __DIR__ . "/SqlMigrationToolTest/SQLs");

        try {
            @$tool->migrate();
            $this->fail();
        } catch (MigrationException $ignored) {
            // Ignored exception
        }

        /** @noinspection SqlNoDataSourceInspection,SqlDialectInspection */
        $rows = $pdo->query("SELECT v FROM t");
        foreach ($rows as $row) {
            $this->assertContains($row["v"], array(3, 4, 5, 6, 7, 8, 9));
        }

        /** @noinspection SqlNoDataSourceInspection,SqlDialectInspection */
        $rows = $pdo->query(sprintf("SELECT id FROM %s", self::TABLE_NAME));
        foreach ($rows as $row) {
            $this->assertContains($row["id"], array("create_table", "multi_query"));
        }
    }

    /**
     * @dataProvider dataLoggingWorks
     * @param string $method
     * @param array $arguments
     * @param array $expectedLog
     */
    public function testLoggingWorks($method, array $arguments, array $expectedLog)
    {
        $log = array();
        $pdo = $this->getPDO();
        $tool = $this->getTool($pdo);
        @$tool->migrate();
        $tool->setLogger($this->getLogger($log));

        try {
            $this->invokeMethod($tool, $method, $arguments);
        } catch (\Exception $ignored) {
            // Ignored exception
        }

        $this->assertLogEquals($expectedLog, $log);
    }

    public function dataLoggingWorks()
    {
        return array(
            array(
                "createMigrationTable",
                array(),
                array(
                    "debug" => array(
                        SqlMigrationTool::MESSAGE_CREATED_MIGRATION_TABLE_NAME,
                    ),
                ),
            ),
            array(
                "registerMigrationFile",
                array(
                    __DIR__ . "/SqlMigrationToolTest/migrations/2016-06-22.1 - First migration.sql",
                ),
                array(
                    "debug" => array(
                        SqlMigrationTool::MESSAGE_MIGRATION_ID_EXTRACTED_PATH_ID,
                        SqlMigrationTool::MESSAGE_MIGRATION_ID_EXTRACTED_PATH_ID,
                    ),
                    "critical" => array(
                        SqlMigrationTool::MESSAGE_COULD_NOT_REGISTER_MIGRATION_ID,
                    ),
                ),
            ),
            array(
                "registerMigrationFile",
                array(
                    __DIR__ . "/SqlMigrationToolTest/SQLs/create_table.sql",
                ),
                array(
                    "debug" => array(
                        SqlMigrationTool::MESSAGE_MIGRATION_ID_EXTRACTED_PATH_ID,
                        SqlMigrationTool::MESSAGE_MIGRATION_REGISTERED_ID,
                    ),
                ),
            ),
            array(
                "isMigrationApplied",
                array(
                    __DIR__ . "/SqlMigrationToolTest/migrations/2016-06-22.1 - First migration.sql",
                ),
                array(
                    "debug" => array(
                        SqlMigrationTool::MESSAGE_MIGRATION_ID_EXTRACTED_PATH_ID,
                        SqlMigrationTool::MESSAGE_MIGRATION_IS_APPLIED_ID_APPLIED,
                    ),
                ),
            ),
            array(
                "applyMigrationFile",
                array(
                    __DIR__ . "/SqlMigrationToolTest/SQLs/multi_query_with_error.sql",
                ),
                array(
                    "critical" => array(
                        SqlMigrationTool::MESSAGE_YOU_HAVE_AN_ERROR_IN_YOUR_SQL_SYNTAX_PATH,
                    ),
                ),
            ),
            array(
                "applyMigrationFile",
                array(
                    __DIR__ . "/SqlMigrationToolTest/SQLs/create_table.sql",
                ),
                array(
                    "debug" => array(
                        SqlMigrationTool::MESSAGE_MIGRATION_ID_EXTRACTED_PATH_ID,
                        SqlMigrationTool::MESSAGE_MIGRATION_REGISTERED_ID,
                    ),
                ),
            ),
        );
    }
}
