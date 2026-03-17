<?php

namespace Tests\Feature;

use App\Models\Affiliation;
use App\Models\Area;
use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\Dependency;
use App\Models\Event;
use App\Models\Participant;
use App\Models\ParticipantType;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiRoutesPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private const MAX_MS = 400;

    private User $admin;
    private array $paramValues = [];
    private string $dbDriver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbDriver = DB::getDriverName();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $dependency = Dependency::factory()->create();
        $area = Area::factory()->create(['dependency_id' => $dependency->id]);

        $program = Program::create([
            'name' => 'Ingenieria',
            'campus' => 'Riohacha',
            'program_type' => 'Pregrado',
        ]);

        $participantType = ParticipantType::create(['name' => 'Estudiante']);
        $affiliation = Affiliation::create(['name' => 'Planta']);

        $participant = Participant::create([
            'document' => 'P-0001',
            'first_name' => 'Ana',
            'last_name' => 'Perez',
            'email' => 'ana.perez@example.com',
        ]);

        $participant->programs()->syncWithoutDetaching([$program->id]);
        $participant->types()->syncWithoutDetaching([$participantType->id]);
        $participant->affiliations()->syncWithoutDetaching([$affiliation->id]);

        $event = Event::create([
            'title' => 'Evento Demo',
            'description' => 'Descripcion',
            'date' => now()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'location' => 'Riohacha',
            'link' => 'evento-demo',
            'user_id' => $this->admin->id,
            'dependency_id' => $dependency->id,
            'area_id' => $area->id,
        ]);

        $attendance = Attendance::create([
            'event_id' => $event->id,
            'participant_id' => $participant->id,
        ]);

        AttendanceDetail::create([
            'attendance_id' => $attendance->id,
            'gender' => 'Femenino',
            'priority_group' => 'Ninguno',
            'program_id' => $program->id,
            'participant_type_id' => $participantType->id,
        ]);

        $this->paramValues = [
            'date' => $event->date,
            'event' => $event->id,
            'user_id' => $this->admin->id,
            'program_id' => $program->id,
        ];
    }

    public function test_all_api_get_routes_respond_ok_and_fast(): void
    {
        $this->actingAs($this->admin);

        $routes = collect(Route::getRoutes())->filter(function ($route) {
            if (! in_array('GET', $route->methods(), true)) {
                return false;
            }

            $uri = $route->uri();
            if (! str_starts_with($uri, 'api/')) {
                return false;
            }

            if (in_array('auth:sanctum', $route->gatherMiddleware(), true)) {
                return false;
            }

            if ($this->shouldSkipUri($uri)) {
                return false;
            }

            return true;
        })->values();

        $tested = 0;

        foreach ($routes as $route) {
            $url = $this->buildUrl($route->uri());
            if (! $url) {
                continue;
            }

            [$res, $ms] = $this->timedGetJson($url);

            $this->assertTrue(
                $res->isOk() || $res->getStatusCode() === 204,
                "{$route->uri()} devolvió {$res->getStatusCode()}"
            );
            $this->assertLessThan(
                self::MAX_MS,
                $ms,
                "{$route->uri()} tardó {$ms}ms (máx " . self::MAX_MS . "ms)"
            );

            if ($res->isOk()) {
                $contentType = (string) $res->headers->get('content-type', '');
                $this->assertTrue(
                    str_contains($contentType, 'application/json'),
                    "{$route->uri()} no devolvió JSON (content-type: {$contentType})"
                );
            }

            $tested++;
        }

        $this->assertGreaterThan(0, $tested, 'No se probaron rutas API.');
    }

    private function timedGetJson(string $url): array
    {
        $this->getJson($url);

        $start = microtime(true);
        $response = $this->getJson($url);
        $ms = (microtime(true) - $start) * 1000;

        return [$response, (int) round($ms)];
    }

    private function buildUrl(string $uri): ?string
    {
        $resolved = $uri;

        if (! preg_match_all('/\{([^}]+)\}/', $uri, $matches)) {
            return '/' . ltrim($resolved, '/');
        }

        foreach ($matches[1] as $raw) {
            $optional = str_ends_with($raw, '?');
            $name = rtrim($raw, '?');

            if (! array_key_exists($name, $this->paramValues)) {
                if ($optional) {
                    $resolved = str_replace('{' . $raw . '}', '', $resolved);
                    continue;
                }
                return null;
            }

            $resolved = str_replace('{' . $raw . '}', (string) $this->paramValues[$name], $resolved);
        }

        $resolved = preg_replace('#//+#', '/', $resolved);
        return '/' . trim($resolved, '/');
    }

    private function shouldSkipUri(string $uri): bool
    {
        // MySQL-only DATE_FORMAT() en esta ruta (falla en SQLite de tests)
        if ($this->dbDriver === 'sqlite' && $uri === 'api/mis-eventos-json') {
            return true;
        }

        return false;
    }
}
