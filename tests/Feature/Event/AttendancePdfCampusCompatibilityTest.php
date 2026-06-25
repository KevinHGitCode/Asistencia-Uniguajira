<?php

namespace Tests\Feature\Event;

use App\Models\Campus;
use App\Models\Dependency;
use App\Models\Event;
use App\Models\Format;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttendancePdfCampusCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_existing_event_pdf_using_dependency_campus_when_event_has_no_campus_id(): void
    {
        $maicao = Campus::create(['name' => 'Maicao']);
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $maicao->id,
            'email_verified_at' => now(),
        ]);
        $dependency = Dependency::factory()->create([
            'name' => 'Bienestar Universitario - Maicao',
            'campus_id' => $maicao->id,
        ]);
        $event = $this->eventFor($admin, $dependency, null, 'Evento historico Maicao');

        $response = $this->actingAs($admin)->get(route('events.download', $event));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_admin_can_download_pdf_for_event_with_maicao_campus_id(): void
    {
        $maicao = Campus::create(['name' => 'Maicao']);
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $maicao->id,
            'email_verified_at' => now(),
        ]);
        $dependency = Dependency::factory()->create([
            'name' => 'Centro de Investigacion - Maicao',
            'campus_id' => $maicao->id,
        ]);
        $event = $this->eventFor($admin, $dependency, $maicao->id, 'Evento Maicao');

        $response = $this->actingAs($admin)->get(route('events.download', $event));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_admin_can_download_pdf_for_event_with_riohacha_campus_id(): void
    {
        $riohacha = Campus::create(['name' => 'Riohacha']);
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $riohacha->id,
            'email_verified_at' => now(),
        ]);
        $dependency = Dependency::factory()->create([
            'name' => 'Biblioteca - Riohacha',
            'campus_id' => $riohacha->id,
        ]);
        $event = $this->eventFor($admin, $dependency, $riohacha->id, 'Evento Riohacha');

        $response = $this->actingAs($admin)->get(route('events.download', $event));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_same_custom_format_remains_available_for_dependencies_in_different_campuses(): void
    {
        $maicao = Campus::create(['name' => 'Maicao']);
        $riohacha = Campus::create(['name' => 'Riohacha']);
        $format = Format::create([
            'name' => 'Formato global de prueba',
            'slug' => 'global-prueba',
            'mapping' => [
                'file' => 'general_1777609692.pdf',
                'startY' => 60.2,
                'rowHeight' => 8.15,
                'maxRows' => 16,
                'header' => [
                    'title' => ['x' => 43.8, 'y' => 39.39, 'w' => 218.6],
                    'dependency' => ['x' => 77.61, 'y' => 30.86, 'w' => 123.9],
                ],
                'columns' => [],
                'date_format' => ['day' => 'd', 'year' => 'Y', 'month' => 'm'],
                'time_format' => 'h:i A',
            ],
        ]);

        $maicaoAdmin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $maicao->id,
            'email_verified_at' => now(),
        ]);
        $riohachaAdmin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $riohacha->id,
            'email_verified_at' => now(),
        ]);
        $maicaoDependency = Dependency::factory()->create(['campus_id' => $maicao->id]);
        $riohachaDependency = Dependency::factory()->create(['campus_id' => $riohacha->id]);

        $maicaoDependency->formats()->attach($format);
        $riohachaDependency->formats()->attach($format);

        $maicaoEvent = $this->eventFor($maicaoAdmin, $maicaoDependency, $maicao->id, 'Formato Maicao');
        $riohachaEvent = $this->eventFor($riohachaAdmin, $riohachaDependency, $riohacha->id, 'Formato Riohacha');

        $this->actingAs($maicaoAdmin)
            ->get(route('events.download', ['id' => $maicaoEvent->id, 'formatSlug' => $format->slug]))
            ->assertOk();

        $this->actingAs($riohachaAdmin)
            ->get(route('events.download', ['id' => $riohachaEvent->id, 'formatSlug' => $format->slug]))
            ->assertOk();
    }

    public function test_general_format_is_allowed_even_when_dependency_has_custom_formats(): void
    {
        $riohacha = Campus::create(['name' => 'Riohacha']);
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $riohacha->id,
            'email_verified_at' => now(),
        ]);
        $dependency = Dependency::factory()->create(['campus_id' => $riohacha->id]);
        $customFormat = Format::create([
            'name' => 'Bienestar',
            'slug' => 'bienestar-prueba',
            'mapping' => [
                'file' => 'general_1777609692.pdf',
                'startY' => 60.2,
                'rowHeight' => 8.15,
                'maxRows' => 16,
                'header' => [
                    'title' => ['x' => 43.8, 'y' => 39.39, 'w' => 218.6],
                    'dependency' => ['x' => 77.61, 'y' => 30.86, 'w' => 123.9],
                ],
                'columns' => [],
            ],
        ]);

        $dependency->formats()->attach($customFormat);
        $event = $this->eventFor($admin, $dependency, $riohacha->id, 'Evento con formatos');

        $this->actingAs($admin)
            ->get(route('events.download', ['id' => $event->id, 'formatSlug' => 'general']))
            ->assertOk();
    }

    private function eventFor(User $user, Dependency $dependency, ?int $campusId, string $title): Event
    {
        return Event::create([
            'title' => $title,
            'description' => 'Evento de prueba para PDF',
            'date' => now()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'location' => 'Campus',
            'link' => Str::slug($title).'-'.$dependency->id,
            'user_id' => $user->id,
            'dependency_id' => $dependency->id,
            'campus_id' => $campusId,
        ]);
    }
}
