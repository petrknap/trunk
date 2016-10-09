<?php

namespace PetrKnap\Php\Enum\Test;

use PetrKnap\Php\Enum\Enum;
use PetrKnap\Php\Enum\Exception\EnumException;
use PetrKnap\Php\Enum\Exception\EnumNotFoundException;
use PetrKnap\Php\Enum\Test\EnumTest\MixedValues;
use PetrKnap\Php\Enum\Test\EnumTest\MyBoolean;

class EnumTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataCallStaticsWorks
     * @param string $name
     * @param mixed|EnumException $expectedValue
     */
    public function testCallStaticsWorks($name, $expectedValue)
    {
        if ($expectedValue instanceof EnumException) {
            $this->setExpectedException(get_class($expectedValue));
        }

        $this->assertSame($expectedValue, MyBoolean::__callStatic($name, array())->getValue());
    }

    public function dataCallStaticsWorks()
    {
        return array(
            array("MY_TRUE", 1),
            array("MY_FALSE", 2),
            array("MY_NULL", new EnumNotFoundException())
        );
    }

    /**
     * @dataProvider dataGetEnumByValueWorks
     * @param mixed $value
     * @param Enum|EnumException $expectedEnum
     */
    public function testGetEnumByValueWorks($value, $expectedEnum)
    {
        if ($expectedEnum instanceof EnumException) {
            $this->setExpectedException(get_class($expectedEnum));
        }

        $this->assertSame($expectedEnum, MyBoolean::getEnumByValue($value));
    }

    public function dataGetEnumByValueWorks()
    {
        return array(
            array(1, MyBoolean::MY_TRUE()),
            array(2, MyBoolean::MY_FALSE()),
            array(3, new EnumNotFoundException())
        );
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
        $this->assertEquals(array(
            "MY_TRUE" => 1,
            "MY_FALSE" => 2
        ), MyBoolean::getMembers());
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
        return array(
            array(MyBoolean::MY_TRUE(), MyBoolean::getClass() . "::MY_TRUE"),
            array(MyBoolean::MY_FALSE(), MyBoolean::getClass() . "::MY_FALSE")
        );
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
        $data = array();
        foreach (MixedValues::getMembers() as $name => $ignored) {
            $data[] = array(MixedValues::__callStatic($name, array()), $name);
        }
        return $data;
    }
}
