{{-- Logo AURA para el sidebar: centrado y con buen tamaño en escritorio. --}}
<div class="w-full flex items-center justify-center py-2">
    <img
        src="{{ asset('images/aura_blanco.png') }}"
        alt="AURA"
        class="aura-logo-sidebar hidden dark:block"
    >
    <img
        src="{{ asset('images/aura_negro.png') }}"
        alt="AURA"
        class="aura-logo-sidebar block dark:hidden"
    >
</div>

<style>
    .aura-logo-sidebar {
        width: auto;
        height: 96px;
        max-height: 96px;
        max-width: 100%;
        object-fit: contain;
    }

    /* Escritorio: logo más grande */
    @media (min-width: 1024px) {
        .aura-logo-sidebar {
            height: 130px;
            max-height: 130px;
        }
    }

    /* Monitores grandes */
    @media (min-width: 1536px) {
        .aura-logo-sidebar {
            height: 150px;
            max-height: 150px;
        }
    }
</style>
