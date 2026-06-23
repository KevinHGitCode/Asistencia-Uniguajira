@push('head-scripts')
    <!-- Cal-Heatmap (calendario semestral) -->
    <script src="https://d3js.org/d3.v6.min.js"></script>
    <script src="https://unpkg.com/cal-heatmap/dist/cal-heatmap.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/cal-heatmap/dist/cal-heatmap.css">
    <!-- Apache ECharts (gráficos legacy) [Reemplazado por Recharts]-->
    {{-- <script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script> --}}
@endpush

<x-layouts.app :title="__('Dashboard')">
    @include('calendar.modal')

    <div class="flex min-h-full w-full flex-1 flex-col gap-5 p-1 sm:p-2 md:px-4 md:py-2">
        <!-- Header de bienvenida -->
        <div class="mb-2">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-2">
                ¡Bienvenido, {{ $username }}! 👋
            </h1>
            <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">
                Gestiona tus eventos y consulta estadísticas de asistencia
            </p>

            @if(auth()->user()->isSuperadmin())
                <div class="mt-4 rounded-2xl border border-neutral-200 bg-white p-3 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                    <form
                        method="POST"
                        action="{{ route('dashboard.campus') }}"
                        data-dashboard-campus-form
                        class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
                    >
                        @csrf
                        <div class="flex items-start gap-3">
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300">
                                <flux:icon.map-pin class="size-5" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    Vista por sede
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Cambia la sede y se actualizarán las cards y el calendario.
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                            <label for="dashboard-campus-id" class="sr-only">Sede activa</label>
                            <select
                                id="dashboard-campus-id"
                                name="campus_id"
                                data-dashboard-campus-select
                                class="w-full rounded-xl border border-neutral-200 bg-zinc-50 px-3 py-2 text-sm font-medium text-gray-800 shadow-sm transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 disabled:cursor-wait disabled:opacity-70 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:focus:bg-zinc-900 sm:w-60">
                                <option value="">Todas las sedes</option>
                                @foreach($campuses as $campusId => $campusName)
                                    <option value="{{ $campusId }}" @selected((int) $activeCampusId === (int) $campusId)>
                                        {{ $campusName }}
                                    </option>
                                @endforeach
                            </select>

                            <span
                                data-dashboard-campus-status
                                class="hidden items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium shadow-sm transition-colors"
                                role="status"
                                aria-live="polite"
                            >
                                <span data-dashboard-campus-status-icon class="flex size-3 items-center justify-center"></span>
                                <span data-dashboard-campus-status-text></span>
                            </span>
                        </div>
                    </form>
                </div>

                @once
                    <script>
                        (() => {
                            const eventName = 'dashboard:campus-changed';
                            const numberFormatter = new Intl.NumberFormat('es-CO');

                            const setCardValue = (key, value) => {
                                const wrapper = document.querySelector(`[data-dashboard-stat="${key}"]`);
                                const valueEl = wrapper?.querySelector('[data-card-stat-value]');
                                if (!valueEl) return;

                                valueEl.textContent = numberFormatter.format(value ?? 0);
                                wrapper.classList.add('transition', 'duration-200', 'scale-[1.01]');
                                window.setTimeout(() => wrapper.classList.remove('scale-[1.01]'), 220);
                            };

                            document.addEventListener('change', async (event) => {
                                const select = event.target.closest('[data-dashboard-campus-select]');
                                if (!select) return;

                                const form = select.closest('[data-dashboard-campus-form]');
                                const status = form?.querySelector('[data-dashboard-campus-status]');
                                const statusIcon = form?.querySelector('[data-dashboard-campus-status-icon]');
                                const statusText = form?.querySelector('[data-dashboard-campus-status-text]');
                                const formData = new FormData(form);

                                const setStatus = (type, text) => {
                                    if (!status || !statusIcon || !statusText) return;

                                    const styles = {
                                        loading: [
                                            'border-blue-200', 'bg-blue-50', 'text-blue-700',
                                            'dark:border-blue-900', 'dark:bg-blue-950/40', 'dark:text-blue-300',
                                        ],
                                        success: [
                                            'border-emerald-200', 'bg-emerald-50', 'text-emerald-700',
                                            'dark:border-emerald-900', 'dark:bg-emerald-950/40', 'dark:text-emerald-300',
                                        ],
                                        error: [
                                            'border-red-200', 'bg-red-50', 'text-red-700',
                                            'dark:border-red-900', 'dark:bg-red-950/40', 'dark:text-red-300',
                                        ],
                                    };

                                    status.className = [
                                        'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium shadow-sm transition-colors',
                                        ...styles[type],
                                    ].join(' ');

                                    statusIcon.innerHTML = type === 'loading'
                                        ? '<span class="size-3 rounded-full border-2 border-current border-t-transparent animate-spin"></span>'
                                        : type === 'success'
                                            ? '<svg viewBox="0 0 16 16" class="size-3" fill="currentColor"><path fill-rule="evenodd" d="M13.78 3.72a.75.75 0 0 1 0 1.06l-6.25 6.25a.75.75 0 0 1-1.06 0L3.22 7.78a.75.75 0 0 1 1.06-1.06L7 9.44l5.72-5.72a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>'
                                            : '<svg viewBox="0 0 16 16" class="size-3" fill="currentColor"><path fill-rule="evenodd" d="M8 1.5a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13ZM7.25 4.75a.75.75 0 0 1 1.5 0V8a.75.75 0 0 1-1.5 0V4.75ZM8 11.5a.875.875 0 1 0 0-1.75.875.875 0 0 0 0 1.75Z" clip-rule="evenodd"/></svg>';
                                    statusText.textContent = text;
                                };

                                select.disabled = true;
                                setStatus('loading', 'Actualizando dashboard');

                                try {
                                    const response = await fetch(form.action, {
                                        method: 'POST',
                                        body: formData,
                                        headers: {
                                            'Accept': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest',
                                        },
                                    });

                                    if (!response.ok) throw new Error(`HTTP ${response.status}`);

                                    const payload = await response.json();
                                    setCardValue('eventos', payload.stats?.eventos);
                                    setCardValue('asistencias', payload.stats?.asistencias);
                                    setCardValue('participantes', payload.stats?.participantes);

                                    window.dispatchEvent(new CustomEvent(eventName, {
                                        detail: { campusId: payload.campus_id ?? null },
                                    }));

                                    setStatus('success', 'Dashboard actualizado');
                                    window.setTimeout(() => status?.classList.add('hidden'), 1800);
                                } catch (error) {
                                    setStatus('error', 'No se pudo actualizar');
                                } finally {
                                    select.disabled = false;
                                }
                            });
                        })();
                    </script>
                @endonce
            @endif
        </div>

        <!-- Cards de estadísticas -->
        <div class="text-center grid grid-cols-2 gap-3 sm:gap-3 md:grid-cols-3 md:gap-5">
            <!-- Primera card -->
            <div data-dashboard-stat="eventos">
                <livewire:card-stat title="Eventos creados" :value="$eventosCount">
                    <x-slot name="icon">
                        <flux:icon.calendar-check class="size-7" />
                    </x-slot>
                </livewire:card-stat>
            </div>

            <!-- Segunda card -->
            <div data-dashboard-stat="asistencias">
                <livewire:card-stat title="Asistencias totales" :value="$asistenciasCount">
                    <x-slot name="icon">
                        <flux:icon.list-checks class="size-7" />
                    </x-slot>
                </livewire:card-stat>
            </div>

            <!-- Tercera card - ocupa todo el ancho en móvil, columna normal en desktop -->
            <div class="col-span-2 md:col-span-1" data-dashboard-stat="participantes">
                <livewire:card-stat title="Participantes totales" :value="$participantesCount">
                    <x-slot name="icon">
                        <flux:icon.users class="size-7" />
                    </x-slot>
                </livewire:card-stat>
            </div>
        </div>

        <!-- Contenedor del calendario -->
        {{-- shrink-0: evita que el flex column del dashboard comprima la tarjeta cuando hay
             poco alto; así no se recorta el calendario y el sobrante lo absorbe el scroll de
             flux:main (toda la página). --}}
        <div class="relative shrink-0 border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-md overflow-hidden">
            <!-- Header del calendario -->
            <div class="px-3 sm:px-6 py-3 border-b border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900 text-center">
                <h2 class="flex items-center justify-center gap-2 text-lg sm:text-xl font-bold text-gray-900 dark:text-white">
                    <flux:icon.calendar-check class="size-6 text-[#e2a542]" />
                    <span>Calendario Semestral de Eventos</span>
                </h2>

                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Visualiza tu actividad de eventos a lo largo del año
                </p>
            </div>

            <!-- Contenedor con scroll -->
            <div class="relative overflow-x-auto overflow-y-hidden px-3 sm:px-5 py-5 sm:py-6 bg-white dark:bg-zinc-800 border dark:border-neutral-700" style="scroll-behavior: smooth;">
                <div class="flex justify-center min-w-max" id="cal-heatmap"></div>
            </div>

            <!-- Leyenda -->
            <div class="px-4 sm:px-6 py-4 border-t border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
                <div class="flex items-center justify-center gap-4 sm:gap-7 flex-wrap">
                    <!-- Tus eventos -->
                    <div class="flex items-center gap-1">
                        <div class="w-4 h-4 rounded-sm bg-[#cc5e50]"></div>
                        <span class="text-xs sm:text-sm text-gray-900 dark:text-gray-300">Tus eventos</span>
                    </div>

                    <!-- Hoy -->
                    <div class="flex items-center gap-1 today-indicator">
                        <div class="w-4 h-4 rounded-sm bg-[#e2a542]"></div>
                        <span class="text-xs sm:text-sm text-gray-900 dark:text-gray-300">Hoy</span>
                    </div>

                    <!-- Eventos de otros -->
                    <div class="flex items-center gap-1">
                        <div class="w-4 h-4 rounded-sm bg-[#62a9b6]"></div>
                        <span class="text-xs sm:text-sm text-gray-900 dark:text-gray-300">Eventos de otros</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @livewire('event.create-event-modal')
</x-layouts.app>
