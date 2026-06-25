<?php

namespace Tests\Feature\Statistics\Concerns;

use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ParticipantRole;
use App\Models\ParticipantType;
use App\Models\Program;
use App\Models\User;

/**
 * Escenario de datos reutilizable para los tests de estadisticas.
 *
 * Periodo amplio: 2026-01-01 a 2026-03-31
 * Periodo estrecho: 2026-03-01
 */
trait HasStatisticsScenario
{
    protected const WIDE_DATE_FROM = '2026-01-01';

    protected const WIDE_DATE_TO = '2026-03-31';

    protected const NARROW_DATE = '2026-03-01';

    protected const WIDE_ATTENDANCES = 4;

    protected const WIDE_PARTICIPANTS = 3;

    protected const WIDE_EVENTS = 2;

    protected const NARROW_ATTENDANCES = 2;

    protected const NARROW_PARTICIPANTS = 2;

    protected const ALL_ATTENDANCES = 5;

    protected const ALL_PARTICIPANTS = 3;

    protected function createScenario(): array
    {
        $progA = Program::factory()->create(['name' => 'Ingenieria de Sistemas']);
        $progB = Program::factory()->create(['name' => 'Administracion de Empresas']);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin);

        $event1 = Event::factory()->create(['user_id' => $admin->id, 'date' => '2026-01-10']);
        $event2 = Event::factory()->create(['user_id' => $admin->id, 'date' => '2026-03-01']);
        $eventOld = Event::factory()->create(['user_id' => $admin->id, 'date' => '2025-11-01']);

        $alice = Participant::create([
            'document' => 'STAT-001',
            'first_name' => 'Alice',
            'last_name' => 'Garcia',
            'email' => 'alice.stats@example.com',
        ]);

        $bob = Participant::create([
            'document' => 'STAT-002',
            'first_name' => 'Bob',
            'last_name' => 'Martinez',
            'email' => 'bob.stats@example.com',
        ]);

        $carol = Participant::create([
            'document' => 'STAT-003',
            'first_name' => 'Carol',
            'last_name' => 'Lopez',
            'email' => 'carol.stats@example.com',
        ]);

        $aliceRole = $this->participantRole($alice, 'Estudiante', $progA);
        $bobRole = $this->participantRole($bob, 'Docente', $progB);
        $carolRole = $this->participantRole($carol, 'Estudiante', $progA);

        // Alice asiste a los 3 eventos (2 dentro del periodo amplio + 1 fuera)
        $this->attendanceWithDetails($event1, $alice, $aliceRole, 'F', 'Victimas');
        $this->attendanceWithDetails($event2, $alice, $aliceRole, 'F', 'Victimas');
        $this->attendanceWithDetails($eventOld, $alice, $aliceRole, 'F', 'Victimas');

        // Bob solo asiste al evento 1
        $this->attendanceWithDetails($event1, $bob, $bobRole, 'M');

        // Carol solo asiste al evento 2
        $this->attendanceWithDetails($event2, $carol, $carolRole, 'F', 'LGBTQ+');

        return compact('admin', 'progA', 'progB', 'alice', 'bob', 'carol',
            'event1', 'event2', 'eventOld');
    }

    protected function wideFilter(): array
    {
        return ['dateFrom' => self::WIDE_DATE_FROM, 'dateTo' => self::WIDE_DATE_TO];
    }

    protected function narrowFilter(): array
    {
        return ['dateFrom' => self::NARROW_DATE, 'dateTo' => self::NARROW_DATE];
    }

    private function participantRole(Participant $participant, string $typeName, Program $program): ParticipantRole
    {
        return ParticipantRole::create([
            'participant_id' => $participant->id,
            'participant_type_id' => ParticipantType::firstOrCreate(['name' => $typeName])->id,
            'program_id' => $program->id,
            'is_active' => true,
        ]);
    }

    private function attendanceWithDetails(
        Event $event,
        Participant $participant,
        ParticipantRole $role,
        ?string $gender,
        ?string $priorityGroup = null,
    ): Attendance {
        $attendance = Attendance::create([
            'event_id' => $event->id,
            'participant_id' => $participant->id,
        ]);

        AttendanceDetail::create([
            'attendance_id' => $attendance->id,
            'participant_role_id' => $role->id,
            'gender' => $gender,
            'priority_group' => $priorityGroup,
        ]);

        return $attendance;
    }
}
