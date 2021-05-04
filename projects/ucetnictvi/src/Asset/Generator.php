<?php

namespace Ucetnictvi\Asset;

use Ucetnictvi\Entity\AssetMovement;

class Generator
{
    const MASTER_FIAT = 'EUR';
    const FINAL_FIAT = 'CZK';

    public function generateCsv(array $transactions, string $path)
    {
        $csv = fopen($path, 'w');

        fputcsv($csv, [
            'date',
            'input',
            'input unit',
            'output',
            'output unit',
            '',
            'size total',
            'size unit',
            'price total',
            'price unit',
            '',
            'output expected',
            'output real',
            'output diff',
            'output expected/real/diff unit',
        ]);

        $totalSizes = [];
        $totalPrices = [];

        foreach ($transactions as $transaction) {
            /** @var AssetMovement $transaction */
            $totalSize = &$totalSizes[$transaction->size->unit];
            $totalPrice = &$totalPrices[$transaction->size->unit];
            if ($this->isSell($transaction)) {
                $input = abs($transaction->size->value);
                $inputUnit = $transaction->size->unit;
                $output = abs($transaction->total->value) - $transaction->fee->value;
                $outputUnit = $transaction->total->unit;

                $expectedValue = $totalPrices[$transaction->size->unit] / $totalSizes[$transaction->size->unit] * $input;

                $totalSize -= $input;
                $totalPrice -= $expectedValue;
            } else {
                $input = abs($transaction->total->value) - $transaction->fee->value;
                $inputUnit = $transaction->total->unit;
                $output = abs($transaction->size->value);
                $outputUnit = $transaction->size->unit;

                $expectedValue = "";

                $totalSize += $output;
                $totalPrice += $input;
            }
            if ($transaction->fee->value) {
                $feeExpectedValue = $totalPrices[$transaction->fee->unit] / $totalSizes[$transaction->fee->unit] * $transaction->fee->value;
                $feeTotalSize = &$totalSizes[$transaction->fee->unit];
                $feeTotalSize -= $transaction->fee->value;
                $feeTotalPrice = &$totalPrices[$transaction->fee->unit];
                $feeTotalPrice -= $feeExpectedValue;
                fputcsv($csv, [
                    $transaction->dateTime->format('Y-m-d'),
                    $transaction->fee->value,
                    $transaction->fee->unit,
                    0,
                    "",
                    "",
                    $feeTotalSize,
                    $transaction->fee->unit,
                    $feeTotalPrice,
                    self::FINAL_FIAT,
                    "",
                    $feeExpectedValue,
                    0,
                    -$feeExpectedValue,
                    self::FINAL_FIAT,
                ]);
            }
            fputcsv($csv, [
                $transaction->dateTime->format('Y-m-d'),
                $input,
                $inputUnit,
                $output,
                $outputUnit,
                "",
                $totalSize,
                $transaction->size->unit,
                $totalPrice,
                $transaction->total->unit,
                "",
                $expectedValue ?: "",
                "",
                "",
                $expectedValue ? $outputUnit : "",
            ]);
            if ($expectedValue && $outputUnit !== self::FINAL_FIAT) {
                fputcsv($csv, [
                    "",
                    $output,
                    $outputUnit,
                    "= {$output} * ČNB",
                    self::FINAL_FIAT,
                    "",
                    "",
                    "",
                    "",
                    "",
                    "",
                    "= {$expectedValue} * ČNB",
                    "= {$output} * ČNB",
                    "= {$output} - {$expectedValue} * ČNB",
                    self::FINAL_FIAT,
                ]);
            }
        }

        fclose($csv);
    }

    private function isSell(AssetMovement $transaction): bool
    {
        return $transaction->size->value < 0 && $transaction->total->value > 0;
    }
}
