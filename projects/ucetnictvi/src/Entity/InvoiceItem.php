<?php

namespace Ucetnictvi\Entity;

class InvoiceItem
{
    private $description;
    private $unitPrice;
    private $unit;
    private $quantity;

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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function getTotalPrice(): float
    {
        return $this->unitPrice * $this->quantity;
    }
}
