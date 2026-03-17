<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParticipantTypeTemplateExport implements FromArray, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [
            ['Estudiante'],
            ['Docente'],
            ['Administrativo'],
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
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF0D9488']],
            ],
            2 => ['font' => ['color' => ['argb' => 'FF6B7280'], 'italic' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFF9FAFB']]],
            3 => ['font' => ['color' => ['argb' => 'FF6B7280'], 'italic' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFF9FAFB']]],
            4 => ['font' => ['color' => ['argb' => 'FF6B7280'], 'italic' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFF9FAFB']]],
        ];
    }
}
