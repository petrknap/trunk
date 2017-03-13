<?php

namespace PetrKnap\Php\Enum\Test\EnumTest;

use PetrKnap\Php\Enum\Enum;

class MixedValues extends Enum
{
    /**
     * @inheritdoc
     */
    protected function members()
    {
        return array(
            "null" => null,
            "boolean" => true,
            "integer" => 1,
            "float" => 1.0,
            "string" => "s",
            "array" => array(),
            "object" => new \stdClass(),
            "callable" => function() {}
        );
    }
}
