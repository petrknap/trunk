<?php

namespace Ucetnictvi\Entity;

class Asset
{
    public $value;
    public $unit;

    public function __construct(float $value, string $unit)
    {
        $this->value = $value;
        $this->unit = $unit;
    }
}
