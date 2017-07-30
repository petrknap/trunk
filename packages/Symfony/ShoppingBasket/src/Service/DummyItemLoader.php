<?php

namespace PetrKnap\Symfony\ShoppingBasket\Service;

use PetrKnap\Symfony\ShoppingBasket\Model\Item;

/**
 * @internal Test purpose only
 */
class DummyItemLoader
{
    public function load($id)
    {
        return new Item(['id' => $id]);
    }
}
