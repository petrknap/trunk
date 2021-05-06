<?php

namespace Ucetnictvi\Asset;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
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

        return $style->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => $colors[$color]],
            ]
        ]);
    }

    public function generateXlsx(array $operations, string $path)
    {
        $spreadsheet = new Spreadsheet();

        $movements = $spreadsheet->setActiveSheetIndex(0);
        $movements
            ->setTitle('balance')
            ->setCellValue('A1', 'date')
            ->setCellValue('B1', 'input')
            ->setCellValue('D1', 'output')
            ->setCellValue('G1', 'price total')
            ->setCellValue('I1', 'size total')
            ->setCellValue('L1', 'outcome')
            ->setCellValue('M1', 'income')
            ->setCellValue('N1', 'difference')
            ->setCellValue('Q1', self::FINAL_FIAT . '/' . self::MASTER_FIAT)
            ->setCellValue('S1', 'outcome')
            ->setCellValue('T1', 'income')
            ->setCellValue('U1', 'difference')
            ->setCellValue('X1', 'reference');

        $parentRows = [];
        $row = 1;
        foreach ($operations as $operation) {
            /** @var AssetOperation $operation */
            $parentRow = &$parentRows[$operation->size->unit];

            /** @var AssetMovement $operation */
            if ($operation->fee->value) {
                $row++;
                $movements
                    ->setCellValue("A{$row}", $operation->dateTime->format(self::DATE_FORMAT))
                    ->setCellValue("B{$row}", $operation->fee->value)
                    ->setCellValue("C{$row}", $operation->fee->unit)
                    ->setCellValue("D{$row}", 0)
                    ->setCellValue("L{$row}", "=B{$row}")
                    ->setCellValue("M{$row}", "=D{$row}")
                    ->setCellValue("N{$row}", "=M{$row} - L{$row}")
                    ->setCellValue("O{$row}", self::MASTER_FIAT)
                    ->setCellValue("X{$row}", $operation->reference);
                $this->applyUnitColor(
                    $movements->getStyle("B{$row}:C{$row}"),
                    $operation->fee->unit,
                    $parentRows
                );
                $feeRow = $row;
            } else {
                $feeRow = null;
            }
            $row++;
            $movements
                ->setCellValue("A{$row}", $operation->dateTime->format(self::DATE_FORMAT))
                ->setCellValue("X{$row}", $operation->reference);
            if ($this->isSell($operation)) {
                $movements
                    ->setCellValue("B{$row}", "=ABS({$operation->size->value})")
                    ->setCellValue("C{$row}", $operation->size->unit)
                    ->setCellValue("D{$row}", $feeRow ? "=ABS({$operation->total->value}) - B{$feeRow}" : "=ABS({$operation->total->value})")
                    ->setCellValue("E{$row}", $operation->total->unit)
                    ->setCellValue("G{$row}", "=G{$parentRow} - IF(L{$row} > 0, L{$row}, S{$row} / Q{$row})")
                    ->setCellValue("H{$row}", "=H{$parentRow}")
                    ->setCellValue("I{$row}", "=I{$parentRow} - B{$row}")
                    ->setCellValue("J{$row}", "=J{$parentRow}");
                switch ($operation->total->unit) {
                    case self::MASTER_FIAT:
                        $movements
                            ->setCellValue("L{$row}", "=G{$parentRow} / I{$parentRow} * B{$row}")
                            ->setCellValue("M{$row}", "=D{$row}")
                            ->setCellValue("N{$row}", "=M{$row} - L{$row}")
                            ->setCellValue("O{$row}", $operation->total->unit);
                        break;
                    case self::FINAL_FIAT:
                        $movements
                            ->setCellValue("Q{$row}", $operation->exchangeRate)
                            ->setCellValue("S{$row}", "=G{$parentRow} / I{$parentRow} * B{$row} * Q{$row}")
                            ->setCellValue("T{$row}", "=D{$row}")
                            ->setCellValue("U{$row}", "=T{$row} - S{$row}")
                            ->setCellValue("V{$row}", $operation->total->unit);
                }
                $this->applyUnitColor(
                    $movements->getStyle("B{$row}:C{$row}"),
                    $operation->size->unit,
                    $parentRows
                );
                $this->applyUnitColor(
                    $movements->getStyle("D{$row}:E{$row}"),
                    $operation->total->unit,
                    $parentRows
                );
            } else {
                $movements
                    ->setCellValue("B{$row}", $feeRow ? "=ABS({$operation->total->value}) - B{$feeRow}" : "=ABS({$operation->total->value})")
                    ->setCellValue("C{$row}", $operation->total->unit)
                    ->setCellValue("D{$row}", $operation->size->value)
                    ->setCellValue("E{$row}", $operation->size->unit)
                    ->setCellValue("G{$row}", $parentRow ? "=G{$parentRow} + B{$row}" : "=B{$row}")
                    ->setCellValue("H{$row}", "=C{$row}")
                    ->setCellValue("I{$row}", $parentRow ? "=I{$parentRow} + D{$row}" : "=D{$row}")
                    ->setCellValue("J{$row}", "=E{$row}");
                $this->applyUnitColor(
                    $movements->getStyle("B{$row}:C{$row}"),
                    $operation->total->unit,
                    $parentRows
                );
                $this->applyUnitColor(
                    $movements->getStyle("D{$row}:E{$row}"),
                    $operation->size->unit,
                    $parentRows
                );
            }

            $parentRow = $row;
        }
        for ($r = 1; $r <= $row; $r++) {
            foreach (['A', 'B', 'C', 'D', 'E', 'G', 'H', 'I', 'J', 'L', 'M', 'N', 'O', 'S', 'T', 'U', 'V'] as $c) {
                $movements->getStyle("{$c}{$r}")->applyFromArray([
                    'borders' => [
                        'bottom' => ['borderStyle' => Border::BORDER_THIN],
                        'right' => ['borderStyle' => Border::BORDER_THIN],
                        'top' => ['borderStyle' => Border::BORDER_THIN],
                        'left' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);
            }
        }

        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($path);
    }
}
