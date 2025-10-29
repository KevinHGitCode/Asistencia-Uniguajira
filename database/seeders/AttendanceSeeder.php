<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use \App\Models\Event;
use \App\Models\Participant;
use \App\Models\Attendance;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Solo eventos cuya fecha sea hoy o anterior
        $today = now()->toDateString();
        $events = Event::whereDate('date', '<=', $today)->get();

        foreach ($events as $event) {
            // Seleccionar entre 2 y 5 programas aleatorios
            $programIds = \App\Models\Program::inRandomOrder()->limit(fake()->numberBetween(2, 5))->pluck('id');

            $rows = [];

            foreach ($programIds as $programId) {
                // Seleccionar entre 40 y 60 estudiantes del programa
                $studentIds = Participant::where('role', 'Estudiante')
                    ->where('program_id', $programId)
                    ->inRandomOrder()
                    ->limit(fake()->numberBetween(40, 60))
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

            // Seleccionar entre 0 y 10 profesores
            $teacherIds = Participant::where('role', 'Docente')
                ->inRandomOrder()
                ->limit(fake()->numberBetween(0, 10))
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
