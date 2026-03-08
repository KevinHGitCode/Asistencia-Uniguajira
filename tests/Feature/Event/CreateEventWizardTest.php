<?php

namespace Tests\Feature\Event;

use App\Livewire\Event\CreateEventWizard;
use App\Models\Area;
use App\Models\Dependency;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tests del wizard de creación de eventos.
 *
 * Valida:
 *  - Navegación entre pasos (nextStep / prevStep)
 *  - Reglas de validación por paso
 *  - Flujo completo: 3 pasos → evento creado en BD
 *  - Seguridad: usuario no puede asignar dependencias ajenas
 */
class CreateEventWizardTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Dependency $dependency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dependency = Dependency::factory()->create(['name' => 'Bienestar Universitario']);
        $this->user = User::factory()->create(['role' => 'user']);
        $this->user->dependencies()->attach($this->dependency);
    }

    // ── Renderizado ──────────────────────────────────────────────────────────

    #[Test]
    public function wizard_se_renderiza_correctamente(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->assertStatus(200)
            ->assertSee('¿De qué trata el evento?');
    }

    #[Test]
    public function wizard_inicia_en_el_paso_uno(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->assertSet('step', 1);
    }

    // ── Paso 1: Identidad ────────────────────────────────────────────────────

    #[Test]
    public function paso_uno_requiere_titulo(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', '')
            ->call('nextStep')
            ->assertHasErrors(['title' => 'required'])
            ->assertSet('step', 1);
    }

    #[Test]
    public function paso_uno_titulo_no_puede_superar_255_caracteres(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', str_repeat('a', 256))
            ->call('nextStep')
            ->assertHasErrors(['title' => 'max'])
            ->assertSet('step', 1);
    }

    #[Test]
    public function paso_uno_descripcion_es_opcional(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', 'Mi evento')
            ->set('description', '')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 2);
    }

    #[Test]
    public function paso_uno_con_datos_validos_avanza_al_paso_dos(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', 'Día del amor y la amistad')
            ->set('description', 'Evento especial de bienestar')
            ->call('nextStep')
            ->assertHasNoErrors()
            ->assertSet('step', 2);
    }

    // ── Paso 2: Organización ─────────────────────────────────────────────────

    #[Test]
    public function paso_dos_es_completamente_opcional(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', 'Mi evento')
            ->call('nextStep')   // paso 1 → 2
            ->call('nextStep')   // paso 2 → 3 (sin ningún campo)
            ->assertHasNoErrors()
            ->assertSet('step', 3);
    }

    #[Test]
    public function paso_dos_ubicacion_no_puede_superar_255_caracteres(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', 'Mi evento')
            ->call('nextStep')
            ->set('location', str_repeat('x', 256))
            ->call('nextStep')
            ->assertHasErrors(['location' => 'max'])
            ->assertSet('step', 2);
    }

    #[Test]
    public function paso_dos_dependencia_invalida_no_pasa_la_validacion(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', 'Mi evento')
            ->call('nextStep')
            ->set('dependency_id', '99999')  // ID que no existe
            ->call('nextStep')
            ->assertHasErrors(['dependency_id' => 'exists'])
            ->assertSet('step', 2);
    }

    #[Test]
    public function seleccionar_dependencia_carga_sus_areas(): void
    {
        $area = Area::factory()->create(['dependency_id' => $this->dependency->id, 'name' => 'Área de Salud']);
        $this->actingAs($this->user);

        // Añadir segunda dependencia para que showDependencySelect sea true
        $dep2 = Dependency::factory()->create();
        $this->user->dependencies()->attach($dep2);

        Livewire::test(CreateEventWizard::class)
            ->set('title', 'Mi evento')
            ->call('nextStep')
            ->set('dependency_id', (string) $this->dependency->id)
            ->assertSet('areas', [['id' => $area->id, 'name' => $area->name]]);
    }

    #[Test]
    public function cambiar_dependencia_limpia_el_area_seleccionada(): void
    {
        $dep2 = Dependency::factory()->create();
        $this->user->dependencies()->attach($dep2);

        $area1 = Area::factory()->create(['dependency_id' => $this->dependency->id]);
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', 'Mi evento')
            ->call('nextStep')
            ->set('dependency_id', (string) $this->dependency->id)
            ->set('area_id', (string) $area1->id)
            ->set('dependency_id', (string) $dep2->id)   // cambiar dependencia
            ->assertSet('area_id', null);
    }

    // ── Paso 3: Fecha & Hora ─────────────────────────────────────────────────

    #[Test]
    public function paso_tres_requiere_fecha(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', 'Mi evento')
            ->call('nextStep')
            ->call('nextStep')
            ->set('date', '')
            ->call('save')
            ->assertHasErrors(['date' => 'required'])
            ->assertSet('step', 3);
    }

    #[Test]
    public function paso_tres_fecha_debe_ser_valida(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', 'Mi evento')
            ->call('nextStep')
            ->call('nextStep')
            ->set('date', 'no-es-una-fecha')
            ->call('save')
            ->assertHasErrors(['date']);
    }

    #[Test]
    public function paso_tres_requiere_hora_inicio(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', 'Mi evento')
            ->call('nextStep')
            ->call('nextStep')
            ->set('date', '2026-06-15')
            ->set('start_time', '')
            ->call('save')
            ->assertHasErrors(['start_time' => 'required'])
            ->assertSet('step', 3);
    }

    #[Test]
    public function hora_fin_sin_hora_inicio_es_valida(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', 'Mi evento')
            ->call('nextStep')
            ->call('nextStep')
            ->set('date', '2026-06-15')
            ->set('start_time', '')
            ->set('end_time', '14:00')
            ->call('save')
            ->assertHasNoErrors(['end_time']);
    }

    #[Test]
    public function hora_fin_anterior_a_hora_inicio_es_invalida(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', 'Mi evento')
            ->call('nextStep')
            ->call('nextStep')
            ->set('date', '2026-06-15')
            ->set('start_time', '14:00')
            ->set('end_time', '09:00')
            ->call('save')
            ->assertHasErrors(['end_time']);
    }

    #[Test]
    public function hora_fin_igual_a_hora_inicio_es_valida(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', 'Mi evento')
            ->call('nextStep')
            ->call('nextStep')
            ->set('date', '2026-06-15')
            ->set('start_time', '09:00')
            ->set('end_time', '09:00')
            ->call('save')
            ->assertHasNoErrors(['end_time'])
            ->assertRedirect(route('events.list'));
    }

    // ── Navegación ───────────────────────────────────────────────────────────

    #[Test]
    public function volver_atras_no_requiere_validacion(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', 'Mi evento')
            ->call('nextStep')   // → paso 2
            ->call('prevStep')   // → paso 1
            ->assertHasNoErrors()
            ->assertSet('step', 1);
    }

    #[Test]
    public function no_se_puede_retroceder_mas_alla_del_paso_uno(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->call('prevStep')
            ->assertSet('step', 1);  // nunca baja de 1
    }

    #[Test]
    public function los_datos_previos_se_conservan_al_volver_atras(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', 'Evento especial')
            ->set('description', 'Descripción importante')
            ->call('nextStep')   // → paso 2
            ->set('location', 'Auditorio principal')
            ->call('prevStep')   // → paso 1
            ->assertSet('title', 'Evento especial')
            ->assertSet('description', 'Descripción importante');
    }

    // ── Flujo completo ───────────────────────────────────────────────────────

    #[Test]
    public function flujo_completo_crea_el_evento_y_redirige(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', 'Día del amor y la amistad')
            ->set('description', 'Evento especial de bienestar universitario')
            ->call('nextStep')                          // → paso 2
            ->set('location', 'Auditorio principal')
            ->call('nextStep')                          // → paso 3
            ->set('date', '2026-06-15')
            ->set('start_time', '09:00')
            ->set('end_time', '12:00')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('events.list'));

        $this->assertDatabaseHas('events', [
            'title'    => 'Día del amor y la amistad',
            'location' => 'Auditorio principal',
            'date'     => '2026-06-15',
            'user_id'  => $this->user->id,
        ]);
    }

    #[Test]
    public function flujo_sin_campos_opcionales_crea_el_evento(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', 'Evento mínimo')
            ->call('nextStep')   // → paso 2
            ->call('nextStep')   // → paso 3
            ->set('date', '2026-07-20')
            ->set('start_time', '08:00')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('events.list'));

        $this->assertDatabaseHas('events', [
            'title'   => 'Evento mínimo',
            'date'    => '2026-07-20',
            'user_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function el_slug_del_evento_se_genera_automaticamente(): void
    {
        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', 'Evento con slug')
            ->call('nextStep')
            ->call('nextStep')
            ->set('date', '2026-08-10')
            ->set('start_time', '10:00')
            ->call('save');

        $event = Event::where('title', 'Evento con slug')->first();
        $this->assertNotNull($event);
        $this->assertNotEmpty($event->link);
    }

    // ── Seguridad ────────────────────────────────────────────────────────────

    #[Test]
    public function usuario_normal_no_puede_asignar_dependencia_ajena(): void
    {
        $otraDependencia = Dependency::factory()->create(['name' => 'Rectoría']);
        // $otraDependencia NO está asociada a $this->user

        $dep2 = Dependency::factory()->create();
        $this->user->dependencies()->attach($dep2); // para que showDependencySelect sea true

        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', 'Evento malicioso')
            ->call('nextStep')
            ->set('dependency_id', (string) $otraDependencia->id)
            ->call('nextStep')
            ->set('date', '2026-06-15')
            ->set('start_time', '10:00')
            ->call('save')
            ->assertHasErrors(['dependency_id']);

        $this->assertDatabaseMissing('events', ['title' => 'Evento malicioso']);
    }

    #[Test]
    public function area_de_otra_dependencia_es_rechazada_por_el_servicio(): void
    {
        $otraDep  = Dependency::factory()->create();
        $areaAjena = Area::factory()->create(['dependency_id' => $otraDep->id]);

        $dep2 = Dependency::factory()->create();
        $this->user->dependencies()->attach($dep2);

        $this->actingAs($this->user);

        Livewire::test(CreateEventWizard::class)
            ->set('title', 'Evento con área ajena')
            ->call('nextStep')
            ->set('dependency_id', (string) $this->dependency->id)
            ->set('area_id', (string) $areaAjena->id)
            ->call('nextStep')
            ->set('date', '2026-06-15')
            ->set('start_time', '10:00')
            ->call('save')
            ->assertHasErrors(['area_id']);

        $this->assertDatabaseMissing('events', ['title' => 'Evento con área ajena']);
    }

    // ── Admin ────────────────────────────────────────────────────────────────

    #[Test]
    public function admin_puede_crear_evento_sin_dependencia(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        Livewire::test(CreateEventWizard::class)
            ->assertSet('isAdmin', true)
            ->set('title', 'Evento admin')
            ->call('nextStep')
            ->set('dependency_id', '')  // sin dependencia
            ->call('nextStep')
            ->set('date', '2026-09-01')
            ->set('start_time', '09:00')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('events.list'));

        $this->assertDatabaseHas('events', [
            'title'         => 'Evento admin',
            'dependency_id' => null,
        ]);
    }
}
