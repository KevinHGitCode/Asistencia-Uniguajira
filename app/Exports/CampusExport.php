<?php

namespace App\Exports;

use App\Models\Campus;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CampusExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Campus::orderBy('name')->get()->map(fn (Campus $campus) => [$campus->name]);
    }

    public function headings(): array
    {
        return ['Nombre'];
    }
}
