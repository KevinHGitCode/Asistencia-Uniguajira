<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    // Constantes configurables
    private const NUM_EVENTS = 50; // Número de eventos a crear

    public function run(): void
    {
        $faker = \Faker\Factory::create();
        $userIds = \App\Models\User::pluck('id')->toArray();
        $rows = [];

        for ($i = 0; $i < self::NUM_EVENTS; $i++) {
            $start = $faker->time('H:i:s');
            $end = $faker->time('H:i:s');
            $title = $faker->sentence(3);
            $slug = Str::slug($title) . '-' . date('Ymd', strtotime($faker->date())) . '-' . uniqid();

            $rows[] = [
                'title' => $faker->sentence(3),
                'description' => $faker->paragraph(),
                'date' => $faker->dateTimeBetween('-6 months', '+3 months')->format('Y-m-d'),
                'start_time' => $start,
                'end_time' => $end,
                'location' => $faker->city(), // ubicación aleatoria
                'link' => $slug, 
                'user_id' => $faker->randomElement($userIds),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (!empty($rows)) {
            Event::insert($rows);
        }
    }
}
