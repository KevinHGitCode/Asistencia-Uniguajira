<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    public function definition(): array
    {
        $municipios = [
            'Riohacha', 'Maicao', 'Uribia', 'Manaure', 'Villanueva',
            'San Juan del Cesar', 'Fonseca', 'Barrancas', 'Albania',
            'Dibulla', 'Distracción', 'El Molino', 'Hatonuevo',
            'La Jagua del Pilar', 'Urumita',
        ];

        $barrios = [
            'Centro', 'El Progreso', 'La Esperanza', 'Villa del Mar',
            'El Prado', 'Los Almendros', 'Simón Bolívar', 'La Paz',
            'El Carmen', 'Nueva Colombia',
        ];

        return [
            'municipio' => $this->faker->randomElement($municipios),
            'barrio'    => $this->faker->optional(0.8)->randomElement($barrios),
            'direccion' => $this->faker->optional(0.7)->streetAddress(),
        ];
    }
}
