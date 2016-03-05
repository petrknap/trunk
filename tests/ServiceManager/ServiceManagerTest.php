<?php

namespace PetrKnap\Test\Php\ServiceManager;

use PetrKnap\Php\ServiceManager\ConfigBuilder;
use PetrKnap\Php\ServiceManager\ServiceManager;

/**
 * @runTestsInSeparateProcesses
 */
class ServiceManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesValidInstance()
    {
        $instance = ServiceManager::getInstance();

        $this->assertInstanceOf("Interop\\Container\\ContainerInterface", $instance);
        $this->assertInstanceOf("PetrKnap\\Php\\ServiceManager\\ServiceManager", $instance);
    }

    public function setAddGetConfigWorksDataProvider()
    {
        return [
            [[], ["A" => ["B" => "C"]], true, ["A" => ["B" => "C"]]],
            [[], ["A" => ["B" => "C"]], false, ["A" => ["B" => "C"]]],
            [["A" => ["B" => "C"]], ["A" => ["B" => "D"]], true, ["A" => ["B" => "D"]]],
            [["A" => ["B" => "C"]], ["A" => ["B" => "D"]], false, ["A" => ["B" => "D"]]],
            [["A" => ["B" => "C"]], ["A" => ["D" => "E"]], true, ["A" => ["D" => "E"]]],
            [["A" => ["B" => "C"]], ["A" => ["D" => "E"]], false, ["A" => ["B" => "C", "D" => "E"]]]
        ];
    }

    /**
     * @dataProvider setAddGetConfigWorksDataProvider
     *
     * @param array $initialConfig
     * @param array $config
     * @param bool $override
     * @param array $expectedConfig
     */
    public function testSetAddGetConfigWorks(array $initialConfig, array $config, $override, array $expectedConfig)
    {
        ServiceManager::setConfig($initialConfig);
        if ($override) {
            ServiceManager::setConfig($config);
        } else {
            ServiceManager::addConfig($config);
        }

        $this->assertEquals($expectedConfig, ServiceManager::getConfig());
    }

    public function testGetWorks()
    {
        ServiceManager::setConfig([ConfigBuilder::SERVICES => ["this" => $this]]);

        $this->assertInstanceOf(get_class($this), ServiceManager::getInstance()->get("this"));
    }

    public function testHasWorks()
    {
        ServiceManager::setConfig([ConfigBuilder::SERVICES => ["this" => $this]]);

        $this->assertTrue(ServiceManager::getInstance()->has("this"));
    }
}
