<?php

namespace App\Exports;

use App\Models\Banner;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Reporte de impresiones/clics por día de un banner (ADR-0031), para
 * entregarlo como evidencia al patrocinador.
 */
class BannerReportExport implements FromCollection, WithHeadings, WithStyles
{
    public function __construct(
        private readonly Banner $banner,
        private readonly Collection $days,
        private readonly array $totals,
        private readonly string $dateFrom,
        private readonly string $dateTo,
    ) {}

    public function collection()
    {
        $rows = $this->days->map(fn ($day) => [
            'Fecha' => $day->date->format('d/m/Y'),
            'Impresiones' => $day->impressions,
            'Clics' => $day->clicks,
            'CTR (%)' => $day->impressions > 0 ? round($day->clicks / $day->impressions * 100, 2) : 0,
        ]);

        $rows->push([
            'Fecha' => 'TOTAL ('.$this->dateFrom.' a '.$this->dateTo.')',
            'Impresiones' => $this->totals['impressions'],
            'Clics' => $this->totals['clicks'],
            'CTR (%)' => $this->totals['ctr'],
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return ['Fecha', 'Impresiones', 'Clics', 'CTR (%)'];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(10);

        $lastRow = $this->days->count() + 2; // encabezado + días + fila TOTAL

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFF97316']],
            ],
            $lastRow => ['font' => ['bold' => true]],
        ];
    }
}
