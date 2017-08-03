<?php

namespace PetrKnap\Symfony\Order\Service;

use PetrKnap\Symfony\Order\Model\Order;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrderService implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var string
     */
    private $cookieName;

    /**
     * @var int
     */
    private $cookieExpireAfter;

    /**
     * @var callable
     */
    private $provideItem;

    /**
     * @var callable
     */
    private $provideCustomer;

    public function __construct($cookieName, $cookieExpireAfter, $itemProvider, $customerProvider)
    {
        $this->cookieName = $cookieName;
        $this->cookieExpireAfter = $cookieExpireAfter;
        $this->provideItem = function ($itemId) use ($itemProvider) {
            /** @var ItemProvider $provider */
            static $provider;

            if (!$provider) {
                $provider = $this->container->get(substr($itemProvider, 1));
            }

            return call_user_func([$provider, 'provideItem'], $itemId);
        };
        $this->provideCustomer = function ($customerId) use ($customerProvider) {
            /** @var CustomerProvider $provider */
            static $provider;

            if (!$provider) {
                $provider = $this->container->get(substr($customerProvider, 1));
            }

            return call_user_func([$provider, 'provideCustomer'], $customerId);
        };
    }

    public function load(Request $request) // TODO use OrderAdapter
    {
        if ($request->cookies->has($this->cookieName)) {
            $data = json_decode($request->cookies->get($this->cookieName), true);
        } else {
            $data = [null, []];
        }

        $customer = call_user_func($this->provideCustomer, $data[0]);
        $items = [];
        foreach ($data[1] as $id => $amount) {
            $items[$id] = call_user_func($this->provideItem, $id);
            $items[$id]->setAmount($amount);
        }

        return new Order($customer, $items);
    }

    public function save(Order $order) // TODO use OrderAdapter
    {
        $items = [];
        foreach ($order->getItems() as $item) {
            if (0 < $item->getAmount()) {
                $items[$item->getId()] = $item->getAmount();
            }
        }

        $response = new Response(null, Response::HTTP_NO_CONTENT);
        $response->headers->setCookie(new Cookie(
                $this->cookieName,
                json_encode([
                    $order->getCustomer()->getId(),
                    $items,
                ]),
                time() + $this->cookieExpireAfter)
        );

        return $response;
    }
}
