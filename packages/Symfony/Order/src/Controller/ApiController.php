<?php

namespace PetrKnap\Symfony\Order\Controller;

use PetrKnap\Symfony\Order\DependencyInjection\OrderConfiguration;
use PetrKnap\Symfony\Order\Service\OrderProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller
{
    /**
     * @return OrderProvider|object|null
     */
    private function getOrderProvider()
    {
        $config = $this->get(OrderConfiguration::class);

        return $this->get($config['provider']);
    }

    /**
     * @Route("/", name="order_api_get")
     * @Method("GET")
     * @return Response
     */
    public function getAction()
    {
        return $this->json($this->getOrderProvider()->provide());
    }

    /**
     * @Route("/add", name="order_api_add")
     * @Method("POST")
     * @param Request $request
     * @return Response
     */
    public function addAction(Request $request)
    {
        $order = $this->getOrderProvider()->provide();
        $item = $order->getItem($request->request->get('id'));
        $item->setAmount($item->getAmount() + $request->request->getInt('amount'));

        $this->getOrderProvider()->persist($order);

        return $this->redirectToRoute('order_api_get');
    }

    /**
     * @Route("/remove", name="order_api_remove")
     * @Method("DELETE")
     * @param Request $request
     * @return Response
     */
    public function removeAction(Request $request)
    {
        $order = $this->getOrderProvider()->provide();
        $item = $order->getItem($request->request->getAlnum('id'));
        $item->setAmount(0);

        $this->getOrderProvider()->persist($order);

        return $this->redirectToRoute('order_api_get');
    }
}
