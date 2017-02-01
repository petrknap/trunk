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
            $this->invokeMethod($tool, "getMigrationFiles")
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
            $this->invokeMethod($tool, "getMigrationId", array($pathToMigrationFile))
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
                    AbstractMigrationTool::MESSAGE_FOUND_UNSUPPORTED_FILE_PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_MIGRATION_FILES_COUNT,
                    AbstractMigrationTool::MESSAGE_MIGRATION_FILE_APPLIED_PATH,
                    AbstractMigrationTool::MESSAGE_MIGRATION_FILE_APPLIED_PATH,
                    AbstractMigrationTool::MESSAGE_MIGRATION_FILE_APPLIED_PATH,
                ),
            )),
            array(array("2016-06-22.1"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_UNSUPPORTED_FILE_PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_MIGRATION_FILES_COUNT,
                    AbstractMigrationTool::MESSAGE_MIGRATION_FILE_APPLIED_PATH,
                    AbstractMigrationTool::MESSAGE_MIGRATION_FILE_APPLIED_PATH,
                ),
            )),
            array(array("2016-06-22.1", "2016-06-22.2"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_UNSUPPORTED_FILE_PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_MIGRATION_FILES_COUNT,
                    AbstractMigrationTool::MESSAGE_MIGRATION_FILE_APPLIED_PATH,
                ),
            )),
            array(array("2016-06-22.1", "2016-06-22.2", "2016-06-22.3"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_UNSUPPORTED_FILE_PATH,
                    AbstractMigrationTool::MESSAGE_THERE_IS_NOTHING_TO_MIGRATE_CLASS,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_MIGRATION_FILES_COUNT,
                ),
            )),
            array(array("2016-06-22.2"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_UNSUPPORTED_FILE_PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_MIGRATION_FILES_COUNT,
                ),
                "critical" => array(
                    AbstractMigrationTool::MESSAGE_DETECTED_GAPE_BEFORE_MIGRATION_ID,
                )
            )),
            array(array("2016-06-22.3"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_UNSUPPORTED_FILE_PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_MIGRATION_FILES_COUNT,
                ),
                "critical" => array(
                    AbstractMigrationTool::MESSAGE_DETECTED_GAPE_BEFORE_MIGRATION_ID,
                ),
            )),
            array(array("2016-06-22.1", "2016-06-22.3"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_UNSUPPORTED_FILE_PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_MIGRATION_FILES_COUNT,
                ),
                "critical" => array(
                    AbstractMigrationTool::MESSAGE_DETECTED_GAPE_BEFORE_MIGRATION_ID,
                ),
            )),
            array(array("2016-06-22.2", "2016-06-22.3"), array(
                "notice" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_UNSUPPORTED_FILE_PATH,
                ),
                "info" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_MIGRATION_FILES_COUNT,
                ),
                "critical" => array(
                    AbstractMigrationTool::MESSAGE_DETECTED_GAPE_BEFORE_MIGRATION_ID,
                ),
            )),
        );
    }
}
