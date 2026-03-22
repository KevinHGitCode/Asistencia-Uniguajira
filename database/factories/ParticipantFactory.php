<?php

namespace Database\Factories;

use App\Models\Affiliation;
use App\Models\Participant;
use App\Models\ParticipantRole;
use App\Models\ParticipantType;
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
            'document'     => fake()->unique()->numerify('##########'),
            'student_code' => fake()->boolean(40) ? fake()->unique()->numerify('##########') : null,
            'first_name'   => fake('es_CO')->firstName(),
            'last_name'    => fake('es_CO')->lastName(),
            'email'        => fake()->boolean(85) ? fake()->unique()->safeEmail() : null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Participant $participant) {
            $roleName = fake()->randomElement(['Estudiante', 'Docente', 'Administrativos', 'Graduado']);
            $type = ParticipantType::firstOrCreate(['name' => $roleName]);

            $programId    = null;
            $affiliationId = null;

            if (in_array($roleName, ['Estudiante', 'Graduado'])) {
                $program = Program::inRandomOrder()->first();
                $programId = $program?->id;
            }

            if ($roleName === 'Docente') {
                $affiliation = Affiliation::firstOrCreate([
                    'name' => fake()->randomElement(['Catedratico', 'Ocasional', 'Planta']),
                ]);
                $affiliationId = $affiliation->id;

                // Docentes pueden tener múltiples programas
                $count = fake()->randomElement([1, 1, 2]);
                $programs = Program::inRandomOrder()->limit($count)->pluck('id');
                foreach ($programs as $pid) {
                    ParticipantRole::create([
                        'participant_id'      => $participant->id,
                        'participant_type_id' => $type->id,
                        'program_id'          => $pid,
                        'affiliation_id'      => $affiliationId,
                        'is_active'           => true,
                    ]);
                }
                return;
            }

            ParticipantRole::create([
                'participant_id'      => $participant->id,
                'participant_type_id' => $type->id,
                'program_id'          => $programId,
                'affiliation_id'      => $affiliationId,
                'is_active'           => true,
            ]);
        });
    }

    public function estudiante(): static
    {
        return $this->state(fn () => [
            'student_code' => fake()->unique()->numerify('##########'),
        ])->afterCreating(function (Participant $participant) {
            $type = ParticipantType::firstOrCreate(['name' => 'Estudiante']);
            $program = Program::inRandomOrder()->first();
            ParticipantRole::create([
                'participant_id'      => $participant->id,
                'participant_type_id' => $type->id,
                'program_id'          => $program?->id,
                'is_active'           => true,
            ]);
        });
    }

    public function docente(): static
    {
        return $this->state(fn () => [
            'student_code' => null,
        ])->afterCreating(function (Participant $participant) {
            $type = ParticipantType::firstOrCreate(['name' => 'Docente']);
            $affiliation = Affiliation::firstOrCreate([
                'name' => fake()->randomElement(['Catedratico', 'Ocasional', 'Planta']),
            ]);
            $program = Program::inRandomOrder()->first();
            ParticipantRole::create([
                'participant_id'      => $participant->id,
                'participant_type_id' => $type->id,
                'program_id'          => $program?->id,
                'affiliation_id'      => $affiliation->id,
                'is_active'           => true,
            ]);
        });
    }

    public function administrativo(): static
    {
        return $this->state(fn () => [
            'student_code' => null,
        ])->afterCreating(function (Participant $participant) {
            $type = ParticipantType::firstOrCreate(['name' => 'Administrativos']);
            ParticipantRole::create([
                'participant_id'      => $participant->id,
                'participant_type_id' => $type->id,
                'is_active'           => true,
            ]);
        });
    }

    public function graduado(): static
    {
        return $this->state(fn () => [
            'student_code' => fake()->unique()->numerify('##########'),
        ])->afterCreating(function (Participant $participant) {
            $type = ParticipantType::firstOrCreate(['name' => 'Graduado']);
            $program = Program::inRandomOrder()->first();
            ParticipantRole::create([
                'participant_id'      => $participant->id,
                'participant_type_id' => $type->id,
                'program_id'          => $program?->id,
                'is_active'           => true,
            ]);
        });
    }

    public function sinGrupoPriorizado(): static
    {
        return $this->state(fn () => []);
    }
}