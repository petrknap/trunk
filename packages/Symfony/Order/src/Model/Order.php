<?php

namespace PetrKnap\Symfony\Order\Model;

use JsonSerializable;

class Order implements JsonSerializable
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
     * @param Customer $customer
     * @param Item[] $items
     */
    public function __construct(Customer $customer, array $items)
    {
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

    /**
     * @param Item $item
     */
    public function setItem(Item $item)
    {
        $this->items[$item->getId()] = $item;
    }

    function jsonSerialize()
    {
        return [
            'customer' => $this->getCustomer()->getArrayCopy(),
            'items' => array_map(function (Item $item) { return $item->getArrayCopy(); }, array_values($this->items)),
        ];
    }
}
