<?php

namespace PetrKnap\Symfony\MarkdownWeb\Test;

require_once __DIR__ . '/../app/autoload.php';

class MarkdownWebTestCase extends \AppTestCase
{
    protected function invoke(array $callable, array $arguments = [])
    {
        $object = $callable[0];
        $method = $callable[1];

        $method = (new \ReflectionClass($object))->getMethod($method);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $arguments);
    }
}
