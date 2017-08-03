<?php

namespace PetrKnap\Symfony\Order\Controller;

use PetrKnap\Symfony\Order\Service\OrderService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ApiController extends Controller
{
    /**
     * @return OrderService|object|null
     */
    private function getOrder()
    {
        return $this->get(OrderService::class);
    }

    /**
     * @Route("/", name="order_api_get")
     * @Method("GET")
     */
    public function getAction(Request $request)
    {
        return $this->json($this->getOrder()->load($request));
    }

    /**
     * @Route("/add", name="order_api_add")
     * @Method("POST")
     */
    public function addAction(Request $request)
    {
        $order = $this->getOrder()->load($request);
        $item = $order->getItem($request->request->getAlnum('id'));
        $item->setAmount($item->getAmount() + $request->request->getInt('amount'));

        return $this->getOrder()->save($order);
    }

    /**
     * @Route("/remove", name="order_api_remove")
     * @Method("DELETE")
     */
    public function removeAction(Request $request)
    {
        $order = $this->getOrder()->load($request);
        $item = $order->getItem($request->request->getAlnum('id'));
        $item->setAmount(0);

        return $this->getOrder()->save($order);
    }
}
