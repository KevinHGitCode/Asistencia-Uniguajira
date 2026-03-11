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
            'date_day'   => ['x' => 224, 'y' => 30.5],
            'date_month' => ['x' => 233, 'y' => 30.5],
            'date_year'  => ['x' => 245.5, 'y' => 30.5],
        ],
        'columns' => [
            'number'  => ['x' => 19, 'w' => 12, 'align' => 'C'],
            'name'    => ['x' => 30.2, 'w' => 61, 'align' => 'L', 'limit' => 32],
            'role'    => ['x' => 105.5, 'w' => 28, 'align' => 'L', 'limit' => 13],
            'program' => ['x' => 137.3, 'w' => 34, 'align' => 'L', 'limit' => 25, 'fontSize' => 10],
            'email'   => ['x' => 180, 'w' => 34, 'align' => 'L', 'limit' => 30],
            'time'    => ['x' => 242.2, 'w' => 20, 'align' => 'C'],
        ],
        'date_format' => [
            'day'   => 'd',
            'month' => 'm',  // numérico para el default
            'year'  => 'Y',
        ],
        'time_format' => 'h:i A',
    ],

    'dependency_1' => [
        'file' => 'LISTADO_DE_ASISTENCIA_BIENESTAR_REVISION_6.pdf',
        'startY' => 80,
        'rowHeight' => 9.15,
        'maxRows' => 12,
        'header' => [
            'dependency'  => ['x' => 78, 'y' => 28],
            'area'        => ['x' => 137, 'y' => 28],
            'title'       => ['x' => 140, 'y' => 36],
            'date_day'    => ['x' => 237.5, 'y' => 28],
            'date_month'  => ['x' => 274.5, 'y' => 28],
            'date_year'   => ['x' => 317.5, 'y' => 28],
            'responsible' => ['x' => 262, 'y' => 36],
        ],
        'columns' => [
            'name'           => ['x' => 30, 'w' => 55, 'h' => 7, 'align' => 'L', 'limit' => 28],
            'identification' => ['x' => 99, 'w' => 25, 'h' => 7, 'align' => 'C', 'limit' => 15],
            'role'           => ['x' => 143, 'w' => 30, 'h' => 7, 'align' => 'L', 'limit' => 15],
            'program'        => ['x' => 183, 'w' => 35, 'h' => 7, 'align' => 'L', 'limit' => 40, 'fontSize' => 12],
            'gender' => [
                'Femenino'  => ['x' => 260, 'y_offset' => 4.5],
                'Masculino' => ['x' => 270, 'y_offset' => 4.5],
                // 'Otro' no se mapea, no marca nada
            ],
            'priority_group' => [
                'Indígena'                   => ['x' => 280, 'y_offset' => 4.5],
                'Afrodescendiente'           => ['x' => 290, 'y_offset' => 4.5],
                'Discapacitado'              => ['x' => 300, 'y_offset' => 4.5],
                'Víctima de Conflicto Armado' => ['x' => 310, 'y_offset' => 4.5],
                'Comunidad LGTBQ+'           => ['x' => 320, 'y_offset' => 4.5],
                'Habitante de Frontera'      => ['x' => 331.5, 'y_offset' => 4.5],
                // // 'Ninguno' no se mapea, no marca nada
            ],
        ],
        'date_format' => [
            'day'   => 'd',
            'month' => 'F',  // nombre completo del mes
            'year'  => 'Y',
        ],
        'time_format' => 'H:i',
    ],
];