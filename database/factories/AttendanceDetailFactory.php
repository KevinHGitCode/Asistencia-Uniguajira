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
                'Masculino', 'Femenino', 'Otro',
            ]),
            'telefono'         => $this->faker->optional(0.7)->numerify('3##-###-####'),
            'municipio'        => $this->faker->optional(0.6)->city(),
            'barrio'           => $this->faker->optional(0.4)->word(),
            'direccion'        => $this->faker->optional(0.3)->streetAddress(),
            'grupo_priorizado' => $this->faker->optional(0.6)->randomElement([
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
