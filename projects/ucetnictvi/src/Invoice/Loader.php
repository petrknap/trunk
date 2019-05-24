<?php

namespace Ucetnictvi\Invoice;

use Ucetnictvi\Entity\Invoice;

class Loader
{
    public function getAllData(string $inputDirectory): array
    {
        return [
            new Invoice(),
        ]; // TODO
    }
}
