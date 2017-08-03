<?php

namespace PetrKnap\Symfony\Order\Test;

use Netpromotion\SymfonyUp\UpTestCase;
use PetrKnap\Symfony\Order\Service\OrderService;

class OrderTestCase extends UpTestCase
{
    public static function getKernelClass()
    {
        return OrderKernel::class;
    }

    public function setUp()
    {
        parent::setUp();

        /** @var OrderService $service */
        $service = $this->getContainer()->get(OrderService::class);
        $service->setContainer($this->getContainer()); // TODO Why does not Symfony do that?
    }
}
