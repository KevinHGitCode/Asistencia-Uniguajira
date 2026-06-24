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

    public function test_superadmin_filtra_usuarios_por_sede_seleccionada(): void
    {
        [$maicao, $riohacha] = $this->campuses();
        $superadmin = User::factory()->create(['role' => User::ROLE_SUPERADMIN, 'campus_id' => null]);
        $otherSuperadmin = User::factory()->create(['name' => 'Super Admin', 'role' => User::ROLE_SUPERADMIN, 'campus_id' => null]);
        $maicaoUser = User::factory()->create(['name' => 'Usuario Maicao', 'role' => User::ROLE_ADMIN, 'campus_id' => $maicao->id]);
        $riohachaUser = User::factory()->create(['name' => 'Usuario Riohacha', 'role' => User::ROLE_ADMIN, 'campus_id' => $riohacha->id]);

        Event::factory()->create(['user_id' => $maicaoUser->id, 'campus_id' => $maicao->id]);
        Event::factory()->create(['user_id' => $riohachaUser->id, 'campus_id' => $riohacha->id]);
        Event::factory()->create(['user_id' => $otherSuperadmin->id, 'campus_id' => $riohacha->id]);

        $this->actingAs($superadmin)
            ->getJson('/api/statistics/filter-options?campusIds[]='.$maicao->id)
            ->assertOk()
            ->assertJsonPath('showCampuses', true)
            ->assertJsonPath('users.'.$maicaoUser->id, 'Usuario Maicao')
            ->assertJsonMissingPath('users.'.$riohachaUser->id)
            ->assertJsonMissingPath('users.'.$otherSuperadmin->id);

        $this->actingAs($superadmin)
            ->getJson('/api/statistics/filter-options?campusIds[]='.$maicao->id.'&includeSuperadmins=1')
            ->assertOk()
            ->assertJsonPath('users.'.$maicaoUser->id, 'Usuario Maicao')
            ->assertJsonPath('users.'.$superadmin->id, $superadmin->name)
            ->assertJsonPath('users.'.$otherSuperadmin->id, 'Super Admin')
            ->assertJsonMissingPath('users.'.$riohachaUser->id);

        $this->actingAs($superadmin)
            ->getJson('/api/statistics/filter-options?campusIds[]='.$maicao->id.'&userIds[]='.$riohachaUser->id)
            ->assertOk()
            ->assertJsonPath('users.'.$maicaoUser->id, 'Usuario Maicao')
            ->assertJsonPath('users.'.$riohachaUser->id, 'Usuario Riohacha');

        $response = $this->actingAs($superadmin)
            ->getJson('/api/statistics/total-events?campusIds[]='.$maicao->id.'&userIds[]='.$maicaoUser->id)
            ->assertOk();
        $this->assertSame(1, $response->json());

        $response = $this->actingAs($superadmin)
            ->getJson('/api/statistics/total-events?campusIds[]='.$maicao->id.'&userIds[]='.$riohachaUser->id)
            ->assertOk();
        $this->assertSame(1, $response->json());

        $response = $this->actingAs($superadmin)
            ->getJson('/api/statistics/total-events?campusIds[]='.$riohacha->id.'&userIds[]='.$otherSuperadmin->id)
            ->assertOk();
        $this->assertSame(0, $response->json());

        $response = $this->actingAs($superadmin)
            ->getJson('/api/statistics/total-events?campusIds[]='.$riohacha->id.'&includeSuperadmins=1&userIds[]='.$otherSuperadmin->id)
            ->assertOk();
        $this->assertSame(1, $response->json());
    }

    public function test_admin_filtra_solo_usuarios_de_su_sede(): void
    {
        [$maicao, $riohacha] = $this->campuses();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'campus_id' => $maicao->id]);
        $superadmin = User::factory()->create(['role' => User::ROLE_SUPERADMIN, 'campus_id' => null]);
        $maicaoUser = User::factory()->create(['name' => 'Usuario Maicao', 'role' => User::ROLE_USER, 'campus_id' => $maicao->id]);
        $riohachaUser = User::factory()->create(['name' => 'Usuario Riohacha', 'role' => User::ROLE_USER, 'campus_id' => $riohacha->id]);

        Event::factory()->create(['user_id' => $maicaoUser->id, 'campus_id' => $maicao->id]);
        Event::factory()->create(['user_id' => $riohachaUser->id, 'campus_id' => $riohacha->id]);

        $this->actingAs($admin)
            ->getJson('/api/statistics/filter-options')
            ->assertOk()
            ->assertJsonPath('showCampuses', false)
            ->assertJsonPath('users.'.$admin->id, $admin->name)
            ->assertJsonPath('users.'.$maicaoUser->id, 'Usuario Maicao')
            ->assertJsonMissingPath('users.'.$riohachaUser->id);

        $this->actingAs($admin)
            ->getJson('/api/statistics/filter-options?includeSuperadmins=1')
            ->assertOk()
            ->assertJsonMissingPath('users.'.$superadmin->id)
            ->assertJsonMissingPath('users.'.$riohachaUser->id);

        $response = $this->actingAs($admin)
            ->getJson('/api/statistics/total-events?userIds[]='.$maicaoUser->id)
            ->assertOk();
        $this->assertSame(1, $response->json());

        $response = $this->actingAs($admin)
            ->getJson('/api/statistics/total-events?userIds[]='.$riohachaUser->id)
            ->assertOk();
        $this->assertSame(0, $response->json());
    }

    private function campuses(): array
    {
        return [
            Campus::create(['name' => 'Maicao']),
            Campus::create(['name' => 'Riohacha']),
        ];
    }
}
