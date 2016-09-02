<?php

namespace PetrKnap\Php\Enum;

interface EnumInterface
{
    /**
     * Returns member name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns member value
     *
     * @return mixed
     */
    public function getValue();
}
