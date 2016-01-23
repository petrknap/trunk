<?php

namespace PetrKnap\Php\Enum\Test\EnumTest;

use PetrKnap\Php\Enum\AbstractEnum;

/**
 * @method static EnumMock A()
 * @method static EnumMock B()
 */
class EnumMock extends AbstractEnum
{
    public function __construct($key)
    {
        $this->setItems([
            "A" => "a",
            "B" => "b"
        ]);

        parent::__construct($key);
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
