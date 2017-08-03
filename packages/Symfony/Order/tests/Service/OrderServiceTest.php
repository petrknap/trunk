<?php

namespace PetrKnap\Symfony\Order\Test\Service;

use PetrKnap\Symfony\Order\Model\Customer;
use PetrKnap\Symfony\Order\Model\Item;
use PetrKnap\Symfony\Order\Model\Order;
use PetrKnap\Symfony\Order\Service\OrderService;
use PetrKnap\Symfony\Order\Test\OrderTestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

class OrderServiceTest extends OrderTestCase
{
    /**
     * @return OrderService|object
     */
    private function getService()
    {
        return $this->getContainer()->get(OrderService::class);
    }

    /**
     * @dataProvider dataCanSaveAndLoadOrder
     * @param Order $order
     * @param bool $hasSavedOrder
     */
    public function testCanSaveAndLoadOrder(Order $order, $hasSavedOrder)
    {
        $cookies = [];
        if ($hasSavedOrder) {
            foreach ($this->getService()->save($order)->headers->getCookies() as $cookie) {
                /** @var Cookie $cookie */
                $cookies[$cookie->getName()] = $cookie->getValue();
            }
        }

        $order->getCustomer()->offsetSet('id', 1);
        $order->getCustomer()->offsetSet('name', 'John');

        foreach ($order->getItems() as $item) {
            $item->offsetSet('title', 'Item');
        }

        $this->assertEquals($order, $this->getService()->load(new Request([], [], [], $cookies)));
    }

    public function dataCanSaveAndLoadOrder()
    {
        return [
            'without previous order' => [
                new Order(
                    new Customer(['id' => null]),
                    []
                ),
                false,
            ],
            'empty order without customer' => [
                new Order(
                    new Customer(['id' => null]),
                    []
                ),
                true,
            ],
            'empty order with customer' => [
                new Order(
                    new Customer(['id' => 1]),
                    []
                ),
                true,
            ],
            'order without customer' => [
                new Order(
                    new Customer(['id' => null]),
                    [
                        1 => new Item(['id' => 1, 'amount' => 3]),
                        2 => new Item(['id' => 2, 'amount' => 5]),
                    ]
                ),
                true,
            ],
            'order with customer' => [
                new Order(
                    new Customer(['id' => 1]),
                    [
                        1 => new Item(['id' => 1, 'amount' => 3]),
                        2 => new Item(['id' => 2, 'amount' => 5]),
                    ]
                ),
                true,
            ],
        ];
    }
}
