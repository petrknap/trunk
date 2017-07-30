<?php

namespace PetrKnap\Symfony\Order\Model;

abstract class Providable extends \ArrayObject
{
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->offsetGet('id');
    }
}
