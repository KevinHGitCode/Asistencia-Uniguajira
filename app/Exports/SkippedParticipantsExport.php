<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SkippedParticipantsExport implements FromArray, WithHeadings, WithStyles
{
    private array $rows;

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function array(): array
    {
        return array_map(function ($row) {
            // $row puede ser un array indexado (0..7) más '_motivo'
            $motivo = $row['_motivo'] ?? '';
            unset($row['_motivo']);

            // Asegurar que sea array plano
            $data = array_values($row);

            // Rellenar hasta 8 columnas
            while (count($data) < 8) {
                $data[] = null;
            }

            // Agregar motivo al final
            $data[] = $motivo;

            return $data;
        }, $this->rows);
    }

    public function headings(): array
    {
        return [
            'Documento',
            'Nombres',
            'Apellidos',
            'Rol',
            'Correo',
            'Programa - Sede',
            'Tipo Programa',
            'Afiliación',
            'Motivo de omisión',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFCC5E50']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}
