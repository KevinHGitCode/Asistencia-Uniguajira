<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SkippedParticipantsExport implements FromArray, WithHeadings, WithStyles
{
    private array $rows;

    /** Cabeceras canónicas del Excel (las mismas que usa la plantilla). */
    private const COLS = [
        'Documento',
        'Nombres',
        'Apellidos',
        'Tipo de Estamento',
        'Correo',
        'Programa o Dependencia',
        'Tipo_progama',
        'Vinculacion',
    ];

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function array(): array
    {
        return array_map(function ($row) {
            $motivo = $row['_motivo'] ?? '';

            // Construir la fila en el orden canónico de columnas
            $data = [];
            foreach (self::COLS as $col) {
                $data[] = $row[$col] ?? null;
            }

            $data[] = $motivo;

            return $data;
        }, $this->rows);
    }

    public function headings(): array
    {
        return array_merge(self::COLS, ['Motivo de omisión']);
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
