<?php

namespace Tests\Feature\Configuration;

use App\Models\Banner;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BannerTest extends TestCase
{
    use RefreshDatabase;

    private function superadmin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPERADMIN, 'campus_id' => null]);
    }

    private function bannerConImagen(array $attributes = []): Banner
    {
        $banner = Banner::create(array_merge([
            'name' => 'Patrocinador de prueba',
            'target_url' => 'https://ejemplo.com/promo',
            'active' => true,
        ], $attributes));

        $banner->storeImage('bytes-de-imagen-falsos', 'image/png');

        return $banner;
    }

    public function test_superadmin_puede_crear_banner_con_imagen(): void
    {
        $this->actingAs($this->superadmin())
            ->post(route('banners.store'), [
                'name' => 'Librería El Saber',
                'target_url' => 'https://libreria.example',
                'image' => UploadedFile::fake()->image('banner.png', 600, 80),
                'active' => '1',
            ])
            ->assertRedirect(route('banners.index'));

        $banner = Banner::firstWhere('name', 'Librería El Saber');
        $this->assertNotNull($banner);
        $this->assertTrue($banner->active);
        $this->assertNotNull($banner->fileRecord);
        $this->assertSame('image/png', $banner->fileRecord->mime);
    }

    public function test_admin_normal_no_accede_a_la_administracion_de_banners(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get(route('banners.index'))->assertForbidden();
    }

    public function test_pagina_publica_muestra_banner_vigente_sin_contar_impresion(): void
    {
        $banner = $this->bannerConImagen();
        $event = Event::factory()->create(['link' => 'evento-con-banner']);

        $this->get(route('events.access', $event->link))
            ->assertOk()
            ->assertSee('Publicidad')
            ->assertSee(route('banners.image', $banner), false)
            ->assertSee(route('banners.impression', $banner), false);

        // La impresión ya no se cuenta al renderizar: la reporta el navegador
        // con sendBeacon solo si el banner se muestra (ADR-0030 fase 2).
        $this->assertSame(0, $banner->fresh()->impressions);
    }

    public function test_endpoint_de_impresion_suma_total_y_acumulado_diario(): void
    {
        $banner = $this->bannerConImagen();

        $this->post(route('banners.impression', $banner))->assertNoContent();
        $this->post(route('banners.impression', $banner))->assertNoContent();

        $this->assertSame(2, $banner->fresh()->impressions);
        $daily = $banner->dailyStats()->whereDate('date', now()->toDateString())->first();
        $this->assertSame(2, $daily->impressions);
    }

    public function test_pagina_publica_no_muestra_banners_inactivos_o_vencidos(): void
    {
        $this->bannerConImagen(['active' => false]);
        $this->bannerConImagen(['name' => 'Vencido', 'ends_at' => now()->subDay()->toDateString()]);
        $event = Event::factory()->create(['link' => 'evento-sin-banner']);

        $this->get(route('events.access', $event->link))
            ->assertOk()
            ->assertDontSee('Publicidad');

        $this->assertSame(0, Banner::sum('impressions'));
    }

    public function test_clic_incrementa_contador_y_redirige_al_enlace(): void
    {
        $banner = $this->bannerConImagen();

        $this->get(route('banners.click', $banner))
            ->assertRedirect('https://ejemplo.com/promo');

        $this->assertSame(1, $banner->fresh()->clicks);
        $this->assertSame(1, $banner->dailyStats()->first()->clicks);
    }

    public function test_reporte_muestra_totales_y_ctr_del_rango(): void
    {
        $banner = $this->bannerConImagen();
        $banner->dailyStats()->create(['date' => now()->subDays(2)->toDateString(), 'impressions' => 900, 'clicks' => 27]);
        $banner->dailyStats()->create(['date' => now()->subDay()->toDateString(), 'impressions' => 300, 'clicks' => 9]);
        // Fuera del rango consultado: no debe sumar.
        $banner->dailyStats()->create(['date' => now()->subDays(40)->toDateString(), 'impressions' => 5555, 'clicks' => 99]);

        $this->actingAs($this->superadmin())
            ->get(route('banners.report', $banner))
            ->assertOk()
            ->assertSee('1.200')      // impresiones del rango (últimos 30 días)
            ->assertSee('3,00')       // CTR: 36/1200 = 3 %
            ->assertDontSee('5.555'); // lo de hace 40 días queda fuera
    }

    public function test_reporte_se_exporta_a_excel(): void
    {
        $banner = $this->bannerConImagen(['name' => 'Patrocinador Excel']);
        $banner->dailyStats()->create(['date' => now()->toDateString(), 'impressions' => 10, 'clicks' => 1]);

        $this->actingAs($this->superadmin())
            ->get(route('banners.report-export', $banner))
            ->assertOk()
            ->assertDownload('reporte-banner-patrocinador-excel-'.now()->subDays(29)->toDateString().'-a-'.now()->toDateString().'.xlsx');
    }

    public function test_el_peso_se_guarda_y_la_seleccion_respeta_vigencia(): void
    {
        $this->actingAs($this->superadmin())
            ->post(route('banners.store'), [
                'name' => 'Banner pesado',
                'image' => UploadedFile::fake()->image('banner.png', 600, 80),
                'active' => '1',
                'weight' => 5,
            ])
            ->assertRedirect(route('banners.index'));

        $this->assertSame(5, Banner::firstWhere('name', 'Banner pesado')->weight);

        // pickVigente nunca devuelve banners inactivos, aunque pesen más.
        $this->bannerConImagen(['name' => 'Inactivo pesado', 'active' => false, 'weight' => 100]);
        for ($i = 0; $i < 10; $i++) {
            $this->assertSame('Banner pesado', Banner::pickVigente()->name);
        }
    }

    public function test_imagen_se_sirve_desde_la_bd_con_su_mime(): void
    {
        $banner = $this->bannerConImagen();

        $this->get(route('banners.image', $banner))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }
}
