<?php

namespace PetrKnap\Php\MigrationTool\Test;

use PetrKnap\Php\MigrationTool\Exception\DatabaseException;
use PetrKnap\Php\MigrationTool\Exception\MigrationException;
use PetrKnap\Php\MigrationTool\Exception\MigrationFileException;
use PetrKnap\Php\MigrationTool\SqlMigrationTool;
use PetrKnap\Php\MigrationTool\Test\SqlMigrationToolTest\SqlMigrationToolMock;
use Psr\Log\LoggerInterface;

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
        $tool->setMigrationTableName(self::TABLE_NAME);

        return $tool;
    }

    public function testItAcceptsOnlySqlFiles()
    {
        $this->assertEquals('/\.sql$/i', SqlMigrationTool::MIGRATION_FILE_PATTERN);
    }

    public function testMethodGetPhpDataObjectIsCalledOnlyOnce()
    {
        $this->markTestIncomplete();
    }

    public function testMethodGetMigrationTableNameIsCalledOnlyOnce()
    {
        $this->markTestIncomplete();
    }

    public function testCreateMigrationTableMethodWorks(LoggerInterface $logger = null)
    {
        $pdo = $this->getPDO();
        $tool = $this->getTool($pdo);

        if ($logger) {
            $tool->setLogger($logger);
        }

        $this->invokeMethods($tool, array(
            array("createMigrationTable"), // create table
            array("createMigrationTable"), // if not exists
        ));

        /** @noinspection SqlDialectInspection, SqlNoDataSourceInspection */
        $statement = $pdo->prepare("SELECT name FROM sqlite_master WHERE name = :name");
        $statement->execute(array("name" => self::TABLE_NAME));

        $this->assertEquals(array("name" => self::TABLE_NAME), $statement->fetch(\PDO::FETCH_ASSOC));
    }

    public function testCreateMigrationTableMethodLogs()
    {
        $log = array();
        $this->testCreateMigrationTableMethodWorks($this->getLogger($log));

        $this->assertLogEquals(array(
            "debug" => array(
                SqlMigrationTool::MESSAGE__CREATED_MIGRATION_TABLE__TABLE,
            ),
        ), $log);
    }

    public function testCreateMigrationTableMethodThrowsDatabaseExceptionIfCouldNotCreateTable(LoggerInterface $logger = null)
    {
        $pdo = $this->getPDO();
        $tool = $this->getTool($pdo);
        $tool->setMigrationTableName("invalid name");

        if ($logger) {
            $tool->setLogger($logger);
        }

        try {
            $this->invokeMethods($tool, array(
                array("createMigrationTable"),
            ));
            $this->fail();
        } catch (DatabaseException $exception) {
            $this->assertStringMatchesFormat(
                $this->getFormatForMessage(SqlMigrationTool::MESSAGE__COULD_NOT_CREATE_TABLE__TABLE),
                $exception->getMessage()
            );
        }
    }

    public function testCreateMigrationTableMethodLogsDatabaseExceptionIfCouldNotCreateTable()
    {
        $log = array();
        $this->testCreateMigrationTableMethodThrowsDatabaseExceptionIfCouldNotCreateTable($this->getLogger($log));

        $this->assertLogEquals(array(
            "critical" => array(
                SqlMigrationTool::MESSAGE__COULD_NOT_CREATE_TABLE__TABLE,
            ),
        ), $log);
    }

    public function testRegisterMigrationFileMethodWorks(LoggerInterface $logger = null)
    {
        $pdo = $this->getPDO();
        $tool = $this->getTool($pdo);

        if ($logger) {
            $tool->setLogger($logger);
        }

        $this->invokeMethods($tool, array(
            array("createMigrationTable"),
            array("registerMigrationFile", array(
                __DIR__ . "/SqlMigrationToolTest/RegisterMigrationFileMethodWorks/2017-02-05.1 - First migration.sql",
            )),
        ));

        /** @noinspection SqlDialectInspection, SqlNoDataSourceInspection */
        $statement = $pdo->prepare(sprintf("SELECT COUNT(id) AS count FROM %s", self::TABLE_NAME));
        $statement->execute();

        $this->assertEquals(array("count" => 1), $statement->fetch(\PDO::FETCH_ASSOC));
    }

    public function testRegisterMigrationFileMethodLogs()
    {
        $log = array();
        $this->testRegisterMigrationFileMethodWorks($this->getLogger($log));

        $this->assertLogEquals(array(
            "debug" => array(
                SqlMigrationTool::MESSAGE__CREATED_MIGRATION_TABLE__TABLE,
            ),
        ), $log);
    }

    public function testRegisterMigrationFileMethodThrowsDatabaseExceptionIfCouldNotRegisterMigrationId(LoggerInterface $logger = null)
    {
        $pdo = $this->getPDO();
        $tool = $this->getTool($pdo);

        if ($logger) {
            $tool->setLogger($logger);
        }

        try {
            $this->invokeMethods($tool, array(
                array("createMigrationTable"),
                array("registerMigrationFile", array(
                    "/2017-02-05.1.sql",
                )),
                array("registerMigrationFile", array(
                    "/2017-02-05.1.sql",
                )),
            ));
            $this->fail();
        } catch (DatabaseException $exception) {
            $this->assertStringMatchesFormat(
                $this->getFormatForMessage(SqlMigrationTool::MESSAGE__COULD_NOT_REGISTER_MIGRATION__ID),
                $exception->getMessage()
            );
        }
    }

    public function testRegisterMigrationFileMethodLogsDatabaseExceptionIfCouldNotRegisterMigrationId()
    {
        $log = array();
        $this->testRegisterMigrationFileMethodThrowsDatabaseExceptionIfCouldNotRegisterMigrationId($this->getLogger($log));

        $this->assertLogEquals(array(
            "debug" => array(
                SqlMigrationTool::MESSAGE__CREATED_MIGRATION_TABLE__TABLE,
            ),
            "critical" => array(
                SqlMigrationTool::MESSAGE__COULD_NOT_REGISTER_MIGRATION__ID,
            ),
        ), $log);
    }

    /**
     * @dataProvider dataIsMigrationAppliedMethodWorks
     * @param string $migrationFile
     * @param bool $expectedResult
     * @param LoggerInterface $logger
     */
    public function testIsMigrationAppliedMethodWorks($migrationFile, $expectedResult, LoggerInterface $logger = null)
    {
        $pdo = $this->getPDO();
        $tool = $this->getTool($pdo);

        if ($logger) {
            $tool->setLogger($logger);
        }

        $this->assertEquals(
            $expectedResult,
            $this->invokeMethods($tool, array(
                array("createMigrationTable"),
                array("registerMigrationFile", array(
                    "/2017-02-05.1.sql",
                )),
                array("isMigrationApplied", array($migrationFile)),
            ))
        );
    }

    public function dataIsMigrationAppliedMethodWorks()
    {
        return array(
            array("/2017-02-05.1.sql", true),
            array("/2017-02-05.2.sql", false),
        );
    }

    public function testIsMigrationAppliedMethodLogs()
    {
        $log = array();
        $data = $this->dataIsMigrationAppliedMethodWorks();
        $this->testIsMigrationAppliedMethodWorks(
            $data[0][0],
            $data[0][1],
            $this->getLogger($log)
        );

        $this->assertLogEquals(array(
            "debug" => array(
                SqlMigrationTool::MESSAGE__CREATED_MIGRATION_TABLE__TABLE,
            ),
        ), $log);
    }

    public function testIsMigrationAppliedMethodThrowsDatabaseExceptionIfCouldNotReadFromTable(LoggerInterface $logger = null)
    {
        $pdo = $this->getPDO();
        $tool = $this->getTool($pdo);

        if ($logger) {
            $tool->setLogger($logger);
        }

        try {
            $this->invokeMethods($tool, array(
                array("isMigrationApplied", array("/2017-02-05.1.sql")),
            ));
            $this->fail();
        } catch (DatabaseException $exception) {
            $this->assertStringMatchesFormat(
                $this->getFormatForMessage(SqlMigrationTool::MESSAGE__COULD_NOT_READ_FROM_TABLE__TABLE),
                $exception->getMessage()
            );
        }
    }

    public function testIsMigrationAppliedMethodLogsDatabaseExceptionIfCouldNotReadFromTable()
    {
        $log = array();
        $this->testIsMigrationAppliedMethodThrowsDatabaseExceptionIfCouldNotReadFromTable(
            $this->getLogger($log)
        );

        $this->assertLogEquals(array(
            "critical" => array(
                SqlMigrationTool::MESSAGE__COULD_NOT_READ_FROM_TABLE__TABLE,
            ),
        ), $log);
    }

    /**
     * @dataProvider dataApplyMigrationFileMethodWorks
     * @param string $pathToMigrationFile
     * @param int $expectedCount
     * @param LoggerInterface $logger
     */
    public function testApplyMigrationFileMethodWorks($pathToMigrationFile, $expectedCount, LoggerInterface $logger = null)
    {
        $pdo = $this->getPDO();
        $tool = $this->getTool($pdo);

        if ($logger) {
            $tool->setLogger($logger);
        }

        $this->invokeMethods($tool, array(
            array("createMigrationTable"),
            array("applyMigrationFile", array(
                __DIR__ . "/SqlMigrationToolTest/ApplyMigrationFileMethodWorks/create_table.sql",
            )),
        ));

        $this->invokeMethods($tool, array(
            array("applyMigrationFile", array(
                $pathToMigrationFile,
            )),
        ));

        /** @noinspection SqlNoDataSourceInspection, SqlDialectInspection */
        $this->assertEquals(
            array("count" => $expectedCount),
            $pdo->query("SELECT COUNT(*) AS count FROM t")->fetch(\PDO::FETCH_ASSOC)
        );
    }

    public function dataApplyMigrationFileMethodWorks()
    {
        return array(
            array(
                __DIR__ . "/SqlMigrationToolTest/ApplyMigrationFileMethodWorks/single_query.sql",
                1,
            ),
            array(
                __DIR__ . "/SqlMigrationToolTest/ApplyMigrationFileMethodWorks/multi_query.sql",
                0,
            ),
        );
    }

    public function testApplyMigrationFileMethodLogs()
    {
        $log = array();
        $data = $this->dataApplyMigrationFileMethodWorks();
        $this->testApplyMigrationFileMethodWorks(
            $data[0][0],
            $data[0][1],
            $this->getLogger($log)
        );

        $this->assertLogEquals(array(
            "debug" => array(
                SqlMigrationTool::MESSAGE__CREATED_MIGRATION_TABLE__TABLE,
            ),
        ), $log);
    }

    /**
     * @dataProvider dataApplyMigrationFileMethodThrowsMigrationFileExceptionIfThereIsBrokenMigrationFile
     * @param string $pathToMigrationFile
     * @param string $expectedMessage
     * @param LoggerInterface $logger
     */
    public function testApplyMigrationFileMethodThrowsMigrationFileExceptionIfThereIsBrokenMigrationFile($pathToMigrationFile, $expectedMessage, LoggerInterface $logger = null)
    {
        $pdo = $this->getPDO();
        $tool = $this->getTool($pdo);

        if ($logger) {
            $tool->setLogger($logger);
        }

        $this->invokeMethods($tool, array(
            array("createMigrationTable"),
            array("applyMigrationFile", array(
                __DIR__ . "/SqlMigrationToolTest/ApplyMigrationFileMethodThrowsMigrationFileExceptionIfThereIsBrokenMigrationFile/create_table.sql",
            )),
        ));

        try {
            $this->invokeMethods($tool, array(
                array("applyMigrationFile", array(
                    $pathToMigrationFile,
                )),
            ));
            $this->fail();
        } catch (MigrationFileException $exception) {
            $this->assertStringMatchesFormat(
                $this->getFormatForMessage($expectedMessage),
                $exception->getMessage()
            );
        }
    }

    public function dataApplyMigrationFileMethodThrowsMigrationFileExceptionIfThereIsBrokenMigrationFile()
    {
        return array(
            array(
                __DIR__ . "/SqlMigrationToolTest/ApplyMigrationFileMethodThrowsMigrationFileExceptionIfThereIsBrokenMigrationFile/single_query_with_error.sql",
                SqlMigrationTool::MESSAGE__YOU_HAVE_AN_ERROR_IN_YOUR_SQL_SYNTAX__PATH,
            ),
            array(
                __DIR__ . "/SqlMigrationToolTest/ApplyMigrationFileMethodThrowsMigrationFileExceptionIfThereIsBrokenMigrationFile/multi_query_with_error.sql",
                SqlMigrationTool::MESSAGE__YOU_HAVE_AN_ERROR_IN_YOUR_SQL_SYNTAX__PATH,
            ),
            array(
                __DIR__ . "/SqlMigrationToolTest/ApplyMigrationFileMethodThrowsMigrationFileExceptionIfThereIsBrokenMigrationFile/file_not_found.sql",
                SqlMigrationTool::MESSAGE__COULD_NOT_READ_MIGRATION_FILE__PATH
            ),
        );
    }

    public function testApplyMigrationFileMethodLogsMigrationFileExceptionIfThereIsBrokenMigrationFile()
    {
        $log = array();
        $data = $this->dataApplyMigrationFileMethodThrowsMigrationFileExceptionIfThereIsBrokenMigrationFile();
        $this->testApplyMigrationFileMethodThrowsMigrationFileExceptionIfThereIsBrokenMigrationFile(
            $data[0][0],
            $data[0][1],
            $this->getLogger($log)
        );
        $this->testApplyMigrationFileMethodThrowsMigrationFileExceptionIfThereIsBrokenMigrationFile(
            $data[2][0],
            $data[2][1],
            $this->getLogger($log)
        );

        $this->assertLogEquals(array(
            "debug" => array(
                SqlMigrationTool::MESSAGE__CREATED_MIGRATION_TABLE__TABLE,
                SqlMigrationTool::MESSAGE__CREATED_MIGRATION_TABLE__TABLE,
            ),
            "critical" => array(
                SqlMigrationTool::MESSAGE__YOU_HAVE_AN_ERROR_IN_YOUR_SQL_SYNTAX__PATH,
                SqlMigrationTool::MESSAGE__COULD_NOT_READ_MIGRATION_FILE__PATH,
            ),
        ), $log);
    }

    /**
     * @dataProvider dataApplyMigrationFileMethodRollbacksTransactionIfThereIsBrokenMigrationFile
     * @param string $pathToMigrationFile
     */
    public function testApplyMigrationFileMethodRollbacksTransactionIfThereIsBrokenMigrationFile($pathToMigrationFile)
    {
        $pdo = $this->getPDO();
        $tool = $this->getTool($pdo);

        $this->invokeMethods($tool, array(
            array("createMigrationTable"),
            array("applyMigrationFile", array(
                __DIR__ . "/SqlMigrationToolTest/ApplyMigrationFileMethodRollbacksTransactionIfThereIsBrokenMigrationFile/create_table.sql",
            )),
        ));

        try {
            $this->invokeMethods($tool, array(
                array("applyMigrationFile", array(
                    $pathToMigrationFile,
                )),
            ));
            $this->fail();
        } catch (MigrationFileException $ignored) {
            /** @noinspection SqlDialectInspection, SqlNoDataSourceInspection */
            $this->assertEquals(
                array("count" => 2),
                $pdo->query("SELECT COUNT(*) AS count FROM t")->fetch(\PDO::FETCH_ASSOC)
            );
        }
    }

    public function dataApplyMigrationFileMethodRollbacksTransactionIfThereIsBrokenMigrationFile()
    {
        return array(
            array(
                __DIR__ . "/SqlMigrationToolTest/ApplyMigrationFileMethodRollbacksTransactionIfThereIsBrokenMigrationFile/single_query_with_error.sql",
            ),
            array(
                __DIR__ . "/SqlMigrationToolTest/ApplyMigrationFileMethodRollbacksTransactionIfThereIsBrokenMigrationFile/multi_query_with_error.sql",
            ),
        );
    }

    public function testMigrationProcessStopsAtFirstException()
    {
        $pdo = $this->getPDO();
        $tool = $this->getTool($pdo);
        $tool->setPathToDirectoryWithMigrationFiles(__DIR__ . "/SqlMigrationToolTest/MigrationProcessStopsAtFirstException");

        try {
            $tool->migrate();
            $this->fail();
        } catch (MigrationException $ignored) {
            // Ignored exception
        }

        /** @noinspection SqlDialectInspection, SqlNoDataSourceInspection */
        $rows = $pdo->query("SELECT v FROM t");
        foreach ($rows as $row) {
            $this->assertContains($row["v"], array(2, 3, 4, 5, 6));
        }

        /** @noinspection SqlDialectInspection, SqlNoDataSourceInspection */
        $rows = $pdo->query(sprintf("SELECT id FROM %s", self::TABLE_NAME));
        foreach ($rows as $row) {
            $this->assertContains($row["id"], array("2017-02-05.1", "2017-02-05.2"));
        }
    }
}
