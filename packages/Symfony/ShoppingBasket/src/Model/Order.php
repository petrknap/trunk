<?php

namespace PetrKnap\Symfony\ShoppingBasket\Model;

class Order
{
    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var Item[]
     */
    private $items;

    /**
     * @param Customer|null $customer
     * @param Item[] $items
     */
    public function __construct(Customer $customer = null, array $items = [])
    {
        if (null === $customer) {
            $customer = new Customer();
        }

        $this->customer = $customer;
        $this->items = $items;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return Item[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param int $id
     * @return Item
     */
    public function getItem($id)
    {
        if (!isset($this->items[$id])) {
            $this->items[$id] = new Item(['id' => $id]);
        }

        return $this->items[$id];
    }
}
