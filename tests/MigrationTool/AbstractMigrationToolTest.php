<?php

namespace PetrKnap\Php\MigrationTool\Test;

use PetrKnap\Php\MigrationTool\AbstractMigrationTool;
use PetrKnap\Php\MigrationTool\Exception\MismatchException;
use PetrKnap\Php\MigrationTool\Test\AbstractMigrationToolTest\AbstractMigrationToolMock;
use Psr\Log\LoggerInterface;

class AbstractMigrationToolTest extends TestCase
{
    /**
     * @dataProvider dataMigrateWorks
     * @param array $appliedMigrations
     * @param \Exception $expectedException
     * @throws \Exception
     */
    public function testMigrateWorks($appliedMigrations, $expectedException = null)
    {
        $tool = new AbstractMigrationToolMock($appliedMigrations);

        if ($expectedException) {
            $this->setExpectedException(get_class($expectedException));
        }

        try {
            $tool->migrate();
        } catch (\Exception $e) {
            $this->assertStringMatchesFormat($expectedException->getMessage(), $e->getMessage());
            throw $e;
        }
    }

    public function dataMigrateWorks()
    {
        return array(
            array(array(), null),
            array(array("2016-06-22.1"), null),
            array(array("2016-06-22.1", "2016-06-22.2"), null),
            array(array("2016-06-22.1", "2016-06-22.2", "2016-06-22.3"), null),
            array(array("2016-06-22.2"), new MismatchException("%a/2016-06-22.1 - First migration.ext")),
            array(array("2016-06-22.3"), new MismatchException("%a/2016-06-22.1 - First migration.ext%a/2016-06-22.2 - Second migration.ext")),
            array(array("2016-06-22.1", "2016-06-22.3"), new MismatchException("%a/2016-06-22.2 - Second migration.ext")),
            array(array("2016-06-22.2", "2016-06-22.3"), new MismatchException("%a/2016-06-22.1 - First migration.ext")),
        );
    }

    public function testGetMigrationFilesWorks()
    {
        $tool = new AbstractMigrationToolMock(array());

        $this->assertEquals(
            array(
                __DIR__ . "/AbstractMigrationToolTest/migrations/2016-06-22.1 - First migration.ext",
                __DIR__ . "/AbstractMigrationToolTest/migrations/2016-06-22.2 - Second migration.ext",
                __DIR__ . "/AbstractMigrationToolTest/migrations/2016-06-22.3 - Third migration.ext",
            ),
            $this->invokeMethods($tool, array(array("getMigrationFiles")))
        );
    }

    /**
     * @dataProvider dataGetMigrationIdWorks
     * @param string $pathToMigrationFile
     * @param string $expectedMigrationId
     */
    public function testGetMigrationIdWorks($pathToMigrationFile, $expectedMigrationId)
    {
        $tool = new AbstractMigrationToolMock(array());

        $this->assertEquals(
            $expectedMigrationId,
            $this->invokeMethods($tool, array(array("getMigrationId", array($pathToMigrationFile))))
        );
    }

    public function dataGetMigrationIdWorks()
    {
        return array(
            array("/migration_file.ext", "migration_file"),
            array("/migration file.ext", "migration"),
            array("/migration_file", "migration_file"),
            array("/migration file", "migration")
        );
    }

    /**
     * @dataProvider dataLoggingWorks
     * @param array $appliedMigrations
     * @param array $expectedLog
     */
    public function testLoggingWorks(array $appliedMigrations, array $expectedLog)
    {
        $log = array();

        try {
            /** @var LoggerInterface $logger */
            $tool = new AbstractMigrationToolMock($appliedMigrations);
            $tool->setLogger($this->getLogger($log));
            $tool->migrate();
        } catch (\Exception $ignored) {
            // Ignored exception
        }

        $this->assertLogEquals($expectedLog, $log);
    }

    public function dataLoggingWorks()
    {
        return array(
            array(array(), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_UNSUPPORTED_FILE__PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN,
                    AbstractMigrationTool::MESSAGE__MIGRATION_FILE_APPLIED__PATH,
                    AbstractMigrationTool::MESSAGE__MIGRATION_FILE_APPLIED__PATH,
                    AbstractMigrationTool::MESSAGE__MIGRATION_FILE_APPLIED__PATH,
                    AbstractMigrationTool::MESSAGE__DONE,
                ),
            )),
            array(array("2016-06-22.1"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_UNSUPPORTED_FILE__PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN,
                    AbstractMigrationTool::MESSAGE__MIGRATION_FILE_APPLIED__PATH,
                    AbstractMigrationTool::MESSAGE__MIGRATION_FILE_APPLIED__PATH,
                    AbstractMigrationTool::MESSAGE__DONE,
                ),
            )),
            array(array("2016-06-22.1", "2016-06-22.2"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_UNSUPPORTED_FILE__PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN,
                    AbstractMigrationTool::MESSAGE__MIGRATION_FILE_APPLIED__PATH,
                    AbstractMigrationTool::MESSAGE__DONE,
                ),
            )),
            array(array("2016-06-22.1", "2016-06-22.2", "2016-06-22.3"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_UNSUPPORTED_FILE__PATH,
                    AbstractMigrationTool::MESSAGE__THERE_IS_NOTHING_TO_MIGRATE__PATH_PATTERN,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN,
                    AbstractMigrationTool::MESSAGE__DONE,
                ),
            )),
            array(array("2016-06-22.2"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_UNSUPPORTED_FILE__PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN,
                ),
                "critical" => array(
                    AbstractMigrationTool::MESSAGE__DETECTED_GAPE_BEFORE_MIGRATION__ID,
                )
            )),
            array(array("2016-06-22.3"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_UNSUPPORTED_FILE__PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN,
                ),
                "critical" => array(
                    AbstractMigrationTool::MESSAGE__DETECTED_GAPE_BEFORE_MIGRATION__ID,
                ),
            )),
            array(array("2016-06-22.1", "2016-06-22.3"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_UNSUPPORTED_FILE__PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN,
                ),
                "critical" => array(
                    AbstractMigrationTool::MESSAGE__DETECTED_GAPE_BEFORE_MIGRATION__ID,
                ),
            )),
            array(array("2016-06-22.2", "2016-06-22.3"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_UNSUPPORTED_FILE__PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE__FOUND_MIGRATION_FILES__COUNT_PATH_PATTERN,
                ),
                "critical" => array(
                    AbstractMigrationTool::MESSAGE__DETECTED_GAPE_BEFORE_MIGRATION__ID,
                ),
            )),
        );
    }
}
