<x-layouts.app :title="__('Estadísticas por Participantes')">

    <div class="flex h-full w-full flex-1 flex-col gap-4 p-1 sm:p-4 md:p-6">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('statistics') }}"
               class="hover:text-gray-700 dark:hover:text-gray-200 transition-colors"
               wire:navigate>
                Estadísticas
            </a>
            <flux:icon.chevron-right class="size-4 shrink-0" />
            <span class="text-gray-700 dark:text-gray-200 font-medium">Por Participantes</span>
        </nav>

        {{-- Header --}}
        <div class="flex items-center gap-3 mb-1">
            <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 shrink-0">
                <flux:icon.users class="size-6 text-emerald-500 dark:text-emerald-400" />
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white leading-tight">
                    Por Participantes
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Cada persona cuenta una sola vez, independientemente de cuántos eventos haya asistido
                </p>
            </div>
        </div>

        {{-- Punto de montaje React --}}
        <div id="statistics-react-root" data-module="participantes"></div>

        @vite(['resources/js/statistics/index.jsx'])

    </div>

</x-layouts.app>
