<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AffiliationTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ['Tiempo completo'],
            ['Medio tiempo'],
            ['Cátedra'],
        ];
    }

    public function headings(): array
    {
        return ['Nombre'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getColumnDimension('A')->setWidth(40);

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF7C6FCD']],
            ],
            2 => ['font' => ['color' => ['argb' => 'FF6B7280'], 'italic' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFF9FAFB']]],
            3 => ['font' => ['color' => ['argb' => 'FF6B7280'], 'italic' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFF9FAFB']]],
            4 => ['font' => ['color' => ['argb' => 'FF6B7280'], 'italic' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFF9FAFB']]],
        ];
    }
}
