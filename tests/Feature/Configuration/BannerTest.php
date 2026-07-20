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

    public function test_pagina_publica_muestra_banner_vigente_y_cuenta_impresion(): void
    {
        $banner = $this->bannerConImagen();
        $event = Event::factory()->create(['link' => 'evento-con-banner']);

        $this->get(route('events.access', $event->link))
            ->assertOk()
            ->assertSee('Publicidad')
            ->assertSee(route('banners.image', $banner), false);

        $this->assertSame(1, $banner->fresh()->impressions);
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
    }

    public function test_imagen_se_sirve_desde_la_bd_con_su_mime(): void
    {
        $banner = $this->bannerConImagen();

        $this->get(route('banners.image', $banner))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }
}
