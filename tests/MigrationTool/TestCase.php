<?php

namespace PetrKnap\Php\MigrationTool\Test;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function invokeMethod($object, $methodName, array $arguments = array())
    {
        $reflectionClass = new \ReflectionClass($object);

        $methodReflection = $reflectionClass->getMethod($methodName);
        $methodReflection->setAccessible(true);

        return $methodReflection->invokeArgs($object, $arguments);
    }
}
