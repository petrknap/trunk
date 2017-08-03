<?php

namespace PetrKnap\Symfony\Order;

use PetrKnap\Symfony\Order\DependencyInjection\OrderExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OrderBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new OrderExtension();
    }
}
