<?php

namespace Database\Factories;

use App\Models\Dependency;
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
}
