<?php

/**
 * Genera un .xlsx de prueba con 100 participantes aleatorios usando catálogos
 * REALES de la base de datos local, para que clasifiquen como "nuevos".
 *
 * Uso:  php tools/generar_excel_prueba.php
 * Salida: participantes_prueba_100.xlsx en la raíz del proyecto.
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Dependency;
use App\Models\ParticipantType;
use App\Models\Program;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// ── Diagnóstico rápido de la cola ─────────────────────────────────────────
$jobs = \Illuminate\Support\Facades\DB::table('jobs')->count();
$procesando = \App\Models\ImportBatch::where('status', 'procesando')->count();
echo "Jobs pendientes en la cola (tabla jobs): {$jobs}\n";
echo "Lotes en estado 'procesando': {$procesando}\n";
echo str_repeat('-', 50)."\n";

// ── Catálogos reales ──────────────────────────────────────────────────────
$estudiante = ParticipantType::whereRaw('LOWER(name) = ?', ['estudiante'])->first()
    ?? ParticipantType::first();

if (! $estudiante) {
    fwrite(STDERR, "No hay estamentos (participant_types) en la BD. Aborta.\n");
    exit(1);
}

// ¿La BD tiene sedes y programas con campus_id? Si no (BD local sin multi-sede),
// dejamos "Programa o Dependencia" en blanco para que las filas clasifiquen como
// NUEVOS (un participante con solo su estamento, sin programa) en vez de omitidos.
$programsWithCampus = Program::with('campus')
    ->whereNotNull('campus_id')
    ->get()
    ->filter(fn ($p) => $p->campus)
    ->values();

$usarPrograma = $programsWithCampus->isNotEmpty();
$programsForCampus = collect();
$campusName = null;

if ($usarPrograma) {
    $campusId = $programsWithCampus->countBy('campus_id')->sortDesc()->keys()->first();
    $programsForCampus = $programsWithCampus->where('campus_id', $campusId)->values();
    $campusName = $programsForCampus->first()->campus->name;
    echo "Estamento usado: {$estudiante->name}\n";
    echo "Sede objetivo: {$campusName} ({$programsForCampus->count()} programas)\n";
    echo "Tip: súbelo como superadmin, o como admin de la sede «{$campusName}».\n";
} else {
    echo "Estamento usado: {$estudiante->name}\n";
    echo "AVISO: la BD no tiene sedes/programas con campus_id; las filas irán con\n";
    echo "       «Programa o Dependencia» en blanco → clasificarán como NUEVOS.\n";
    echo "       Súbelo como SUPERADMIN (un admin sin sede asignada será rechazado).\n";
}
echo str_repeat('-', 50)."\n";

// ── Generar filas ─────────────────────────────────────────────────────────
$nombres = ['Ana', 'Luis', 'María', 'Carlos', 'Sofía', 'Andrés', 'Valentina', 'Juan', 'Camila',
    'Diego', 'Laura', 'Santiago', 'Daniela', 'Felipe', 'Isabella', 'Mateo', 'Gabriela', 'Sebastián',
    'Lucía', 'Nicolás'];
$apellidos = ['Pérez', 'Gómez', 'Rodríguez', 'Martínez', 'López', 'Díaz', 'Torres', 'Ramírez',
    'Flores', 'Vargas', 'Castro', 'Romero', 'Suárez', 'Mendoza', 'Ortiz', 'Silva', 'Rojas',
    'Moreno', 'Gutiérrez', 'Jiménez'];

$headers = ['Documento', 'Nombres', 'Apellidos', 'Tipo de Estamento', 'Correo',
    'Programa o Dependencia', 'Tipo_progama', 'Vinculacion'];

$rows = [$headers];
$base = 900000000 + random_int(0, 9000000); // documentos altos, improbables en BD

for ($i = 0; $i < 100; $i++) {
    $nombre = $nombres[array_rand($nombres)];
    $apellido = $apellidos[array_rand($apellidos)];
    $apellido2 = $apellidos[array_rand($apellidos)];
    $doc = (string) ($base + $i);
    $correo = strtolower($nombre.'.'.$apellido.$i.'@prueba.test');
    $correo = preg_replace('/[^a-z0-9.@]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $correo));

    if ($usarPrograma) {
        $program = $programsForCampus[$i % $programsForCampus->count()];
        $programLabel = $program->name.' - '.$campusName;
        $tipoPrograma = 'Pregrado';
    } else {
        $programLabel = '';
        $tipoPrograma = '';
    }

    $rows[] = [
        $doc,
        $nombre,
        $apellido.' '.$apellido2,
        $estudiante->name,
        $correo,
        $programLabel,
        $tipoPrograma,
        '',
    ];
}

// ── Escribir el .xlsx ─────────────────────────────────────────────────────
$spreadsheet = new Spreadsheet;
$sheet = $spreadsheet->getActiveSheet();
$sheet->fromArray($rows, null, 'A1');

$outPath = __DIR__.'/../participantes_prueba_100.xlsx';
(new Xlsx($spreadsheet))->save($outPath);

echo "OK: generado ".realpath($outPath)."\n";
echo "Filas de datos: ".(count($rows) - 1)."\n";
