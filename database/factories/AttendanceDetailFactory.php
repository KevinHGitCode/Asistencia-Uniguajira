<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttendanceDetail>
 */
class AttendanceDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'gender'             => $this->faker->optional(0.85)->randomElement([
                'Masculino', 'Femenino', 'Otro',
            ]),
            'phone'         => $this->faker->optional(0.7)->numerify('3##-###-####'),
            'city'        => $this->faker->optional(0.6)->city(),
            'neighborhood'           => $this->faker->optional(0.4)->word(),
            'address'        => $this->faker->optional(0.3)->streetAddress(),
            'priority_group' => $this->faker->optional(0.6)->randomElement([
                'Indígena',
                'Afrodescendiente',
                'Discapacitado',
                'Víctima de Conflicto Armado',
                'Comunidad LGTBQ+',
                'Habitante de Frontera',
                'Ninguno',
            ]),
        ];
    }
}
