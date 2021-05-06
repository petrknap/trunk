<?php

namespace Ucetnictvi\Entity;

class AssetMovement extends AssetOperation
{
    public $fee;
    public $total;
    public $exchangeRate;

    public function __construct(\DateTimeInterface $dateTime, Asset $size, Asset $fee, Asset $total, float $exchangeRate = null, string $reference = null)
    {
        parent::__construct($dateTime, $size, $reference);
        $this->dateTime = $dateTime;
        $this->size = $size;
        $this->fee = $fee;
        $this->total = $total;
        $this->exchangeRate = $exchangeRate;
    }
}
