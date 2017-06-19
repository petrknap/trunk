<?php

namespace PetrKnap\Php\Enum\Test\ConstantsAsMembersTest;

use PetrKnap\Php\Enum\ConstantsAsMembers;
use PetrKnap\Php\Enum\Enum;

/**
 * @method static MyBoolean MY_TRUE()
 * @method static MyBoolean MY_FALSE()
 */
class MyBoolean extends Enum
{
    use ConstantsAsMembers;

    const MY_TRUE = 1;
    const MY_FALSE = 2;
}
