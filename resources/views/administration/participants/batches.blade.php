<x-layouts.app :title="__('Historial de importaciones')">

<div class="flex min-h-full w-full flex-1 flex-col gap-6 p-1 pb-8 sm:p-4 sm:pb-10 md:p-6 md:pb-12">

    {{-- Header --}}
    <div>
        <x-breadcrumb class="mb-1" :items="[
            ['label' => 'Administración', 'route' => 'administracion.index'],
            ['label' => 'Participantes', 'route' => 'participants-import.index'],
            ['label' => 'Importaciones'],
        ]" />
        <h1 class="flex items-center gap-2 text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
            <flux:icon name="rectangle-stack" class="size-8 text-[#3b82f6]" />
            <span>Historial de importaciones</span>
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Lotes de carga masiva. Puedes volver a un lote ya procesado para revisarlo o descargar sus filas omitidas.
        </p>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 text-sm">
            <flux:icon.check-circle class="size-5 shrink-0" />
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
            <flux:icon.x-circle class="size-5 shrink-0" />
            {{ session('error') }}
        </div>
    @endif

    {{-- Tabla --}}
    <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-zinc-700">
        <table class="min-w-[900px] w-full divide-y divide-neutral-200 dark:divide-zinc-700 text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Lote</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Archivo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Usuario</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Estado</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Nuevos</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Actualizan</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Omitidos</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Fecha</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-neutral-100 dark:divide-zinc-800">
                @forelse($batches as $b)
                    @php
                        [$badge, $label] = match($b->status) {
                            'aprobado'  => ['bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300', 'Aprobado'],
                            'rechazado' => ['bg-gray-200 text-gray-600 dark:bg-zinc-700 dark:text-gray-300', 'Rechazado'],
                            default     => ['bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300', 'En revisión'],
                        };
                    @endphp
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                        <td class="px-4 py-3 font-mono text-xs text-gray-600 dark:text-zinc-400">#{{ $b->id }}</td>
                        <td class="px-4 py-3 text-gray-800 dark:text-gray-200 truncate max-w-[16rem]" title="{{ $b->original_filename }}">{{ $b->original_filename }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $b->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ $label }}</span>
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums text-emerald-700 dark:text-emerald-400">{{ number_format($b->new_count) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums text-blue-700 dark:text-blue-400">{{ number_format($b->update_count) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums text-amber-700 dark:text-amber-400">{{ number_format($b->skipped_count) }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-zinc-400 whitespace-nowrap">{{ $b->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('participants-import.review', $b) }}"
                                   class="inline-flex items-center gap-1 rounded-lg border border-neutral-200 dark:border-zinc-700 px-2.5 py-1 text-xs font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors">
                                    {{ $b->isPending() ? 'Revisar' : 'Ver' }}
                                </a>
                                @if($b->skipped_count > 0)
                                    <a href="{{ route('participants-import.batch-skipped', $b) }}"
                                       class="inline-flex items-center gap-1 rounded-lg border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 px-2.5 py-1 text-xs font-medium text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors"
                                       title="Descargar omitidos">
                                        <flux:icon.arrow-down-tray class="size-3.5" />
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-sm text-gray-400 dark:text-zinc-500">
                            Aún no hay importaciones registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($batches->hasPages())
        <div>{{ $batches->links() }}</div>
    @endif

</div>

</x-layouts.app>
