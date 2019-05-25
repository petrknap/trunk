<?php

namespace Ucetnictvi\Entity;

class Invoice
{
    public $id;
    public $seller;
    public $buyer;
    public $subject;
    public $issueDate;
    public $dueDate;

    /**
     * @var InvoiceItem[]
     */
    public $items;

    public $currency;

    public static function create(array $data): self
    {
        $invoice = new self();
        foreach ($data as $key => $value) {
            if (!property_exists($invoice, $key)) {
                continue;
            }
            switch ($key) {
                case 'issueDate':
                case 'dueDate':
                    $value = new \DateTimeImmutable($value);
                    break;
            }
            $invoice->{$key} = $value;
        }
        return $invoice;
    }

    public function getTotalPrice(): float
    {
        $totalPrice = 0;
        foreach ($this->items as $item) {
            $totalPrice += $item->getTotalPrice();
        }
        return $totalPrice;
    }
}
