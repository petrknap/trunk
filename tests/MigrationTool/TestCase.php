<?php

namespace PetrKnap\Php\MigrationTool\Test;

use Psr\Log\LoggerInterface;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param object $object
     * @param array $invokes
     * @param bool $returnAllReturnsAsArray
     * @return array|mixed
     */
    protected function invokeMethods($object, array $invokes, $returnAllReturnsAsArray = false)
    {
        $reflectionClass = new \ReflectionClass($object);

        $returns = array();
        foreach ($invokes as $invoke) {
            $methodName = $invoke[0];
            $arguments = (array)@$invoke[1];
            $methodReflection = $reflectionClass->getMethod($methodName);
            $methodReflection->setAccessible(true);

            $returns[] = $methodReflection->invokeArgs($object, $arguments);
        }

        if (true === $returnAllReturnsAsArray) {
            return $returns;
        } else {
            return array_pop($returns);
        }
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
                $this->assertEquals($message, array_shift($actual[$key]));
            }
        }
    }

    protected function getFormatForMessage($message)
    {
        return preg_replace('/\{[^\{]*\}/', "%a", $message);
    }
}
