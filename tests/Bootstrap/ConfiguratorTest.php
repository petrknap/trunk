<?php

namespace PetrKnap\Nette\Bootstrap\Test;

use PetrKnap\Nette\Bootstrap\Configurator;

class ConfiguratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataConfiguratorCanOverrideDefaultParameters
     * @param string $name
     * @param string $value
     */
    public function testConfiguratorCanOverrideDefaultParameters($name, $value)
    {
        $configurator = new Configurator(array($name => $value));
        $configurator->setTempDirectory(__DIR__ . "/ConfiguratorTest/tmp");

        $parameters = $configurator->createContainer()->getParameters();

        $this->assertEquals($value, $parameters[$name]);
    }

    public function dataConfiguratorCanOverrideDefaultParameters()
    {
        return array(
            array("appDir", __DIR__),
            array("consoleMode", false)
        );
    }
}
