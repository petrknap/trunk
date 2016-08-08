<?php

namespace PetrKnap\Php\Enum\Test;

use PetrKnap\Php\Enum\EnumException;
use PetrKnap\Php\Enum\Test\EnumTest\MyBoolean;

class EnumTest extends \PHPUnit_Framework_TestCase
{
    public function goodKeyProvider()
    {
        return array(array("MY_TRUE", 1), array("MY_FALSE", 2));
    }

    public function wrongKeyProvider()
    {
        return array(array("MY_NULL"), array("MY_VOID"));
    }

    /**
     * @covers       EnumMock::__callStatic
     * @dataProvider goodKeyProvider
     *
     * @param string $name
     * @param mixed $value
     */
    public function testMagicConstruction_GoodKey($name, $value)
    {
        /** @var MyBoolean $enum */
        $enum = MyBoolean::$name();

        $this->assertInstanceOf(MyBoolean::getClass(), $enum);
        $this->assertSame($name, $enum->getName());
        $this->assertSame($value, $enum->getValue());
    }

    /**
     * @covers       EnumMock::__callStatic
     * @dataProvider wrongKeyProvider
     *
     * @param string $name
     */
    public function testMagicConstruction_WrongKey($name)
    {
        $this->setExpectedException(
            get_class(new EnumException()),
            "",
            EnumException::OUT_OF_RANGE
        );

        MyBoolean::$name();
    }

    /**
     * @covers EnumMock::__callStatic
     */
    public function testComparable()
    {
        $this->assertSame(MyBoolean::MY_TRUE(), MyBoolean::MY_TRUE());
        $this->assertNotSame(MyBoolean::MY_TRUE(), MyBoolean::MY_FALSE());

        $this->assertTrue(MyBoolean::MY_TRUE() == MyBoolean::MY_TRUE());
        $this->assertFalse(MyBoolean::MY_TRUE() == MyBoolean::MY_FALSE());
    }

    /**
     * @covers EnumMock::getMembers
     * @runInSeparateProcess
     */
    public function testGetMembers()
    {
        $members = MyBoolean::getMembers();

        $this->assertInternalType("array", $members);
        $this->assertCount(2, $members);
        $this->assertArrayHasKey("MY_TRUE", $members);
        $this->assertEquals(1, $members["MY_TRUE"]);
        $this->assertArrayHasKey("MY_FALSE", $members);
        $this->assertEquals(2, $members["MY_FALSE"]);
    }

    /**
     * @dataProvider dataFindByValue
     * @param mixed $value
     * @param mixed $expected
     */
    public function testFindByValue($value, $expected)
    {
        if ($expected instanceof \Exception) {
            $this->setExpectedException(get_class($expected));
        }
        $this->assertSame($expected, MyBoolean::findByValue($value));
    }

    public function dataFindByValue()
    {
        return [
            [1, MyBoolean::MY_TRUE()],
            [2, MyBoolean::MY_FALSE()],
            [3, new EnumException()]
        ];
    }
}
