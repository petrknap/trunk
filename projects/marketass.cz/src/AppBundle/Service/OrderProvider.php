<?php

namespace AppBundle\Service;

use PetrKnap\Symfony\MarkdownWeb\Model\Index;
use PetrKnap\Symfony\MarkdownWeb\Service\Crawler;
use PetrKnap\Symfony\Order\Service\SessionOrderProvider;
use Symfony\Component\Filesystem\Exception\IOException;
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

    /**
     * @var string
     */
    private $permanentJsonFile;

    public function __construct(SessionInterface $session, RequestStack $requestStack, Crawler $crawler, $urlPrefix, $permanentJsonFile)
    {
        parent::__construct($session);

        $this->requestStack = $requestStack;
        $this->index = $crawler->getIndex(function ($url) use ($urlPrefix) {
            return $urlPrefix . $url;
        });
        $this->permanentJsonFile = $permanentJsonFile;
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
     * @return string max 10 chars long string
     */
    public function createOrderNumber()
    {
        $fp = fopen($this->permanentJsonFile, 'r+');
        if (false === $fp) {
            throw new IOException('open failed', 0, null, $this->permanentJsonFile);
        }

        $index = 0;
        $offset = 0;
        $throw = null;
        try {
            if (false === flock($fp, LOCK_EX)) {
                throw new IOException('lock failed', 0, null, $this->permanentJsonFile);
            }

            $data = '';
            while (true !== feof($fp)) {
                $data .= fread($fp, 8192);
            }
            $data = json_decode($data, true);
            if (null === $data) {
                throw new IOException('decode failed', 0, null, $this->permanentJsonFile);
            }

            $offset = &$data['number_offset'];
            $index = &$data['last_number'][date('Y')];
            $index++;

            if (false === ftruncate($fp, 0)) {
                throw new IOException('truncate failed', 0, null, $this->permanentJsonFile);
            }

            if (false === rewind($fp)) {
                throw new IOException('rewind failed', 0, null, $this->permanentJsonFile);
            }

            if (false === fwrite($fp, json_encode($data))) {
                throw new IOException('write failed', 0, null, $this->permanentJsonFile);
            }

            if (false === flock($fp, LOCK_UN)) {
                throw new IOException('unlock failed', 0, null, $this->permanentJsonFile);
            }
        } catch (IOException $exception) {
            $throw = $exception;
        }

        if (false === fclose($fp)) {
            throw new IOException('close failed', 0, $throw, $this->permanentJsonFile);
        }

        if ($throw) {
            throw $throw;
        }

        return substr(date('Y') * 1000 + ($index + $offset) % 1000, -5);
    }
}
