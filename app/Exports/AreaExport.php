<?php

namespace App\Exports;

use App\Models\Area;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AreaExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return Area::orderBy('name')->get()->map(fn ($a) => ['Nombre' => $a->name]);
    }

    public function headings(): array
    {
        return ['Nombre'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getColumnDimension('A')->setWidth(50);

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF62A9B6']],
            ],
        ];
    }
}
