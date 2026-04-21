<?php

namespace Database\Seeders;

use App\Models\Dependency;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seeders base
        $this->call([
            DependenciesSeeder::class,
            ParticipantTypeSeeder::class,
        ]);

        // Lista de administradores
        $admins = [
            ['name' => 'carlos', 'email' => 'carlos@uniguajira.edu.co'],
            ['name' => 'luis', 'email' => 'lfelipezapata@uniguajira.edu.co'],
            ['name' => 'kevin', 'email' => 'khafiddiaz@uniguajira.edu.co'],
            ['name' => 'daniel', 'email' => 'dandressierra@uniguajira.edu.co'],
            ['name' => 'renzo', 'email' => 'rdamiansanchez@uniguajira.edu.co'],
        ];

        foreach ($admins as $admin) {
            User::create([
                'name' => $admin['name'],
                'email' => $admin['email'],
                'password' => Hash::make('12345678'),
                'role' => 'admin',
            ]);
        }

        // Usuario con dependencia
        $kevinUser = User::create([
            'name' => 'kevin User',
            'email' => 'kevin.user@example.com',
            'password' => Hash::make('12345678'),
            'role' => 'user',
        ]);

        $kevinUser->dependencies()->sync([
            Dependency::where('name', 'Bienestar Universitario')->first()->id,
        ]);

        // Usuario con varias dependencias
        $user = User::create([
            'name' => 'user',
            'email' => 'user@example.com',
            'password' => Hash::make('12345678'),
            'role' => 'user',
        ]);

        $user->dependencies()->sync([
            Dependency::where('name', 'Gestión Documental')->first()->id,
            Dependency::where('name', 'Gestión Administrativa y Financiera')->first()->id,
        ]);

        // Otros seeders
        $this->call([
            AreasSeeder::class,
            // EventSeeder::class,
            // ProgramSeeder::class,
            // AffiliationSeeder::class,
            // ParticipantSeeder::class,
            // AttendanceSeeder::class,
            FormatSeeder::class,
        ]);
    }
}