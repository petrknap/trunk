<?php

namespace PetrKnap\Symfony\Order\Service;
use PetrKnap\Symfony\Order\Model\Customer;
use PetrKnap\Symfony\Order\Model\Item;

/**
 * @internal Test purpose only
 */
class TestProviders implements CustomerProvider, ItemProvider
{
    public function provideCustomer($id = null)
    {
        if (null === $id) {
            $id = 1;
        }
        return new Customer(['id' => $id, 'name' => 'John']);
    }

    public function provideItem($id)
    {
        return new Item(['id' => $id, 'title' => 'Item']);
    }
}
