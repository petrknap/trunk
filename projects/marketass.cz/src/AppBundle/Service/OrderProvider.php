<?php

namespace AppBundle\Service;

use PetrKnap\Symfony\MarkdownWeb\Model\Index;
use PetrKnap\Symfony\MarkdownWeb\Service\Crawler;
use PetrKnap\Symfony\Order\Service\SessionOrderProvider;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class OrderProvider extends SessionOrderProvider
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var Index
     */
    private $index;

    public function __construct(SessionInterface $session, RequestStack $requestStack, Crawler $crawler, $urlPrefix)
    {
        parent::__construct($session);

        $this->requestStack = $requestStack;
        $this->index = $crawler->getIndex(function ($url) use ($urlPrefix) {
            return $urlPrefix . $url;
        });
    }

    /**
     * @inheritdoc
     */
    protected function loadItem($id)
    {
        $item = $this->index->getPage($id)->getParameters();

        return new \PetrKnap\Symfony\Order\Model\Item([
            'id' => $id,
            'price' => $item['price'],
            'title' => $item['title'],
            'description' => $item['description'],
            'url' => $item['url'],
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function loadCustomer()
    {
        $customer = $this->requestStack->getCurrentRequest()->cookies->get(static::class);
        $customer = json_decode($customer, true);

        return new \PetrKnap\Symfony\Order\Model\Customer([
            'name' => (string) @$customer['name'],
            'email' => (string) @$customer['email'],
            'address' => (string) @$customer['address'],
            'shipping_method' => (string) @$customer['shipping_method'],
            'billing_name' => (string) @$customer['billing_name'],
            'billing_address' => (string) @$customer['billing_address'],
        ]);
    }

    /**
     * @return Response
     */
    public function updateCustomer(Response $response)
    {
        $request = $this->requestStack->getCurrentRequest()->request;

        $response->headers->setCookie(new Cookie(static::class, json_encode([
            'name' => $request->get('name', null),
            'email' => $request->get('email', null),
            'address' => $request->get('address', null),
            'shipping_method' => $request->get('shipping_method', null),
            'billing_name' => $request->get('billing_name', null),
            'billing_address' => $request->get('billing_address', null),
        ]), new \DateTime('+1 year')));

        return $response;
    }

    /**
     * @return string 10 chars long string
     */
    public function createVariableSymbol()
    {
        return substr(floor(microtime(true) * 10), -10);
    }
}
