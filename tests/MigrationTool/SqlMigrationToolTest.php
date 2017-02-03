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

    private function getTool(\PDO $pdo)
    {
        $tool = new SqlMigrationToolMock();
        $tool->setPhpDataObject($pdo);
        $tool->setNameOfMigrationTable(self::TABLE_NAME);

        return $tool;
    }

    public function testCreateMigrationTableWorks()
    {
        $pdo = $this->getPDO();
        $tool = $this->getTool($pdo);

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

        $this->invokeMethod($tool, "createMigrationTable");
        $this->invokeMethod($tool, "registerMigrationFile", array(
            __DIR__ . "/SqlMigrationToolTest/migrations/2016-06-22.2 - Ignored migration.ext"
        ));

        /** @noinspection SqlDialectInspection */
        /** @noinspection SqlNoDataSourceInspection */
        $statement = $pdo->prepare(sprintf("SELECT COUNT(id) AS count FROM %s", self::TABLE_NAME));
        $statement->execute();

        $this->assertEquals(array("count" => 1), $statement->fetch(\PDO::FETCH_ASSOC));

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
        $tool->migrate();

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

        $this->invokeMethod($tool, "createMigrationTable");
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
            $this->invokeMethod($tool, "getMigrationFiles")
        );
    }

    public function testMigrateStopsAtFirstException()
    {
        $pdo = $this->getPDO();
        $tool = $this->getTool($pdo);
        $tool->setPathToDirectoryWithMigrationFiles(__DIR__ . "/SqlMigrationToolTest/SQLs");

        try {
            $tool->migrate();
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
     * @param array $invokes
     * @param array $expectedLog
     */
    public function testLoggingWorks(array $invokes, array $expectedLog)
    {
        $log = array();
        $tool = $this->getTool($this->getPDO());
        $tool->setLogger($this->getLogger($log));

        try {
            foreach ($invokes as $invoke) {
                $this->invokeMethod($tool, $invoke[0], $invoke[1]);
            }
        } catch (\Exception $ignored) {
            // Ignored exception
        }

        $this->assertLogEquals($expectedLog, $log);
    }

    public function dataLoggingWorks()
    {
        return array(
            // createMigrationTable
            array(
                array(
                    array("setNameOfMigrationTable", array("invalid table name")),
                    array("createMigrationTable", array()),
                ),
                array(
                    "critical" => array(
                        SqlMigrationTool::MESSAGE__COULD_NOT_CREATE_TABLE__TABLE,
                    ),
                ),
            ),
            array(
                array(
                    array("createMigrationTable", array()),
                ),
                array(
                    "debug" => array(
                        SqlMigrationTool::MESSAGE__CREATED_MIGRATION_TABLE__TABLE,
                    ),
                ),
            ),
            array(
                array(
                    array("createMigrationTable", array()),
                    array("createMigrationTable", array()),
                ),
                array(
                    "debug" => array(
                        SqlMigrationTool::MESSAGE__CREATED_MIGRATION_TABLE__TABLE,
                    ),
                ),
            ),
            // registerMigrationFile
            array(
                array(
                    array("registerMigrationFile", array(__DIR__ . "/SqlMigrationToolTest/SQLs/single_query.sql")),
                ),
                array(
                    "critical" => array(
                        SqlMigrationTool::MESSAGE__COULD_NOT_REGISTER_MIGRATION__ID
                    ),
                ),
            ),
            array(
                array(
                    array("createMigrationTable", array()),
                    array("registerMigrationFile", array(__DIR__ . "/SqlMigrationToolTest/SQLs/single_query.sql")),
                ),
                array(
                    "debug" => array(
                        SqlMigrationTool::MESSAGE__CREATED_MIGRATION_TABLE__TABLE,
                    ),
                ),
            ),
            array(
                array(
                    array("createMigrationTable", array()),
                    array("registerMigrationFile", array(__DIR__ . "/SqlMigrationToolTest/SQLs/single_query.sql")),
                    array("registerMigrationFile", array(__DIR__ . "/SqlMigrationToolTest/SQLs/single_query.sql")),
                ),
                array(
                    "debug" => array(
                        SqlMigrationTool::MESSAGE__CREATED_MIGRATION_TABLE__TABLE,
                    ),
                    "critical" => array(
                        SqlMigrationTool::MESSAGE__COULD_NOT_REGISTER_MIGRATION__ID,
                    )
                ),
            ),
            // isMigrationApplied
            array(
                array(
                    array("isMigrationApplied", array(__DIR__ . "/SqlMigrationToolTest/SQLs/single_query.sql")),
                ),
                array(
                    "critical" => array(
                        SqlMigrationTool::MESSAGE__COULD_NOT_READ_FROM_TABLE__TABLE,
                    ),
                ),
            ),
            // applyMigrationFile
            array(
                array(
                    array("applyMigrationFile", array(__DIR__ . "/SqlMigrationToolTest/SQLs/missing.sql")),
                ),
                array(
                    "critical" => array(
                        SqlMigrationTool::MESSAGE__COULD_NOT_READ_MIGRATION_FILE__PATH,
                    ),
                ),
            ),
            array(
                array(
                    array("applyMigrationFile", array(__DIR__ . "/SqlMigrationToolTest/SQLs/single_query_with_error.sql")),
                ),
                array(
                    "critical" => array(
                        SqlMigrationTool::MESSAGE__YOU_HAVE_AN_ERROR_IN_YOUR_SQL_SYNTAX__PATH,
                    ),
                ),
            ),
        );
    }
}
