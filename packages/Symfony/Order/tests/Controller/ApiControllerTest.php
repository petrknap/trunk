<?php

namespace PetrKnap\Symfony\Order\Test\Controller;

use PetrKnap\Symfony\Order\Controller\ApiController;
use PetrKnap\Symfony\Order\Model\Customer;
use PetrKnap\Symfony\Order\Model\Item;
use PetrKnap\Symfony\Order\Model\Order;
use PetrKnap\Symfony\Order\Service\OrderService;
use PetrKnap\Symfony\Order\Test\OrderTestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class ApiControllerTest extends OrderTestCase
{
    /**
     * @return OrderService|object
     */
    private function getService()
    {
        return $this->getContainer()->get(OrderService::class);
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


    private function toRequest(Response $response)
    {
        $request = new Request();
        foreach ($response->headers->getCookies() as $cookie) {
            /** @var Cookie $cookie */
            $request->cookies->set($cookie->getName(), $cookie->getValue());
        }
        return $request;
    }

    private function getRequest(Order $order = null)
    {
        if ($order) {
            $response = $this->getService()->save($order);
        } else {
            $response = new Response();
        }
        return $this->toRequest($response);
    }

    private function getOrder(Response $response)
    {
        return $this->getService()->load($this->toRequest($response));
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
        $response = $this->getController()->getAction($this->getRequest($order));

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($expected, json_decode($response->getContent(), true));
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
        $request = $this->getRequest($order);
        $request->request->set('id', $id);
        $request->request->set('amount', $amount);

        $this->assertEquals($expected, $this->getOrder($this->getController()->addAction($request))->jsonSerialize());
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
        $request = $this->getRequest($order);
        $request->request->set('id', $id);

        $this->assertEquals($expected, $this->getOrder($this->getController()->removeAction($request))->jsonSerialize());
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
