<?php

namespace PetrKnap\Nette\Bootstrap\Test;

use PetrKnap\Nette\Bootstrap\Test\BootstrapTest\Bootstrap;

class BootstrapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testMethodGetContainerReturnsContainer()
    {
        $this->assertInstanceOf("Nette\\DI\\Container", Bootstrap::getContainer());
    }

    /**
     * @dataProvider dataMethodGetConfiguratorSetsCorrectParameters
     * @param string $name
     * @param mixed $expectedValue
     * @runInSeparateProcess
     */
    public function testMethodGetConfiguratorSetsCorrectParameters($name, $expectedValue)
    {
        $parameters = Bootstrap::getContainer()->getParameters();
        $this->assertEquals($expectedValue, $parameters[$name]);
    }

    public function dataMethodGetConfiguratorSetsCorrectParameters()
    {
        $bootstrap = @new Bootstrap();
        return array(
            array("appDir", $bootstrap->getAppDir()),
            array("tempDir", __DIR__ . "/BootstrapTest/tmp"),
            array("debugMode", $bootstrap->getDebugMode()),
            array("productionMode", !$bootstrap->getDebugMode()),
            array("key", "second value")
        );
    }

    public function testMethodGetContainerSetsOptions()
    {
        $parameters = Bootstrap::getContainer(array(Bootstrap::OPTION_IS_TEST_RUN => true))->getParameters();
        $this->assertTrue($parameters["debugMode"]);
    }
}
