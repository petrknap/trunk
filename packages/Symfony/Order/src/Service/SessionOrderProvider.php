<?php

namespace PetrKnap\Symfony\Order\Service;

use PetrKnap\Symfony\Order\Model\Customer;
use PetrKnap\Symfony\Order\Model\Item;
use PetrKnap\Symfony\Order\Model\Order;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

abstract class SessionOrderProvider implements OrderProvider
{
    /**
     * @var SessionInterface
     */
    protected $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }
    
    /**
     * @inheritdoc
     */
    public function provide()
    {
        $order = new Order($this->loadCustomer(), []);

        if ($this->session->has(__CLASS__)) {
            foreach ($this->session->get(__CLASS__) as $id => $amount) {
                $item = $this->loadItem($id);
                $item->setAmount($amount);
                $order->setItem($item);
            }
        }

        return $order;
    }

    /**
     * @inheritdoc
     */
    public function persist(Order $order)
    {
        $items = [];
        foreach ($order->getItems() as $item) {
            if (0 < $item->getAmount()) {
                $items[$item->getId()] = $item->getAmount();
            }
        }
        $this->session->set(__CLASS__, $items);
    }

    /**
     * @param mixed $id
     * @return Item
     */
    abstract protected function loadItem($id);

    /**
     * @return Customer
     */
    abstract protected function loadCustomer();
}
