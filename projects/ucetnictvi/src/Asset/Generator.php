<?php

namespace Ucetnictvi\Asset;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Ucetnictvi\Entity\Asset;
use Ucetnictvi\Entity\AssetCreation;
use Ucetnictvi\Entity\AssetMovement;
use Ucetnictvi\Entity\AssetOperation;

class Generator
{
    const MASTER_FIAT = 'EUR';
    const FINAL_FIAT = 'CZK';
    const DATE_FORMAT = '=\D\A\T\E\(Y\,m\,d\)';
    const STYLE_BORDER = [
        'borders' => [
            'bottom' => ['borderStyle' => Border::BORDER_THIN],
            'right' => ['borderStyle' => Border::BORDER_THIN],
            'top' => ['borderStyle' => Border::BORDER_THIN],
            'left' => ['borderStyle' => Border::BORDER_THIN],
        ],
    ];

    public function generateXlsx(array $operations, string $path)
    {
        $spreadsheet = new Spreadsheet();

        $analytics = $spreadsheet->createSheet(2);
        $analytics
            ->setTitle('analytics')
            ->setCellValue('A1', 'size')
            ->setCellValue('B1', '100 ' . self::MASTER_FIAT)
            ->setCellValue('C1', 'total')
            ->setCellValue('D1', 'price total');

        $creations = $spreadsheet->createSheet(1);
        $creations
            ->setTitle('creations')
            ->setCellValue('A1', 'date')
            ->setCellValue('B1', 'size')
            ->setCellValue('D1', 'price')
            ->setCellValue('G1', 'exchange rate')
            ->setCellValue('Z1', 'reference');

        $movements = $spreadsheet->setActiveSheetIndex(0);
        $movements
            ->setTitle('movements')
            ->setCellValue('A1', 'date')
            ->setCellValue('B1', 'input')
            ->setCellValue('D1', 'output')
            ->setCellValue('G1', 'price total')
            ->setCellValue('I1', 'size total')
            ->setCellValue('L1', 'outcome')
            ->setCellValue('M1', 'income')
            ->setCellValue('N1', 'difference')
            ->setCellValue('Q1', 'exchange rate')
            ->setCellValue('S1', 'outcome')
            ->setCellValue('T1', 'income')
            ->setCellValue('U1', 'difference')
            ->setCellValue('Z1', 'reference');

        $creationRow = 1;
        $movementRows = [];
        $movementRow = 1;
        foreach ($operations as $operation) {
            /** @var AssetOperation $operation */
            $parentMovementRow = &$movementRows[$operation->size->unit];
            $movementRow++;

            if ($operation instanceof AssetCreation) {
                /** @var AssetCreation $operation */
                $creationRow++;
                $creations
                    ->setCellValue("A{$creationRow}", $operation->dateTime->format(self::DATE_FORMAT))
                    ->setCellValue("B{$creationRow}", $operation->size->value)
                    ->setCellValue("C{$creationRow}", $operation->size->unit)
                    ->setCellValue("D{$creationRow}", "=B{$creationRow} * AVERAGE(I{$creationRow}:X{$creationRow}) * G{$creationRow}")
                    ->setCellValue("E{$creationRow}", self::FINAL_FIAT)
                    ->setCellValue("G{$creationRow}", $operation->exchangeRates[$operation->priceUnit])
                    ->setCellValue("Z{$creationRow}", $operation->reference);

                $priceIndex = 0;
                foreach ($operation->prices as $source => $price) {
                    $column = chr(ord('I') + $priceIndex++);
                    $creations
                        ->setCellValue("{$column}1", $source)
                        ->setCellValue("{$column}{$creationRow}", $price);
                }
                $column = chr(ord('I') + $priceIndex);
                $creations
                    ->setCellValue("{$column}1", "price unit")
                    ->setCellValue("{$column}{$creationRow}", $operation->priceUnit);

                $this->applyUnitColor(
                    $creations->getStyle("B{$creationRow}:C{$creationRow}"),
                    $operation->size->unit,
                    $movementRows
                );
                $this->applyUnitColor(
                    $creations->getStyle("D{$creationRow}:E{$creationRow}"),
                    self::FINAL_FIAT,
                    $movementRows
                );

                $movements
                    ->setCellValue("A{$movementRow}", "={$creations->getTitle()}!A{$creationRow}")
                    ->setCellValue("B{$movementRow}", "={$creations->getTitle()}!D{$creationRow}")
                    ->setCellValue("C{$movementRow}", "={$creations->getTitle()}!E{$creationRow}")
                    ->setCellValue("D{$movementRow}", "={$creations->getTitle()}!B{$creationRow}")
                    ->setCellValue("E{$movementRow}", "={$creations->getTitle()}!C{$creationRow}")
                    ->setCellValue("G{$movementRow}", $parentMovementRow ? "=G{$parentMovementRow} + B{$movementRow} / Q{$movementRow}" : "=B{$movementRow} / Q{$movementRow}")
                    ->setCellValue("H{$movementRow}", self::MASTER_FIAT)
                    ->setCellValue("I{$movementRow}", $parentMovementRow ? "=I{$parentMovementRow} + D{$movementRow}" : "=D{$movementRow}")
                    ->setCellValue("J{$movementRow}", "=E{$movementRow}")
                    ->setCellValue("Q{$movementRow}", $operation->exchangeRates[self::MASTER_FIAT])
                    ->setCellValue("Z{$movementRow}", "={$creations->getTitle()}!Z{$creationRow}");

                $this->applyUnitColor(
                    $movements->getStyle("B{$movementRow}:C{$movementRow}"),
                    self::FINAL_FIAT,
                    $movementRows
                );
                $this->applyUnitColor(
                    $movements->getStyle("D{$movementRow}:E{$movementRow}"),
                    $operation->size->unit,
                    $movementRows
                );

                $parentMovementRow = $movementRow;
            } elseif ($operation instanceof AssetMovement) {
                if ($this->isExchange($operation)) {
                    $addedRows = self::renderExchange($operation, $movements, $movementRow, $movementRows);
                } else {
                    $feeRow = $movementRow;
                    $movementRow += self::renderFee($operation, $movements, $movementRow, $movementRows);
                    if ($feeRow === $movementRow) {
                        $feeRow = null;
                    }

                    if ($this->isSell($operation)) {
                        $addedRows = self::renderSell($operation, $movements, $movementRow, $feeRow, $parentMovementRow, $movementRows);
                    } else {
                        $addedRows = self::renderBuy($operation, $movements, $movementRow, $feeRow, $parentMovementRow, $movementRows);
                    }
                }

                $movementRow += $addedRows - 1;
                $parentMovementRow = $movementRow - ($addedRows - 1);
            }
        }

        self::renderAnalytics($movements, $analytics, $movementRows);

        for ($r = 1; $r <= $creationRow; $r++) {
            foreach (['A', 'B', 'C', 'D', 'E'] as $c) {
                $creations->getStyle("{$c}{$r}")->applyFromArray(self::STYLE_BORDER + ($r === 1 ? [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF000000']
                    ],
                    'font' => [
                        'color' => ['argb' => 'FFFFFFFF']
                    ],
                ] : []));
            }
        }
        $creations
            ->getStyle("A2:A{$creationRow}")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD);
        for ($r = 1; $r <= $movementRow; $r++) {
            foreach (['A', 'B', 'C', 'D', 'E', 'G', 'H', 'I', 'J', 'L', 'M', 'N', 'O', 'S', 'T', 'U', 'V'] as $c) {
                $movements->getStyle("{$c}{$r}")->applyFromArray(self::STYLE_BORDER + ($r === 1 ? [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF000000']
                    ],
                        'font' => [
                        'color' => ['argb' => 'FFFFFFFF']
                    ],
                ] : []));
            }
        }
        $movements
            ->getStyle("A2:A{$movementRow}")
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD);

        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($path);
    }

    private function isExchange(AssetMovement $movement): bool
    {
        return $movement->total->unit != self::MASTER_FIAT && $movement->total->unit != self::FINAL_FIAT;
    }

    private static function renderExchange(AssetMovement $exchange, Worksheet $movements, int $movementRow, array $movementRows): int
    {
        $sellParentRow = &$movementRows[$exchange->size->unit];
        $sell = new AssetMovement(
            $exchange->dateTime,
            $exchange->size,
            new Asset(0, self::MASTER_FIAT),
            new Asset(0, self::MASTER_FIAT),
            null,
            $exchange->reference
        );
        $buyParentRow = &$movementRows[$exchange->total->unit];
        $buy = new AssetMovement(
            $exchange->dateTime,
            $exchange->total,
            new Asset(0, self::MASTER_FIAT),
            new Asset(0, self::MASTER_FIAT),
            null,
            $exchange->reference
        );

        $sellRow = $movementRow;
        $sellRows = self::renderSell($sell, $movements, $movementRow, null, $sellParentRow, $movementRows);
        $sellParentRow = $sellRow;
        $buyRow = $sellRow + $sellRows;
        $buyRows = self::renderBuy($buy, $movements, $movementRow + $sellRows, null, $buyParentRow, $movementRows);
        $buyParentRow = $buyRow;
        $feeRows = self::renderFee($exchange, $movements, $movementRow + $sellRows + $buyRows, $movementRows);

        $movements
            ->setCellValue("D{$sellRow}", "=L{$sellRow}")
            ->setCellValue("E{$sellRow}", "=O{$sellRow}")
            ->setCellValue("B{$buyRow}", "=D{$sellRow}")
            ->setCellValue("C{$buyRow}", "=E{$sellRow}");

        return $sellRows + $buyRows + $feeRows;
    }

    private static function renderFee(AssetMovement $movement, Worksheet $movements, int $movementRow, array $movementRows): int
    {
        if ($movement->fee->value) {
            $parentFeeRow = @$movementRows[$movement->fee->unit];

            return self::renderSell(
                new AssetMovement(
                    $movement->dateTime,
                    $movement->fee,
                    new Asset(0, self::MASTER_FIAT),
                    new Asset(0, self::MASTER_FIAT),
                    null,
                    $movement->reference
                ), $movements, $movementRow, null, $parentFeeRow, $movementRows
            );
        } else {
            return 0;
        }
    }

    private static function isSell(AssetMovement $movement): bool
    {
        return $movement->size->value < 0 || $movement->total->value > 0;
    }

    private static function renderSell(AssetMovement $sell, Worksheet $movements, int $movementRow, ?int $feeRow, ?int $parentMovementRow, array $movementRows): int
    {
        $movements = self::prerenderMovement($sell, $movements, $movementRow)
            ->setCellValue("B{$movementRow}", "=ABS({$sell->size->value})")
            ->setCellValue("C{$movementRow}", $sell->size->unit);

        if (self::isFiat($sell)) {
            $movements->setCellValue("D{$movementRow}", 0);
        } else {
            $movements
                ->setCellValue("D{$movementRow}", $feeRow ? "=ABS({$sell->total->value}) - B{$feeRow}" : "=ABS({$sell->total->value})")
                ->setCellValue("E{$movementRow}", $sell->total->unit)
                ->setCellValue("G{$movementRow}", "=G{$parentMovementRow} - IF(L{$movementRow} > 0, L{$movementRow}, S{$movementRow} / Q{$movementRow})")
                ->setCellValue("H{$movementRow}", "=H{$parentMovementRow}")
                ->setCellValue("I{$movementRow}", "=I{$parentMovementRow} - B{$movementRow}")
                ->setCellValue("J{$movementRow}", "=J{$parentMovementRow}");
        }

        switch ($sell->total->unit) {
            case self::MASTER_FIAT:
                $movements
                    ->setCellValue("L{$movementRow}", $sell->size->unit === self::MASTER_FIAT ? "=B{$movementRow}" : "=G{$parentMovementRow} / I{$parentMovementRow} * B{$movementRow}")
                    ->setCellValue("M{$movementRow}", "=D{$movementRow}")
                    ->setCellValue("N{$movementRow}", "=M{$movementRow} - L{$movementRow}")
                    ->setCellValue("O{$movementRow}", $sell->total->unit);
                break;
            case self::FINAL_FIAT:
                $movements
                    ->setCellValue("Q{$movementRow}", $sell->exchangeRate)
                    ->setCellValue("S{$movementRow}", "=G{$parentMovementRow} / I{$parentMovementRow} * B{$movementRow} * Q{$movementRow}")
                    ->setCellValue("T{$movementRow}", "=D{$movementRow}")
                    ->setCellValue("U{$movementRow}", "=T{$movementRow} - S{$movementRow}")
                    ->setCellValue("V{$movementRow}", $sell->total->unit);
        }

        self::applyUnitColor(
            $movements->getStyle("B{$movementRow}:C{$movementRow}"),
            $sell->size->unit,
            $movementRows
        );

        if (!self::isFiat($sell)) {
            self::applyUnitColor(
                $movements->getStyle("D{$movementRow}:E{$movementRow}"),
                $sell->total->unit,
                $movementRows
            );
        }

        return 1;
    }

    private static function isFiat(AssetMovement $movement): bool
    {
        return $movement->size->unit === self::MASTER_FIAT || $movement->size->unit === self::FINAL_FIAT;
    }

    private static function renderBuy(AssetMovement $buy, Worksheet $movements, int $movementRow, ?int $feeRow, ?int $parentMovementRow, array $movementRows): int
    {
        self::prerenderMovement($buy, $movements, $movementRow)
            ->setCellValue("B{$movementRow}", $feeRow ? "=ABS({$buy->total->value}) - B{$feeRow}" : "=ABS({$buy->total->value})")
            ->setCellValue("C{$movementRow}", $buy->total->unit)
            ->setCellValue("D{$movementRow}", $buy->size->value)
            ->setCellValue("E{$movementRow}", $buy->size->unit)
            ->setCellValue("G{$movementRow}", $parentMovementRow ? "=G{$parentMovementRow} + B{$movementRow}" : "=B{$movementRow}")
            ->setCellValue("H{$movementRow}", "=C{$movementRow}")
            ->setCellValue("I{$movementRow}", $parentMovementRow ? "=I{$parentMovementRow} + D{$movementRow}" : "=D{$movementRow}")
            ->setCellValue("J{$movementRow}", "=E{$movementRow}");

        self::applyUnitColor(
            $movements->getStyle("B{$movementRow}:C{$movementRow}"),
            $buy->total->unit,
            $movementRows
        );
        self::applyUnitColor(
            $movements->getStyle("D{$movementRow}:E{$movementRow}"),
            $buy->size->unit,
            $movementRows
        );

        return 1;
    }

    private static function prerenderMovement(AssetMovement $movement, Worksheet $movements, int $movementRow): Worksheet
    {
        return $movements
            ->setCellValue("A{$movementRow}", $movement->dateTime->format(self::DATE_FORMAT))
            ->setCellValue("Z{$movementRow}", $movement->reference);
    }

    private static function renderAnalytics(Worksheet $movements, Worksheet $analytics, array $movementRows): void
    {
        if (isset($movementRows[self::MASTER_FIAT])) {
            unset($movementRows[self::MASTER_FIAT]);
        }

        $row = 1;
        $rowsPlus1 = count($movementRows) + 1;
        foreach ($movementRows as $symbol => $symbolRow) {
            $row++;
            $analytics
                ->setCellValue("A{$row}", $symbol)
                ->setCellValue("B{$row}", "=(D{$row} / SUM(D2:D{$rowsPlus1}) * 100) / (D{$row} / C{$row})")
                ->setCellValue("C{$row}", "={$movements->getTitle()}!I{$symbolRow}")
                ->setCellValue("D{$row}", "={$movements->getTitle()}!G{$symbolRow}")
                ->setCellValue("E{$row}", "={$movements->getTitle()}!H{$symbolRow}");
        }

        for ($r = 1; $r <= $rowsPlus1; $r++) {
            foreach (['A', 'B', 'C', 'D', 'E'] as $c) {
                $analytics->getStyle("{$c}{$r}")->applyFromArray(self::STYLE_BORDER);
            }
        }
    }

    private static function applyUnitColor(Style $style, string $unit, array $rows): Style
    {
        $colors = [
            'FFFFFF',
            'EAE4E9',
            'CDDAFD',
            'FFF1E6',
            'DFE7FD',
            'FDE2E4',
            'F0EFEB',
            'FAD2E1',
            'BEE1E6',
            'E2ECE9',
        ];

        $color = array_search($unit, array_keys($rows));
        if ($color !== false) {
            $color = ($color + 1) % count($colors);
        }

        return $style->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => "FF{$colors[$color]}"],
            ]
        ]);
    }
}
