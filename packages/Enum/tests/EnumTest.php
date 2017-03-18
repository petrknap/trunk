<?php

namespace PetrKnap\Php\Enum\Test;

use PetrKnap\Php\Enum\Enum;
use PetrKnap\Php\Enum\Exception\EnumNotFoundException;
use PetrKnap\Php\Enum\Test\EnumTest\MixedValues;
use PetrKnap\Php\Enum\Test\EnumTest\MyBoolean;

class EnumTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataCallStaticsWorks
     * @param string $name
     * @param mixed $expectedValue
     * @param string $expectedException
     */
    public function testCallStaticsWorks($name, $expectedValue, $expectedException = null)
    {
        if ($expectedException) {
            $this->setExpectedException($expectedException);
        }

        $this->assertSame($expectedValue, MyBoolean::__callStatic($name, [])->getValue());
    }

    public function dataCallStaticsWorks()
    {
        return [
            ["MY_TRUE", 1],
            ["MY_FALSE", 2],
            ["MY_NULL", null, EnumNotFoundException::class],
        ];
    }

    /**
     * @dataProvider dataGetEnumByValueWorks
     * @param mixed $value
     * @param Enum $expectedEnum
     * @param string $expectedException
     */
    public function testGetEnumByValueWorks($value, $expectedEnum, $expectedException = null)
    {
        if ($expectedException) {
            $this->setExpectedException($expectedException);
        }

        $this->assertSame($expectedEnum, MyBoolean::getEnumByValue($value));
    }

    public function dataGetEnumByValueWorks()
    {
        return [
            [1, MyBoolean::MY_TRUE()],
            [2, MyBoolean::MY_FALSE()],
            [3, null, EnumNotFoundException::class],
        ];
    }

    public function testComparableWorks()
    {
        $this->assertTrue(MyBoolean::MY_TRUE() == MyBoolean::MY_TRUE());
        $this->assertFalse(MyBoolean::MY_TRUE() == MyBoolean::MY_FALSE());

        $this->assertTrue(MyBoolean::MY_TRUE() === MyBoolean::MY_TRUE());
        $this->assertFalse(MyBoolean::MY_TRUE() === MyBoolean::MY_FALSE());

        $this->assertSame(MyBoolean::MY_TRUE(), MyBoolean::MY_TRUE());
        $this->assertNotSame(MyBoolean::MY_TRUE(), MyBoolean::MY_FALSE());
    }

    public function testGetMembersWorks()
    {
        $this->assertEquals([
            "MY_TRUE" => 1,
            "MY_FALSE" => 2,
        ], MyBoolean::getMembers());
    }

    /**
     * @dataProvider dataToStringWorks
     * @param Enum $enum
     * @param string $expectedString
     */
    public function testToStringWorks(Enum $enum, $expectedString)
    {
        $this->assertSame($expectedString, $enum->__toString());
        $this->assertSame($expectedString, (string) $enum);
        $this->assertSame($expectedString, "{$enum}");
        $this->assertSame($expectedString, $enum . "");
    }

    public function dataToStringWorks()
    {
        return [
            [MyBoolean::MY_TRUE(), MyBoolean::getClass() . "::MY_TRUE"],
            [MyBoolean::MY_FALSE(), MyBoolean::getClass() . "::MY_FALSE"]
        ];
    }

    /**
     * @dataProvider dataMixedValuesAreSupported
     * @param MixedValues $enum
     * @param string $expectedDataType
     */
    public function testMixedValuesAreSupported(MixedValues $enum, $expectedDataType)
    {
        $this->assertInternalType($expectedDataType, $enum->getValue());
    }

    public function dataMixedValuesAreSupported()
    {
        $data = [];
        foreach (MixedValues::getMembers() as $name => $ignored) {
            $data[] = [MixedValues::__callStatic($name, []), $name];
        }
        return $data;
    }
}
