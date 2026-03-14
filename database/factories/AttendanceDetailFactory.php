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
            'sexo'             => $this->faker->optional(0.85)->randomElement([
                'Masculino', 'Femenino', 'No binario', 'Prefiero no decir',
            ]),
            'telefono'         => $this->faker->optional(0.7)->numerify('3##-###-####'),
            'address_id'       => $this->faker->boolean(60)
                ? Address::factory()
                : null,
            'grupo_priorizado' => $this->faker->optional(0.6)->randomElement([
                'Víctimas del conflicto armado',
                'Población con discapacidad',
                'Comunidades indígenas',
                'Comunidades afrodescendientes',
                'Jóvenes rurales',
                'Adulto mayor',
                'LGBTIQ+',
                'Ninguno',
            ]),
        ];
    }
}
