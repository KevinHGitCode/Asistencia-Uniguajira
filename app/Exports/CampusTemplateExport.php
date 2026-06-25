<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CampusTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [['Riohacha'], ['Maicao']];
    }

    public function headings(): array
    {
        return ['Nombre'];
    }
}
