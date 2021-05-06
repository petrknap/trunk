<?php

namespace Ucetnictvi\Entity;

class AssetMovement
{
    public $dateTime;
    public $size;
    public $fee;
    public $total;
    public $exchangeRate;
    public $reference;

    public function __construct(\DateTimeInterface $dateTime, Asset $size, Asset $fee, Asset $total, float $exchangeRate = null, string $reference = null)
    {
        $this->dateTime = $dateTime;
        $this->size = $size;
        $this->fee = $fee;
        $this->total = $total;
        $this->exchangeRate = $exchangeRate;
        $this->reference = $reference;
    }
}
