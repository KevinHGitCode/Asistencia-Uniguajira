<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SkippedProgramsExport implements FromArray, WithHeadings, WithStyles
{
    private array $rows;

    private const COLS = [
        'Nombre',
        'Tipo',
    ];

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function array(): array
    {
        return array_map(function ($row) {
            $data = [];

            foreach (self::COLS as $col) {
                $data[] = $row[$col] ?? null;
            }

            $data[] = $row['_motivo'] ?? '';

            return $data;
        }, $this->rows);
    }

    public function headings(): array
    {
        return array_merge(self::COLS, ['Motivo de omision']);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E3A8A']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}
