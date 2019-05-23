<?php

namespace Ucetnictvi\Invoice;

class Loader
{
    public function getAllData(string $inputDirectory): array
    {
        return [
            new Data(),
        ]; // TODO
    }
}
