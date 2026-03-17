<?php

namespace Tests\Feature;

use App\Models\Affiliation;
use App\Models\Area;
use App\Models\Attendance;
use App\Models\Dependency;
use App\Models\Event;
use App\Models\Format;
use App\Models\Participant;
use App\Models\ParticipantType;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class WebRoutesPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private const DEFAULT_MAX_MS = 500;
    private const HEAVY_MAX_MS = 1500;

    private User $admin;
    private array $paramValues = [];

    protected function setUp(): void
    {
        parent::setUp();

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

        $format = Format::create([
            'name' => 'General',
            'slug' => 'general',
        ]);

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

        $this->paramValues = [
            'id' => 1,
            'user' => $this->admin->id,
            'dependency' => $dependency->id,
            'area' => $area->id,
            'format' => $format->id,
            'program' => $program->id,
            'participantType' => $participantType->id,
            'participant' => $participant->id,
            'attendanceId' => $attendance->id,
            'slug' => $event->link,
            'formatSlug' => 'general',
            'token' => 'test-token',
        ];
    }

    public function test_all_web_get_routes_respond_ok_and_fast(): void
    {
        $this->actingAs($this->admin);

        $routes = collect(Route::getRoutes())->filter(function ($route) {
            if (! in_array('GET', $route->methods(), true)) {
                return false;
            }

            $uri = $route->uri();
            if (str_starts_with($uri, 'api/')) {
                return false;
            }

            if (in_array('signed', $route->gatherMiddleware(), true)) {
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

            $maxMs = $this->maxMsFor($route->uri());
            [$res, $ms] = $this->timedGet($url);

            $this->assertTrue(
                $res->isOk() || $res->isRedirection() || $res->getStatusCode() === 204,
                "{$route->uri()} devolvió {$res->getStatusCode()}"
            );
            $this->assertLessThan(
                $maxMs,
                $ms,
                "{$route->uri()} tardó {$ms}ms (máx {$maxMs}ms)"
            );

            $tested++;
        }

        $this->assertGreaterThan(0, $tested, 'No se probaron rutas web.');
    }

    private function timedGet(string $url): array
    {
        $this->get($url);

        $start = microtime(true);
        $response = $this->get($url);
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
        if (str_contains($uri, 'descargar-asistencia')) {
            return true;
        }

        if (str_starts_with($uri, 'flux/')) {
            return true;
        }

        return (bool) preg_match('/\.(css|js|map|png|jpe?g|gif|svg|ico|woff2?|ttf)$/i', $uri);
    }

    private function maxMsFor(string $uri): int
    {
        if (str_contains($uri, 'download') || str_contains($uri, 'descargar')) {
            return self::HEAVY_MAX_MS;
        }

        return self::DEFAULT_MAX_MS;
    }
}
