<?php

return [
    'default' => [
        'file' => 'LISTADO_DE_ASISTENCIA_GENERAL_REVISION_8.pdf',
        'startY' => 62.2,
        'rowHeight' => 8.15,
        'maxRows' => 16,
        'header' => [
            'dependency' => ['x' => 78, 'y' => 30.5],
            'area'       => ['x' => 128, 'y' => 30.5],
            'title'      => ['x' => 44, 'y' => 38.5],
            'date'       => ['x' => 224, 'y' => 30.5],
        ],
        'columns' => [
            'number'  => ['x' => 19, 'w' => 12, 'align' => 'C'],
            'name'    => ['x' => 30.2, 'w' => 61, 'align' => 'L', 'limit' => 32],
            'role'    => ['x' => 105.5, 'w' => 28, 'align' => 'L', 'limit' => 13],
            'program' => ['x' => 137.3, 'w' => 34, 'align' => 'L', 'limit' => 25, 'fontSize' => 10],
            'email'   => ['x' => 180, 'w' => 34, 'align' => 'L', 'limit' => 30],
            'time'    => ['x' => 242.2, 'w' => 20, 'align' => 'C'],
        ],
        'date_format' => 'd   m    Y',
        'time_format' => 'h:i A',
    ],

    'dependency_1' => [
        'file' => 'LISTADO_DE_ASISTENCIA_BIENESTAR_REVISION_6.pdf',
        'startY' => 80,
        'rowHeight' => 9.15,
        'maxRows' => 12,
        'header' => [
            'dependency' => ['x' => 78, 'y' => 28],
            'area'       => ['x' => 137, 'y' => 28],
            'title'      => ['x' => 140, 'y' => 36],
            'date'       => ['x' => 230, 'y' => 28],
        ],
        'columns' => [
            'name'           => ['x' => 30, 'w' => 55, 'h' => 7, 'align' => 'L', 'limit' => 28],
            'identification' => ['x' => 99, 'w' => 25, 'h' => 7, 'align' => 'C', 'limit' => 15],
            'role'           => ['x' => 143, 'w' => 30, 'h' => 7, 'align' => 'L', 'limit' => 15],
            'program'        => ['x' => 183, 'w' => 35, 'h' => 7, 'align' => 'L', 'limit' => 40, 'fontSize' => 12],
            // 'gender' => [
            //     'F' => ['x' => 178],
            //     'M' => ['x' => 184],
            // ],
            // 'priority_group' => [
            //     'E' => ['x' => 192],
            //     'D' => ['x' => 198],
            //     'V' => ['x' => 204],
            //     'C' => ['x' => 210],
            //     'H' => ['x' => 216],
            // ],
        ],
        'date_format' => 'd/m/Y',
        'time_format' => 'H:i',
    ],
];