<?php

namespace Database\Factories;

use App\Models\Dependency;
use App\Models\Campus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dependency>
 */
class DependencyFactory extends Factory
{
    protected $model = Dependency::class;

    public function definition(): array
    {
        return [
            'name' => fake('es_CO')->unique()->company(),
        ];
    }

    public function forCampus(Campus $campus): static
    {
        return $this->state(fn () => ['campus_id' => $campus->id]);
    }
}
