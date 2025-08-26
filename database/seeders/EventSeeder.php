<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        $userIds = \App\Models\User::pluck('id')->toArray();
        $rows = [];
        for ($i = 0; $i < 20; $i++) {
            $start = $faker->time('H:i:s');
            $end = $faker->time('H:i:s');
            $rows[] = [
                'title' => $faker->sentence(3),
                'description' => $faker->paragraph(),
                'date' => $faker->dateTimeBetween('-6 months', '+3 months')->format('Y-m-d'),
                'start_time' => $start,
                'end_time' => $end,
                'location' => $faker->city(), // ubicación aleatoria
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
