<?php

namespace App\Services;

use setasign\Fpdi\Tfpdf\Fpdi;
use Carbon\Carbon;

class AttendancePdfService
{
    public function generarPdf($evento)
    {   
        function limitarTexto($texto, $limite = 25) {
            return mb_strlen($texto) > $limite
                ? mb_substr($texto, 0, $limite - 3) . '...'
                : $texto;
        }

        $pdf = new Fpdi();
        $path = public_path('formatos/LISTADO_DE_ASISTENCIA_GENERAL_REVISION_8.pdf');

        if (!file_exists($path)) {
            throw new \Exception("Archivo no encontrado en $path");
        }

        $pageCount = $pdf->setSourceFile($path);
        $tplIdx = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tplIdx);

        // ====== Función para crear página con encabezado ======
        $crearPagina = function() use ($pdf, $tplIdx, $size, $evento) {

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplIdx);

            $pdf->SetFont('Arial', 'B', 12);

            // Dependencia
            $pdf->SetXY(78, 30.5);
            $pdf->Cell(0, 8,
                iconv('UTF-8', 'ISO-8859-1//TRANSLIT',
                    mb_strtoupper($evento->dependency->name ?? 'SIN DEPENDENCIA', 'UTF-8')
                ),
                0, 0, 'L'
            );

            // Area
            if ($evento->area) {
                $pdf->SetXY(128.5, 30.5);
                $pdf->Cell(0,8,
                    iconv('UTF-8','ISO-8859-1//TRANSLIT',
                        ' - ' . mb_strtoupper($evento->area->name, 'UTF-8')
                    ),
                    0,0,'L'
                );
            }


            // Título
            $pdf->SetXY(44, 38.5);
            $pdf->Cell(0, 8,
                iconv('UTF-8', 'ISO-8859-1//TRANSLIT',
                    mb_strtoupper($evento->title ?? 'SIN TÍTULO', 'UTF-8')
                ),
                0, 0, 'L'
            );

            // Fecha
            $pdf->SetXY(224, 30.5);
            $pdf->Cell(0, 8,
                iconv('UTF-8', 'ISO-8859-1//TRANSLIT',
                    Carbon::parse($evento->date)->format('d   m    Y')
                ),
                0, 0, 'L'
            );

            $pdf->SetFont('Arial', '', 12);
        };

        // Primera página
        $crearPagina();

        $startY = 62.2;
        $rowHeight = 8.15;
        $maxRows = 16;
        $row = 0;

        foreach ($evento->asistencias as $i => $asistencia) {

            if ($row >= $maxRows) {
                $crearPagina();
                $row = 0;
            }

            $p = $asistencia->participant;
            $y = round($startY + ($row * $rowHeight), 2);

            // Número
            $pdf->SetXY(19, $y);
            $pdf->Cell(12, 7.8, $i + 1, 0, 0, 'C');

            // Nombre
            $pdf->SetXY(30.2, $y);
            $pdf->Cell(61, 7.8,
                iconv('UTF-8', 'ISO-8859-1//TRANSLIT',
                    limitarTexto(trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? '')), 32)
                ),
                0, 0, 'L'
            );

            // Cargo
            $pdf->SetXY(105.5, $y);
            $pdf->Cell(28, 7.8,
                iconv('UTF-8', 'ISO-8859-1//TRANSLIT',
                    limitarTexto($p->role ?? '', 13)
                ),
                0, 0, 'L'
            );

            // Dependencia / programa
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetXY(137.3, $y);
            $pdf->Cell(34, 7.8,
                iconv('UTF-8', 'ISO-8859-1//TRANSLIT',
                    limitarTexto($p->program->name ?? '', 18)
                ),
                0, 0, 'L'
            );

            // Correo
            $pdf->SetXY(180, $y);
            $pdf->Cell(34, 7.8,
                iconv('UTF-8', 'ISO-8859-1//TRANSLIT',
                    limitarTexto($p->email ?? '', 30)
                ),
                0, 0, 'L'
            );

            // Hora
            $pdf->SetFont('Arial', '', 12);
            $pdf->SetXY(242.2, $y);
            $pdf->Cell(20, 7.8,
                Carbon::parse($asistencia->created_at)->format('h:i A'),
                0, 0, 'C'
            );

            // // === Sexo ===
            // if (($p->gender ?? '') === 'F') { $pdf->SetXY(178, $y); $pdf->Write(0, 'X'); }
            // if (($p->gender ?? '') === 'M') { $pdf->SetXY(184, $y); $pdf->Write(0, 'X'); }

            // // === Grupo priorizado ===
            // $grupos = is_array($p->priority_group)
            //     ? $p->priority_group
            //     : explode(',', $p->priority_group ?? '');

            // foreach ($grupos as $g) {
            //     switch (trim($g)) {
            //         case 'E': $pdf->SetXY(192, $y); $pdf->Write(0, 'X'); break;
            //         case 'D': $pdf->SetXY(198, $y); $pdf->Write(0, 'X'); break;
            //         case 'V': $pdf->SetXY(204, $y); $pdf->Write(0, 'X'); break;
            //         case 'C': $pdf->SetXY(210, $y); $pdf->Write(0, 'X'); break;
            //         case 'H': $pdf->SetXY(216, $y); $pdf->Write(0, 'X'); break;
            //     }
            // }

            $row++;
        }
        return $pdf->Output('S'); // 'S' devuelve el PDF como string
    }
}
