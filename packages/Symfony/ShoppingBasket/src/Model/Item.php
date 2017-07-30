<?php

namespace PetrKnap\Symfony\ShoppingBasket\Model;

class Item extends \ArrayObject
{
    public function getId()
    {
        return $this->offsetGet('id');
    }

    public function getAmount()
    {
        return $this->offsetGet('amount');
    }

    public function setAmount($amount)
    {
        $this->offsetSet('amount', $amount);
    }
}
