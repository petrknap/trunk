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
        return [
            "null" => null,
            "boolean" => true,
            "integer" => 1,
            "float" => 1.0,
            "string" => "s",
            "array" => [],
            "object" => new \stdClass(),
            "callable" => function() {},
        ];
    }
}
