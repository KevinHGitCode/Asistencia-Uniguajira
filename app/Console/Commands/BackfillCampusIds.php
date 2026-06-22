<?php

namespace App\Console\Commands;

use App\Models\Campus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillCampusIds extends Command
{
    protected $signature = 'campuses:backfill {--dry-run : Reportar los cambios sin actualizar registros}';

    protected $description = 'Pobla campus_id en tablas base usando inferencia por nombre y fallback Maicao.';

    private const CAMPUS_NAMES = [
        'Maicao',
        'Riohacha',
        'Fonseca',
        'Villanueva',
        'Manaure',
    ];

    /**
     * @var array<int, int>
     */
    private array $dependencyCampusCache = [];

    /**
     * @var array<int, int>
     */
    private array $userCampusCache = [];

    /**
     * @var array<string, int>
     */
    private array $campuses = [];

    private int $fallbackCampusId;

    public function handle(): int
    {
        if (! Schema::hasTable('campuses')) {
            $this->error('La tabla campuses no existe. Ejecuta primero las migraciones.');

            return self::FAILURE;
        }

        foreach (['dependencies', 'programs', 'areas', 'users', 'events'] as $table) {
            if (! Schema::hasColumn($table, 'campus_id')) {
                $this->error("La tabla {$table} no tiene campus_id. Ejecuta primero las migraciones.");

                return self::FAILURE;
            }
        }

        $dryRun = (bool) $this->option('dry-run');
        $this->campuses = $this->campusesByName();
        $missingCampuses = array_diff(self::CAMPUS_NAMES, array_keys($this->campuses));
        if ($missingCampuses !== []) {
            $this->error('Faltan las sedes requeridas: '.implode(', ', $missingCampuses).'. Ejecuta CampusSeeder antes del backfill.');

            return self::FAILURE;
        }

        $this->fallbackCampusId = $this->campuses['Maicao'];
        $summary = [];

        if ($dryRun) {
            $this->warn('Modo dry-run: no se actualizaran registros.');
        }

        $runBackfill = function () use ($dryRun, &$summary) {
            $summary['dependencies'] = $this->backfillByName('dependencies', 'name', $dryRun);
            $summary['programs'] = $this->backfillByName('programs', 'name', $dryRun);
            $summary['areas'] = $this->backfillByName('areas', 'name', $dryRun);
            $summary['users'] = $this->backfillUsers($dryRun);
            $summary['events'] = $this->backfillEvents($dryRun);
        };

        $dryRun ? $runBackfill() : DB::transaction($runBackfill);

        $this->printSummary($summary, $this->campuses, $dryRun);

        return self::SUCCESS;
    }

    /**
     * @return array<string, int>
     */
    private function campusesByName(): array
    {
        return Campus::query()
            ->whereIn('name', self::CAMPUS_NAMES)
            ->pluck('id', 'name')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @param  array<string, int>  $campuses
     * @return array<string, int>
     */
    private function backfillByName(string $table, string $column, bool $dryRun): array
    {
        $counts = $this->emptyCounts();

        DB::table($table)
            ->select(['id', $column])
            ->whereNull('campus_id')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($table, $column, $dryRun, &$counts) {
                foreach ($rows as $row) {
                    $campusId = $this->inferCampusId((string) ($row->{$column} ?? '')) ?? $this->fallbackCampusId;
                    $campusName = $this->campusNameForId($campusId, $this->campuses);

                    $counts[$campusName]++;

                    if (! $dryRun) {
                        DB::table($table)
                            ->where('id', $row->id)
                            ->whereNull('campus_id')
                            ->update([
                                'campus_id' => $campusId,
                                'updated_at' => now(),
                            ]);
                    }
                }
            });

        return $counts;
    }

    /**
     * @return array<string, int>
     */
    private function backfillUsers(bool $dryRun): array
    {
        $counts = $this->emptyCounts();

        DB::table('users')
            ->select(['id'])
            ->whereNull('campus_id')
            ->where('role', '!=', 'superadmin')
            ->orderBy('id')
            ->chunkById(500, function ($users) use ($dryRun, &$counts) {
                foreach ($users as $user) {
                    $campusId = $this->inferUserCampusId((int) $user->id);

                    $campusName = $this->campusNameForId((int) $campusId);
                    $counts[$campusName]++;

                    if (! $dryRun) {
                        DB::table('users')
                            ->where('id', $user->id)
                            ->whereNull('campus_id')
                            ->update([
                                'campus_id' => $campusId,
                                'updated_at' => now(),
                            ]);
                    }
                }
            });

        return $counts;
    }

    /**
     * @return array<string, int>
     */
    private function backfillEvents(bool $dryRun): array
    {
        $counts = $this->emptyCounts();

        DB::table('events')
            ->select(['id', 'dependency_id', 'user_id'])
            ->whereNull('campus_id')
            ->orderBy('id')
            ->chunkById(500, function ($events) use ($dryRun, &$counts) {
                foreach ($events as $event) {
                    $campusId = null;

                    if ($event->dependency_id) {
                        $campusId = $this->inferDependencyCampusId((int) $event->dependency_id);
                    }

                    if (! $campusId && $event->user_id) {
                        $campusId = $this->inferUserCampusId((int) $event->user_id);
                    }

                    $campusId = (int) ($campusId ?: $this->fallbackCampusId);
                    $campusName = $this->campusNameForId($campusId);
                    $counts[$campusName]++;

                    if (! $dryRun) {
                        DB::table('events')
                            ->where('id', $event->id)
                            ->whereNull('campus_id')
                            ->update([
                                'campus_id' => $campusId,
                                'updated_at' => now(),
                            ]);
                    }
                }
            });

        return $counts;
    }

    private function inferCampusId(string $name): ?int
    {
        $normalizedName = mb_strtolower($name, 'UTF-8');

        foreach (self::CAMPUS_NAMES as $campusName) {
            if (str_contains($normalizedName, mb_strtolower($campusName, 'UTF-8'))) {
                return $this->campuses[$campusName];
            }
        }

        return null;
    }

    private function inferDependencyCampusId(int $dependencyId): int
    {
        if (isset($this->dependencyCampusCache[$dependencyId])) {
            return $this->dependencyCampusCache[$dependencyId];
        }

        $dependency = DB::table('dependencies')
            ->where('id', $dependencyId)
            ->first(['campus_id', 'name']);

        $campusId = $dependency
            ? (int) ($dependency->campus_id ?: ($this->inferCampusId((string) $dependency->name) ?? $this->fallbackCampusId))
            : $this->fallbackCampusId;

        return $this->dependencyCampusCache[$dependencyId] = $campusId;
    }

    private function inferUserCampusId(int $userId): int
    {
        if (isset($this->userCampusCache[$userId])) {
            return $this->userCampusCache[$userId];
        }

        $userCampusId = DB::table('users')
            ->where('id', $userId)
            ->value('campus_id');

        if ($userCampusId) {
            return $this->userCampusCache[$userId] = (int) $userCampusId;
        }

        $dependencyId = DB::table('dependency_user')
            ->where('user_id', $userId)
            ->orderBy('dependency_id')
            ->value('dependency_id');

        $campusId = $dependencyId
            ? $this->inferDependencyCampusId((int) $dependencyId)
            : $this->fallbackCampusId;

        return $this->userCampusCache[$userId] = $campusId;
    }

    /**
     * @return array<string, int>
     */
    private function emptyCounts(): array
    {
        return array_fill_keys(self::CAMPUS_NAMES, 0);
    }

    /**
     * @param  array<string, int>|null  $campuses
     */
    private function campusNameForId(int $campusId, ?array $campuses = null): string
    {
        $campuses ??= Campus::pluck('id', 'name')->all();

        $name = array_search($campusId, $campuses, true);

        return is_string($name) ? $name : "Campus {$campusId}";
    }

    /**
     * @param  array<string, array<string, int>>  $summary
     * @param  array<string, int>  $campuses
     */
    private function printSummary(array $summary, array $campuses, bool $dryRun): void
    {
        $headers = ['Tabla', ...self::CAMPUS_NAMES, 'Total'];
        $rows = [];

        foreach ($summary as $table => $counts) {
            $row = [$table];
            $total = 0;

            foreach (self::CAMPUS_NAMES as $campusName) {
                $count = $counts[$campusName] ?? 0;
                $row[] = $count;
                $total += $count;
            }

            $row[] = $total;
            $rows[] = $row;
        }

        $this->newLine();
        $this->info($dryRun ? 'Registros que se actualizarian:' : 'Registros actualizados:');
        $this->table($headers, $rows);

        $this->newLine();
        $this->info('Sedes disponibles:');
        $this->table(
            ['Sede', 'ID'],
            collect($campuses)->map(fn (int $id, string $name) => [$name, $id])->values()->all()
        );
    }
}
