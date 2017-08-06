<?php

namespace PetrKnap\Symfony\Order\Service;

use PetrKnap\Symfony\Order\Model\Order;

interface OrderProvider
{
    /**
     * @return Order
     */
    public function provide();

    /**
     * @param Order $order
     * @return void
     */
    public function persist(Order $order);
}
