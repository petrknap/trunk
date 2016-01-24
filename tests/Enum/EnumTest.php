<?php

namespace PetrKnap\Php\Enum\Test;

use PetrKnap\Php\Enum\EnumException;
use PetrKnap\Php\Enum\Test\EnumTest\EnumMock;

class EnumTest extends \PHPUnit_Framework_TestCase
{
    public function goodKeyProvider()
    {
        return [["A", "a"], ["B", "b"]];
    }

    public function wrongKeyProvider()
    {
        return [["C"], ["D"]];
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
        /** @var EnumMock $enum */
        $enum = EnumMock::$name();

        $this->assertInstanceOf(EnumMock::getClass(), $enum);
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

        EnumMock::$name();
    }

    /**
     * @covers EnumMock::__callStatic
     */
    public function testComparable()
    {
        $this->assertSame(EnumMock::A(), EnumMock::A());
        $this->assertNotSame(EnumMock::A(), EnumMock::B());

        $this->assertTrue(EnumMock::A() == EnumMock::A());
        $this->assertFalse(EnumMock::A() == EnumMock::B());
    }

    /**
     * @covers EnumMock::getMembers
     * @runInSeparateProcess
     */
    public function testGetMembers()
    {
        $members = EnumMock::getMembers();

        $this->assertInternalType("array", $members);
        $this->assertCount(2, $members);
        $this->assertArrayHasKey("A", $members);
        $this->assertEquals("a", $members["A"]);
        $this->assertArrayHasKey("B", $members);
        $this->assertEquals("b", $members["B"]);
    }
}
