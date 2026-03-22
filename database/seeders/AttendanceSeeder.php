<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Event;
use App\Models\ParticipantType;
use App\Models\Program;

class AttendanceSeeder extends Seeder
{
    private const MIN_PROGRAMS = 2;
    private const MAX_PROGRAMS = 5;
    private const MIN_STUDENTS = 40;
    private const MAX_STUDENTS = 60;
    private const MIN_TEACHERS = 0;
    private const MAX_TEACHERS = 10;

    private const GENDERS = [
        'Masculino',
        'Femenino',
        'Otro',
    ];

    private const PRIORITY_GROUPS = [
        'Indigena',
        'Afrodescendiente',
        'Discapacitado',
        'Victima de Conflicto Armado',
        'Comunidad LGTBQ+',
        'Habitante de Frontera',
        'Ninguno',
    ];

    public function run(): void
    {
        $today = now()->toDateString();
        $events = Event::whereDate('date', '<=', $today)->get();
        if ($events->isEmpty()) {
            return;
        }

        $typeIds = ParticipantType::pluck('id', 'name')->toArray();
        $studentTypeId = $typeIds['Estudiante'] ?? null;
        $teacherTypeId = $typeIds['Docente'] ?? null;

        $faker = \Faker\Factory::create();

        foreach ($events as $event) {
            $selected = [];
            $now = now()->toDateTimeString();

            $programIds = Program::inRandomOrder()
                ->limit(fake()->numberBetween(self::MIN_PROGRAMS, self::MAX_PROGRAMS))
                ->pluck('id');

            // ── Estudiantes por programa ───────────────────────────────────
            foreach ($programIds as $programId) {
                // Buscar roles activos de estudiantes en este programa
                $roles = DB::table('participant_roles')
                    ->where('program_id', $programId)
                    ->where('is_active', 1)
                    ->when($studentTypeId, fn ($q) => $q->where('participant_type_id', $studentTypeId))
                    ->when(! empty($selected), fn ($q) => $q->whereNotIn('participant_id', array_keys($selected)))
                    ->inRandomOrder()
                    ->limit(fake()->numberBetween(self::MIN_STUDENTS, self::MAX_STUDENTS))
                    ->get(['id', 'participant_id']);

                foreach ($roles as $role) {
                    if (isset($selected[$role->participant_id])) {
                        continue;
                    }
                    $selected[$role->participant_id] = true;

                    $attendanceId = DB::table('attendances')->insertGetId([
                        'event_id'       => $event->id,
                        'participant_id' => $role->participant_id,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]);

                    DB::table('attendance_details')->insert([
                        'attendance_id'       => $attendanceId,
                        'participant_role_id' => $role->id,
                        'gender'              => self::GENDERS[array_rand(self::GENDERS)],
                        'phone'               => $faker->numerify('3#########'),
                        'city'                => $faker->city(),
                        'neighborhood'        => $faker->streetName(),
                        'address'             => $faker->streetAddress(),
                        'priority_group'      => self::PRIORITY_GROUPS[array_rand(self::PRIORITY_GROUPS)],
                        'created_at'          => $now,
                        'updated_at'          => $now,
                    ]);
                }
            }

            // ── Docentes ──────────────────────────────────────────────────
            if (! $teacherTypeId) {
                continue;
            }

            $teacherRoles = DB::table('participant_roles')
                ->where('participant_type_id', $teacherTypeId)
                ->where('is_active', 1)
                ->when(! empty($selected), fn ($q) => $q->whereNotIn('participant_id', array_keys($selected)))
                ->inRandomOrder()
                ->limit(fake()->numberBetween(self::MIN_TEACHERS, self::MAX_TEACHERS))
                ->get(['id', 'participant_id']);

            foreach ($teacherRoles as $role) {
                if (isset($selected[$role->participant_id])) {
                    continue;
                }
                $selected[$role->participant_id] = true;

                $attendanceId = DB::table('attendances')->insertGetId([
                    'event_id'       => $event->id,
                    'participant_id' => $role->participant_id,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);

                DB::table('attendance_details')->insert([
                    'attendance_id'       => $attendanceId,
                    'participant_role_id' => $role->id,
                    'gender'              => self::GENDERS[array_rand(self::GENDERS)],
                    'phone'               => $faker->numerify('3#########'),
                    'city'                => $faker->city(),
                    'neighborhood'        => $faker->streetName(),
                    'address'             => $faker->streetAddress(),
                    'priority_group'      => self::PRIORITY_GROUPS[array_rand(self::PRIORITY_GROUPS)],
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);
            }
        }
    }
}