<x-layouts.app :title="__('Statistics')">
    <h1 class="text-xl mb-4">Tipos de graficos</h1>
    {{-- <div id="main" style="width: 600px;height:400px;"></div> --}}

    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <!-- Fila con 3 gráficos -->
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div id="chart_bar" class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700"></div>
            <div id="chart_line" class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700"></div>
            <div id="chart_pie" class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700"></div>
        </div>

        <!-- Gráfico ancho abajo -->
        <div id="chart_radar" class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700"></div>
    </div>
</x-layouts.app>

@vite('resources/js/charts.js')

