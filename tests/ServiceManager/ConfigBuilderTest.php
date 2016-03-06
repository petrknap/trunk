<?php

namespace PetrKnap\Test\Php\ServiceManager;

use PetrKnap\Php\ServiceManager\ConfigurationBuilder;

class ConfigBuilderTest extends \PHPUnit_Framework_TestCase
{
    const CONFIGURATION_EXCEPTION = "PetrKnap\\Php\\ServiceManager\\Exception\\ConfigurationException";

    /**
     * @var ConfigurationBuilder
     */
    private $builder;

    public function setUp()
    {
        $this->builder = new ConfigurationBuilder();
    }

    public function testGetConfig()
    {
        $this->assertEquals([
            ConfigurationBuilder::SERVICES => [],
            ConfigurationBuilder::INVOKABLES => [],
            ConfigurationBuilder::FACTORIES => [],
            ConfigurationBuilder::SHARED => [],
            ConfigurationBuilder::SHARED_BY_DEFAULT => false
        ], $this->builder->getConfig());
    }

    public function testAddService()
    {
        $expectedServices = [
            "A" => "Instance A",
            "B" => "Instance B"
        ];

        foreach ($expectedServices as $name => $instance) {
            $this->assertInstanceOf(get_class($this->builder), $this->builder->addService($name, $instance));
        }

        $this->assertEquals($expectedServices, $this->builder->getConfig()[ConfigurationBuilder::SERVICES]);
    }

    public function testAddInvokable()
    {
        $expectedServices = [
            "A" => "stdClass",
            "B" => get_class($this)
        ];

        foreach ($expectedServices as $name => $className) {
            $this->assertInstanceOf(get_class($this->builder), $this->builder->addInvokable($name, $className));
        }

        $this->assertEquals($expectedServices, $this->builder->getConfig()[ConfigurationBuilder::INVOKABLES]);
    }

    public function addInvokable_InvalidArgumentDataProvider()
    {
        return [[null], [true], [0], ["string"], [new \stdClass()], [[]], [function() {}]];
    }

    /**
     * @dataProvider addInvokable_InvalidArgumentDataProvider
     *
     * @param mixed $invalidArgument
     */
    public function testAddInvokable_InvalidArgument($invalidArgument)
    {
        $this->setExpectedException(self::CONFIGURATION_EXCEPTION);

        $this->builder->AddInvokable("A", $invalidArgument);
    }

    public function testAddFactory()
    {
        $expectedServices = [
            "A" => "stdClass",
            "B" => get_class($this)
        ];

        foreach ($expectedServices as $name => $factory) {
            $this->assertInstanceOf(get_class($this->builder), $this->builder->addFactory($name, $factory));
        }

        $this->assertEquals($expectedServices, $this->builder->getConfig()[ConfigurationBuilder::FACTORIES]);
    }

    public function addFactory_InvalidArgumentDataProvider()
    {
        return [[null], [true], [0], ["string"], [new \stdClass()], [[]]];
    }

    /**
     * @dataProvider addFactory_InvalidArgumentDataProvider
     * @param mixed $invalidArgument
     */
    public function testAddFactory_InvalidArgument($invalidArgument)
    {
        $this->setExpectedException(self::CONFIGURATION_EXCEPTION);

        $this->builder->addFactory("A", $invalidArgument);
    }

    public function testSetShared()
    {
        $expectedServices = [
            "A" => true,
            "B" => false
        ];

        foreach ($expectedServices as $name => $isShared) {
            $this->assertInstanceOf(get_class($this->builder), $this->builder->setShared($name, $isShared));
        }

        $this->assertEquals($expectedServices, $this->builder->getConfig()[ConfigurationBuilder::SHARED]);
    }

    public function setShared_InvalidArgumentDataProvider()
    {
        return [[null], [0], ["string"], [new \stdClass()], [[]], [function() {}]];
    }

    /**
     * @dataProvider setShared_InvalidArgumentDataProvider
     *
     * @param mixed $invalidArgument
     */
    public function testSetShared_InvalidArgument($invalidArgument)
    {
        $this->setExpectedException(self::CONFIGURATION_EXCEPTION);

        $this->builder->setShared("A", $invalidArgument);
    }

    public function setSharedByDefaultDataProvider()
    {
        return [[true], [false]];
    }

    /**
     * @dataProvider setSharedByDefaultDataProvider
     *
     * @param $isShared
     */
    public function testSetSharedByDefault($isShared)
    {
        $this->assertInstanceOf(get_class($this->builder), $this->builder->setSharedByDefault($isShared));

        $this->assertEquals($isShared, $this->builder->getConfig()[ConfigurationBuilder::SHARED_BY_DEFAULT]);
    }

    public function setSharedByDefault_InvalidArgumentDataProvider()
    {
        return [[null], [0], ["string"], [new \stdClass()], [[]], [function() {}]];
    }

    /**
     * @dataProvider setSharedByDefault_InvalidArgumentDataProvider
     *
     * @param mixed $invalidArgument
     */
    public function testSetSharedByDefault_InvalidArgument($invalidArgument)
    {
        $this->setExpectedException(self::CONFIGURATION_EXCEPTION);

        $this->builder->setSharedByDefault($invalidArgument);
    }
}
