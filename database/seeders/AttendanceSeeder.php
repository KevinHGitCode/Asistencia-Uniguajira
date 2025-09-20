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

        // Seleccionar 400 participantes aleatorios para todo el proceso
        $allParticipantIds = Participant::inRandomOrder()->limit(400)->pluck('id')->toArray();

        foreach ($events as $event) {
            $attendancesCount = fake()->numberBetween(30, 60);
            // Seleccionar IDs aleatorios de los 400 para este evento
            $participantIds = collect($allParticipantIds)->shuffle()->take($attendancesCount);
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
