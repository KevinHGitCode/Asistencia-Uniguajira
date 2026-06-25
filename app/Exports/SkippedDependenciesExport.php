<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SkippedDependenciesExport implements FromArray, WithHeadings, WithStyles
{
    public function __construct(private readonly array $rows) {}

    public function array(): array
    {
        return array_map(fn (array $row) => [
            $row['Nombre'] ?? null,
            $row['_motivo'] ?? '',
        ], $this->rows);
    }

    public function headings(): array
    {
        return ['Nombre', 'Motivo de omision'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF9F1239']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}
