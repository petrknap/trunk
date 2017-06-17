<?php

namespace PetrKnap\Symfony\MarkdownWeb\Test;

use Netpromotion\SymfonyUp\UpTestCase;

class TestCase extends UpTestCase
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
