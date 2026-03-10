@props(['items' => []])

{{--
    Componente de navegación breadcrumb.

    Uso:
        <x-breadcrumb :items="[
            ['label' => 'Usuarios',   'route' => 'users.index'],
            ['label' => 'Información'],          // último ítem = página actual (sin enlace)
        ]" />

    Props por ítem:
        label  (string) — texto visible
        route  (string) — nombre de ruta Laravel; si se omite el ítem se trata como página actual
        params (array)  — parámetros opcionales para route()

    Se pueden pasar atributos HTML adicionales (class, id…) y se fusionan con las clases base.
--}}

<nav {{ $attributes->class('flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400') }}
     aria-label="Breadcrumb">

    @foreach($items as $item)

        @unless($loop->first)
            <flux:icon.chevron-right class="size-3 shrink-0" />
        @endunless

        @if(isset($item['route']))
            <a href="{{ route($item['route'], $item['params'] ?? []) }}"
               class="hover:text-gray-700 dark:hover:text-gray-200 transition-colors"
               wire:navigate>{{ $item['label'] }}</a>
        @else
            <span class="text-gray-700 dark:text-gray-200 font-medium">{{ $item['label'] }}</span>
        @endif

    @endforeach

</nav>
