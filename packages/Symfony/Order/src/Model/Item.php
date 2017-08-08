<?php

namespace PetrKnap\Symfony\Order\Model;

class Item extends \ArrayObject
{
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->offsetGet('id');
    }

    public function getAmount()
    {
        return (int) @$this->offsetGet('amount');
    }

    public function setAmount($amount)
    {
        $this->offsetSet('amount', $amount);
    }
}
