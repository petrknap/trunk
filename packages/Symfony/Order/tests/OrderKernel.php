<?php

namespace PetrKnap\Symfony\Order\Test;

use Netpromotion\SymfonyUp\UpKernel;
use PetrKnap\Symfony\Order\OrderBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

class OrderKernel extends UpKernel
{

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new SensioFrameworkExtraBundle(),
            new OrderBundle(),
        ];
    }
}
