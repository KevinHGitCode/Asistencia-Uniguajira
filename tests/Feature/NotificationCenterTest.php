<?php

namespace Tests\Feature;

use App\Livewire\NotificationBell;
use App\Models\Event;
use App\Models\User;
use App\Notifications\EventEndingSoon;
use App\Notifications\EventStartingSoon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    private function notify(User $user, array $data): void
    {
        DB::table('notifications')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\Test',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode($data),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_la_campana_muestra_el_conteo_y_las_notificaciones(): void
    {
        $user = User::factory()->create();
        $this->notify($user, ['titulo' => 'Hola', 'mensaje' => 'Mensaje', 'url' => '/x', 'icono' => 'bell']);

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->assertSet('unreadCount', 1)
            ->call('loadItems')
            ->assertSet('loaded', true)
            ->assertSee('Hola')
            ->assertSee('Mensaje');
    }

    public function test_marcar_todas_como_leidas_pone_el_conteo_en_cero(): void
    {
        $user = User::factory()->create();
        $this->notify($user, ['titulo' => 'A', 'mensaje' => 'a']);
        $this->notify($user, ['titulo' => 'B', 'mensaje' => 'b']);

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->assertSet('unreadCount', 2)
            ->call('markAllAsRead')
            ->assertSet('unreadCount', 0);

        $this->assertSame(0, $user->unreadNotifications()->count());
    }

    public function test_marcar_una_como_leida_redirige_a_su_url(): void
    {
        $user = User::factory()->create();
        $this->notify($user, ['titulo' => 'A', 'mensaje' => 'a', 'url' => 'http://localhost/destino']);
        $id = DB::table('notifications')->where('notifiable_id', $user->id)->value('id');

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->call('markAsRead', $id)
            ->assertRedirect('http://localhost/destino');

        $this->assertSame(0, $user->unreadNotifications()->count());
    }

    public function test_el_escaneo_crea_aviso_de_evento_proximo_y_es_idempotente(): void
    {
        $user = User::factory()->create();
        Event::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => now()->addMinutes(30)->format('H:i:s'),
            'end_time' => now()->addHours(3)->format('H:i:s'),
            'ended_at' => null,
            'reminder_notified_at' => null,
            'ending_notified_at' => null,
        ]);

        $this->artisan('notifications:escanear-eventos')->assertSuccessful();
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'type' => EventStartingSoon::class,
        ]);
        $this->assertDatabaseCount('notifications', 1);

        // Segundo escaneo: no debe duplicar (idempotente).
        $this->artisan('notifications:escanear-eventos')->assertSuccessful();
        $this->assertDatabaseCount('notifications', 1);
    }

    public function test_el_escaneo_crea_aviso_de_evento_por_finalizar(): void
    {
        $user = User::factory()->create();
        Event::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'start_time' => now()->subHours(2)->format('H:i:s'),
            'end_time' => now()->addMinutes(20)->format('H:i:s'),
            'ended_at' => null,
            'reminder_notified_at' => now(), // ya avisado el inicio
            'ending_notified_at' => null,
        ]);

        $this->artisan('notifications:escanear-eventos')->assertSuccessful();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'type' => EventEndingSoon::class,
        ]);
    }

    public function test_la_limpieza_purga_notificaciones_leidas_antiguas(): void
    {
        $user = User::factory()->create();
        // Leída y vieja → se purga.
        DB::table('notifications')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\Test',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode(['titulo' => 'vieja']),
            'read_at' => now()->subDays(60),
            'created_at' => now()->subDays(61),
            'updated_at' => now()->subDays(60),
        ]);
        // No leída y vieja → se conserva.
        $this->notify($user, ['titulo' => 'nueva']);

        $this->artisan('notifications:limpiar')->assertSuccessful();

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseMissing('notifications', ['read_at' => null, 'data' => json_encode(['titulo' => 'vieja'])]);
    }
}
