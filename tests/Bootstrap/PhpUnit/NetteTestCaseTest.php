<?php

namespace PetrKnap\Nette\Bootstrap\Test\PhpUnit;

use Nette\Application\Responses\JsonResponse;
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

    /**
     * @dataProvider dataRunPresenterWorks
     * @param array $params
     * @param array $post
     * @param array $expected
     */
    public function testRunPresenterWorks(array $params, array $post, array $expected)
    {
        NetteTestCase::setUpBeforeClass();
        $testCase = new NetteTestCase();

        $classReflection = new \ReflectionClass($testCase);
        $methodReflection = $classReflection->getMethod("runPresenter");
        $methodReflection->setAccessible(true);
        $response = $methodReflection->invoke($testCase, "Test", "test", $params, $post);

        /** @var JsonResponse $response */
        $expected["parameters"] = array_merge($expected["parameters"], array("action" => "test"));
        $this->assertInstanceOf("Nette\\Application\\Responses\\JsonResponse", $response);
        $this->assertEquals($expected, $response->getPayload());
    }

    public function dataRunPresenterWorks()
    {
        return array(
            "default" => array(array(), array(), array(
                "parameters" => array(),
                "post" => array(),
                "files" => array()
            )),
            "parameters" => array(array("key" => "value"), array(), array(
                "parameters" => array("key" => "value"),
                "post" => array(),
                "files" => array()
            )),
            "post" => array(array(), array("key" => "value"), array(
                "parameters" => array(),
                "post" => array("key" => "value"),
                "files" => array()
            )),
            "parameters + post" => array(array("key1" => "value"), array("key2" => "value"), array(
                "parameters" => array("key1" => "value"),
                "post" => array("key2" => "value"),
                "files" => array()
            ))
        );
    }
}
