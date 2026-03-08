<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\Dependency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Area>
 */
class AreaFactory extends Factory
{
    protected $model = Area::class;

    public function definition(): array
    {
        return [
            'name'          => fake('es_CO')->words(2, true),
            'dependency_id' => Dependency::factory(),
        ];
    }
}
