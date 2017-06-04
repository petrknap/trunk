<?php

namespace PetrKnap\Symfony\MarkdownWeb\Test;

use Netpromotion\SymfonyUp\AppTestCase;

class TestCase extends AppTestCase
{
    protected static function getKernelClass()
    {
        return TestKernel::class;
    }

    protected function invoke(array $callable, array $arguments = [])
    {
        $object = $callable[0];
        $method = $callable[1];

        $method = (new \ReflectionClass($object))->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $arguments);
    }
}
