<?php

namespace PetrKnap\Test\Php\ServiceManager;

use PetrKnap\Php\ServiceManager\ConfigurationBuilder;
use PetrKnap\Php\ServiceManager\Exception\ServiceLocatorException;
use PetrKnap\Php\ServiceManager\Exception\ServiceNotCreatedException;
use PetrKnap\Php\ServiceManager\Exception\ServiceNotFoundException;
use PetrKnap\Php\ServiceManager\ServiceManager;
use PetrKnap\Test\Php\ServiceManager\ServiceManagerTest\DefectiveService;
use PetrKnap\Test\Php\ServiceManager\ServiceManagerTest\DependentService;
use PetrKnap\Test\Php\ServiceManager\ServiceManagerTest\DependentServiceFactory;
use PetrKnap\Test\Php\ServiceManager\ServiceManagerTest\IndependentService;

/**
 * @runTestsInSeparateProcesses
 */
class ServiceManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesValidInstance()
    {
        $instance = ServiceManager::getInstance();

        $this->assertInstanceOf("PetrKnap\\Php\\ServiceManager\\ServiceLocatorInterface", $instance);
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
            @ServiceManager::setConfig($config);
        } else {
            @ServiceManager::addConfig($config);
        }

        $this->assertEquals($expectedConfig, ServiceManager::getConfig());
    }

    public function getWorksDataProvider()
    {
        return [
            ["UnknownService", "", new ServiceNotFoundException()],
            ["DefectiveService", "", new ServiceNotCreatedException()],
            ["StandardService", "stdClass"],
            ["IndependentService", IndependentService::getClass()],
            ["StandardServiceCreatedByFactory", "stdClass"],
            ["DependentService", DependentService::getClass()],
            ["ServiceWithNonExistentFactory", "", new ServiceNotCreatedException()],
            ["ServiceWithUnsupportedFactory", "", new ServiceNotCreatedException()],
        ];
    }

    /**
     * @dataProvider getWorksDataProvider
     *
     * @param string $serviceName
     * @param string $expectedClass
     * @param ServiceLocatorException $expectedException
     */
    public function testGetWorks($serviceName, $expectedClass, $expectedException = null)
    {
        ServiceManager::setConfig(
            [
                ConfigurationBuilder::SERVICES => [
                    "StandardService" => new \stdClass()
                ],
                ConfigurationBuilder::INVOKABLES => [
                    "DefectiveService" => DefectiveService::getClass(),
                    "IndependentService" => IndependentService::getClass()
                ],
                ConfigurationBuilder::FACTORIES => [
                    "StandardServiceCreatedByFactory" => function () {
                        return new \stdClass();
                    },
                    "ServiceWithNonExistentFactory" => "ServiceWithNonExistentFactory",
                    "ServiceWithUnsupportedFactory" => IndependentService::getClass()
                ]
            ]
        );
        ServiceManager::addConfig(DependentServiceFactory::getConfig());

        if ($expectedException !== null) {
            $this->setExpectedException(get_class($expectedException));
        }

        $service = ServiceManager::getInstance()->get($serviceName);

        $this->assertInstanceOf($expectedClass, $service);
    }

    public function testGetWorks_SharedService()
    {
        ServiceManager::setConfig(
            [
                ConfigurationBuilder::INVOKABLES => [
                    "StandardService" => IndependentService::getClass(),
                    "SharedService" => IndependentService::getClass()
                ],
                ConfigurationBuilder::SHARED => [
                    "SharedService" => true
                ],
                ConfigurationBuilder::SHARED_BY_DEFAULT => false
            ]
        );

        $a = ServiceManager::getInstance()->get("SharedService");
        $b = ServiceManager::getInstance()->get("SharedService");

        $this->assertSame($a, $b);

        $c = ServiceManager::getInstance()->get("StandardService");
        $d = ServiceManager::getInstance()->get("StandardService");

        $this->assertNotSame($c, $d);
    }

    public function hasWorksDataProvider()
    {
        return [
            [[ConfigurationBuilder::SERVICES => ["A" => "B"]], "A", true],
            [[ConfigurationBuilder::SERVICES => ["A" => "B"]], "B", false],
            [[ConfigurationBuilder::INVOKABLES => ["A" => "B"]], "A", true],
            [[ConfigurationBuilder::INVOKABLES => ["A" => "B"]], "B", false],
            [[ConfigurationBuilder::FACTORIES => ["A" => "B"]], "A", true],
            [[ConfigurationBuilder::FACTORIES => ["A" => "B"]], "B", false]
        ];
    }

    /**
     * @dataProvider hasWorksDataProvider
     *
     * @param array $config
     * @param string $serviceName
     * @param bool $expectedOutput
     */
    public function testHasWorks(array $config, $serviceName, $expectedOutput)
    {
        ServiceManager::setConfig($config);

        $this->assertEquals($expectedOutput, ServiceManager::getInstance()->has($serviceName));
    }
}
