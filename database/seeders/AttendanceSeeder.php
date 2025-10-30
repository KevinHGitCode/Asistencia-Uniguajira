<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use \App\Models\Event;
use \App\Models\Participant;
use \App\Models\Attendance;

class AttendanceSeeder extends Seeder
{
    // Constantes configurables
    private const MIN_PROGRAMS = 2; // Mínimo de programas por evento
    private const MAX_PROGRAMS = 5; // Máximo de programas por evento
    private const MIN_STUDENTS = 40; // Mínimo de estudiantes por programa
    private const MAX_STUDENTS = 60; // Máximo de estudiantes por programa
    private const MIN_TEACHERS = 0; // Mínimo de docentes por evento
    private const MAX_TEACHERS = 10; // Máximo de docentes por evento

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Solo eventos cuya fecha sea hoy o anterior
        $today = now()->toDateString();
        $events = Event::whereDate('date', '<=', $today)->get();

        foreach ($events as $event) {
            // Seleccionar programas aleatorios
            $programIds = \App\Models\Program::inRandomOrder()
                ->limit(fake()->numberBetween(self::MIN_PROGRAMS, self::MAX_PROGRAMS))
                ->pluck('id');

            $rows = [];

            foreach ($programIds as $programId) {
                // Seleccionar estudiantes aleatorios
                $studentIds = Participant::where('role', 'Estudiante')
                    ->where('program_id', $programId)
                    ->inRandomOrder()
                    ->limit(fake()->numberBetween(self::MIN_STUDENTS, self::MAX_STUDENTS))
                    ->pluck('id');

                foreach ($studentIds as $studentId) {
                    $rows[] = [
                        'event_id' => $event->id,
                        'participant_id' => $studentId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Seleccionar docentes aleatorios
            $teacherIds = Participant::where('role', 'Docente')
                ->inRandomOrder()
                ->limit(fake()->numberBetween(self::MIN_TEACHERS, self::MAX_TEACHERS))
                ->pluck('id');

            foreach ($teacherIds as $teacherId) {
                $rows[] = [
                    'event_id' => $event->id,
                    'participant_id' => $teacherId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($rows)) {
                Attendance::insert($rows);
            }
        }
    }
}
