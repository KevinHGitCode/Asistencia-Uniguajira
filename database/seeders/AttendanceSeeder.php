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
            $attendancesCount = fake()->numberBetween(20, 50);
            // Seleccionar participantes aleatorios directamente en la base de datos
            $participantIds = Participant::inRandomOrder()->limit($attendancesCount)->pluck('id');
            $rows = [];
            foreach ($participantIds as $participantId) {
                $rows[] = [
                    'event_id' => $event->id,
                    'participant_id' => $participantId,
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
