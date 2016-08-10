<?php

namespace PetrKnap\Php\Enum\Test;

use PetrKnap\Php\Enum\Exception\EnumNotFoundException;
use PetrKnap\Php\Enum\Test\EnumTest\MyBoolean;

class EnumTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataCallStaticsWorks
     * @param string $name
     * @param mixed $expectedValue
     */
    public function testCallStaticsWorks($name, $expectedValue)
    {
        if ($expectedValue instanceof \Exception) {
            $this->setExpectedException(get_class($expectedValue));
        }

        $this->assertSame($expectedValue, MyBoolean::__callStatic($name, [])->getValue());
    }

    public function dataCallStaticsWorks()
    {
        return [
            ["MY_TRUE", 1],
            ["MY_FALSE", 2],
            ["MY_NULL", new EnumNotFoundException()]
        ];
    }

    /**
     * @dataProvider dataFindByValueWorks
     * @param mixed $value
     * @param mixed $expectedEnum
     */
    public function testFindByValueWorks($value, $expectedEnum)
    {
        if ($expectedEnum instanceof \Exception) {
            $this->setExpectedException(get_class($expectedEnum));
        }

        $this->assertSame($expectedEnum, MyBoolean::findByValue($value));
    }

    public function dataFindByValueWorks()
    {
        return [
            [1, MyBoolean::MY_TRUE()],
            [2, MyBoolean::MY_FALSE()],
            [3, new EnumNotFoundException()]
        ];
    }

    public function testComparableWorks()
    {
        $this->assertSame(MyBoolean::MY_TRUE(), MyBoolean::MY_TRUE());
        $this->assertNotSame(MyBoolean::MY_TRUE(), MyBoolean::MY_FALSE());

        $this->assertTrue(MyBoolean::MY_TRUE() == MyBoolean::MY_TRUE());
        $this->assertFalse(MyBoolean::MY_TRUE() == MyBoolean::MY_FALSE());
    }

    public function testGetMembersWorks()
    {
        $this->assertEquals([
            "MY_TRUE" => 1,
            "MY_FALSE" => 2
        ], MyBoolean::getMembers());
    }
}
