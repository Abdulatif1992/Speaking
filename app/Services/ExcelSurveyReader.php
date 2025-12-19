<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ExcelSurveyReader
{
    public function read(string $filePath, string $sheetName): array
    {
        if (!is_file($filePath)) {
            throw new RuntimeException("File topilmadi: {$filePath}");
        }

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);

        $sheet = $spreadsheet->getSheetByName($sheetName);
        if (!$sheet) {
            $available = array_map(fn($s) => $s->getTitle(), $spreadsheet->getAllSheets());
            throw new RuntimeException("Sheet topilmadi: {$sheetName}. Mavjud: " . implode(', ', $available));
        }

        $startRow = 6;
        $highestRow = $sheet->getHighestRow();

        $tenStartIdx  = Coordinate::columnIndexFromString('B');
        $tenEndIdx    = Coordinate::columnIndexFromString('AC');

        $fiveStartIdx = Coordinate::columnIndexFromString('AD');
        $fiveEndIdx   = Coordinate::columnIndexFromString('AK');

        $out = [];

        for ($row = $startRow; $row <= $highestRow; $row++) {
            $question = trim((string)$sheet->getCell("A{$row}")->getFormattedValue());
            if ($question === '') {
                continue;
            }

            $answers10 = [];
            for ($c = $tenStartIdx; $c <= $tenEndIdx; $c++) {
                $col = Coordinate::stringFromColumnIndex($c);
                $val = trim((string)$sheet->getCell("{$col}{$row}")->getFormattedValue());
                if ($val !== '') {
                    $answers10[] = $val;
                }
            }

            $answers5 = [];
            for ($c = $fiveStartIdx; $c <= $fiveEndIdx; $c++) {
                $col = Coordinate::stringFromColumnIndex($c);
                $val = trim((string)$sheet->getCell("{$col}{$row}")->getFormattedValue());
                if ($val !== '') {
                    $answers5[] = $val;
                }
            }

            $out[] = [
                'question' => $question,
                'answers' => [
                    '10_point' => $answers10,
                    '5_point'  => $answers5,
                ],
            ];
        }

        return $out;
    }
}
