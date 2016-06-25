<?php

namespace PetrKnap\Php\MigrationTool\Test;

use PetrKnap\Php\MigrationTool\Exception\MismatchException;
use PetrKnap\Php\MigrationTool\Test\AbstractMigrationToolTest\AbstractMigrationToolMock;

class AbstractMigrationToolTest extends TestCase
{
    /**
     * @dataProvider dataMigrateWorks
     * @param array $appliedMigrations
     * @param \Exception $expectedException
     */
    public function testMigrateWorks($appliedMigrations, $expectedException = null)
    {
        $tool = new AbstractMigrationToolMock($appliedMigrations);

        if ($expectedException) {
            $this->setExpectedException(get_class($expectedException));
        }

        $tool->migrate();
    }

    public function dataMigrateWorks()
    {
        return array(
            array(array(), null),
            array(array("2016-06-22.1"), null),
            array(array("2016-06-22.2"), new MismatchException()),
            array(array("2016-06-22.1", "2016-06-22-2"), null)
        );
    }

    public function testGetMigrationFilesWorks()
    {
        $tool = new AbstractMigrationToolMock(array());

        $this->assertEquals(
            array(
                __DIR__ . "/AbstractMigrationToolTest/migrations/2016-06-22.1 - First migration.ext",
                __DIR__ . "/AbstractMigrationToolTest/migrations/2016-06-22.2 - Second migration.ext"
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
}
