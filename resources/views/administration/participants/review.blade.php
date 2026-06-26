<x-layouts.app :title="__('Revisar importación')">

<div class="flex min-h-full w-full flex-1 flex-col gap-6 p-1 pb-8 sm:p-4 sm:pb-10 md:p-6 md:pb-12">

    {{-- Header --}}
    <div>
        <x-breadcrumb class="mb-1" :items="[
            ['label' => 'Administración', 'route' => 'administracion.index'],
            ['label' => 'Participantes', 'route' => 'participants-import.index'],
            ['label' => 'Revisar importación'],
        ]" />
        <h1 class="flex items-center gap-2 text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
            <flux:icon name="clipboard-document-check" class="size-8 text-[#3b82f6]" />
            <span>Revisar importación</span>
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Archivo <strong class="text-gray-700 dark:text-gray-300">{{ $batch->original_filename }}</strong>
            · Lote #{{ $batch->id }}
            · {{ $batch->created_at->format('d/m/Y H:i') }}
        </p>
    </div>

    {{-- Flash error --}}
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
            <flux:icon.x-circle class="size-5 shrink-0" />
            {{ session('error') }}
        </div>
    @endif

    {{-- Estado del lote --}}
    @if($batch->status === 'procesando')
        {{-- Parseo en segundo plano (ADR-0004): poll al estado y recarga al terminar. --}}
        <div x-data="{
                poll: null,
                init() {
                    this.poll = setInterval(() => {
                        fetch('{{ route('participants-import.status', $batch) }}', { headers: { 'Accept': 'application/json' } })
                            .then(r => r.json())
                            .then(d => { if (d.status !== 'procesando') { clearInterval(this.poll); window.location.reload(); } })
                            .catch(() => {});
                    }, 2500);
                },
                destroy() { if (this.poll) clearInterval(this.poll); }
             }"
             class="flex items-start gap-3 px-4 py-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 text-sm">
            <svg class="size-5 shrink-0 animate-spin mt-0.5" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
            </svg>
            <div>
                <strong>Procesando el archivo…</strong>
                <p class="mt-0.5 text-blue-600/90 dark:text-blue-300/80">
                    Esto puede tardar con listas grandes. Puedes salir de esta página y seguir usando el sistema;
                    te avisaremos con una notificación cuando esté listo. Esta página se actualizará sola al terminar.
                </p>
            </div>
        </div>
    @elseif($batch->status === 'error')
        <div class="flex items-start gap-3 px-4 py-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
            <flux:icon.x-circle class="size-5 shrink-0 mt-0.5" />
            <div>
                <strong>No se pudo procesar el archivo.</strong>
                <p class="mt-0.5">{{ $batch->error_message ?? 'Ocurrió un error inesperado al procesar el archivo.' }}</p>
                <a href="{{ route('participants-import.index') }}" class="inline-flex items-center gap-1 mt-2 font-medium underline">
                    <flux:icon.arrow-left class="size-3.5" /> Volver e intentar de nuevo
                </a>
            </div>
        </div>
    @elseif($batch->status !== 'en_revision')
        <div class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gray-50 dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 text-gray-600 dark:text-gray-300 text-sm">
            <flux:icon.information-circle class="size-5 shrink-0" />
            Este lote está <strong class="mx-1">{{ $batch->status }}</strong>; ya no se puede volver a procesar.
        </div>
    @else
        <div class="flex items-start gap-3 px-4 py-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 text-sm">
            <flux:icon.shield-check class="size-5 shrink-0 mt-0.5" />
            <span><strong>Nada se ha guardado todavía.</strong> Revisa los registros clasificados abajo y, cuando estés
            seguro, <strong>aprueba</strong> para incluirlos en el sistema o <strong>rechaza</strong> para descartar el lote.</span>
        </div>
    @endif

    @if(! in_array($batch->status, ['procesando', 'error'], true))
    {{-- Contadores + filtros --}}
    @php
        $chips = [
            ['key' => null,        'label' => 'Todos',      'count' => $batch->total_rows,   'active' => 'ring-2 ring-gray-400'],
            ['key' => 'nuevo',     'label' => 'Nuevos',     'count' => $batch->new_count,    'active' => 'ring-2 ring-emerald-400'],
            ['key' => 'actualiza', 'label' => 'Actualizan', 'count' => $batch->update_count, 'active' => 'ring-2 ring-blue-400'],
            ['key' => 'omitido',   'label' => 'Omitidos',   'count' => $batch->skipped_count,'active' => 'ring-2 ring-amber-400'],
        ];
    @endphp
    <div class="flex flex-wrap items-center gap-2">
        @foreach($chips as $chip)
            <a href="{{ route('participants-import.review', ['batch' => $batch->id, 'estado' => $chip['key']]) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700 transition-colors {{ $estado === $chip['key'] ? $chip['active'] : '' }}">
                {{ $chip['label'] }}
                <span class="inline-flex items-center justify-center rounded-full bg-gray-100 dark:bg-zinc-700 px-2 text-xs font-bold">{{ number_format($chip['count']) }}</span>
            </a>
        @endforeach

        @if($batch->skipped_count > 0)
            <a href="{{ route('participants-import.batch-skipped', $batch) }}"
               class="ml-auto inline-flex items-center gap-1.5 rounded-lg border border-amber-300 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 px-3 py-1.5 text-xs font-medium text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors">
                <flux:icon.arrow-down-tray class="size-3.5" />
                Descargar omitidos
            </a>
        @endif
    </div>

    {{-- Tabla de registros en staging --}}
    <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-zinc-700">
        <table class="min-w-[760px] w-full divide-y divide-neutral-200 dark:divide-zinc-700 text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Documento</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Nombre</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Roles / Motivo</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-neutral-100 dark:divide-zinc-800">
                @forelse($rows as $row)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors align-top">
                        {{-- Estado --}}
                        <td class="px-4 py-3 whitespace-nowrap">
                            @php
                                $badge = match($row->status) {
                                    'nuevo'     => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
                                    'actualiza' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                    default     => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                                };
                                $label = match($row->status) {
                                    'nuevo'     => 'Nuevo',
                                    'actualiza' => 'Actualiza',
                                    default     => 'Omitido',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ $label }}</span>
                        </td>

                        {{-- Documento --}}
                        <td class="px-4 py-3 font-mono text-xs text-gray-600 dark:text-zinc-400 whitespace-nowrap">{{ $row->document ?? '—' }}</td>

                        {{-- Nombre --}}
                        <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ trim(($row->first_name ?? '') . ' ' . ($row->last_name ?? '')) ?: '—' }}</td>

                        {{-- Roles / Motivo --}}
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                            @if($row->status === 'omitido')
                                <span class="text-amber-600 dark:text-amber-400">{{ $row->error ?? 'Sin motivo' }}</span>
                            @elseif(!empty($row->roles))
                                <div class="flex flex-col gap-1">
                                    @foreach($row->roles as $r)
                                        <div class="flex flex-wrap items-center gap-1.5 text-xs">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-teal-50 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300">
                                                {{ $typeNames[$r['participant_type_id'] ?? null] ?? '¿Estamento?' }}
                                            </span>
                                            @if($r['program_id'] ?? null)
                                                <span class="text-gray-600 dark:text-gray-400">{{ $programNames[$r['program_id']] ?? '—' }}</span>
                                            @endif
                                            @if($r['dependency_id'] ?? null)
                                                <span class="text-gray-600 dark:text-gray-400">{{ $dependencyNames[$r['dependency_id']] ?? '—' }}</span>
                                            @endif
                                            @if($r['affiliation_id'] ?? null)
                                                <span class="text-gray-400 dark:text-zinc-500">· {{ $affiliationNames[$r['affiliation_id']] ?? '—' }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-gray-400 dark:text-zinc-500">Sin roles</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-400 dark:text-zinc-500">
                            No hay registros en este filtro.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    @if($rows->hasPages())
        <div>{{ $rows->links() }}</div>
    @endif
    @endif

    {{-- Acciones --}}
    @if($batch->status === 'en_revision')
        <div x-data="{ rejectOpen: false, approveOpen: {{ $errors->has('password') ? 'true' : 'false' }} }">

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-end gap-3 border-t border-neutral-200 dark:border-zinc-700 pt-4">
                <button type="button" @click="rejectOpen = true"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg border border-neutral-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 text-sm font-medium transition-colors cursor-pointer">
                    <flux:icon.x-mark class="size-4" />
                    Rechazar lote
                </button>
                <button type="button" @click="approveOpen = true"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-colors shadow-sm cursor-pointer">
                    <flux:icon.check class="size-4" />
                    Aprobar e importar
                </button>
            </div>

            {{-- Modal: Rechazar --}}
            <div x-show="rejectOpen" x-cloak x-trap.noscroll="rejectOpen"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 dark:bg-black/70" @click="rejectOpen = false"></div>
                <div class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-neutral-200 dark:border-zinc-700 z-10 p-6 flex flex-col gap-4"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-50 dark:bg-red-900/30 mx-auto">
                        <flux:icon.exclamation-triangle class="size-6 text-red-500" />
                    </div>
                    <div class="text-center">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">¿Rechazar este lote?</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            No se guardará ningún registro en el sistema. El lote quedará como rechazado
                            (podrás seguir descargando sus omitidos desde el historial).
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="rejectOpen = false"
                            class="flex-1 px-4 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">Cancelar</button>
                        <form method="POST" action="{{ route('participants-import.reject', $batch) }}" class="flex-1"
                              x-data="{ s: false }" @submit="if (s) { $event.preventDefault(); return; } s = true;">
                            @csrf
                            <button type="submit" :disabled="s"
                                class="w-full px-4 py-2 text-sm rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium transition-colors shadow-sm cursor-pointer disabled:opacity-60">
                                Sí, rechazar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Modal: Aprobar (requiere contraseña del admin) --}}
            <div x-show="approveOpen" x-cloak x-trap.noscroll="approveOpen"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/50 dark:bg-black/70" @click="approveOpen = false"></div>
                <div class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-neutral-200 dark:border-zinc-700 z-10 p-6 flex flex-col gap-4"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-11 h-11 rounded-full bg-emerald-50 dark:bg-emerald-900/30 shrink-0">
                            <flux:icon.shield-check class="size-6 text-emerald-600 dark:text-emerald-400" />
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Confirmar importación</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Se aplicarán los cambios a la base de datos.</p>
                        </div>
                    </div>

                    <div class="rounded-lg bg-zinc-50 dark:bg-zinc-800/60 border border-neutral-200 dark:border-zinc-700 px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                        Vas a importar <strong class="text-emerald-700 dark:text-emerald-400">{{ number_format($batch->new_count) }}</strong> nuevos
                        y actualizar <strong class="text-blue-700 dark:text-blue-400">{{ number_format($batch->update_count) }}</strong>.
                        @if($batch->skipped_count > 0)
                            <span class="text-amber-600 dark:text-amber-400">{{ number_format($batch->skipped_count) }} omitidas no se importarán.</span>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('participants-import.approve', $batch) }}"
                          x-data="{ submitting: false }"
                          @submit="if (submitting) { $event.preventDefault(); return; } submitting = true;"
                          class="flex flex-col gap-3">
                        @csrf
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tu contraseña de administrador</label>
                            <input type="password" name="password" required autocomplete="current-password"
                                   placeholder="••••••••"
                                   class="px-3 py-2 rounded-lg border @error('password') border-red-400 @else border-neutral-200 dark:border-zinc-700 @enderror bg-white dark:bg-zinc-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 transition" />
                            @error('password')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex gap-3 pt-1">
                            <button type="button" @click="approveOpen = false"
                                class="flex-1 px-4 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">Cancelar</button>
                            <button type="submit" :disabled="submitting"
                                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 text-sm rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium transition-colors shadow-sm cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                                <svg x-show="submitting" x-cloak class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                                </svg>
                                <span x-text="submitting ? 'Importando…' : 'Confirmar e importar'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    @endif

</div>

</x-layouts.app>
