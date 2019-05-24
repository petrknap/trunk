<?php

namespace Ucetnictvi\Entity;

class InvoiceItem
{
    public $type;
    public $description;
    public $quantity;
    public $unit;
    public $unitPrice;

    public static function create(array $data): self
    {
        $invoiceItem = new self();
        foreach ($data as $key => $value) {
            if (!property_exists($invoiceItem, $key)) {
                continue;
            }
            switch ($key) {
                case 'quantity':
                case 'unitPrice':
                    $value = (float) $value;
                    break;

            }
            $invoiceItem->{$key} = $value;
        }
        return $invoiceItem;
    }
}
