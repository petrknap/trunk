<?php

namespace PetrKnap\Symfony\Order\Service;

use PetrKnap\Symfony\Order\Model\Customer;

interface CustomerProvider
{
    /**
     * @param mixed|null $id expected current user if null
     * @return Customer
     */
    public function provideCustomer($id = null);
}
