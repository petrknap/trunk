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
    const SUMMARY_DATE = 'Y';
    const USED_COLUMNS = [
        'A', 'B', 'C', 'D', 'E',
        'G', 'H', 'I', 'J',
        'L', 'M', 'N', 'O',
        'S', 'T', 'U', 'V'
    ];
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
        $analytics->setTitle('analytics');

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
        $previousOperation = null;
        $previousSummaryRow = 1;
        foreach ($operations as $operation) {
            /** @var AssetOperation $operation */
            $parentMovementRow = &$movementRows[$operation->size->unit];
            $movementRow++;

            if ($previousOperation && $operation->dateTime->format(self::SUMMARY_DATE) !== $previousOperation->dateTime->format(self::SUMMARY_DATE)) {
                $movementRow += self::renderSummary($movements, $movementRow, $previousSummaryRow);
                $previousSummaryRow = $movementRow - 1;
            }

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

            $previousOperation = $operation;
        }

        $movementRow += self::renderSummary($movements, $movementRow + 1, $previousSummaryRow);
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
            foreach (self::USED_COLUMNS as $c) {
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

        $movements->getColumnDimension('A')->setWidth(12);
        $creations->getColumnDimension('A')->setWidth(12);

        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($path);
    }

    private static function renderSummary(Worksheet $movements, int $summaryRow, int $previousSummaryRow): int
    {
        $negativeSummaryRow = $summaryRow + 1;
        $firstRow = $previousSummaryRow + 1;
        $lastRow = $summaryRow - 1;
        $movements
            ->setCellValue("L{$summaryRow}", "=SUM(L{$firstRow}:L{$lastRow})")
            ->setCellValue("M{$summaryRow}", "=SUM(M{$firstRow}:M{$lastRow})")
            ->setCellValue("N{$summaryRow}", "=SUM(N{$firstRow}:N{$lastRow})")
            ->setCellValue("O{$summaryRow}", self::MASTER_FIAT)
            ->setCellValue("S{$summaryRow}", "=SUM(S{$firstRow}:S{$lastRow})")
            ->setCellValue("T{$summaryRow}", "=SUM(T{$firstRow}:T{$lastRow})")
            ->setCellValue("U{$summaryRow}", "=SUM(U{$firstRow}:U{$lastRow})")
            ->setCellValue("V{$summaryRow}", self::FINAL_FIAT)
            ->setCellValue("Z{$summaryRow}", "+SUM")
            ->setCellValue("L{$negativeSummaryRow}", "=-L{$summaryRow}")
            ->setCellValue("M{$negativeSummaryRow}", "=-M{$summaryRow}")
            ->setCellValue("N{$negativeSummaryRow}", "=-N{$summaryRow}")
            ->setCellValue("O{$negativeSummaryRow}", "=O{$summaryRow}")
            ->setCellValue("S{$negativeSummaryRow}", "=-S{$summaryRow}")
            ->setCellValue("T{$negativeSummaryRow}", "=-T{$summaryRow}")
            ->setCellValue("U{$negativeSummaryRow}", "=-U{$summaryRow}")
            ->setCellValue("V{$negativeSummaryRow}", "=V{$summaryRow}")
            ->setCellValue("Z{$negativeSummaryRow}", "-SUM");

        foreach (self::USED_COLUMNS as $column) {
            $movements->getStyle("{$column}{$summaryRow}")
                ->applyFromArray(self::STYLE_BORDER + [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF000000']
                    ],
                    'font' => [
                        'color' => ['argb' => 'FFFFFFFF']
                    ],
                ]);
        }

        return 2;
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

    private function isExchange(AssetMovement $movement): bool
    {
        return $movement->total->unit != self::MASTER_FIAT && $movement->total->unit != self::FINAL_FIAT;
    }

    private static function renderExchange(AssetMovement $exchange, Worksheet $movements, int $movementRow, array &$movementRows): int
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
                ->setCellValue("G{$movementRow}", "=ROUND(G{$parentMovementRow} - IF(L{$movementRow} > 0, L{$movementRow}, IF(S{$movementRow} > 0, S{$movementRow} / Q{$movementRow}, 0)), 10)")
                ->setCellValue("H{$movementRow}", "=H{$parentMovementRow}")
                ->setCellValue("I{$movementRow}", "=ROUND(I{$parentMovementRow} - B{$movementRow}, 10)")
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

    private static function prerenderMovement(AssetMovement $movement, Worksheet $movements, int $movementRow): Worksheet
    {
        return $movements
            ->setCellValue("A{$movementRow}", $movement->dateTime->format(self::DATE_FORMAT))
            ->setCellValue("Z{$movementRow}", $movement->reference);
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

    private static function renderAnalytics(Worksheet $movements, Worksheet $analytics, array $movementRows): void
    {
        $analytics
            ->setCellValue('B1', '100 ' . self::MASTER_FIAT)
            ->setCellValue('C1', 'size total')
            ->setCellValue('D1', 'price total');

        $row = 1;
        $rowsPlus1 = count($movementRows) + 1;
        foreach ([null => null] + $movementRows as $symbol => $symbolRow) {
            if ($symbol === self::MASTER_FIAT) {
                continue;
            }

            if ($row > 1) {
                $analytics
                    ->setCellValue("A{$row}", $symbol)
                    ->setCellValue("B{$row}", "=(D{$row} / SUM(D2:D{$rowsPlus1}) * 100) / (IF(D{$row} = 0, 0.000000001, D{$row}) / IF(C{$row} = 0, 0.000000001, C{$row}))")
                    ->setCellValue("C{$row}", "={$movements->getTitle()}!I{$symbolRow}")
                    ->setCellValue("D{$row}", "={$movements->getTitle()}!G{$symbolRow}")
                    ->setCellValue("E{$row}", "={$movements->getTitle()}!H{$symbolRow}");

                self::applyUnitColor($analytics->getStyle("A{$row}:C{$row}"), $symbol, $movementRows);
            }

            foreach (['A', 'B', 'C', 'D', 'E'] as $column) {
                $analytics->getStyle("{$column}{$row}")->applyFromArray(
                    self::STYLE_BORDER + ($row === 1 ? [
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FF000000']
                        ],
                        'font' => [
                            'color' => ['argb' => 'FFFFFFFF']
                        ],
                    ] : [])
                );
            }

            $row++;
        }
    }
}
