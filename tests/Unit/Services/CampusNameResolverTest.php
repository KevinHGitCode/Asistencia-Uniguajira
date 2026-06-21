<?php

namespace Tests\Unit\Services;

use App\Models\Campus;
use App\Services\CampusNameResolver;
use Illuminate\Support\Collection;
use Tests\TestCase;

class CampusNameResolverTest extends TestCase
{
    public function test_infiere_campus_id_desde_el_sufijo_sin_modificar_el_nombre_visible(): void
    {
        $maicao = new Campus(['name' => 'Maicao']);
        $riohacha = new Campus(['name' => 'Riohacha']);
        $resolver = new CampusNameResolver;

        $name = 'Bienestar Universitario - RIOHACHA';
        $resolved = $resolver->resolve($name, new Collection([$maicao, $riohacha]));

        $this->assertSame($riohacha, $resolved);
        $this->assertSame('RIOHACHA', $resolver->suffix($name));
        $this->assertSame('Bienestar Universitario - RIOHACHA', $name);
    }

    public function test_no_confunde_un_guion_que_no_corresponde_a_una_sede(): void
    {
        $resolver = new CampusNameResolver;
        $campuses = new Collection([new Campus(['name' => 'Maicao'])]);

        $this->assertNull($resolver->resolve('Grupo de danza - Folclor', $campuses));
        $this->assertSame('Folclor', $resolver->suffix('Grupo de danza - Folclor'));
    }

    public function test_detecta_sede_mencionada_en_datos_historicos_sin_sufijo(): void
    {
        $maicao = new Campus(['name' => 'Maicao']);
        $riohacha = new Campus(['name' => 'Riohacha']);
        $resolver = new CampusNameResolver;

        $resolved = $resolver->resolveMentioned('Coordinacion academica Riohacha centro', new Collection([$maicao, $riohacha]));

        $this->assertSame($riohacha, $resolved);
    }
}
