<?php

namespace Ucetnictvi\Entity;

class AssetCreation extends AssetOperation
{
    public $prices;
    public $priceUnit;
    public $exchangeRates;

    public function __construct(\DateTimeInterface $dateTime, Asset $size, array $prices, string $priceUnit, array $exchangeRates, string $reference = null)
    {
        parent::__construct($dateTime, $size, $reference);
        $this->prices = $prices;
        $this->priceUnit = $priceUnit;
        $this->exchangeRates = $exchangeRates;
    }
}
