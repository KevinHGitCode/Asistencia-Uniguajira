<?php

namespace Tests\Feature\Livewire;

use App\Livewire\User\OnlineCount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class UserOnlineCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_contador_muestra_los_usuarios_en_linea(): void
    {
        $user = User::factory()->create();
        DB::table('sessions')->insert([
            'id'            => 'sess-online',
            'user_id'      => $user->id,
            'payload'      => 'x',
            'last_activity' => now()->getTimestamp(),
        ]);

        Livewire::test(OnlineCount::class)
            ->assertSee('1')
            ->assertSee('usuario en línea');
    }

    public function test_la_pagina_de_informacion_muestra_el_panel_de_actividad(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPERADMIN, 'campus_id' => null]);
        $target = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('users.information', $target->id))
            ->assertOk()
            ->assertSee('Actividad y uso');
    }

    public function test_la_pagina_de_usuarios_renderiza_con_los_cambios_de_diseno(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPERADMIN, 'campus_id' => null]);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Referencias de color')
            ->assertSee('Buscar usuario…');
    }
}
