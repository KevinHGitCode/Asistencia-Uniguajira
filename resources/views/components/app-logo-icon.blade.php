@props([
    'class' => '',
])

{{-- Logo AURA a tamaño grande para pantallas de login/registro.
     Se muestra la versión blanca sobre fondos oscuros y la versión
     negra sobre fondos claros. --}}
<img
    src="{{ asset('images/aura_blanco.png') }}"
    alt="AURA"
    class="aura-logo-large hidden dark:block {{ $class }}"
>
<img
    src="{{ asset('images/aura_negro.png') }}"
    alt="AURA"
    class="aura-logo-large block dark:hidden {{ $class }}"
>

<style>
    .aura-logo-large {
        width: auto;
        height: auto;
        object-fit: contain;
        /* Móvil por defecto */
        max-width: 240px;
        max-height: 130px;
    }

    /* Tablet */
    @media (min-width: 641px) {
        .aura-logo-large {
            max-width: 360px;
            max-height: 200px;
        }
    }

    /* Escritorio */
    @media (min-width: 1024px) {
        .aura-logo-large {
            max-width: 520px;
            max-height: 280px;
        }
    }

    /* Monitores grandes */
    @media (min-width: 1536px) {
        .aura-logo-large {
            max-width: 640px;
            max-height: 340px;
        }
    }
</style>
