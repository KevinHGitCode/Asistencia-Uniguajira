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
        $role = fake()->randomElement(['Estudiante', 'Docente', 'Administrativo', 'Graduado']);

        return [
            'document'         => fake()->unique()->numerify('##########'),
            // student_code solo aplica para Estudiante y Graduado (65 % de probabilidad)
            'student_code'     => in_array($role, ['Estudiante', 'Graduado']) && fake()->boolean(65)
                                    ? fake()->unique()->numerify('##########')
                                    : null,
            'first_name'       => fake('es_CO')->firstName(),
            'last_name'        => fake('es_CO')->lastName(),
            'email'            => fake()->optional(0.85)->unique()->safeEmail(),
            'role'             => $role,
            'affiliation'      => $role === 'Docente'
                                    ? fake()->randomElement(['Catedratico', 'Ocasional', 'Planta', null])
                                    : null,
            'sexo'             => fake()->randomElement(['Masculino', 'Femenino', 'No binario', null]),
            'grupo_priorizado' => fake()->randomElement([
                'Víctimas del conflicto armado',
                'Población con discapacidad',
                'Comunidades indígenas',
                'Ninguno', null, null,
            ]),
            'program_id'       => in_array($role, ['Estudiante', 'Graduado'])
                                    ? Program::factory()
                                    : null,
        ];
    }

    public function estudiante(): static
    {
        return $this->state(fn () => [
            'role'         => 'Estudiante',
            'student_code' => fake()->unique()->numerify('##########'),
            'affiliation'  => null,
        ]);
    }

    public function docente(): static
    {
        return $this->state(fn () => [
            'role'         => 'Docente',
            'student_code' => null,
            'affiliation'  => fake()->randomElement(['Catedratico', 'Ocasional', 'Planta']),
        ]);
    }

    public function administrativo(): static
    {
        return $this->state(fn () => [
            'role'         => 'Administrativo',
            'student_code' => null,
            'affiliation'  => null,
        ]);
    }

    public function graduado(): static
    {
        return $this->state(fn () => [
            'role'         => 'Graduado',
            'student_code' => fake()->unique()->numerify('##########'),
            'affiliation'  => null,
        ]);
    }

    public function sinGrupoPriorizado(): static
    {
        return $this->state(fn () => ['grupo_priorizado' => null]);
    }
}
