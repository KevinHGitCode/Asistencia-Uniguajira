<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\UserActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserActivityServiceTest extends TestCase
{
    use RefreshDatabase;

    private function insertSession(User $user, int $lastActivity): void
    {
        DB::table('sessions')->insert([
            'id'            => 'sess-'.$user->id.'-'.$lastActivity,
            'user_id'      => $user->id,
            'ip_address'   => '127.0.0.1',
            'user_agent'   => 'test',
            'payload'      => 'x',
            'last_activity' => $lastActivity,
        ]);
    }

    public function test_online_user_ids_detecta_solo_sesiones_recientes(): void
    {
        $online = User::factory()->create();
        $offline = User::factory()->create();

        $this->insertSession($online, now()->getTimestamp());
        $this->insertSession($offline, now()->subMinutes(20)->getTimestamp());

        $ids = app(UserActivityService::class)->onlineUserIds();

        $this->assertContains($online->id, $ids);
        $this->assertNotContains($offline->id, $ids);
    }

    public function test_usage_for_cuenta_logins_tiempo_y_acciones_por_modulo(): void
    {
        $user = User::factory()->create();

        ActivityLog::create(['user_id' => $user->id, 'module' => 'sesion', 'action' => 'login',  'description' => 'x', 'created_at' => now()->subMinutes(30)]);
        ActivityLog::create(['user_id' => $user->id, 'module' => 'sesion', 'action' => 'logout', 'description' => 'x', 'created_at' => now()->subMinutes(20)]);
        ActivityLog::create(['user_id' => $user->id, 'module' => 'eventos', 'action' => 'crear', 'description' => 'x', 'created_at' => now()->subMinutes(25)]);

        $usage = app(UserActivityService::class)->usageFor($user);

        $this->assertSame(1, $usage['login_count']);
        $this->assertSame(600, $usage['usage_seconds']); // 10 minutos entre login y logout
        $this->assertEqualsCanonicalizing(
            ['sesion', 'eventos'],
            collect($usage['actions_by_module'])->pluck('module')->all(),
        );
    }
}
