<?php

namespace PetrKnap\Symfony\Order\Test\Service;

use PetrKnap\Symfony\Order\Model\Customer;
use PetrKnap\Symfony\Order\Model\Item;
use PetrKnap\Symfony\Order\Model\Order;
use PetrKnap\Symfony\Order\Test\OrderTestCase;
use PetrKnap\Symfony\Order\Test\Service\SessionOrderProviderTest\DummySessionOrderProvider;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionOrderProviderTest extends OrderTestCase
{
    /**
     * @dataProvider dataProvidesOrder
     * @param SessionInterface $session
     * @param $expected
     */
    public function testProvidesOrder(SessionInterface $session, $expected)
    {
        $this->assertEquals($expected, (new DummySessionOrderProvider($session))->provide());
    }

    public function dataProvidesOrder()
    {
        $emptySession = $this->getMockBuilder(SessionInterface::class)->getMock();
        $emptyOrder = new Order(new Customer([]), []);

        $fullSession = $this->getMockBuilder(SessionInterface::class)->getMock();
        $fullOrder = new Order(new Customer([]), [1 => new Item(['id' => 1, 'amount' => 1])]);
        $fullSession->method('has')->willReturn(true);
        $fullSession->method('get')->willReturn([1 => 1]);

        return [
            [$emptySession, $emptyOrder],
            [$fullSession, $fullOrder],
        ];
    }

    /**
     * @dataProvider dataPersistsOrder
     * @param Order $order
     * @param array $serialized
     */
    public function testPersistsOrder(Order $order, array $serialized)
    {
        $session = $this->getMockBuilder(SessionInterface::class)->getMock();
        $session->expects($this->once())->method('set')->willReturnCallback(function ($name, $value) use ($serialized) {
            $this->assertEquals($serialized, $value);
        });

        /** @noinspection PhpParamsInspection */
        (new DummySessionOrderProvider($session))->persist($order);
    }

    public function dataPersistsOrder()
    {
        return [
            [new Order(new Customer([]), []), []],
            [new Order(new Customer([]), [1 => new Item(['id' => 1, 'amount' => 2])]), [1 => 2]],
        ];
    }
}
