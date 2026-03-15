<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParticipantTemplateExport implements FromArray, WithHeadings, WithStyles
{
    /** Fila de ejemplo para guiar al usuario. */
    public function array(): array
    {
        return [
            [
                '1234567890',
                'Juan Carlos',
                'Pérez García',
                'Estudiante',
                'juan@correo.co',
                'Ingeniería de Sistemas - Riohacha',
                'Pregrado',
                '',
            ],
        ];
    }

    /** Cabeceras exactas que espera el importador. */
    public function headings(): array
    {
        return [
            'Documento',
            'Nombres',
            'Apellidos',
            'Tipo de Estamento',
            'Correo',
            'Programa o Dependencia',
            'Tipo_progama',
            'Vinculacion',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(16);
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->getColumnDimension('C')->setWidth(22);
        $sheet->getColumnDimension('D')->setWidth(22);
        $sheet->getColumnDimension('E')->setWidth(28);
        $sheet->getColumnDimension('F')->setWidth(38);
        $sheet->getColumnDimension('G')->setWidth(16);
        $sheet->getColumnDimension('H')->setWidth(22);

        return [
            // Fila 1: encabezado (azul oscuro + blanco)
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1E3A8A']],
            ],
            // Fila 2: ejemplo (gris suave)
            2 => [
                'font'  => ['color' => ['argb' => 'FF6B7280'], 'italic' => true],
                'fill'  => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFF9FAFB']],
            ],
        ];
    }
}
