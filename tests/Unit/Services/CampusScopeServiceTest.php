<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\CampusScopeService;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Tests\TestCase;

class CampusScopeServiceTest extends TestCase
{
    private CampusScopeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new CampusScopeService;
        session()->forget(CampusScopeService::SESSION_KEY);
    }

    public function test_superadmin_sin_filtro_no_restringe_query(): void
    {
        $query = $this->newQuery();
        $user = new User([
            'role' => User::ROLE_SUPERADMIN,
            'campus_id' => null,
        ]);

        $this->service->applyToQuery($query, $user);

        $this->assertEmpty($query->wheres ?? []);
        $this->assertNull($this->service->activeCampusId($user));
    }

    public function test_superadmin_con_campus_seleccionado_restringe_query(): void
    {
        session([CampusScopeService::SESSION_KEY => 2]);

        $query = $this->newQuery();
        $user = new User([
            'role' => User::ROLE_SUPERADMIN,
            'campus_id' => null,
        ]);

        $this->service->applyToQuery($query, $user);

        $this->assertSame(2, $this->service->activeCampusId($user));
        $this->assertWhereEquals($query, 'campus_id', 2);
    }

    public function test_admin_restringe_a_su_sede(): void
    {
        $query = $this->newQuery();
        $user = new User([
            'role' => User::ROLE_ADMIN,
            'campus_id' => 3,
        ]);

        $this->service->applyToQuery($query, $user);

        $this->assertSame(3, $this->service->activeCampusId($user));
        $this->assertWhereEquals($query, 'campus_id', 3);
    }

    public function test_user_restringe_a_su_sede(): void
    {
        $query = $this->newQuery();
        $user = new User([
            'role' => User::ROLE_USER,
            'campus_id' => 4,
        ]);

        $this->service->applyToQuery($query, $user);

        $this->assertSame(4, $this->service->activeCampusId($user));
        $this->assertWhereEquals($query, 'campus_id', 4);
    }

    public function test_valida_acceso_a_recurso_con_campus_id(): void
    {
        $admin = new User([
            'role' => User::ROLE_ADMIN,
            'campus_id' => 1,
        ]);

        $this->assertTrue($this->service->canAccessResource($admin, (object) ['campus_id' => 1]));
        $this->assertFalse($this->service->canAccessResource($admin, (object) ['campus_id' => 2]));
    }

    private function newQuery(): Builder
    {
        $connection = $this->createMock(Connection::class);

        return new Builder($connection, new Grammar($connection), new Processor);
    }

    private function assertWhereEquals(Builder $query, string $column, int $value): void
    {
        $this->assertCount(1, $query->wheres);
        $this->assertSame('Basic', $query->wheres[0]['type']);
        $this->assertSame($column, $query->wheres[0]['column']);
        $this->assertSame('=', $query->wheres[0]['operator']);
        $this->assertSame($value, $query->wheres[0]['value']);
    }
}
