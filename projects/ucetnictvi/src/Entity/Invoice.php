<?php

namespace Ucetnictvi\Entity;

class Invoice
{
    private $id;
    private $seller;
    private $buyer;
    private $issueDate;
    private $dueDate;
    private $items;
    private $currency;

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

    public function getId(): int
    {
        return $this->id;
    }

    public function getSeller(): Contact
    {
        return $this->seller;
    }

    public function getBuyer(): Contact
    {
        return $this->buyer;
    }

    public function getIssueDate(): \DateTimeImmutable
    {
        return $this->issueDate;
    }

    public function getDueDate(): \DateTimeImmutable
    {
        return $this->dueDate;
    }

    /**
     * @return InvoiceItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}
