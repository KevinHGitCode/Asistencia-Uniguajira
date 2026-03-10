<x-layouts.app :title="__('Todos los Eventos')">

    {{-- Breadcrumb --}}
    <x-breadcrumb class="mb-4" :items="[
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Todos los Eventos'],
    ]" />

    {{-- Leyenda --}}
    <div class="relative flex w-full flex-1 flex-col gap-4 p-6 mb-4 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
        <div class="flex items-center justify-center gap-4 sm:gap-8 flex-wrap">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-sm bg-[#cc5e50]"></div>
                <span class="text-xs sm:text-sm text-black dark:text-white">Dependencias</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-sm bg-[#62a9b6]"></div>
                <span class="text-xs sm:text-sm text-black dark:text-white">Áreas</span>
            </div>
        </div>
    </div>

    {{-- Punto de montaje React --}}
    <div id="statistics-react-root" data-module="admin-eventos"></div>

    @vite(['resources/js/statistics/index.jsx'])

</x-layouts.app>