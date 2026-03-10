<x-layouts.app :title="__('Compara Eventos')">

    <div class="flex h-full w-full flex-1 flex-col gap-4 p-1 sm:p-4 md:p-6">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Estadísticas', 'route' => 'statistics'],
            ['label' => 'Compara Eventos'],
        ]" />

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-1">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/30 shrink-0">
                <flux:icon.arrows-right-left class="size-6 text-amber-500 dark:text-amber-400" />
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white leading-tight">
                    Compara Eventos
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Selecciona eventos del período para comparar asistencias y perfil demográfico
                </p>
            </div>
        </div>

        {{-- Punto de montaje React --}}
        <div id="statistics-react-root" data-module="compara-eventos"></div>

        @vite(['resources/js/statistics/index.jsx'])

    </div>

</x-layouts.app>
