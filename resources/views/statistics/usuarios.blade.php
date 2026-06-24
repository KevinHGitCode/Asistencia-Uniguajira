<x-layouts.app :title="__('Estadísticas por Usuarios')">

    <div class="flex h-full w-full flex-1 flex-col gap-4 p-1 sm:p-4 md:p-6">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Estadísticas', 'route' => 'statistics'],
            ['label' => 'Por Usuarios'],
        ]" />

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-1">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-violet-50 dark:bg-violet-950/50 shrink-0">
                <flux:icon.user class="size-6 text-violet-500 dark:text-violet-400" />
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white leading-tight">
                    Por Usuarios
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Actividad y rendimiento de los usuarios que crean y gestionan eventos
                </p>
            </div>
        </div>

        {{-- Punto de montaje React --}}
        <div id="statistics-react-root" data-module="usuarios"></div>

        @vite(['resources/js/statistics/index.jsx'])

    </div>

</x-layouts.app>
