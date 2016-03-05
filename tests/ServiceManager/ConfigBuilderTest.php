<?php

namespace PetrKnap\Test\Php\ServiceManager;

use PetrKnap\Php\ServiceManager\ConfigBuilder;

class ConfigBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigBuilder
     */
    private $builder;

    public function setUp()
    {
        $this->builder = new ConfigBuilder();
    }

    public function testGetConfig()
    {
        $this->assertEquals([
            ConfigBuilder::SERVICES => [],
            ConfigBuilder::INVOKABLES => [],
            ConfigBuilder::FACTORIES => [],
            ConfigBuilder::SHARED => [],
            ConfigBuilder::SHARED_BY_DEFAULT => false
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

        $this->assertEquals($expectedServices, $this->builder->getConfig()[ConfigBuilder::SERVICES]);
    }

    public function testAddInvokable()
    {
        $expectedServices = [
            "A" => "Class name A",
            "B" => "Class name B"
        ];

        foreach ($expectedServices as $name => $className) {
            $this->assertInstanceOf(get_class($this->builder), $this->builder->addInvokable($name, $className));
        }

        $this->assertEquals($expectedServices, $this->builder->getConfig()[ConfigBuilder::INVOKABLES]);
    }

    public function addInvokable_InvalidArgumentDataProvider()
    {
        return [[null], [true], [0], [new \stdClass()], [[]], [function() {}]];
    }

    /**
     * @dataProvider addInvokable_InvalidArgumentDataProvider
     *
     * @param mixed $invalidArgument
     */
    public function testAddInvokable_InvalidArgument($invalidArgument)
    {
        $this->setExpectedException("InvalidArgumentException");

        $this->builder->AddInvokable("A", $invalidArgument);
    }

    public function testAddFactory()
    {
        $expectedServices = [
            "A" => "Factory A",
            "B" => "Factory B"
        ];

        foreach ($expectedServices as $name => $factory) {
            $this->assertInstanceOf(get_class($this->builder), $this->builder->addFactory($name, $factory));
        }

        $this->assertEquals($expectedServices, $this->builder->getConfig()[ConfigBuilder::FACTORIES]);
    }

    public function addFactory_InvalidArgumentDataProvider()
    {
        return [[null], [true], [0], [new \stdClass()], [[]]];
    }

    /**
     * @dataProvider addFactory_InvalidArgumentDataProvider
     * @param mixed $invalidArgument
     */
    public function testAddFactory_InvalidArgument($invalidArgument)
    {
        $this->setExpectedException("InvalidArgumentException");

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

        $this->assertEquals($expectedServices, $this->builder->getConfig()[ConfigBuilder::SHARED]);
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
        $this->setExpectedException("InvalidArgumentException");

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

        $this->assertEquals($isShared, $this->builder->getConfig()[ConfigBuilder::SHARED_BY_DEFAULT]);
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
        $this->setExpectedException("InvalidArgumentException");

        $this->builder->setSharedByDefault($invalidArgument);
    }
}
