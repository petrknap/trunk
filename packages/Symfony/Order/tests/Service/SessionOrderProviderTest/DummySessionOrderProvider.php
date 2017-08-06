<?php

namespace PetrKnap\Symfony\Order\Test\Service\SessionOrderProviderTest;

use PetrKnap\Symfony\Order\Model\Customer;
use PetrKnap\Symfony\Order\Model\Item;
use PetrKnap\Symfony\Order\Service\SessionOrderProvider;

class DummySessionOrderProvider extends SessionOrderProvider
{
    /**
     * @inheritdoc
     */
    protected function loadItem($id)
    {
        return new Item(['id' => $id]);
    }

    /**
     * @inheritdoc
     */
    protected function loadCustomer()
    {
        return new Customer([]);
    }
}
