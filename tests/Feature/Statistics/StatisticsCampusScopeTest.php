<?php

namespace Tests\Feature\Statistics;

use App\Models\Attendance;
use App\Models\Campus;
use App\Models\Event;
use App\Models\User;
use App\Services\CampusScopeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticsCampusScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_solo_ve_conteos_de_su_sede(): void
    {
        [$maicao, $riohacha] = $this->campuses();
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $maicao->id,
        ]);

        Event::factory()->create(['campus_id' => $maicao->id]);
        Event::factory()->create(['campus_id' => $riohacha->id]);

        $response = $this->actingAs($admin)
            ->getJson('/api/statistics/total-events')
            ->assertOk();

        $this->assertSame(1, $response->json());
    }

    public function test_superadmin_sin_sede_activa_ve_todas_las_sedes(): void
    {
        [$maicao, $riohacha] = $this->campuses();
        $superadmin = User::factory()->create([
            'role' => User::ROLE_SUPERADMIN,
            'campus_id' => null,
        ]);

        Event::factory()->create(['campus_id' => $maicao->id]);
        Event::factory()->create(['campus_id' => $riohacha->id]);

        $response = $this->actingAs($superadmin)
            ->getJson('/api/statistics/total-events')
            ->assertOk();

        $this->assertSame(2, $response->json());
    }

    public function test_superadmin_con_sede_activa_del_dashboard_no_filtra_estadisticas_sin_filtro_propio(): void
    {
        [$maicao, $riohacha] = $this->campuses();
        $superadmin = User::factory()->create([
            'role' => User::ROLE_SUPERADMIN,
            'campus_id' => null,
        ]);

        Event::factory()->create(['campus_id' => $maicao->id]);
        Event::factory()->create(['campus_id' => $riohacha->id]);

        $response = $this->withSession([CampusScopeService::SESSION_KEY => $riohacha->id])
            ->actingAs($superadmin)
            ->getJson('/api/statistics/total-events')
            ->assertOk();

        $this->assertSame(2, $response->json());
    }

    public function test_superadmin_filtra_estadisticas_solo_con_campus_ids_del_modulo(): void
    {
        [$maicao, $riohacha] = $this->campuses();
        $superadmin = User::factory()->create([
            'role' => User::ROLE_SUPERADMIN,
            'campus_id' => null,
        ]);

        Event::factory()->create(['campus_id' => $maicao->id]);
        Event::factory()->create(['campus_id' => $riohacha->id]);

        $response = $this->withSession([CampusScopeService::SESSION_KEY => $riohacha->id])
            ->actingAs($superadmin)
            ->getJson('/api/statistics/total-events?campusIds[]='.$maicao->id)
            ->assertOk();

        $this->assertSame(1, $response->json());

        $this->actingAs($superadmin)
            ->getJson('/api/statistics/filter-options')
            ->assertOk()
            ->assertJsonPath('campusIds', []);
    }

    public function test_asistencias_se_filtran_por_sede_del_evento(): void
    {
        [$maicao, $riohacha] = $this->campuses();
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'campus_id' => $maicao->id,
        ]);

        $maicaoEvent = Event::factory()->create(['campus_id' => $maicao->id]);
        $riohachaEvent = Event::factory()->create(['campus_id' => $riohacha->id]);
        Attendance::factory()->count(2)->create(['event_id' => $maicaoEvent->id]);
        Attendance::factory()->count(3)->create(['event_id' => $riohachaEvent->id]);

        $response = $this->actingAs($admin)
            ->getJson('/api/statistics/total-attendances')
            ->assertOk();

        $this->assertSame(2, $response->json());
    }

    private function campuses(): array
    {
        return [
            Campus::create(['name' => 'Maicao']),
            Campus::create(['name' => 'Riohacha']),
        ];
    }
}
