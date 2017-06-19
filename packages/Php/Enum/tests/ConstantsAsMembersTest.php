<?php

namespace PetrKnap\Php\Enum\Test;

use PetrKnap\Php\Enum\Test\ConstantsAsMembersTest\MyBoolean;

class ConstantsAsMembersTest extends \PHPUnit_Framework_TestCase
{
    public function testMembersWorks()
    {
        $this->assertEquals(
            [
                'MY_TRUE' => 1,
                'MY_FALSE' => 2,
            ],
            MyBoolean::getMembers()
        );
    }
}
