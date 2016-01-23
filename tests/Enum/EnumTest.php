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
     * @dataProvider goodKeyProvider
     * @param string $key
     * @param string $value
     */
    public function testEnumDirectConstruction_GoodKey($key, $value)
    {
        $enum = new EnumMock($key);

        $this->assertInstanceOf(EnumMock::getClass(), $enum);
        $this->assertSame($key, $enum->getKey());
        $this->assertSame($value, $enum->getValue());
    }

    /**
     * @dataProvider wrongKeyProvider
     * @param string $key
     */
    public function testEnumDirectConstruction_WrongKey($key)
    {
        $this->setExpectedException(
            get_class(new EnumException()),
            "",
            EnumException::OUT_OF_RANGE
        );

        new EnumMock($key);
    }

    /**
     * @dataProvider goodKeyProvider
     * @param string $key
     * @param string $value
     */
    public function testEnumMagicConstruction_GoodKey($key, $value)
    {
        /** @var EnumMock $enum */
        $enum = EnumMock::$key();

        $this->assertInstanceOf(EnumMock::getClass(), $enum);
        $this->assertSame($key, $enum->getKey());
        $this->assertSame($value, $enum->getValue());
    }

    /**
     * @dataProvider wrongKeyProvider
     * @param string $key
     */
    public function testEnumMagicConstruction_WrongKey($key)
    {
        $this->setExpectedException(
            get_class(new EnumException()),
            "",
            EnumException::OUT_OF_RANGE
        );

        EnumMock::$key();
    }
}
