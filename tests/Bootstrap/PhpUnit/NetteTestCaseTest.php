<?php

namespace PetrKnap\Nette\Bootstrap\Test\PhpUnit;

use PetrKnap\Nette\Bootstrap\Test\PhpUnit\NetteTestCaseTest\NetteTestCase;

class NetteTestCaseTest extends \PHPUnit_Framework_TestCase
{
    public function testMethodGetContainerWorks()
    {
        NetteTestCase::setUpBeforeClass();
        $testCase = new NetteTestCase();

        $container = $testCase->getContainer();

        $this->assertInstanceOf("Nette\\DI\\Container", $container);
    }
}
