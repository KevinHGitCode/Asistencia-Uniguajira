<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'event_id'       => Event::factory(),
            'participant_id' => Participant::factory(),
        ];
    }
}
