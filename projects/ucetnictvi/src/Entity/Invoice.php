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
}
