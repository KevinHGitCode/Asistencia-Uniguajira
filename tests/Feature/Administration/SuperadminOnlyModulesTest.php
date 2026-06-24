<?php

namespace Tests\Feature\Administration;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperadminOnlyModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_no_puede_acceder_a_formatos_ni_registros_de_actividad(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('formats.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('activity-logs.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('formats.store'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('activity-logs.clear'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('administracion.index'))
            ->assertDontSee(route('formats.index'), false)
            ->assertDontSee(route('activity-logs.index'), false);
    }

    public function test_superadmin_puede_acceder_a_formatos_y_registros_de_actividad(): void
    {
        $superadmin = User::factory()->create([
            'role' => User::ROLE_SUPERADMIN,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($superadmin)
            ->get(route('formats.index'))
            ->assertOk();

        $this->actingAs($superadmin)
            ->get(route('activity-logs.index'))
            ->assertOk();

        $this->actingAs($superadmin)
            ->get(route('administracion.index'))
            ->assertSee(route('formats.index'), false)
            ->assertSee(route('activity-logs.index'), false);
    }
}
