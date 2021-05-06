<?php

namespace Ucetnictvi\Entity;

abstract class AssetOperation
{
    public $dateTime;
    public $size;
    public $reference;

    public function __construct(\DateTimeInterface $dateTime, Asset $size, string $reference = null)
    {
        $this->dateTime = $dateTime;
        $this->size = $size;
        $this->reference = $reference;
    }
}
