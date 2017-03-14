<?php

namespace PetrKnap\Php\ServiceManager\Test;

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

    public function testGetConfigWorks()
    {
        $this->assertEquals([
            ConfigurationBuilder::SERVICES => [],
            ConfigurationBuilder::INVOKABLES => [],
            ConfigurationBuilder::FACTORIES => [],
            ConfigurationBuilder::SHARED => [],
            ConfigurationBuilder::SHARED_BY_DEFAULT => false
        ], $this->builder->getConfig());
    }

    public function testAddServiceWorks()
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

    public function testAddInvokableWorks()
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

    /**
     * @dataProvider dataAddInvokableWithInvalidArgumentWorks
     * @param mixed $invalidArgument
     */
    public function testAddInvokableWithInvalidArgumentWorks($invalidArgument)
    {
        $this->setExpectedException(self::CONFIGURATION_EXCEPTION);

        $this->builder->addInvokable("A", $invalidArgument);
    }

    public function dataAddInvokableWithInvalidArgumentWorks()
    {
        return [[null], [true], [0], ["string"], [new \stdClass()], [[]], [function() {}]];
    }

    public function testAddFactoryWorks()
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

    /**
     * @dataProvider dataAddFactoryWithInvalidArgumentWorks
     * @param mixed $invalidArgument
     */
    public function testAddFactoryWithInvalidArgumentWorks($invalidArgument)
    {
        $this->setExpectedException(self::CONFIGURATION_EXCEPTION);

        $this->builder->addFactory("A", $invalidArgument);
    }

    public function dataAddFactoryWithInvalidArgumentWorks()
    {
        return [[null], [true], [0], ["string"], [new \stdClass()], [[]]];
    }

    public function testSetSharedWorks()
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

    /**
     * @dataProvider dataSetSharedWithInvalidArgumentWorks
     * @param mixed $invalidArgument
     */
    public function testSetSharedWithInvalidArgumentWorks($invalidArgument)
    {
        $this->setExpectedException(self::CONFIGURATION_EXCEPTION);

        $this->builder->setShared("A", $invalidArgument);
    }

    public function dataSetSharedWithInvalidArgumentWorks()
    {
        return [[null], [0], ["string"], [new \stdClass()], [[]], [function() {}]];
    }

    /**
     * @dataProvider dataSetSharedByDefaultWorks
     * @param bool $isShared
     */
    public function testSetSharedByDefaultWorks($isShared)
    {
        $this->assertInstanceOf(get_class($this->builder), $this->builder->setSharedByDefault($isShared));

        $this->assertEquals($isShared, $this->builder->getConfig()[ConfigurationBuilder::SHARED_BY_DEFAULT]);
    }

    public function dataSetSharedByDefaultWorks()
    {
        return [[true], [false]];
    }

    /**
     * @dataProvider dataSetSharedByDefaultWithInvalidArgumentWorks
     * @param mixed $invalidArgument
     */
    public function testSetSharedByDefaultWithInvalidArgumentWorks($invalidArgument)
    {
        $this->setExpectedException(self::CONFIGURATION_EXCEPTION);

        $this->builder->setSharedByDefault($invalidArgument);
    }

    public function dataSetSharedByDefaultWithInvalidArgumentWorks()
    {
        return [[null], [0], ["string"], [new \stdClass()], [[]], [function() {}]];
    }
}
