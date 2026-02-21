<?php

namespace App\Services;

use setasign\Fpdi\Tfpdf\Fpdi;
use Carbon\Carbon;

class AttendancePdfService
{
    private function getConfig($dependencyId): array
    {
        $key = "attendance_formats.dependency_{$dependencyId}";
        
        return config($key) ?? config('attendance_formats.default');
    }

    private function toIso($text): string
    {
        return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
    }

    private function truncateText($text, $limit = 25): string
    {
        return mb_strlen($text) > $limit
            ? mb_substr($text, 0, $limit - 3) . '...'
            : $text;
    }

    public function generatePdf($event): string
    {
        $cfg = $this->getConfig($event->dependency_id);

        $pdf = new Fpdi();
        $path = public_path("formats/{$cfg['file']}");

        if (!file_exists($path)) {
            throw new \Exception("File not found at $path");
        }

        $pdf->setSourceFile($path);
        $tplIdx = $pdf->importPage(1);
        $size = $pdf->getTemplateSize($tplIdx);

        // ====== Header ======
        $createPage = function () use ($pdf, $tplIdx, $size, $event, $cfg) {
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplIdx);
            $pdf->SetFont('Arial', 'B', 12);

            $header = $cfg['header'];

            if (isset($header['dependency'])) {
                $pdf->SetXY($header['dependency']['x'], $header['dependency']['y']);
                $pdf->Cell(0, 8,
                    $this->toIso(mb_strtoupper($event->dependency->name ?? 'SIN DEPENDENCIA', 'UTF-8')),
                    0, 0, 'L'
                );
            }

            if (isset($header['area']) && $event->area) {
                $pdf->SetXY($header['area']['x'], $header['area']['y']);
                $pdf->Cell(0, 8,
                    $this->toIso(' - ' . mb_strtoupper($event->area->name, 'UTF-8')),
                    0, 0, 'L'
                );
            }

            if (isset($header['title'])) {
                $pdf->SetXY($header['title']['x'], $header['title']['y']);
                $pdf->Cell(0, 8,
                    $this->toIso(mb_strtoupper($event->title ?? 'SIN TÍTULO', 'UTF-8')),
                    0, 0, 'L'
                );
            }

            if (isset($header['date'])) {
                $pdf->SetXY($header['date']['x'], $header['date']['y']);
                $pdf->Cell(0, 8,
                    $this->toIso(Carbon::parse($event->date)->format($cfg['date_format'])),
                    0, 0, 'L'
                );
            }

            $pdf->SetFont('Arial', '', 12);
        };

        $createPage();

        $row = 0;
        $cols = $cfg['columns'];

        foreach ($event->asistencias as $i => $attendance) {
            if ($row >= $cfg['maxRows']) {
                $createPage();
                $row = 0;
            }

            $p = $attendance->participant;
            $y = round($cfg['startY'] + ($row * $cfg['rowHeight']), 2);
            $pdf->SetFont('Arial', '', 12);

            if (isset($cols['number'])) {
                $pdf->SetXY($cols['number']['x'], $y);
                $pdf->Cell($cols['number']['w'], $cols['number']['h'] ?? 7.8, $i + 1, 0, 0, $cols['number']['align']);
            }

            if (isset($cols['name'])) {
                $pdf->SetXY($cols['name']['x'], $y);
                $pdf->Cell($cols['name']['w'], $cols['name']['h'] ?? 7.8,
                    $this->toIso($this->truncateText(trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? '')), $cols['name']['limit'])),
                    0, 0, $cols['name']['align']
                );
            }

            if (isset($cols['identification'])) {
                $pdf->SetXY($cols['identification']['x'], $y);
                $pdf->Cell($cols['identification']['w'], $cols['identification']['h'] ?? 7.8,
                    $this->toIso($this->truncateText($p->document ?? '', $cols['identification']['limit'])),
                    0, 0, $cols['identification']['align']
                );
            }

            if (isset($cols['role'])) {
                $pdf->SetXY($cols['role']['x'], $y);
                $pdf->Cell($cols['role']['w'], $cols['role']['h'] ?? 7.8,
                    $this->toIso($this->truncateText($p->role ?? '', $cols['role']['limit'])),
                    0, 0, $cols['role']['align']
                );
            }

            if (isset($cols['program'])) {
                if (isset($cols['program']['fontSize'])) {
                    $pdf->SetFont('Arial', '', $cols['program']['fontSize']);
                }
                $pdf->SetXY($cols['program']['x'], $y);
                $pdf->Cell($cols['program']['w'], $cols['program']['h'] ?? 7.8,
                    $this->toIso($this->truncateText($p->program->name ?? '', $cols['program']['limit'])),
                    0, 0, $cols['program']['align']
                );
            }

            if (isset($cols['email'])) {
                $pdf->SetFont('Arial', '', 12);
                $pdf->SetXY($cols['email']['x'], $y);
                $pdf->Cell($cols['email']['w'], $cols['email']['h'] ?? 7.8,
                    $this->toIso($this->truncateText($p->email ?? '', $cols['email']['limit'])),
                    0, 0, $cols['email']['align']
                );
            }

            if (isset($cols['phone'])) {
                $pdf->SetXY($cols['phone']['x'], $y);
                $pdf->Cell($cols['phone']['w'], $cols['phone']['h'] ?? 7.8,
                    $this->toIso($this->truncateText($p->phone ?? '', $cols['phone']['limit'])),
                    0, 0, $cols['phone']['align']
                );
            }

            if (isset($cols['time'])) {
                $pdf->SetFont('Arial', '', 12);
                $pdf->SetXY($cols['time']['x'], $y);
                $pdf->Cell($cols['time']['w'], $cols['time']['h'] ?? 7.8,
                    Carbon::parse($attendance->created_at)->format($cfg['time_format']),
                    0, 0, $cols['time']['align']
                );
            }

            // === Gender ===
            if (isset($cols['gender'])) {
                $gender = $p->gender ?? '';
                if (isset($cols['gender'][$gender])) {
                    $pdf->SetXY($cols['gender'][$gender]['x'], $y);
                    $pdf->Write(0, 'X');
                }
            }

            // === Priority Group ===
            if (isset($cols['priority_group'])) {
                $groups = is_array($p->priority_group)
                    ? $p->priority_group
                    : explode(',', $p->priority_group ?? '');

                foreach ($groups as $g) {
                    $g = trim($g);
                    if (isset($cols['priority_group'][$g])) {
                        $pdf->SetXY($cols['priority_group'][$g]['x'], $y);
                        $pdf->Write(0, 'X');
                    }
                }
            }

            $row++;
        }

        return $pdf->Output('S');
    }
}