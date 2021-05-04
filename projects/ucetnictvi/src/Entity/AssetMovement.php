<?php

namespace Ucetnictvi\Entity;

class AssetMovement
{
    public $dateTime;
    public $size;
    public $fee;
    public $total;
    public $reference;

    public function __construct(\DateTimeInterface $dateTime, Asset $size, Asset $fee, Asset $total, string $reference = null)
    {
        $this->dateTime = $dateTime;
        $this->size = $size;
        $this->fee = $fee;
        $this->total = $total;
        $this->reference = $reference;
    }
}
