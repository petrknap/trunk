<?php

namespace PetrKnap\Php\Enum;

use ReflectionClass;

trait ConstantsAsMembers
{
    /**
     * @return array
     */
    protected function members()
    {
        $classReflection = new ReflectionClass(get_called_class());

        return $classReflection->getConstants();
    }
}
