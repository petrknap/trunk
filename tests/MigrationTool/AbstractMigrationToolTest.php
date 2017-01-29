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
     * @param array $expectedLogs
     */
    public function testLoggingWorks(array $appliedMigrations, array $expectedLogs)
    {
        $logs = array(
            "debug" => array(),
            "critical" => array(),
        );

        $logger = $this->getMock("Psr\\Log\\LoggerInterface");
        $logger->expects($this->any())
            ->method("debug")
            ->willReturnCallback(function ($message) use (&$logs) {
                $logs["debug"][] = $message;
            });
        $logger->expects($this->any())
            ->method("critical")
            ->willReturnCallback(function ($message) use (&$logs) {
                $logs["critical"][] = $message;
            });

        try {
            /** @var LoggerInterface $logger */
            $tool = new AbstractMigrationToolMock($appliedMigrations);
            $tool->setLogger($logger);
            $tool->migrate();
        } catch (\Exception $ignored) {
            // Ignored exception
        }

        foreach ($expectedLogs as $key => $messages) {
            $this->assertCount(count($messages), $logs[$key]);
            foreach ($messages as $message) {
                $this->assertStringMatchesFormat(str_replace("%s", "%a", $message), array_shift($logs[$key]));
            }
        }
    }

    public function dataLoggingWorks()
    {
        return array(
            array(array(), array(
                "debug" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_N_MIGRATION_FILES,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_APPLYING_MIGRATION_FROM_FILE,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_APPLYING_MIGRATION_FROM_FILE,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_APPLYING_MIGRATION_FROM_FILE,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                ),
            )),
            array(array("2016-06-22.1"), array(
                "debug" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_N_MIGRATION_FILES,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_APPLYING_MIGRATION_FROM_FILE,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_APPLYING_MIGRATION_FROM_FILE,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                ),
            )),
            array(array("2016-06-22.1", "2016-06-22.2"), array(
                "debug" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_N_MIGRATION_FILES,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_APPLYING_MIGRATION_FROM_FILE,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                ),
            )),
            array(array("2016-06-22.1", "2016-06-22.2", "2016-06-22.3"), array(
                "debug" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_N_MIGRATION_FILES,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                ),
            )),
            array(array("2016-06-22.2"), array(
                "debug" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_N_MIGRATION_FILES,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                ),
                "critical" => array(
                    AbstractMigrationTool::MESSAGE_DETECTED_GAPE_BEFORE_MIGRATION,
                )
            )),
            array(array("2016-06-22.3"), array(
                "debug" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_N_MIGRATION_FILES,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                ),
                "critical" => array(
                    AbstractMigrationTool::MESSAGE_DETECTED_GAPE_BEFORE_MIGRATION,
                ),
            )),
            array(array("2016-06-22.1", "2016-06-22.3"), array(
                "debug" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_N_MIGRATION_FILES,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                ),
                "critical" => array(
                    AbstractMigrationTool::MESSAGE_DETECTED_GAPE_BEFORE_MIGRATION,
                ),
            )),
            array(array("2016-06-22.2", "2016-06-22.3"), array(
                "debug" => array(
                    AbstractMigrationTool::MESSAGE_FOUND_N_MIGRATION_FILES,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                    AbstractMigrationTool::MESSAGE_MIGRATION_ID_FOR_FILE_IS_ID,
                ),
                "critical" => array(
                    AbstractMigrationTool::MESSAGE_DETECTED_GAPE_BEFORE_MIGRATION,
                ),
            )),
        );
    }
}
