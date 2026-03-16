<?php

namespace Database\Factories;

use App\Models\Participant;
use App\Models\Program;
use App\Models\Affiliation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Participant>
 */
class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition(): array
    {
        $role = fake()->randomElement(['Estudiante', 'Docente', 'Administrativos', 'Graduado']);

        $affiliationId = null;
        if ($role === 'Docente') {
            $affiliationName = fake()->randomElement(['Catedratico', 'Ocasional', 'Planta']);
            $affiliationId = Affiliation::firstOrCreate(['name' => $affiliationName])->id;
        }

        return [
            'document'         => fake()->unique()->numerify('##########'),
            'student_code'     => in_array($role, ['Estudiante', 'Graduado']) && fake()->boolean(65)
                                    ? fake()->unique()->numerify('##########')
                                    : null,
            'first_name'       => fake('es_CO')->firstName(),
            'last_name'        => fake('es_CO')->lastName(),
            'email'            => fake()->optional(0.85)->unique()->safeEmail(),
            'role'             => $role,
            'affiliation_id'   => $affiliationId,
            'sexo'             => fake()->randomElement(['Masculino', 'Femenino', 'No binario', null]),
            'grupo_priorizado' => fake()->randomElement([
                'Víctimas del conflicto armado',
                'Población con discapacidad',
                'Comunidades indígenas',
                'Ninguno', null, null,
            ]),
        ];
    }

    /**
     * Adjunta 1-2 programas al participante después de crearlo
     * (solo para roles Estudiante y Graduado).
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Participant $participant) {
            if (in_array($participant->role, ['Estudiante', 'Graduado'])) {
                $count    = fake()->randomElement([1, 1, 1, 2]); // 75% uno, 25% dos
                $programs = Program::inRandomOrder()->limit($count)->pluck('id');
                $participant->programs()->syncWithoutDetaching($programs);
            }
        });
    }

    public function estudiante(): static
    {
        return $this->state(fn () => [
            'role'            => 'Estudiante',
            'student_code'    => fake()->unique()->numerify('##########'),
            'affiliation_id'  => null,
        ])->afterCreating(function (Participant $participant) {
            $program = Program::inRandomOrder()->first();
            if ($program) {
                $participant->programs()->syncWithoutDetaching([$program->id]);
            }
        });
    }

    public function docente(): static
    {
        return $this->state(fn () => [
            'role'           => 'Docente',
            'student_code'   => null,
            'affiliation_id' => Affiliation::firstOrCreate([
                'name' => fake()->randomElement(['Catedratico', 'Ocasional', 'Planta']),
            ])->id,
        ]);
    }

    public function administrativo(): static
    {
        return $this->state(fn () => [
            'role'           => 'Administrativo',
            'student_code'   => null,
            'affiliation_id' => null,
        ]);
    }

    public function graduado(): static
    {
        return $this->state(fn () => [
            'role'         => 'Graduado',
            'student_code' => fake()->unique()->numerify('##########'),
            'affiliation_id' => null,
        ])->afterCreating(function (Participant $participant) {
            $program = Program::inRandomOrder()->first();
            if ($program) {
                $participant->programs()->syncWithoutDetaching([$program->id]);
            }
        });
    }

    public function sinGrupoPriorizado(): static
    {
        return $this->state(fn () => ['grupo_priorizado' => null]);
    }
}
