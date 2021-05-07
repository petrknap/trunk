<?php

namespace Ucetnictvi\Asset;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Style;
use Ucetnictvi\Entity\AssetCreation;
use Ucetnictvi\Entity\AssetMovement;
use Ucetnictvi\Entity\AssetOperation;

class Generator
{
    const MASTER_FIAT = 'EUR';
    const FINAL_FIAT = 'CZK';
    const DATE_FORMAT = '=\D\A\T\E\(Y\,m\,d\)';

    private function isSell(AssetMovement $movement): bool
    {
        return $movement->size->value < 0 || $movement->total->value > 0;
    }

    private function applyUnitColor(Style $style, string $unit, array $rows): Style
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
            $color++;
        }

        return $style->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => "FF{$colors[$color]}"],
            ]
        ]);
    }

    public function generateXlsx(array $operations, string $path)
    {
        $spreadsheet = new Spreadsheet();

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

            if ($operation instanceof AssetCreation) {
                /** @var AssetCreation $operation */
                $creationRow++;
                $creations
                    ->setCellValue("A{$creationRow}", $operation->dateTime->format(self::DATE_FORMAT))
                    ->setCellValue("B{$creationRow}", $operation->size->value)
                    ->setCellValue("C{$creationRow}", $operation->size->unit)
                    ->setCellValue("D{$creationRow}", "=B{$creationRow} * AVERAGE(I{$creationRow}:Y{$creationRow}) * G{$creationRow}")
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

                $movementRow++;

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
                if ($operation->fee->value) {
                    $movementRow++;
                    $movements
                        ->setCellValue("A{$movementRow}", $operation->dateTime->format(self::DATE_FORMAT))
                        ->setCellValue("B{$movementRow}", $operation->fee->value)
                        ->setCellValue("C{$movementRow}", $operation->fee->unit)
                        ->setCellValue("D{$movementRow}", 0)
                        ->setCellValue("L{$movementRow}", "=B{$movementRow}")
                        ->setCellValue("M{$movementRow}", "=D{$movementRow}")
                        ->setCellValue("N{$movementRow}", "=M{$movementRow} - L{$movementRow}")
                        ->setCellValue("O{$movementRow}", self::MASTER_FIAT)
                        ->setCellValue("Z{$movementRow}", $operation->reference);
                    $this->applyUnitColor(
                        $movements->getStyle("B{$movementRow}:C{$movementRow}"),
                        $operation->fee->unit,
                        $movementRows
                    );
                    $feeRow = $movementRow;
                } else {
                    $feeRow = null;
                }
                $movementRow++;
                $movements
                    ->setCellValue("A{$movementRow}", $operation->dateTime->format(self::DATE_FORMAT))
                    ->setCellValue("Z{$movementRow}", $operation->reference);
                if ($this->isSell($operation)) {
                    $movements
                        ->setCellValue("B{$movementRow}", "=ABS({$operation->size->value})")
                        ->setCellValue("C{$movementRow}", $operation->size->unit)
                        ->setCellValue("D{$movementRow}", $feeRow ? "=ABS({$operation->total->value}) - B{$feeRow}" : "=ABS({$operation->total->value})")
                        ->setCellValue("E{$movementRow}", $operation->total->unit)
                        ->setCellValue("G{$movementRow}", "=G{$parentMovementRow} - IF(L{$movementRow} > 0, L{$movementRow}, S{$movementRow} / Q{$movementRow})")
                        ->setCellValue("H{$movementRow}", "=H{$parentMovementRow}")
                        ->setCellValue("I{$movementRow}", "=I{$parentMovementRow} - B{$movementRow}")
                        ->setCellValue("J{$movementRow}", "=J{$parentMovementRow}");
                    switch ($operation->total->unit) {
                        case self::MASTER_FIAT:
                            $movements
                                ->setCellValue("L{$movementRow}", "=G{$parentMovementRow} / I{$parentMovementRow} * B{$movementRow}")
                                ->setCellValue("M{$movementRow}", "=D{$movementRow}")
                                ->setCellValue("N{$movementRow}", "=M{$movementRow} - L{$movementRow}")
                                ->setCellValue("O{$movementRow}", $operation->total->unit);
                            break;
                        case self::FINAL_FIAT:
                            $movements
                                ->setCellValue("Q{$movementRow}", $operation->exchangeRate)
                                ->setCellValue("S{$movementRow}", "=G{$parentMovementRow} / I{$parentMovementRow} * B{$movementRow} * Q{$movementRow}")
                                ->setCellValue("T{$movementRow}", "=D{$movementRow}")
                                ->setCellValue("U{$movementRow}", "=T{$movementRow} - S{$movementRow}")
                                ->setCellValue("V{$movementRow}", $operation->total->unit);
                    }
                    $this->applyUnitColor(
                        $movements->getStyle("B{$movementRow}:C{$movementRow}"),
                        $operation->size->unit,
                        $movementRows
                    );
                    $this->applyUnitColor(
                        $movements->getStyle("D{$movementRow}:E{$movementRow}"),
                        $operation->total->unit,
                        $movementRows
                    );
                } else {
                    $movements
                        ->setCellValue("B{$movementRow}", $feeRow ? "=ABS({$operation->total->value}) - B{$feeRow}" : "=ABS({$operation->total->value})")
                        ->setCellValue("C{$movementRow}", $operation->total->unit)
                        ->setCellValue("D{$movementRow}", $operation->size->value)
                        ->setCellValue("E{$movementRow}", $operation->size->unit)
                        ->setCellValue("G{$movementRow}", $parentMovementRow ? "=G{$parentMovementRow} + B{$movementRow}" : "=B{$movementRow}")
                        ->setCellValue("H{$movementRow}", "=C{$movementRow}")
                        ->setCellValue("I{$movementRow}", $parentMovementRow ? "=I{$parentMovementRow} + D{$movementRow}" : "=D{$movementRow}")
                        ->setCellValue("J{$movementRow}", "=E{$movementRow}");
                    $this->applyUnitColor(
                        $movements->getStyle("B{$movementRow}:C{$movementRow}"),
                        $operation->total->unit,
                        $movementRows
                    );
                    $this->applyUnitColor(
                        $movements->getStyle("D{$movementRow}:E{$movementRow}"),
                        $operation->size->unit,
                        $movementRows
                    );
                }

                $parentMovementRow = $movementRow;
            }
        }
        for ($r = 1; $r <= $creationRow; $r++) {
            foreach (['A', 'B', 'C', 'D', 'E'] as $c) {
                $creations->getStyle("{$c}{$r}")->applyFromArray([
                    'borders' => [
                        'bottom' => ['borderStyle' => Border::BORDER_THIN],
                        'right' => ['borderStyle' => Border::BORDER_THIN],
                        'top' => ['borderStyle' => Border::BORDER_THIN],
                        'left' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ] + ($r === 1 ? [
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
                $movements->getStyle("{$c}{$r}")->applyFromArray([
                    'borders' => [
                        'bottom' => ['borderStyle' => Border::BORDER_THIN],
                        'right' => ['borderStyle' => Border::BORDER_THIN],
                        'top' => ['borderStyle' => Border::BORDER_THIN],
                        'left' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ] + ($r === 1 ? [
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
}
