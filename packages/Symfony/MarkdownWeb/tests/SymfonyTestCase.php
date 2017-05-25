<?php

namespace PetrKnap\Symfony\MarkdownWeb\Test;

use PetrKnap\Symfony\MarkdownWeb\MarkdownWebKernel;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Configuration;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class SymfonyTestCase extends WebTestCase
{
    const KERNEL_CLASS = MarkdownWebKernel::class;

    protected static function getKernelClass()
    {
        return static::KERNEL_CLASS;
    }

    public function getKernel()
    {
        if (!self::$kernel || !self::$kernel->getContainer()) {
            self::bootKernel();
        }
        return self::$kernel;
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
