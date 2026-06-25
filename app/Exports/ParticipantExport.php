<?php

namespace App\Exports;

use App\Models\ParticipantRole;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParticipantExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function query()
    {
        return ParticipantRole::query()
            ->with([
                'participant:id,document,first_name,last_name,email',
                'type:id,name',
                'program:id,name,program_type',
                'dependency:id,name',
                'affiliation:id,name',
            ])
            ->where('is_active', true)
            ->join('participants', 'participant_roles.participant_id', '=', 'participants.id')
            ->orderBy('participants.first_name')
            ->orderBy('participants.last_name')
            ->orderBy('participants.document')
            ->orderBy('participant_roles.id')
            ->select('participant_roles.*');
    }

    public function map($role): array
    {
        return [
            $role->participant?->document ?? '',
            $role->participant?->first_name ?? '',
            $role->participant?->last_name ?? '',
            $role->type?->name ?? '',
            $role->participant?->email ?? '',
            $role->program?->name ?? $role->dependency?->name ?? '',
            $role->program?->program_type ?? '',
            $role->affiliation?->name ?? '',
        ];
    }

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
        $sheet->getColumnDimension('A')->setWidth(16);
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->getColumnDimension('C')->setWidth(22);
        $sheet->getColumnDimension('D')->setWidth(22);
        $sheet->getColumnDimension('E')->setWidth(28);
        $sheet->getColumnDimension('F')->setWidth(38);
        $sheet->getColumnDimension('G')->setWidth(16);
        $sheet->getColumnDimension('H')->setWidth(22);

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF2563EB']],
            ],
        ];
    }
}
