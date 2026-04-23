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

    /**
     * Busca la clave de un checkbox de "role" dentro del mapeo tolerando
     * diferencias menores entre el nombre del estamento guardado en la BD
     * y la clave definida en el formato: mayúsculas, acentos, espacios
     * redundantes y variantes singular/plural (p. ej. "Administrativo" vs
     * "Administrativos"). Devuelve la clave original del mapeo si encuentra
     * coincidencia, o null si no hay.
     */
    private function findRoleCheckboxKey(array $roleMapping, string $roleText): ?string
    {
        // Coincidencia exacta primero (caso normal).
        if ($roleText !== '' && isset($roleMapping[$roleText])) {
            return $roleText;
        }

        $normalize = function (string $v): string {
            $lower = mb_strtolower(trim($v), 'UTF-8');
            $lower = preg_replace('/\s+/u', ' ', $lower);
            if (class_exists(\Normalizer::class)) {
                $decomposed = \Normalizer::normalize($lower, \Normalizer::FORM_D);
                if ($decomposed !== false) {
                    $lower = preg_replace('/\pM/u', '', $decomposed);
                }
            }
            return $lower ?? '';
        };

        $stripPlural = fn (string $v): string => preg_replace('/(es|s)$/u', '', $v) ?? $v;

        $target         = $normalize($roleText);
        $targetSingular = $stripPlural($target);

        if ($target === '') {
            return null;
        }

        foreach ($roleMapping as $key => $val) {
            // Saltar entradas que no sean casillas (sin coordenadas) como
            // 'fontSize', 'align' globales, o propios 'x'/'w' de modo texto.
            if (! is_array($val) || ! isset($val['x'])) {
                continue;
            }

            $normKey         = $normalize((string) $key);
            $normKeySingular = $stripPlural($normKey);

            if ($target === $normKey
                || $targetSingular === $normKey
                || $target === $normKeySingular
                || $targetSingular === $normKeySingular) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Imprime un texto ajustándolo automáticamente al tamaño de la celda:
     *   1. Intenta con la fuente base (`fontSize` del mapeo).
     *   2. Si no cabe en una sola línea, reduce el tamaño hasta `minFontSize`
     *      (por defecto 6pt). Solo afecta a esta celda.
     *   3. Si aun al mínimo sigue sin caber, usa `MultiCell` para partir
     *      el texto en varias líneas (hasta 4) distribuidas dentro de la
     *      altura de la celda, para que no se salga por abajo.
     *
     * Al terminar restaura la fuente base (Arial regular 12) para que las
     * celdas siguientes no hereden el tamaño reducido.
     */
    private function printAutoFitText($pdf, array $col, float $y, string $text, string $style = '', ?float $h = null): void
    {
        $cw       = $col['w'] ?? 0;
        $ch       = $h ?? ($col['h'] ?? 7.8);
        $baseSize = $col['fontSize'] ?? 12;
        $minSize  = $col['minFontSize'] ?? 6;
        $align    = $col['align'] ?? 'L';
        $cx       = $col['x'];

        $textIso   = $this->toIso((string) $text);
        $available = max(0, $cw - 0.6);

        // 1) Fuente base
        $pdf->SetFont('Arial', $style, $baseSize);
        $textWidth = $textIso === '' ? 0 : $pdf->GetStringWidth($textIso);

        if ($textWidth <= $available) {
            $pdf->SetXY($cx, $y);
            $pdf->Cell($cw, $ch, $textIso, 0, 0, $align);
            $pdf->SetFont('Arial', '', 12);
            return;
        }

        // 2) Reducir tamaño progresivamente
        $fitSize = $baseSize;
        for ($size = $baseSize - 1; $size >= $minSize; $size--) {
            $pdf->SetFont('Arial', $style, $size);
            $fitSize = $size;
            if ($pdf->GetStringWidth($textIso) <= $available) {
                break;
            }
        }

        $pdf->SetFont('Arial', $style, $fitSize);

        if ($pdf->GetStringWidth($textIso) <= $available) {
            // Cabe en una sola línea con la fuente reducida.
            $pdf->SetXY($cx, $y);
            $pdf->Cell($cw, $ch, $textIso, 0, 0, $align);
        } else {
            // 3) Partir en varias líneas dentro de la altura de la celda.
            $textWidth = $pdf->GetStringWidth($textIso);
            $lines     = max(2, (int) ceil($textWidth / max(0.1, $available)));
            $lines     = min($lines, 4);
            $lineH     = $ch / $lines;

            $pdf->SetXY($cx, $y);
            $pdf->MultiCell($cw, $lineH, $textIso, 0, $align);
        }

        $pdf->SetFont('Arial', '', 12);
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
                $text = mb_strtoupper($event->dependency->name ?? 'SIN DEPENDENCIA', 'UTF-8');
                $this->printAutoFitText($pdf, $header['dependency'], $header['dependency']['y'], $text, 'B', 8);
            }

            if (isset($header['area']) && $event->area) {
                $text = ' - ' . mb_strtoupper($event->area->name, 'UTF-8');
                $this->printAutoFitText($pdf, $header['area'], $header['area']['y'], $text, 'B', 8);
            }

            if (isset($header['title'])) {
                $text = mb_strtoupper($event->title ?? 'SIN TÍTULO', 'UTF-8');
                $this->printAutoFitText($pdf, $header['title'], $header['title']['y'], $text, 'B', 8);
            }

            if (isset($cfg['date_format']) && isset($header['date_day']) && is_array($cfg['date_format'])) {
                $date = Carbon::parse($event->date);

                $this->printAutoFitText(
                    $pdf,
                    $header['date_day'],
                    $header['date_day']['y'],
                    $date->format($cfg['date_format']['day']),
                    'B',
                    8
                );

                $this->printAutoFitText(
                    $pdf,
                    $header['date_month'],
                    $header['date_month']['y'],
                    $date->translatedFormat($cfg['date_format']['month']),
                    'B',
                    8
                );

                $this->printAutoFitText(
                    $pdf,
                    $header['date_year'],
                    $header['date_year']['y'],
                    $date->format($cfg['date_format']['year']),
                    'B',
                    8
                );

            } elseif (isset($cfg['date_format']) && isset($header['date']) && is_string($cfg['date_format'])) {
                $this->printAutoFitText(
                    $pdf,
                    $header['date'],
                    $header['date']['y'],
                    Carbon::parse($event->date)->format($cfg['date_format']),
                    'B',
                    8
                );
            }

            if (isset($header['responsible'])) {
                $this->printAutoFitText(
                    $pdf,
                    $header['responsible'],
                    $header['responsible']['y'],
                    $event->user->name ?? '',
                    'B',
                    8
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
            $detail = $attendance->detail;
            $y = round($cfg['startY'] + ($row * $cfg['rowHeight']), 2);

            if (isset($cols['number'])) {
                $this->printAutoFitText($pdf, $cols['number'], $y, (string) ($i + 1));
            }

            if (isset($cols['name'])) {
                $this->printAutoFitText(
                    $pdf,
                    $cols['name'],
                    $y,
                    trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? ''))
                );
            }

            if (isset($cols['identification'])) {
                $this->printAutoFitText($pdf, $cols['identification'], $y, (string) ($p->document ?? ''));
            }

            if (isset($cols['role'])) {
                $roleText = $detail?->participantRole?->type?->name ?? '';
                // Si tiene 'x' y 'w', es texto
                if (isset($cols['role']['x']) && isset($cols['role']['w'])) {
                    $this->printAutoFitText($pdf, $cols['role'], $y, $roleText);
                // Si no, es tipo casilla
                } else {
                    $pdf->SetFont('Arial', '', $cols['role']['fontSize'] ?? 12);
                    $matchedKey = $this->findRoleCheckboxKey($cols['role'], $roleText);
                    if ($matchedKey !== null) {
                        $rx = $cols['role'][$matchedKey]['x'];
                        $ry = $y + ($cols['role'][$matchedKey]['y_offset'] ?? 0);
                        $align = $cols['role'][$matchedKey]['align'] ?? 'C';
                        $pdf->SetXY($rx, $ry);
                        $pdf->Cell(5, 5, 'X', 0, 0, $align);
                    }
                }
            }

            if (isset($cols['program'])) {
                // El mismo campo del formato se reutiliza para Programa o
                // Dependencia: si el participante registró la asistencia con
                // un rol de estamento que pertenece a Dependencia (p. ej.
                // "Administrativo"), se imprime el nombre de la dependencia;
                // de lo contrario, el programa académico.
                $role             = $detail?->participantRole;
                $roleTypeName     = $role?->type?->name ?? '';
                $roleTypeKey      = mb_strtolower(trim($roleTypeName), 'UTF-8');
                $dependencyRoles  = ['administrativo'];

                if (in_array($roleTypeKey, $dependencyRoles, true)) {
                    $programName = $role?->dependency?->name
                        ?? $role?->program?->name
                        ?? '';
                } else {
                    $programName = $role?->program?->name
                        ?? $role?->dependency?->name
                        ?? '';
                }

                $this->printAutoFitText($pdf, $cols['program'], $y, $programName);
            }

            if (isset($cols['email'])) {
                $this->printAutoFitText($pdf, $cols['email'], $y, (string) ($p->email ?? ''));
            }

            if (isset($cols['phone'])) {
                $phoneText = $detail?->phone ?? $p->phone ?? '';
                $this->printAutoFitText($pdf, $cols['phone'], $y, (string) $phoneText);
            }

            if (isset($cols['city'])) {
                $this->printAutoFitText($pdf, $cols['city'], $y, (string) ($detail?->city ?? ''));
            }

            if (isset($cols['neighborhood'])) {
                $this->printAutoFitText($pdf, $cols['neighborhood'], $y, (string) ($detail?->neighborhood ?? ''));
            }

            if (isset($cols['address'])) {
                $this->printAutoFitText($pdf, $cols['address'], $y, (string) ($detail?->address ?? ''));
            }

            if (isset($cols['time'])) {
                $this->printAutoFitText(
                    $pdf,
                    $cols['time'],
                    $y,
                    Carbon::parse($attendance->created_at)->format($cfg['time_format'] ?? 'h:i A')
                );
            }

            // === Gender ===
            if (isset($cols['gender'])) {
                $gender = $detail?->gender ?? $p->gender ?? '';
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
                $priorityGroup = $detail?->priority_group ?? $p->priority_group ?? '';
                $groups = is_array($priorityGroup)
                    ? $priorityGroup
                    : explode(',', $priorityGroup);

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
