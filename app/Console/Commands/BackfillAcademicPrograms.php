<?php

namespace App\Console\Commands;

use App\Models\AcademicProgram;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillAcademicPrograms extends Command
{
    protected $signature = 'academic-programs:backfill {--dry-run : Reportar los cambios sin actualizar registros}';

    protected $description = 'Pobla academic_programs desde programs.name y relaciona programs.academic_program_id.';

    private const CAMPUS_SUFFIXES = [
        'Maicao',
        'Riohacha',
        'Fonseca',
        'Villanueva',
        'Manaure',
    ];

    public function handle(): int
    {
        if (! Schema::hasTable('academic_programs')) {
            $this->error('La tabla academic_programs no existe. Ejecuta primero las migraciones.');

            return self::FAILURE;
        }

        if (! Schema::hasColumn('programs', 'academic_program_id')) {
            $this->error('La tabla programs no tiene academic_program_id. Ejecuta primero las migraciones.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('Modo dry-run: no se crearan ni actualizaran registros.');
        }

        $summary = $dryRun
            ? $this->previewBackfill()
            : DB::transaction(fn () => $this->runBackfill());

        $this->printSummary($summary, $dryRun);

        return self::SUCCESS;
    }

    /**
     * @return array{created: int, related: int, by_academic_program: array<string, int>}
     */
    private function previewBackfill(): array
    {
        $existingAcademicPrograms = AcademicProgram::pluck('id', 'name')->all();
        $knownAcademicProgramKeys = collect(array_keys($existingAcademicPrograms))
            ->mapWithKeys(fn (string $name) => [$this->academicProgramKey($name) => true])
            ->all();
        $academicProgramsToCreate = [];
        $relatedPrograms = 0;
        $byAcademicProgram = [];

        $this->programsWithoutAcademicProgram()
            ->chunkById(500, function ($programs) use (&$knownAcademicProgramKeys, &$academicProgramsToCreate, &$relatedPrograms, &$byAcademicProgram) {
                foreach ($programs as $program) {
                    $academicProgramName = $this->academicProgramNameFromProgramName((string) $program->name);
                    $academicProgramKey = $this->academicProgramKey($academicProgramName);

                    if (! isset($knownAcademicProgramKeys[$academicProgramKey])) {
                        $academicProgramsToCreate[$academicProgramName] = true;
                        $knownAcademicProgramKeys[$academicProgramKey] = true;
                    }

                    $relatedPrograms++;
                    $byAcademicProgram[$academicProgramName] = ($byAcademicProgram[$academicProgramName] ?? 0) + 1;
                }
            });

        ksort($byAcademicProgram);

        return [
            'created' => count($academicProgramsToCreate),
            'related' => $relatedPrograms,
            'by_academic_program' => $byAcademicProgram,
        ];
    }

    /**
     * @return array{created: int, related: int, by_academic_program: array<string, int>}
     */
    private function runBackfill(): array
    {
        $createdAcademicPrograms = 0;
        $relatedPrograms = 0;
        $byAcademicProgram = [];

        $this->programsWithoutAcademicProgram()
            ->chunkById(500, function ($programs) use (&$createdAcademicPrograms, &$relatedPrograms, &$byAcademicProgram) {
                foreach ($programs as $program) {
                    $academicProgramName = $this->academicProgramNameFromProgramName((string) $program->name);

                    $academicProgram = AcademicProgram::firstOrCreate(['name' => $academicProgramName]);

                    if ($academicProgram->wasRecentlyCreated) {
                        $createdAcademicPrograms++;
                    }

                    $updated = DB::table('programs')
                        ->where('id', $program->id)
                        ->whereNull('academic_program_id')
                        ->update([
                            'academic_program_id' => $academicProgram->id,
                            'updated_at' => now(),
                        ]);

                    if ($updated > 0) {
                        $relatedPrograms += $updated;
                        $byAcademicProgram[$academicProgramName] = ($byAcademicProgram[$academicProgramName] ?? 0) + $updated;
                    }
                }
            });

        ksort($byAcademicProgram);

        return [
            'created' => $createdAcademicPrograms,
            'related' => $relatedPrograms,
            'by_academic_program' => $byAcademicProgram,
        ];
    }

    private function programsWithoutAcademicProgram()
    {
        return DB::table('programs')
            ->select(['id', 'name'])
            ->whereNull('academic_program_id')
            ->orderBy('id');
    }

    private function academicProgramNameFromProgramName(string $programName): string
    {
        $normalizedProgramName = trim(preg_replace('/\s+/u', ' ', $programName) ?? $programName);
        $name = $normalizedProgramName;
        $campusSuffixes = implode('|', array_map('preg_quote', self::CAMPUS_SUFFIXES));
        $name = preg_replace("/\s+-\s+({$campusSuffixes})\s*$/iu", '', $name) ?? $name;
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? $name);

        return $name !== '' ? $name : $normalizedProgramName;
    }

    private function academicProgramKey(string $academicProgramName): string
    {
        return mb_strtolower($academicProgramName, 'UTF-8');
    }

    /**
     * @param  array{created: int, related: int, by_academic_program: array<string, int>}  $summary
     */
    private function printSummary(array $summary, bool $dryRun): void
    {
        $this->newLine();
        $this->info($dryRun ? 'Cambios que se realizarian:' : 'Cambios realizados:');
        $this->table(
            ['Academic programs creados', 'Programs relacionados'],
            [[$summary['created'], $summary['related']]]
        );

        if ($summary['by_academic_program'] === []) {
            $this->info('No hay programs pendientes por relacionar.');

            return;
        }

        $this->newLine();
        $this->info($dryRun ? 'Programs que se relacionarian por academic_program:' : 'Programs relacionados por academic_program:');
        $this->table(
            ['Academic program', 'Programs'],
            collect($summary['by_academic_program'])
                ->map(fn (int $count, string $name) => [$name, $count])
                ->values()
                ->all()
        );
    }
}
