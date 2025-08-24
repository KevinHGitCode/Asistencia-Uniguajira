<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::factory()->create([
            'name' => 'carlos',
            'email' => 'carlos@example.com',
            'password' => bcrypt('12345678'),
        ]);

        User::factory()->create([
            'name' => 'luis',
            'email' => 'luis@example.com',
            'password' => bcrypt('12345678'),
        ]);

        User::factory()->create([
            'name' => 'kevin',
            'email' => 'kevin@example.com',
            'password' => bcrypt('12345678'),
        ]);

        User::factory()->create([
            'name' => 'daniel',
            'email' => 'daniel@example.com',
            'password' => bcrypt('12345678'),
        ]);

        User::factory()->create([
            'name' => 'renzo',
            'email' => 'renzo@example.com',
            'password' => bcrypt('12345678'),
        ]);

        $this->call(EventSeeder::class);
        $this->call(ProgramSeeder::class);
        $this->call(ParticipantSeeder::class);
        $this->call(AttendanceSeeder::class);
    }
}
