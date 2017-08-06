<?php

namespace PetrKnap\Symfony\Order\Test\Controller;

use PetrKnap\Symfony\Order\Controller\ApiController;
use PetrKnap\Symfony\Order\Model\Customer;
use PetrKnap\Symfony\Order\Model\Item;
use PetrKnap\Symfony\Order\Model\Order;
use PetrKnap\Symfony\Order\Service\DummySessionOrderProvider;
use PetrKnap\Symfony\Order\Service\OrderProvider;
use PetrKnap\Symfony\Order\Test\OrderTestCase;
use Symfony\Component\Routing\RouterInterface;

class ApiControllerTest extends OrderTestCase
{
    /**
     * @return OrderProvider|object
     */
    private function getService()
    {
        return $this->getContainer()->get(DummySessionOrderProvider::class);
    }

    /**
     * @return ApiController
     */
    private function getController()
    {
        $controller = new ApiController();
        $controller->setContainer($this->getContainer());

        return $controller;
    }

    /**
     * @dataProvider dataRouteExists
     * @param string $route
     */
    public function testRouteExists($route)
    {
        /** @var RouterInterface $router */
        $router = $this->getContainer()->get('router');
        $router->generate($route);
    }

    public function dataRouteExists()
    {
        return [
            ['order_api_get'],
            ['order_api_add'],
            ['order_api_remove'],
        ];
    }

    /**
     * @dataProvider dataGetActionWorks
     * @param Order|null $order
     * @param array $expected
     */
    public function testGetActionWorks(Order $order = null, array $expected)
    {
        $this->markTestIncomplete();
    }

    public function dataGetActionWorks()
    {
        return [
            'without order' => [null, [
                'customer' => ['id' => 1, 'name' => 'John'],
                'items' => [],
            ]],
            'empty order' => [new Order(new Customer(['id' => 1]), []), [
                'customer' => ['id' => 1, 'name' => 'John'],
                'items' => [],
            ]],
            'full order' => [new Order(new Customer(['id' => 1]), [new Item(['id' => 1, 'amount' => 2])]), [
                'customer' => ['id' => 1, 'name' => 'John'],
                'items' => [
                    [
                        'id' => 1,
                        'title' => 'Item',
                        'amount' => 2,
                    ]
                ],
            ]],
        ];
    }

    /**
     * @dataProvider dataAddActionWorks
     * @param Order $order
     * @param mixed $id
     * @param int $amount
     * @param array $expected
     */
    public function testAddActionWorks(Order $order, $id, $amount, array $expected)
    {
        $this->markTestIncomplete();
    }

