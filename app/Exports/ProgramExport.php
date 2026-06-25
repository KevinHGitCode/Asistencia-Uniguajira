<?php

namespace App\Exports;

use App\Models\Program;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProgramExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(private readonly ?int $campusId = null) {}

    public function collection()
    {
        return Program::query()
            ->when($this->campusId, fn ($query) => $query->where('campus_id', $this->campusId))
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'Nombre' => $p->name,
                'Lugar de oferta' => $p->offer_location ?? '',
                'Tipo' => $p->program_type ?? '',
            ]);
    }

    public function headings(): array
    {
        return ['Nombre', 'Lugar de oferta', 'Tipo'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getColumnDimension('A')->setWidth(50);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF2563EB']],
            ],
        ];
    }
}
