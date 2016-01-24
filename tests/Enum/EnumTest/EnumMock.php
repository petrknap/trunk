<?php

namespace PetrKnap\Php\Enum\Test\EnumTest;

use PetrKnap\Php\Enum\AbstractEnum;

/**
 * @method static EnumMock A()
 * @method static EnumMock B()
 */
class EnumMock extends AbstractEnum
{
    protected function __construct($memberName)
    {
        self::setMembers([
            "A" => "a",
            "B" => "b"
        ]);

        parent::__construct($memberName);
    }

    /**
     * Returns class name (PHP <5.5)
     *
     * @return string
     */
    public static function getClass()
    {
        return __CLASS__;
    }
}
