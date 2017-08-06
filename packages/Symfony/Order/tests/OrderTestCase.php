<?php

namespace PetrKnap\Symfony\Order\Test;

use Netpromotion\SymfonyUp\UpTestCase;

class OrderTestCase extends UpTestCase
{
    public static function getKernelClass()
    {
        return OrderKernel::class;
    }
}
