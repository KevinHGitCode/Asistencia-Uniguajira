<x-layouts.app :title="__('Estadísticas por Asistencias')">

    <div class="flex h-full w-full flex-1 flex-col gap-4 p-1 sm:p-4 md:p-6">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Estadísticas', 'route' => 'statistics'],
            ['label' => 'Por Asistencias'],
        ]" />

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-1">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/50 shrink-0">
                <flux:icon.chart-bar class="size-6 text-blue-500 dark:text-blue-400" />
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white leading-tight">
                    Por Asistencias
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Una persona puede contar varias veces según cuántas asistencias tenga registradas
                </p>
            </div>
        </div>

        {{-- Punto de montaje React --}}
        <div id="statistics-react-root" data-module="asistencias"></div>

        @vite(['resources/js/statistics/index.jsx'])

    </div>

</x-layouts.app>
