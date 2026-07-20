<x-layouts.app :title="__('Reporte del banner')">

<div class="flex min-h-full w-full flex-1 flex-col gap-6 p-1 pb-8 sm:p-4 sm:pb-10 md:p-6 md:pb-12">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-breadcrumb class="mb-1" :items="[
                ['label' => 'Administración', 'route' => 'administracion.index'],
                ['label' => 'Banners', 'route' => 'banners.index'],
                ['label' => 'Reporte'],
            ]" />
            <h1 class="flex items-center gap-2 text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                <flux:icon name="chart-bar" class="size-16 text-[#f97316]" />
                <span>Reporte · {{ $banner->name }}</span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Del {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}
                al {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                @if($banner->target_url)
                    · enlaza a <span class="font-mono">{{ Str::limit($banner->target_url, 40) }}</span>
                @endif
            </p>
        </div>
        <a href="{{ route('banners.report-export', array_merge(['banner' => $banner], request()->only('dateFrom', 'dateTo'))) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-colors shadow-sm self-start sm:self-auto">
            <flux:icon.arrow-down-tray class="size-4" />
            Exportar a Excel
        </a>
    </div>

    {{-- Filtro de fechas --}}
    <form method="GET" action="{{ route('banners.report', $banner) }}"
          class="flex flex-wrap items-end gap-3 border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm px-4 sm:px-6 py-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Desde</label>
            <input type="date" name="dateFrom" value="{{ $dateFrom }}"
                class="px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Hasta</label>
            <input type="date" name="dateTo" value="{{ $dateTo }}"
                class="px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
        </div>
        <button type="submit"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#f97316] text-white text-sm font-medium transition-colors cursor-pointer">
            <flux:icon.funnel class="size-4" />
            Aplicar
        </button>
    </form>

    {{-- Totales --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Impresiones</p>
            <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totals['impressions'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">veces que el banner se mostró</p>
        </div>
        <div class="border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Clics</p>
            <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totals['clicks'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-400 mt-1">visitas enviadas al patrocinador</p>
        </div>
        <div class="border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">CTR</p>
            <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totals['ctr'], 2, ',', '.') }} %</p>
            <p class="text-xs text-gray-400 mt-1">clics ÷ impresiones</p>
        </div>
    </div>

    {{-- Tabla por día --}}
    <div class="border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Detalle por día</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-100 dark:border-zinc-800 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        <th class="px-4 sm:px-6 py-3 text-left font-medium">Fecha</th>
                        <th class="px-4 sm:px-6 py-3 text-center font-medium">Impresiones</th>
                        <th class="px-4 sm:px-6 py-3 text-center font-medium">Clics</th>
                        <th class="px-4 sm:px-6 py-3 text-center font-medium">CTR</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-zinc-800">
                    @forelse($days as $day)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <td class="px-4 sm:px-6 py-3 text-gray-900 dark:text-white">{{ $day->date->format('d/m/Y') }}</td>
                            <td class="px-4 sm:px-6 py-3 text-center font-mono text-xs text-gray-600 dark:text-gray-300">{{ number_format($day->impressions, 0, ',', '.') }}</td>
                            <td class="px-4 sm:px-6 py-3 text-center font-mono text-xs text-gray-600 dark:text-gray-300">{{ number_format($day->clicks, 0, ',', '.') }}</td>
                            <td class="px-4 sm:px-6 py-3 text-center font-mono text-xs text-gray-600 dark:text-gray-300">
                                {{ $day->impressions > 0 ? number_format($day->clicks / $day->impressions * 100, 2, ',', '.') : '0,00' }} %
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400 dark:text-zinc-500">
                                    <flux:icon name="chart-bar" class="size-12 opacity-30" />
                                    <p class="text-sm">Sin actividad registrada en este rango de fechas.</p>
                                    <p class="text-xs">Las impresiones se cuentan desde que el banner se muestra en la página pública del QR.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

</x-layouts.app>