    public function dataAddActionWorks()
    {
        return [
            'add new item to empty order' => [new Order(new Customer([]), []), 1, 3, [
                'customer' => [
                    'id' => 1,
                    'name' => 'John',
                ],
                'items' => [
                    [
                        'id' => 1,
                        'title' => 'Item',
                        'amount' => 3
                    ],
                ],
            ]],
            'remove new item from empty order' => [new Order(new Customer([]), []), 1, -3, [
                'customer' => [
                    'id' => 1,
                    'name' => 'John',
                ],
                'items' => [
                ],
            ]],
            'add new item to non-empty order' => [new Order(new Customer([]), [new Item(['id' => 1, 'amount' => 3])]), 2, 5, [
                'customer' => [
                    'id' => 1,
                    'name' => 'John',
                ],
                'items' => [
                    [
                        'id' => 1,
                        'title' => 'Item',
                        'amount' => 3
                    ],
                    [
                        'id' => 2,
                        'title' => 'Item',
                        'amount' => 5
                    ],
                ],
            ]],
            'remove new item from non-empty order' => [new Order(new Customer([]), [new Item(['id' => 1, 'amount' => 3])]), 2, -5, [
                'customer' => [
                    'id' => 1,
                    'name' => 'John',
                ],
                'items' => [
                    [
                        'id' => 1,
                        'title' => 'Item',
                        'amount' => 3
                    ],
                ],
            ]],
            'add existing item to one-item order' => [new Order(new Customer([]), [new Item(['id' => 1, 'amount' => 3])]), 1, 7, [
                'customer' => [
                    'id' => 1,
                    'name' => 'John',
                ],
                'items' => [
                    [
                        'id' => 1,
                        'title' => 'Item',
                        'amount' => 10
                    ],
                ],
            ]],
            'remove existing item from one-item order' => [new Order(new Customer([]), [new Item(['id' => 1, 'amount' => 10])]), 1, -7, [
                'customer' => [
                    'id' => 1,
                    'name' => 'John',
                ],
                'items' => [
                    [
                        'id' => 1,
                        'title' => 'Item',
                        'amount' => 3
                    ],
                ],
            ]],
            'add existing item to two-item order' => [new Order(new Customer([]), [new Item(['id' => 1, 'amount' => 3]), new Item(['id' => 2, 'amount' => 5])]), 1, 7, [
                'customer' => [
                    'id' => 1,
                    'name' => 'John',
                ],
                'items' => [
                    [
                        'id' => 1,
                        'title' => 'Item',
                        'amount' => 10
                    ],
                    [
                        'id' => 2,
                        'title' => 'Item',
                        'amount' => 5
                    ]
                ],
            ]],
            'remove existing item from two-item order' => [new Order(new Customer([]), [new Item(['id' => 1, 'amount' => 10]), new Item(['id' => 2, 'amount' => 5])]), 1, -7, [
                'customer' => [
                    'id' => 1,
                    'name' => 'John',
                ],
                'items' => [
                    [
                        'id' => 1,
                        'title' => 'Item',
                        'amount' => 3
                    ],
                    [
                        'id' => 2,
                        'title' => 'Item',
                        'amount' => 5
                    ]
                ],
            ]],
            'remove more than order contains' => [new Order(new Customer([]), [new Item(['id' => 1, 'amount' => 3]), new Item(['id' => 2, 'amount' => 5])]), 1, -7, [
                'customer' => [
                    'id' => 1,
                    'name' => 'John',
                ],
                'items' => [
                    [
                        'id' => 2,
                        'title' => 'Item',
                        'amount' => 5
                    ]
                ],
            ]],
        ];
    }


    /**
     * @dataProvider dataRemoveActionWorks
     * @param Order $order
     * @param mixed $id
     * @param array $expected
     */
    public function testRemoveActionWorks(Order $order, $id, array $expected)
    {
        $this->markTestIncomplete();
    }

    public function dataRemoveActionWorks()
    {
        return [
            'remove new item from empty order' => [new Order(new Customer([]), []), 1, [
                'customer' => [
                    'id' => 1,
                    'name' => 'John',
                ],
                'items' => [
                ],
            ]],
            'remove new item from non-empty order' => [new Order(new Customer([]), [new Item(['id' => 1, 'amount' => 3])]), 2, [
                'customer' => [
                    'id' => 1,
                    'name' => 'John',
                ],
                'items' => [
                    [
                        'id' => 1,
                        'title' => 'Item',
                        'amount' => 3
                    ],
                ],
            ]],
            'remove existing item from one-item order' => [new Order(new Customer([]), [new Item(['id' => 1, 'amount' => 10])]), 1, [
                'customer' => [
                    'id' => 1,
                    'name' => 'John',
                ],
                'items' => [
                ],
            ]],
            'remove existing item from two-item order' => [new Order(new Customer([]), [new Item(['id' => 1, 'amount' => 10]), new Item(['id' => 2, 'amount' => 5])]), 1, [
                'customer' => [
                    'id' => 1,
                    'name' => 'John',
                ],
                'items' => [
                    [
                        'id' => 2,
                        'title' => 'Item',
                        'amount' => 5
                    ],
                ],
            ]],
        ];
    }
}
