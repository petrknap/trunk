<?php

namespace PetrKnap\Symfony\Order\Model;

class Item extends Providable
{
    public function getAmount()
    {
        return $this->offsetGet('amount');
    }

    public function setAmount($amount)
    {
        $this->offsetSet('amount', $amount);
    }
}
