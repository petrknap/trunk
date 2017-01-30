<?php

namespace PetrKnap\Php\MigrationTool\Test;

use Psr\Log\LoggerInterface;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function invokeMethod($object, $methodName, array $arguments = array())
    {
        $reflectionClass = new \ReflectionClass($object);

        $methodReflection = $reflectionClass->getMethod($methodName);
        $methodReflection->setAccessible(true);

        return $methodReflection->invokeArgs($object, $arguments);
    }

    /**
     * @param array $log
     * @return LoggerInterface
     */
    protected function getLogger(array &$log)
    {
        $interface = "Psr\\Log\\LoggerInterface";
        $logger = $this->getMock($interface);
        foreach (array_filter(get_class_methods($interface), function ($method) {return "_" != $method[0];}) as $method) {
            $logger->expects($this->any())->method($method)->willReturnCallback(function ($message)  use ($method, &$log) {
                $l = &$log[$method];
                if (!$l) {
                    $l = array();
                }
                $l[] = $message;
            });
        }

        /** @var LoggerInterface $logger */
        return $logger;
    }

    protected function assertLogEquals(array $expected, array $actual)
    {
        $this->assertEquals(array_keys($expected), array_keys($actual));
        foreach ($expected as $key => $messages) {
            $this->assertCount(count($messages), $actual[$key]);
            foreach ($messages as $message) {
                $this->assertStringMatchesFormat(
                    str_replace("%s", "%a", $message),
                    array_shift($actual[$key])
                );
            }
        }
    }
}
