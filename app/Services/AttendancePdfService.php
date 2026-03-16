<?php

namespace App\Services;

use setasign\Fpdi\Tfpdf\Fpdi;
use Carbon\Carbon;

class AttendancePdfService
{
    private function getConfig(string $formatSlug): array
    {
        // Primero buscar en BD
        $format = \App\Models\Format::where('slug', $formatSlug)->first();

        if ($format && $format->mapping) {
            $cfg = $format->mapping;
            // Si tiene archivo en BD, usarlo
            if ($format->file) {
                $cfg['file'] = $format->file;
            }
            return $cfg;
        }

        // Fallback al config file
        return config("attendance_formats.{$formatSlug}") ?? config('attendance_formats.default');
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

    public function generatePdf($event, string $formatSlug = 'default'): string
    {

        $cfg = $this->getConfig($formatSlug);

        // Si el formato tiene archivo en BD, sobreescribir el del config
        $format = \App\Models\Format::where('slug', $formatSlug)->first();
        if ($format && $format->file) {
            $cfg['file'] = $format->file;
        }

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

            $header = $cfg['header'];

            if (isset($header['dependency'])) {
                $pdf->SetFont('Arial', 'B', $header['dependency']['fontSize'] ?? 12);
                $pdf->SetXY($header['dependency']['x'], $header['dependency']['y']);
                $w = $header['dependency']['w'] ?? 0;
                $text = mb_strtoupper($event->dependency->name ?? 'SIN DEPENDENCIA', 'UTF-8');
                if (isset($header['dependency']['limit'])) $text = $this->truncateText($text, $header['dependency']['limit']);
                $pdf->Cell($w, 8, $this->toIso($text), 0, 0, 'L');
            }

            if (isset($header['area']) && $event->area) {
                $pdf->SetFont('Arial', 'B', $header['area']['fontSize'] ?? 12);
                $pdf->SetXY($header['area']['x'], $header['area']['y']);
                $w = $header['area']['w'] ?? 0;
                $text = ' - ' . mb_strtoupper($event->area->name, 'UTF-8');
                if (isset($header['area']['limit'])) $text = $this->truncateText($text, $header['area']['limit']);
                $pdf->Cell($w, 8, $this->toIso($text), 0, 0, 'L');
            }

            if (isset($header['title'])) {
                $pdf->SetFont('Arial', 'B', $header['title']['fontSize'] ?? 12);
                $pdf->SetXY($header['title']['x'], $header['title']['y']);
                $w = $header['title']['w'] ?? 0;
                $text = mb_strtoupper($event->title ?? 'SIN TÍTULO', 'UTF-8');
                if (isset($header['title']['limit'])) $text = $this->truncateText($text, $header['title']['limit']);
                $pdf->Cell($w, 8, $this->toIso($text), 0, 0, 'L');
            }

            if (isset($cfg['date_format']) && isset($header['date_day']) && is_array($cfg['date_format'])) {
                $date = Carbon::parse($event->date);

                $pdf->SetFont('Arial', 'B', $header['date_day']['fontSize'] ?? 12);
                $pdf->SetXY($header['date_day']['x'], $header['date_day']['y']);
                $w = $header['date_day']['w'] ?? 0;
                $text = $date->format($cfg['date_format']['day']);
                if (isset($header['date_day']['limit'])) $text = $this->truncateText($text, $header['date_day']['limit']);
                $pdf->Cell($w, 8, $text, 0, 0, 'L');

                $pdf->SetFont('Arial', 'B', $header['date_month']['fontSize'] ?? 12);
                $pdf->SetXY($header['date_month']['x'], $header['date_month']['y']);
                $w = $header['date_month']['w'] ?? 0;
                $text = $date->translatedFormat($cfg['date_format']['month']);
                if (isset($header['date_month']['limit'])) $text = $this->truncateText($text, $header['date_month']['limit']);
                $pdf->Cell($w, 8, $this->toIso($text), 0, 0, 'L');

                $pdf->SetFont('Arial', 'B', $header['date_year']['fontSize'] ?? 12);
                $pdf->SetXY($header['date_year']['x'], $header['date_year']['y']);
                $w = $header['date_year']['w'] ?? 0;
                $text = $date->format($cfg['date_format']['year']);
                if (isset($header['date_year']['limit'])) $text = $this->truncateText($text, $header['date_year']['limit']);
                $pdf->Cell($w, 8, $text, 0, 0, 'L');

            } elseif (isset($cfg['date_format']) && isset($header['date']) && is_string($cfg['date_format'])) {
                $pdf->SetFont('Arial', 'B', $header['date']['fontSize'] ?? 12);
                $pdf->SetXY($header['date']['x'], $header['date']['y']);
                $w = $header['date']['w'] ?? 0;
                $text = Carbon::parse($event->date)->format($cfg['date_format']);
                if (isset($header['date']['limit'])) $text = $this->truncateText($text, $header['date']['limit']);
                $pdf->Cell($w, 8, $this->toIso($text), 0, 0, 'L');
            }

            if (isset($header['responsible'])) {
                $pdf->SetFont('Arial', 'B', $header['responsible']['fontSize'] ?? 12);
                $pdf->SetXY($header['responsible']['x'], $header['responsible']['y']);
                $w = $header['responsible']['w'] ?? 0;
                $text = $event->user->name ?? '';
                if (isset($header['responsible']['limit'])) $text = $this->truncateText($text, $header['responsible']['limit']);
                $pdf->Cell($w, 8, $this->toIso($text), 0, 0, 'L');
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
            // Preferir el programa específico de la asistencia sobre el primero del participante
            $p->setRelation('programs', collect([$attendance->detail?->program ?? $p->programs->first()])->filter());
            $y = round($cfg['startY'] + ($row * $cfg['rowHeight']), 2);

            if (isset($cols['number'])) {
                $pdf->SetFont('Arial', '', $cols['number']['fontSize'] ?? 12);
                $pdf->SetXY($cols['number']['x'], $y);
                $pdf->Cell($cols['number']['w'], $cols['number']['h'] ?? 7.8, $i + 1, 0, 0, $cols['number']['align']);
            }

            if (isset($cols['name'])) {
                $pdf->SetFont('Arial', '', $cols['name']['fontSize'] ?? 12);
                $pdf->SetXY($cols['name']['x'], $y);
                $pdf->Cell($cols['name']['w'], $cols['name']['h'] ?? 7.8,
                    $this->toIso($this->truncateText(trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? '')), $cols['name']['limit'])),
                    0, 0, $cols['name']['align']
                );
            }

            if (isset($cols['identification'])) {
                $pdf->SetFont('Arial', '', $cols['identification']['fontSize'] ?? 12);
                $pdf->SetXY($cols['identification']['x'], $y);
                $pdf->Cell($cols['identification']['w'], $cols['identification']['h'] ?? 7.8,
                    $this->toIso($this->truncateText($p->document ?? '', $cols['identification']['limit'])),
                    0, 0, $cols['identification']['align']
                );
            }

            if (isset($cols['role'])) {
                // Si tiene 'x' y 'w', es texto
                if (isset($cols['role']['x']) && isset($cols['role']['w'])) {
                    $pdf->SetFont('Arial', '', $cols['role']['fontSize'] ?? 12);
                    $pdf->SetXY($cols['role']['x'], $y);
                    $pdf->Cell($cols['role']['w'], $cols['role']['h'] ?? 7.8,
                        $this->toIso($this->truncateText($p->role ?? '', $cols['role']['limit'])),
                        0, 0, $cols['role']['align']
                    );
                // Si no, es tipo casilla
                } else {
                    $pdf->SetFont('Arial', '', $cols['role']['fontSize'] ?? 12);
                    $role = $p->role ?? '';
                    if (isset($cols['role'][$role])) {
                        $rx = $cols['role'][$role]['x'];
                        $ry = $y + ($cols['role'][$role]['y_offset'] ?? 0);
                        $align = $cols['role'][$role]['align'] ?? 'C';
                        $pdf->SetXY($rx, $ry);
                        $pdf->Cell(5, 5, 'X', 0, 0, $align);
                    }
                }
            }

            if (isset($cols['program'])) {
                $pdf->SetFont('Arial', '', $cols['program']['fontSize'] ?? 12);
                $pdf->SetXY($cols['program']['x'], $y);
                $pdf->Cell($cols['program']['w'], $cols['program']['h'] ?? 7.8,
                    $this->toIso($this->truncateText($p->program->name ?? '', $cols['program']['limit'])),
                    0, 0, $cols['program']['align']
                );
            }

            if (isset($cols['email'])) {
                $pdf->SetFont('Arial', '', $cols['email']['fontSize'] ?? 12);
                $pdf->SetXY($cols['email']['x'], $y);
                $pdf->Cell($cols['email']['w'], $cols['email']['h'] ?? 7.8,
                    $this->toIso($this->truncateText($p->email ?? '', $cols['email']['limit'])),
                    0, 0, $cols['email']['align']
                );
            }

            if (isset($cols['phone'])) {
                $pdf->SetFont('Arial', '', $cols['phone']['fontSize'] ?? 12);
                $pdf->SetXY($cols['phone']['x'], $y);
                $pdf->Cell($cols['phone']['w'], $cols['phone']['h'] ?? 7.8,
                    $this->toIso($this->truncateText($p->phone ?? '', $cols['phone']['limit'])),
                    0, 0, $cols['phone']['align']
                );
            }

            if (isset($cols['time'])) {
                $pdf->SetFont('Arial', '', $cols['time']['fontSize'] ?? 12);
                $pdf->SetXY($cols['time']['x'], $y);
                $pdf->Cell($cols['time']['w'], $cols['time']['h'] ?? 7.8,
                    Carbon::parse($attendance->created_at)->format($cfg['time_format'] ?? 'h:i A'),
                    0, 0, $cols['time']['align']
                );
            }

            // === Gender ===
            if (isset($cols['gender'])) {
                $gender = $p->gender ?? '';
                if (isset($cols['gender'][$gender])) {
                    $gx = $cols['gender'][$gender]['x'];
                    $gy = $y + ($cols['gender'][$gender]['y_offset'] ?? 0);
                    $align = $cols['gender'][$gender]['align'] ?? 'C';
                    $pdf->SetFont('Arial', '', $cols['gender']['fontSize'] ?? 12);
                    $pdf->SetXY($gx, $gy);
                    $pdf->Cell(5, 5, 'X', 0, 0, $align);
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
                        $gx = $cols['priority_group'][$g]['x'];
                        $gy = $y + ($cols['priority_group'][$g]['y_offset'] ?? 0);
                        $align = $cols['priority_group'][$g]['align'] ?? 'C';
                        $pdf->SetFont('Arial', '', $cols['priority_group']['fontSize'] ?? 12);
                        $pdf->SetXY($gx, $gy);
                        $pdf->Cell(5, 5, 'X', 0, 0, $align);
                    }
                }
            }

            $row++;
        }

        return $pdf->Output('S');
    }
}