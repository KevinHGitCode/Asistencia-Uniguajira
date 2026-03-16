<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Event;
use App\Models\Participant;
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

            foreach ($programIds as $programId) {
                $studentQuery = Participant::query()
                    ->whereHas('programs', fn ($q) => $q->where('program_id', $programId));

                if ($studentTypeId) {
                    $studentQuery->whereHas('types', fn ($q) => $q->where('participant_type_id', $studentTypeId));
                }

                if (! empty($selected)) {
                    $studentQuery->whereNotIn('id', array_keys($selected));
                }

                $studentIds = $studentQuery->inRandomOrder()
                    ->limit(fake()->numberBetween(self::MIN_STUDENTS, self::MAX_STUDENTS))
                    ->pluck('id');

                foreach ($studentIds as $participantId) {
                    if (isset($selected[$participantId])) {
                        continue;
                    }
                    $selected[$participantId] = true;

                    $attendanceId = DB::table('attendances')->insertGetId([
                        'event_id' => $event->id,
                        'participant_id' => $participantId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    DB::table('attendance_details')->insert([
                        'attendance_id' => $attendanceId,
                        'gender' => self::GENDERS[array_rand(self::GENDERS)],
                        'phone' => $faker->numerify('3#########'),
                        'city' => $faker->city(),
                        'neighborhood' => $faker->streetName(),
                        'address' => $faker->streetAddress(),
                        'priority_group' => self::PRIORITY_GROUPS[array_rand(self::PRIORITY_GROUPS)],
                        'program_id' => $programId,
                        'participant_type_id' => $studentTypeId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            if (! $teacherTypeId) {
                continue;
            }

            $teacherQuery = Participant::query()
                ->whereHas('types', fn ($q) => $q->where('participant_type_id', $teacherTypeId));

            if (! empty($selected)) {
                $teacherQuery->whereNotIn('id', array_keys($selected));
            }

            $teacherIds = $teacherQuery->inRandomOrder()
                ->limit(fake()->numberBetween(self::MIN_TEACHERS, self::MAX_TEACHERS))
                ->pluck('id');

            foreach ($teacherIds as $participantId) {
                if (isset($selected[$participantId])) {
                    continue;
                }
                $selected[$participantId] = true;

                $attendanceId = DB::table('attendances')->insertGetId([
                    'event_id' => $event->id,
                    'participant_id' => $participantId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('attendance_details')->insert([
                    'attendance_id' => $attendanceId,
                    'gender' => self::GENDERS[array_rand(self::GENDERS)],
                    'phone' => $faker->numerify('3#########'),
                    'city' => $faker->city(),
                    'neighborhood' => $faker->streetName(),
                    'address' => $faker->streetAddress(),
                    'priority_group' => self::PRIORITY_GROUPS[array_rand(self::PRIORITY_GROUPS)],
                    'program_id' => null,
                    'participant_type_id' => $teacherTypeId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
