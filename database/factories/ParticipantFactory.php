<?php

namespace Database\Factories;

use App\Models\Participant;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participant>
 */
class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition(): array
    {
        return [
            'document'         => fake()->unique()->numerify('##########'),
            'first_name'       => fake('es_CO')->firstName(),
            'last_name'        => fake('es_CO')->lastName(),
            'email'            => fake()->unique()->safeEmail(),
            'role'             => fake()->randomElement(['Estudiante', 'Docente', 'Administrativo']),
            'affiliation'      => fake('es_CO')->company(),
            'sexo'             => fake()->randomElement(['M', 'F']),
            'grupo_priorizado' => fake()->randomElement(['Víctimas', 'LGBTQ+', 'Discapacidad', null, null]),
            'program_id'       => Program::factory(),
        ];
    }

    public function estudiante(): static
    {
        return $this->state(fn () => ['role' => 'Estudiante']);
    }

    public function docente(): static
    {
        return $this->state(fn () => ['role' => 'Docente']);
    }

    public function sinGrupoPriorizado(): static
    {
        return $this->state(fn () => ['grupo_priorizado' => null]);
    }
}
