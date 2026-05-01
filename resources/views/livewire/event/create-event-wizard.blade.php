@php
    $inputBase = 'w-full rounded-lg border px-3 py-2.5 text-sm bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100
                  placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:outline-none focus:ring-2 transition';
    $inputNormal = $inputBase . ' border-zinc-300 dark:border-zinc-600 focus:ring-blue-500/30 focus:border-blue-500';
    $inputError  = fn(string $field) => $inputBase
        . ($errors->has($field)
            ? ' border-red-500 focus:ring-red-500/30'
            : ' border-zinc-300 dark:border-zinc-600 focus:ring-blue-500/30 focus:border-blue-500');
@endphp

<div class="max-w-2xl mx-auto">

    {{-- ── Indicador de progreso ─────────────────────────────────────────── --}}
    @php
        $wizardSteps = [
            1 => 'Identidad',
            2 => 'Organización',
            3 => 'Fecha & Hora',
        ];
    @endphp

    <div class="flex items-start justify-center mb-5">
        @foreach($wizardSteps as $num => $label)

            <div class="flex flex-col items-center" style="min-width:80px">
                <div @class([
                    'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold border-2 transition-all duration-300',
                    'border-zinc-300 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800/60 text-zinc-400 dark:text-zinc-500' => $step < $num,
                    'border-blue-500 bg-blue-600 text-white ring-4 ring-blue-500/20'                                       => $step === $num,
                    'border-emerald-600 bg-emerald-600 text-white'                                                          => $step > $num,
                ])>
                    {{ $num }}
                </div>
                <p @class([
                    'text-xs font-semibold mt-1.5 text-center transition-colors duration-200',
                    'text-zinc-400 dark:text-zinc-500' => $step < $num,
                    'text-blue-500 dark:text-blue-400' => $step === $num,
                    'text-emerald-600 dark:text-emerald-500' => $step > $num,
                ])>{{ $label }}</p>
            </div>

            @if(!$loop->last)
                <div class="flex-1 flex items-center" style="padding-top:16px;padding-bottom:18px">
                    <div @class([
                        'h-0.5 w-full rounded-full transition-all duration-500',
                        'bg-zinc-300 dark:bg-zinc-700' => $step <= $num,
                        'bg-emerald-600'               => $step > $num,
                    ])></div>
                </div>
            @endif

        @endforeach
    </div>

    {{-- ── Tarjeta principal ────────────────────────────────────────────────── --}}
    <div class="rounded-2xl border border-zinc-200 dark:border-zinc-700/60 bg-zinc-50 dark:bg-zinc-900 shadow-xl overflow-hidden">
        <div class="p-5">

            {{-- ══ PASO 1: Identidad ══════════════════════════════════════════════ --}}
            @if($step === 1)
                <div wire:key="step-1" wire:transition class="flex flex-col gap-4">

                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">¿De qué trata el evento?</h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Dale un nombre claro y una descripción opcional.</p>
                    </div>

                    {{-- Nombre --}}
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Nombre del evento
                            <span class="text-red-400 ml-0.5">*</span>
                        </label>
                        <input
                            wire:model="title"
                            type="text"
                            placeholder="Ej: Día del amor y la amistad"
                            autofocus
                            class="{{ $inputError('title') }}"
                        />
                        @error('title')
                            <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                            Descripción
                            <span class="text-zinc-400 dark:text-zinc-500 font-normal text-xs ml-1">(opcional)</span>
                        </label>
                        <textarea
                            wire:model="description"
                            rows="3"
                            placeholder="¿Qué van a hacer? ¿Para quién es?..."
                            class="{{ $inputNormal }} resize-none"
                        ></textarea>
                    </div>

                </div>
            @endif

            {{-- ══ PASO 2: Organización ═══════════════════════════════════════════ --}}
            @if($step === 2)
                <div wire:key="step-2" wire:transition class="flex flex-col gap-4">

                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">¿Dónde y quién organiza?</h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Todos los campos de este paso son opcionales.</p>
                    </div>

                    {{-- Ubicación --}}
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Ubicación</label>
                        <input
                            wire:model="location"
                            type="text"
                            placeholder="Ej: Auditorio principal, Uniguajira"
                            class="{{ $inputNormal }}"
                        />
                    </div>

                    {{-- Dependencia --}}
                    @if($showDependencySelect)
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">Dependencia</label>
                            <select
                                wire:model.live="dependency_id"
                                class="{{ $inputNormal }} cursor-pointer"
                            >
                                <option value="">{{ $isAdmin ? '— Sin dependencia —' : 'Selecciona una dependencia' }}</option>
                                @foreach($dependencies as $depId => $depName)
                                    <option value="{{ $depId }}" @selected($dependency_id == $depId)>{{ $depName }}</option>
                                @endforeach
                            </select>
                            @error('dependency_id')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                            @if($isAdmin)
                                <p class="text-xs text-zinc-500 dark:text-zinc-500 mt-1">Sin dependencia seleccionada, el evento no estará asociado a ninguna.</p>
                            @endif
                        </div>
                    @endif

                    {{-- Área (deshabilitado temporalmente — no se usa actualmente)
                    <div wire:key="areas-section-{{ $dependency_id ?? 'none' }}">
                        @if(!empty($areas))
                            <div>
                                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                    Área
                                    <span class="text-zinc-400 dark:text-zinc-500 font-normal text-xs ml-1">(opcional)</span>
                                </label>
                                <select
                                    wire:model="area_id"
                                    class="{{ $inputNormal }} cursor-pointer"
                                >
                                    <option value="">Selecciona un área</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area['id'] }}" @selected($area_id == $area['id'])>{{ $area['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('area_id')
                                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @elseif($dependency_id && empty($areas))
                            <p class="text-xs text-zinc-500 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Esta dependencia no tiene áreas registradas.
                            </p>
                        @endif
                    </div>
                    --}}

                </div>
            @endif

            {{-- ══ PASO 3: Fecha & Hora ════════════════════════════════════════════ --}}
            @if($step === 3)
                <div wire:key="step-3" wire:transition class="flex flex-col gap-4">

                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">¿Cuándo es el evento?</h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Todos los campos de este paso son obligatorios. La hora fin determina cuándo el evento deja de recibir asistencias.</p>
                    </div>

                    {{-- Fecha + horas --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Fecha <span class="text-red-400 ml-0.5">*</span>
                            </label>
                            <input wire:model="date" type="date" min="{{ now()->toDateString() }}" class="{{ $inputError('date') }}" />
                            @error('date')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Hora inicio <span class="text-red-400 ml-0.5">*</span>
                            </label>
                            <input wire:model="start_time" type="time" step="300" class="{{ $inputError('start_time') }}" />
                            @error('start_time')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                Hora fin <span class="text-red-400 ml-0.5">*</span>
                            </label>
                            <input wire:model="end_time" type="time" step="300" class="{{ $inputError('end_time') }}" />
                            @error('end_time')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Mini-resumen --}}
                    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700/60 bg-white dark:bg-zinc-800/40 p-3.5 space-y-2">
                        <p class="text-[10px] uppercase tracking-widest text-zinc-400 dark:text-zinc-500 font-semibold">Resumen</p>

                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-zinc-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">{{ $title }}</p>
                        </div>

                        @if($description)
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-zinc-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12"/>
                                </svg>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 line-clamp-2">{{ Str::limit($description, 80) }}</p>
                            </div>
                        @endif

                        @if($location)
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-zinc-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                </svg>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $location }}</p>
                            </div>
                        @endif

                        @if($dependency_id && isset($dependencies[$dependency_id]))
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-zinc-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                                </svg>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $dependencies[$dependency_id] }}</p>
                            </div>
                        @endif
                    </div>

                </div>
            @endif

        </div>

        {{-- ── Pie de navegación ──────────────────────────────────────────────── --}}
        <div class="px-5 py-4 bg-zinc-100 dark:bg-zinc-800/40 border-t border-zinc-200 dark:border-zinc-700/60 flex items-center justify-between">

            <button
                type="button"
                wire:click="prevStep"
                @class([
                    'inline-flex items-center gap-1.5 h-9 px-3 text-sm font-medium rounded-lg transition',
                    'text-zinc-600 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-200 dark:hover:bg-white/10' => $step > 1,
                    'invisible pointer-events-none' => $step === 1,
                    'cursor-pointer' => true,
                ])
            >
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Anterior
            </button>

            <span class="text-xs text-zinc-400 dark:text-zinc-500 tabular-nums select-none">
                Paso {{ $step }} de {{ self::TOTAL_STEPS }}
            </span>

            <div class="flex">
                <button
                    type="button"
                    wire:click="nextStep"
                    wire:loading.attr="disabled"
                    wire:target="nextStep"
                    @class([
                        'items-center gap-2 h-9 px-4 text-sm font-medium rounded-lg bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 hover:bg-zinc-700 dark:hover:bg-zinc-100 transition disabled:opacity-60',
                        'inline-flex' => $step < self::TOTAL_STEPS,
                        'hidden'      => $step >= self::TOTAL_STEPS,
                        'cursor-pointer' => true,
                    ])
                >
                    Siguiente
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <button
                    type="button"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    @class([
                        'items-center gap-2 h-9 px-4 text-sm font-medium rounded-lg bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 hover:bg-zinc-700 dark:hover:bg-zinc-100 transition disabled:opacity-60',
                        'inline-flex' => $step >= self::TOTAL_STEPS,
                        'hidden'      => $step < self::TOTAL_STEPS,
                        'cursor-pointer' => true,
                    ])
                >
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Crear evento
                </button>
            </div>

        </div>
    </div>

</div>