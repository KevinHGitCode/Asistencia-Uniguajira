<?php

namespace Database\Seeders;

use App\Models\Dependency;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Cargar dependencias primero
        $this->call(DependenciesSeeder::class);

        // Admins sin dependencia
        User::factory()->create([
            'name' => 'carlos',
            'email' => 'carlos@example.com',
            'password' => bcrypt('12345678'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'luis',
            'email' => 'luis@example.com',
            'password' => bcrypt('12345678'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'kevin',
            'email' => 'kevin@example.com',
            'password' => bcrypt('12345678'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'daniel',
            'email' => 'daniel@example.com',
            'password' => bcrypt('12345678'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'renzo',
            'email' => 'renzo@example.com',
            'password' => bcrypt('12345678'),
            'role' => 'admin',
        ]);

        // Usuarios con dependencia (MANY TO MANY)
        $kevinUser = User::factory()->create([
            'name' => 'kevin',
            'email' => 'kevin.user@example.com',
            'password' => bcrypt('12345678'),
            'role' => 'user',
        ]);

        $kevinUser->dependencies()->attach(
            Dependency::where('name', 'Bienestar Universitario')->first()->id
        );

        $user = User::factory()->create([
            'name' => 'user',
            'email' => 'user@example.com',
            'password' => bcrypt('12345678'),
            'role' => 'user',
        ]);

        $user->dependencies()->attach(
            Dependency::where('name', 'Gestión Documental')->first()->id
        );

        // Otros seeders
        $this->call(EventSeeder::class);
        $this->call(ProgramSeeder::class);
        $this->call(ParticipantSeeder::class);
        $this->call(AttendanceSeeder::class);
    }
}
