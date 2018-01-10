<?php

namespace PetrKnapCz\Test {

    use PetrKnap\Php\MigrationTool\SqlMigrationTool;
    use const PetrKnapCz\CONTAINER_TEST_FLAG;
    use PHPUnit\Framework\TestCase as PHPUnit_TestCase;

    \PetrKnapCz\container([CONTAINER_TEST_FLAG => true]);

    abstract class TestCase extends PHPUnit_TestCase
    {
        protected function setUp()
        {
            parent::setUp();

            @$this->get(SqlMigrationTool::class)->migrate();
        }

        protected function get($id)
        {
            return \PetrKnapCz\container()->get($id);
        }
    }
}

namespace Symfony\Component\HttpFoundation {
    function time()
    {
        return 0;
    }
}
