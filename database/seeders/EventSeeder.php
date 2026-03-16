<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\User;
use App\Models\Dependency;
use App\Models\Area;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class EventSeeder extends Seeder
{
    private const NUM_EVENTS = 20;

    public function run(): void
    {
        $faker = \Faker\Factory::create();

        $users = User::with('dependencies.areas')->get();
        $allDependencies = Dependency::with('areas')->get();
        $now = now();

        if ($users->isEmpty()) {
            return;
        }

        $rows = [];

        for ($i = 0; $i < self::NUM_EVENTS; $i++) {

            $user = $users->random();

            // 10% de probabilidad de evento sin dependencia (tipo super admin)
            $dependency = null;
            if (! $faker->boolean(10) && $allDependencies->isNotEmpty()) {
                $dependencyPool = $user->dependencies->isNotEmpty()
                    ? $user->dependencies
                    : $allDependencies;
                if ($dependencyPool->isNotEmpty()) {
                    $dependency = $dependencyPool->random();
                }
            }

            $areaId = null;

            if ($dependency) {
                // 50% probabilidad de asignar area si la dependencia tiene areas
                if ($dependency->areas->isNotEmpty() && $faker->boolean(50)) {
                    $areaId = $dependency->areas->random()->id;
                }
            }

            [$date, $start, $end] = match ($i) {
                0 => $this->buildTimedEventWindow($now->copy()->subMinutes(5), 60),
                1 => $this->buildTimedEventWindow($now->copy()->addMinutes(15), 60),
                2 => $this->buildTimedEventWindow($now->copy()->addHour(), 60),
                default => [
                    $faker->dateTimeBetween('-6 months', '+3 months')->format('Y-m-d'),
                    $faker->time('H:i:s'),
                    $faker->time('H:i:s'),
                ],
            };

            $title = $faker->sentence(3);
            $slug = Str::slug($title) . '-' . uniqid();

            $rows[] = [
                'title' => $title,
                'dependency_id' => $dependency?->id,
                'area_id' => $areaId,
                'description' => $faker->paragraph(),
                'date' => $date,
                'start_time' => $start,
                'end_time' => $end,
                'location' => $faker->city(),
                'link' => $slug,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Event::insert($rows);
    }

    private function buildTimedEventWindow(Carbon $startAt, int $durationMinutes): array
    {
        $endAt = $startAt->copy()->addMinutes($durationMinutes);

        return [
            $startAt->format('Y-m-d'),
            $startAt->format('H:i:s'),
            $endAt->format('H:i:s'),
        ];
    }
}
