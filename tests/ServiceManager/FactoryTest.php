<?php

namespace PetrKnap\Test\Php\ServiceManager;

use Interop\Container\ContainerInterface;
use PetrKnap\Php\ServiceManager\ConfigBuilder;
use PetrKnap\Php\ServiceManager\FactoryInterface;
use PetrKnap\Php\ServiceManager\ServiceManager;

/**
 * @runTestsInSeparateProcesses
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCallable()
    {
        ServiceManager::setConfig([
            ConfigBuilder::FACTORIES => [
                FactoryTestMock::getClass() => function(ContainerInterface $serviceLocator) {
                    return new FactoryTestMock();
                }
            ]
        ]);

        $this->assertInstanceOf(
            FactoryTestMock::getClass(),
            ServiceManager::getInstance()->get(FactoryTestMock::getClass())
        );
    }

    public function testClassName()
    {
        ServiceManager::setConfig([
            ConfigBuilder::FACTORIES => [
                FactoryTestMock::getClass() => FactoryTestMockFactory::getClass()
            ]
        ]);

        $this->assertInstanceOf(
            FactoryTestMock::getClass(),
            ServiceManager::getInstance()->get(FactoryTestMock::getClass())
        );
    }
}

class FactoryTestMock
{
    public static function getClass()
    {
        return __CLASS__;
    }
}

class FactoryTestMockFactory implements FactoryInterface
{
    public static function getClass()
    {
        return __CLASS__;
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new FactoryTestMock();
    }
}
