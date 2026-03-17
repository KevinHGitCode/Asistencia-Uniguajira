<?php

namespace App\Exports;

use App\Models\Dependency;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DependencyExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return Dependency::orderBy('name')->get()->map(fn ($d) => ['Nombre' => $d->name]);
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
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFCC5E50']],
            ],
        ];
    }
}
