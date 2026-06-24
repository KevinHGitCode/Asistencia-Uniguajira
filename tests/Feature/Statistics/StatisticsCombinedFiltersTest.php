<?php

namespace Tests\Feature\Statistics;

use App\Models\Campus;
use App\Models\Dependency;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticsCombinedFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_combina_sedes_dependencias_y_solo_sus_eventos(): void
    {
        [$maicao, $riohacha] = $this->campuses();
        $superadmin = User::factory()->create(['role' => User::ROLE_SUPERADMIN, 'campus_id' => null]);
        $other = User::factory()->create(['role' => User::ROLE_ADMIN, 'campus_id' => $maicao->id]);
        $depMaicao = Dependency::factory()->create(['campus_id' => $maicao->id]);
        $depRiohacha = Dependency::factory()->create(['campus_id' => $riohacha->id]);

        Event::factory()->create(['user_id' => $superadmin->id, 'campus_id' => $maicao->id, 'dependency_id' => $depMaicao->id]);
        Event::factory()->create(['user_id' => $superadmin->id, 'campus_id' => $riohacha->id, 'dependency_id' => $depRiohacha->id]);
        Event::factory()->create(['user_id' => $other->id, 'campus_id' => $maicao->id, 'dependency_id' => $depMaicao->id]);

        $response = $this->actingAs($superadmin)
            ->getJson('/api/statistics/total-events?campusIds[]='.$maicao->id.'&dependencyIds[]='.$depMaicao->id.'&onlyOwnEvents=1')
            ->assertOk();
        $this->assertSame(1, $response->json());

        $response = $this->actingAs($superadmin)
            ->getJson('/api/statistics/total-events?campusIds[]='.$maicao->id.'&campusIds[]='.$riohacha->id)
            ->assertOk();
        $this->assertSame(3, $response->json());
    }

    public function test_admin_solo_usa_su_sede_y_sus_dependencias(): void
    {
        [$maicao, $riohacha] = $this->campuses();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'campus_id' => $maicao->id]);
        $depMaicao = Dependency::factory()->create(['campus_id' => $maicao->id]);
        $depRiohacha = Dependency::factory()->create(['campus_id' => $riohacha->id]);

        Event::factory()->create(['user_id' => $admin->id, 'campus_id' => $maicao->id, 'dependency_id' => $depMaicao->id]);
        Event::factory()->create(['user_id' => $admin->id, 'campus_id' => $riohacha->id, 'dependency_id' => $depRiohacha->id]);

        $response = $this->actingAs($admin)
            ->getJson('/api/statistics/total-events?campusIds[]='.$riohacha->id.'&dependencyIds[]='.$depRiohacha->id)
            ->assertOk();
        $this->assertSame(1, $response->json());

        $this->actingAs($admin)
            ->getJson('/api/statistics/filter-options')
            ->assertOk()
            ->assertJsonPath('showCampuses', false)
            ->assertJsonPath('dependencies.'.$depMaicao->id, $depMaicao->name)
            ->assertJsonMissingPath('dependencies.'.$depRiohacha->id);
    }

    public function test_usuario_combina_eventos_propios_y_dependencias_autorizadas(): void
    {
        [$maicao] = $this->campuses();
        $user = User::factory()->create(['role' => User::ROLE_USER, 'campus_id' => $maicao->id]);
        $creator = User::factory()->create(['role' => User::ROLE_ADMIN, 'campus_id' => $maicao->id]);
        $allowedDependency = Dependency::factory()->create(['campus_id' => $maicao->id]);
        $otherDependency = Dependency::factory()->create(['campus_id' => $maicao->id]);
        $user->dependencies()->attach($allowedDependency);

        Event::factory()->create(['user_id' => $user->id, 'campus_id' => $maicao->id]);
        Event::factory()->create(['user_id' => $creator->id, 'campus_id' => $maicao->id, 'dependency_id' => $allowedDependency->id]);
        Event::factory()->create(['user_id' => $creator->id, 'campus_id' => $maicao->id, 'dependency_id' => $otherDependency->id]);

        $response = $this->actingAs($user)
            ->getJson('/api/statistics/total-events')
            ->assertOk();
        $this->assertSame(2, $response->json());

        $response = $this->actingAs($user)
            ->getJson('/api/statistics/total-events?onlyOwnEvents=1')
            ->assertOk();
        $this->assertSame(1, $response->json());

        $this->actingAs($user)
            ->getJson('/api/statistics/filter-options')
            ->assertOk()
            ->assertJsonPath('dependencies.'.$allowedDependency->id, $allowedDependency->name)
            ->assertJsonMissingPath('dependencies.'.$otherDependency->id);
    }

    private function campuses(): array
    {
        return [
            Campus::create(['name' => 'Maicao']),
            Campus::create(['name' => 'Riohacha']),
        ];
    }
}
