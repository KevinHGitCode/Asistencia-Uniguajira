<div>
    {{-- ── Animaciones CSS ──────────────────────────────────────────────── --}}
    <style>
        @@keyframes ar-draw-check {
            to { stroke-dashoffset: 0; }
        }
        @@keyframes ar-shrink-bar {
            from { width: 100%; }
            to   { width: 0%; }
        }
        .ar-draw-check {
            stroke-dasharray: 100;
            stroke-dashoffset: 100;
            animation: ar-draw-check .5s ease-out forwards .2s;
        }
        .ar-shrink-bar {
            animation: ar-shrink-bar 5s linear forwards .1s;
        }
    </style>

    {{-- ══════════════════════════════════════════════════════════════════
         NAVEGACIÓN POR PESTAÑAS
         Solo visible cuando se puede cambiar de pestaña (step inicial)
    ══════════════════════════════════════════════════════════════════ --}}
    {{-- @if (in_array($step, ['search', 'select_role']) || $activeTab === 'participante')
        <div class="mb-4 flex rounded-xl bg-gray-100 p-1 dark:bg-zinc-800">
            <button
                wire:click="switchTab('asistencia')"
                type="button"
                class="flex flex-1 items-center justify-center gap-1.5 rounded-lg py-2.5 text-xs font-bold transition-all duration-200
                       {{ $activeTab === 'asistencia'
                            ? 'bg-white text-gray-900 shadow-sm dark:bg-zinc-700 dark:text-gray-100'
                            : 'text-gray-500 hover:text-gray-700 dark:text-zinc-400 dark:hover:text-gray-300' }}">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M4.5 12.75 6 14.25l3.75-7.5m4.5 4.5 1.5 1.5 3.75-7.5"/>
                </svg>
                Registrar asistencia
            </button>
        </div>
    @endif --}}


    {{-- ══════════════════════════════════════════════════════════════════
         TAB: REGISTRAR ASISTENCIA
    ══════════════════════════════════════════════════════════════════ --}}
    @if ($activeTab === 'asistencia')

        {{-- ─────────────────────────────────────────────────────────────
             STEP: search
        ──────────────────────────────────────────────────────────────── --}}
        @if ($step === 'search')
            <div wire:transition
                 wire:key="step-search"
                 class="rounded-2xl border border-neutral-200 bg-white shadow-lg
                        dark:border-zinc-700 dark:bg-zinc-800"
                 style="border-top: 3px solid #62a9b6;">
                <div class="px-6 pt-8 pb-6">

                    <div class="mb-6 text-center">
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full shadow-md"
                             style="background: linear-gradient(135deg, #62a9b6 0%, #4d94a0 100%);">
                            <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-extrabold tracking-tight text-gray-800 dark:text-gray-100">
                            Ingresa tu documento
                        </h2>
                        <p class="mx-auto mt-1.5 max-w-[260px] text-sm text-gray-500 dark:text-zinc-400">
                            Buscaremos tu perfil para confirmar la asistencia
                        </p>
                    </div>

                    <div class="mb-4">
                        <label for="ar-identification"
                               class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-zinc-400">
                            Documento de identidad
                        </label>
                        <input
                            id="ar-identification"
                            wire:model="identification"
                            wire:keydown.enter="search"
                            type="text"
                            inputmode="numeric"
                            autocomplete="off"
                            spellcheck="false"
                            placeholder="Ej: 1073456789"
                            class="w-full rounded-xl border px-4 py-3.5 text-lg font-semibold tracking-wider
                                   bg-gray-50 dark:bg-zinc-700/70
                                   text-gray-900 dark:text-gray-100
                                   placeholder-gray-300 dark:placeholder-zinc-500
                                   transition focus:outline-none focus:ring-2 focus:bg-white dark:focus:bg-zinc-700
                                   {{ $errors->has('identification')
                                        ? 'border-red-400 focus:border-red-400 focus:ring-red-300/40 dark:border-red-600'
                                        : 'border-neutral-200 dark:border-zinc-600 focus:border-[#62a9b6] focus:ring-[#62a9b6]/25' }}"
                        />
                        @error('identification')
                            <p class="mt-1.5 flex items-center gap-1 text-sm text-red-500 dark:text-red-400">
                                <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                          d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"
                                          clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Tratamiento de datos --}}
                    <div class="mb-5 rounded-xl bg-gray-50 px-3.5 py-3 ring-1 ring-inset ring-gray-100
                                dark:bg-zinc-700/30 dark:ring-zinc-700">
                        <label class="flex items-start gap-2.5 cursor-pointer group">
                            <input
                                type="checkbox"
                                wire:model="acceptsDataTreatment"
                                class="mt-0.5 h-4 w-4 shrink-0 rounded border-neutral-300 text-[#62a9b6]
                                    focus:ring-[#62a9b6]/25 dark:border-zinc-600 dark:bg-zinc-700
                                    accent-[#62a9b6]"
                            />
                            <span class="text-xs text-gray-600 dark:text-zinc-300 leading-relaxed">
                                Acepto el
                                <a href="https://drive.google.com/file/d/14hSAIC_e-6vtsq4kQ6ifCKP4MVKNOsFu/view?usp=sharing"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-[#62a9b6] underline hover:text-[#4d94a0] font-semibold">
                                    tratamiento de datos personales
                                </a>
                                de la Universidad de La Guajira.
                            </span>
                        </label>
                        @error('acceptsDataTreatment')
                            <p class="mt-1.5 flex items-center gap-1 text-xs text-red-500 dark:text-red-400">
                                <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"
                                        clip-rule="evenodd"/>
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <button
                        wire:click="search"
                        wire:loading.attr="disabled"
                        wire:target="search"
                        type="button"
                        class="w-full rounded-xl py-3.5 px-6 text-base font-bold text-white shadow-lg
                               transition-all duration-200 hover:opacity-90 active:scale-[.98]
                               disabled:cursor-not-allowed disabled:opacity-60"
                        style="background: linear-gradient(90deg, #62a9b6 0%, #4d94a0 100%)">
                        <span wire:loading.remove wire:target="search"
                              class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                            </svg>
                            Buscar mi perfil
                        </span>
                        <span wire:loading wire:target="search"
                              class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Buscando…
                        </span>
                    </button>
                </div>

                <div class="flex items-center justify-center gap-2 border-t border-neutral-100 bg-gray-50/60 px-6 py-3
                            text-xs text-gray-500 dark:border-zinc-700 dark:bg-zinc-900/30 dark:text-zinc-400">
                    <svg class="h-3.5 w-3.5 text-[#62a9b6]" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                    Usa tu documento de identidad para registrarte
                </div>
            </div>
        @endif

        {{-- ─────────────────────────────────────────────────────────────
             STEP: register_external
             Documento no encontrado → capturar nombre y registrar
             como Comunidad Externa, luego continúa al step 'details'
        ──────────────────────────────────────────────────────────────── --}}
        @if ($step === 'register_external')
            @php
                $inputCls2 = 'w-full rounded-xl border border-neutral-300 bg-white px-3.5 py-2.5
                              text-sm text-gray-900 placeholder-gray-400 transition
                              focus:border-[#62a9b6] focus:outline-none focus:ring-2 focus:ring-[#62a9b6]/25
                              dark:border-zinc-600 dark:bg-zinc-700 dark:text-gray-100
                              dark:placeholder-zinc-500 dark:focus:border-[#62a9b6]';
                $labelCls2 = 'mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400';
                $errCls2   = 'mt-1 flex items-center gap-1 text-xs text-red-500 dark:text-red-400';
            @endphp

            <div wire:transition
                 wire:key="step-register-external"
                 class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm
                        dark:border-zinc-700 dark:bg-zinc-800">

                {{-- Banner informativo --}}
                <div class="flex items-start gap-3 px-5 py-3.5 text-white"
                     style="background: linear-gradient(90deg, #62a9b6 0%, #4d94a0 100%)">
                    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                    </svg>
                    <div>
                        <p class="text-xs font-bold leading-tight">Documento no registrado</p>
                        <p class="mt-0.5 text-[11px] text-white/90">
                            Doc. <strong>{{ $identification }}</strong> — se registrará como
                            <strong>Comunidad Externa</strong>
                        </p>
                    </div>
                </div>

                <div class="px-5 py-4">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-zinc-500">
                        Datos de la persona
                    </p>

                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="{{ $labelCls2 }}">
                                    Nombre <span class="text-red-400">*</span>
                                </label>
                                <input
                                    wire:model="externalFirstName"
                                    type="text"
                                    placeholder="Ej: Ana"
                                    class="{{ $inputCls2 }} {{ $errors->has('externalFirstName') ? 'border-red-400 focus:ring-red-300/40' : '' }}"
                                />
                                @error('externalFirstName')
                                    <p class="{{ $errCls2 }}">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="{{ $labelCls2 }}">
                                    Apellido <span class="text-red-400">*</span>
                                </label>
                                <input
                                    wire:model="externalLastName"
                                    type="text"
                                    placeholder="Ej: García"
                                    class="{{ $inputCls2 }} {{ $errors->has('externalLastName') ? 'border-red-400 focus:ring-red-300/40' : '' }}"
                                />
                                @error('externalLastName')
                                    <p class="{{ $errCls2 }}">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="{{ $labelCls2 }}">Correo electrónico</label>
                            <input
                                wire:model="externalEmail"
                                type="email"
                                inputmode="email"
                                placeholder="Opcional"
                                class="{{ $inputCls2 }} {{ $errors->has('externalEmail') ? 'border-red-400 focus:ring-red-300/40' : '' }}"
                            />
                            @error('externalEmail')
                                <p class="{{ $errCls2 }}">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="space-y-2.5 border-t border-gray-100 px-5 py-4 dark:border-zinc-700">
                    <button
                        wire:click="registerExternal"
                        wire:loading.attr="disabled"
                        wire:target="registerExternal"
                        type="button"
                        class="w-full rounded-xl py-3.5 px-6 text-base font-bold text-white shadow
                               transition-all duration-200 hover:opacity-90 active:scale-[.98]
                               disabled:cursor-not-allowed disabled:opacity-60"
                        style="background: linear-gradient(90deg, #62a9b6 0%, #4d94a0 100%)">
                        <span wire:loading.remove wire:target="registerExternal"
                              class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                            </svg>
                            Continuar y registrar asistencia
                        </span>
                        <span wire:loading wire:target="registerExternal"
                              class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Registrando…
                        </span>
                    </button>

                    <button
                        wire:click="backToSearch"
                        type="button"
                        class="w-full rounded-xl border border-neutral-300 bg-white py-3 px-6
                               text-sm font-semibold text-gray-600 transition
                               hover:bg-gray-50 active:scale-[.98]
                               dark:border-zinc-600 dark:bg-zinc-700 dark:text-gray-300 dark:hover:bg-zinc-600">
                        ← Verificar documento de nuevo
                    </button>
                </div>
            </div>
        @endif

        {{-- ─────────────────────────────────────────────────────────────
             STEP: found — ¿Eres tú? → va a detalles
        ──────────────────────────────────────────────────────────────── --}}
        @if ($step === 'found' && $participantData)
            @php
                $arColors   = ['#cc5e50', '#e2a542', '#62a9b6'];
                $arBg       = $arColors[$participantData['id'] % 3];
                $arInitials = mb_strtoupper(
                    mb_substr($participantData['first_name'], 0, 1) .
                    mb_substr($participantData['last_name'],  0, 1)
                );
            @endphp

            <div wire:transition
                 wire:key="step-found"
                 class="rounded-2xl border border-neutral-200 bg-white shadow-sm
                        dark:border-zinc-700 dark:bg-zinc-800">

                <div class="px-6 pt-7 pb-4 text-center">
                    <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center
                                rounded-full text-xl font-extrabold text-white shadow-md"
                         style="background-color: {{ $arBg }}">
                        {{ $arInitials }}
                    </div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-zinc-500">
                        ¿Eres tú?
                    </p>
                    <h2 class="mt-1 text-2xl font-extrabold text-gray-800 dark:text-gray-100">
                        {{ $participantData['first_name'] }} {{ $participantData['last_name'] }}
                    </h2>
                </div>

                <div class="mx-6 mb-4 divide-y divide-gray-100 rounded-xl border border-gray-100
                            bg-gray-50 dark:divide-zinc-700 dark:border-zinc-700 dark:bg-zinc-700/40">
                    @foreach ([
                        ['Documento', $participantData['document'] ?? null],
                        ['Correo',    $participantData['email'] ?? null],
                    ] as [$arLabel, $arValue])
                        @if (!empty($arValue))
                            <div class="flex items-center gap-3 px-4 py-2.5">
                                <span class="w-24 shrink-0 text-xs font-medium text-gray-400 dark:text-zinc-500">
                                    {{ $arLabel }}
                                </span>
                                <span class="flex-1 truncate text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    {{ $arValue }}
                                </span>
                            </div>
                        @endif
                    @endforeach
                    @foreach ($participantData['roles'] ?? [] as $role)
                        <div class="flex items-center gap-3 px-4 py-2.5">
                            <span class="w-24 shrink-0 text-xs font-medium text-gray-400 dark:text-zinc-500">
                                {{ $role['type_name'] }}
                            </span>
                            <span class="flex-1 truncate text-sm font-semibold text-gray-800 dark:text-gray-100">
                                {{ $role['program_name'] ?? $role['dependency_name'] ?? $role['affiliation_name'] ?? '—' }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-2.5 px-6 pb-6">
                    <button
                        wire:click="goToDetails"
                        wire:loading.attr="disabled"
                        wire:target="goToDetails"
                        type="button"
                        class="w-full rounded-xl py-3.5 px-6 text-base font-bold text-white shadow
                               transition-all duration-200 hover:opacity-90 active:scale-[.98]
                               disabled:cursor-not-allowed disabled:opacity-60"
                        style="background: linear-gradient(90deg, #cc5e50 0%, #b84a3d 100%)">
                        <span wire:loading.remove wire:target="goToDetails"
                              class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                            </svg>
                            Sí, continuar
                        </span>
                        <span wire:loading wire:target="goToDetails"
                              class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Cargando…
                        </span>
                    </button>

                    <button
                        wire:click="backToSearch"
                        type="button"
                        class="w-full rounded-xl border border-neutral-300 bg-white py-3 px-6
                               text-sm font-semibold text-gray-600 transition
                               hover:bg-gray-50 active:scale-[.98]
                               dark:border-zinc-600 dark:bg-zinc-700 dark:text-gray-300 dark:hover:bg-zinc-600">
                        No soy yo
                    </button>
                </div>
            </div>
        @endif

        {{-- ─────────────────────────────────────────────────────────────
             STEP: select_type — Elegir estamento para esta asistencia
        ──────────────────────────────────────────────────────────────── --}}
        @if ($step === 'select_type' && $participantData)
            @php
                $arColors   = ['#cc5e50', '#e2a542', '#62a9b6'];
                $arBg       = $arColors[$participantData['id'] % 3];
                $arInitials = mb_strtoupper(
                    mb_substr($participantData['first_name'], 0, 1) .
                    mb_substr($participantData['last_name'],  0, 1)
                );
            @endphp

            <div wire:transition
                wire:key="step-select-type"
                class="rounded-2xl border border-neutral-200 bg-white shadow-sm
                        dark:border-zinc-700 dark:bg-zinc-800">

                <div class="px-6 pt-7 pb-4 text-center">
                    <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center
                                rounded-full text-xl font-extrabold text-white shadow-md"
                        style="background-color: {{ $arBg }}">
                        {{ $arInitials }}
                    </div>
                    <h2 class="text-lg font-extrabold text-gray-800 dark:text-gray-100">
                        {{ $participantData['first_name'] }} {{ $participantData['last_name'] }}
                    </h2>
                    <p class="mt-1.5 text-sm text-gray-500 dark:text-zinc-400">
                        Tienes <strong>{{ count($participantData['types']) }}</strong> estamentos registrados.<br>
                        Selecciona con cuál deseas registrarte en este evento.
                    </p>
                </div>

                <div class="px-5 pb-2">
                    <div class="space-y-2">
                        @foreach ($participantData['types'] as $type)
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border p-3.5 transition
                                        {{ $selectedTypeId == $type['id']
                                            ? 'border-[#0d9488] bg-teal-50 dark:bg-teal-900/20'
                                            : 'border-neutral-200 hover:border-neutral-300 dark:border-zinc-700 dark:hover:border-zinc-600' }}">
                                <input
                                    type="radio"
                                    wire:model="selectedTypeId"
                                    value="{{ $type['id'] }}"
                                    class="h-4 w-4 accent-[#0d9488]"
                                />
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                    {{ $type['name'] }}
                                </span>
                            </label>
                        @endforeach
                    </div>

                    @error('selectedTypeId')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2.5 border-t border-gray-100 px-5 py-4 dark:border-zinc-700 mt-2">
                    <button
                        wire:click="confirmTypeSelection"
                        wire:loading.attr="disabled"
                        wire:target="confirmTypeSelection"
                        type="button"
                        class="w-full rounded-xl py-3.5 px-6 text-base font-bold text-white shadow
                            transition-all duration-200 hover:opacity-90 active:scale-[.98]
                            disabled:cursor-not-allowed disabled:opacity-60"
                        style="background: linear-gradient(90deg, #0d9488 0%, #0f766e 100%)">
                        <span wire:loading.remove wire:target="confirmTypeSelection"
                            class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                            </svg>
                            Continuar con este estamento
                        </span>
                        <span wire:loading wire:target="confirmTypeSelection"
                            class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Cargando…
                        </span>
                    </button>

                    <button
                        wire:click="backToSearch"
                        type="button"
                        class="w-full rounded-xl border border-neutral-300 bg-white py-3 px-6
                            text-sm font-semibold text-gray-600 transition
                            hover:bg-gray-50 active:scale-[.98]
                            dark:border-zinc-600 dark:bg-zinc-700 dark:text-gray-300 dark:hover:bg-zinc-600">
                        ← Volver al inicio
                    </button>
                </div>
            </div>
        @endif

        {{-- ─────────────────────────────────────────────────────────────
             STEP: select_role — Elegir programa/dependencia para esta asistencia
        ──────────────────────────────────────────────────────────────── --}}
        @if ($step === 'select_role' && $participantData)
            @php
                $arColors   = ['#cc5e50', '#e2a542', '#62a9b6'];
                $arBg       = $arColors[$participantData['id'] % 3];
                $arInitials = mb_strtoupper(
                    mb_substr($participantData['first_name'], 0, 1) .
                    mb_substr($participantData['last_name'],  0, 1)
                );
                $rolesForType = $this->rolesForSelectedType;
            @endphp

            <div wire:transition
                wire:key="step-select-role"
                class="rounded-2xl border border-neutral-200 bg-white shadow-sm
                        dark:border-zinc-700 dark:bg-zinc-800">

                <div class="px-6 pt-7 pb-4 text-center">
                    <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center
                                rounded-full text-xl font-extrabold text-white shadow-md"
                        style="background-color: {{ $arBg }}">
                        {{ $arInitials }}
                    </div>
                    <h2 class="text-lg font-extrabold text-gray-800 dark:text-gray-100">
                        {{ $participantData['first_name'] }} {{ $participantData['last_name'] }}
                    </h2>
                    <p class="mt-1.5 text-sm text-gray-500 dark:text-zinc-400">
                        Tienes <strong>{{ count($rolesForType) }}</strong> programas/dependencias registrados.<br>
                        Selecciona con cuál asistes a este evento.
                    </p>
                </div>

                <div class="px-5 pb-2">
                    <div class="space-y-2">
                        @foreach ($rolesForType as $role)
                            @php
                                $roleLabel = $role['program_name']
                                    ? $role['program_name']
                                    : ($role['dependency_name'] ?? $role['affiliation_name'] ?? '—');
                            @endphp
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border p-3.5 transition
                                        {{ $selectedRoleId == $role['id']
                                            ? 'border-[#cc5e50] bg-red-50 dark:bg-red-900/20'
                                            : 'border-neutral-200 hover:border-neutral-300 dark:border-zinc-700 dark:hover:border-zinc-600' }}">
                                <input
                                    type="radio"
                                    wire:model="selectedRoleId"
                                    value="{{ $role['id'] }}"
                                    class="h-4 w-4 accent-[#cc5e50]"
                                />
                                <span class="text-sm font-medium text-gray-800 dark:text-gray-100">
                                    {{ $roleLabel }}
                                </span>
                            </label>
                        @endforeach
                    </div>

                    @error('selectedRoleId')
                        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2.5 border-t border-gray-100 px-5 py-4 dark:border-zinc-700 mt-2">
                    <button
                        wire:click="confirmRoleSelection"
                        wire:loading.attr="disabled"
                        wire:target="confirmRoleSelection"
                        type="button"
                        class="w-full rounded-xl py-3.5 px-6 text-base font-bold text-white shadow
                            transition-all duration-200 hover:opacity-90 active:scale-[.98]
                            disabled:cursor-not-allowed disabled:opacity-60"
                        style="background: linear-gradient(90deg, #cc5e50 0%, #b84a3d 100%)">
                        <span wire:loading.remove wire:target="confirmRoleSelection"
                            class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                            </svg>
                            Continuar con esta selección
                        </span>
                        <span wire:loading wire:target="confirmRoleSelection"
                            class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Cargando…
                        </span>
                    </button>

                    <button
                        wire:click="backToSearch"
                        type="button"
                        class="w-full rounded-xl border border-neutral-300 bg-white py-3 px-6
                            text-sm font-semibold text-gray-600 transition
                            hover:bg-gray-50 active:scale-[.98]
                            dark:border-zinc-600 dark:bg-zinc-700 dark:text-gray-300 dark:hover:bg-zinc-600">
                        ← Volver al inicio
                    </button>
                </div>
            </div>
        @endif

        {{-- ─────────────────────────────────────────────────────────────
             STEP: details — Datos adicionales antes de confirmar
        ──────────────────────────────────────────────────────────────── --}}
        @if ($step === 'details' && $participantData)
            @php
                $arColors   = ['#cc5e50', '#e2a542', '#62a9b6'];
                $arBg       = $arColors[$participantData['id'] % 3];
                $arInitials = mb_strtoupper(
                    mb_substr($participantData['first_name'], 0, 1) .
                    mb_substr($participantData['last_name'],  0, 1)
                );
            @endphp

            <div wire:transition
                 wire:key="step-details"
                 class="rounded-2xl border border-neutral-200 bg-white shadow-sm
                        dark:border-zinc-700 dark:bg-zinc-800">

                {{-- Header con participante --}}
                <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-4
                            dark:border-zinc-700">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center
                                rounded-full text-sm font-extrabold text-white shadow"
                         style="background-color: {{ $arBg }}">
                        {{ $arInitials }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-gray-800 dark:text-gray-100">
                            {{ $participantData['first_name'] }} {{ $participantData['last_name'] }}
                        </p>
                        <p class="text-xs text-gray-400 dark:text-zinc-500">
                            Doc. {{ $participantData['document'] }}
                        </p>
                    </div>
                </div>

                <div class="px-5 py-4">
                    <p class="mb-4 text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-zinc-500">
                        Datos adicionales de asistencia
                    </p>

                    @php
                        $inputClass = 'w-full rounded-xl border border-neutral-300 bg-white px-3.5 py-2.5
                                       text-sm text-gray-900 placeholder-gray-400 transition
                                       focus:border-[#62a9b6] focus:outline-none focus:ring-2 focus:ring-[#62a9b6]/25
                                       dark:border-zinc-600 dark:bg-zinc-700 dark:text-gray-100
                                       dark:placeholder-zinc-500 dark:focus:border-[#62a9b6]';
                        $selectClass = $inputClass;
                        $labelClass  = 'mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400';
                    @endphp

                    <div class="space-y-3">

                        {{-- Género --}}
                        <div wire:key="field-detail-gender">
                            <label class="{{ $labelClass }}">
                                Género <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="detailGender"
                                    wire:key="select-detail-gender"
                                    class="{{ $selectClass }} {{ $errors->has('detailGender') ? 'border-red-400 focus:border-red-500 focus:ring-red-300/40' : '' }}">
                                <option value="">— Seleccionar —</option>
                                @foreach (\App\Livewire\Event\AttendanceRegistration::GENDER_OPTIONS as $opcion)
                                    <option value="{{ $opcion }}">{{ $opcion }}</option>
                                @endforeach
                            </select>
                            <p wire:key="error-detail-gender"
                               class="mt-1 text-xs text-red-500 dark:text-red-400 {{ $errors->has('detailGender') ? '' : 'hidden' }}">
                                {{ $errors->first('detailGender') }}
                            </p>
                        </div>

                        {{-- Teléfono --}}
                        <div>
                            <label class="{{ $labelClass }}">Teléfono / Celular</label>
                            <input
                                wire:model="detailPhone"
                                type="text"
                                inputmode="tel"
                                placeholder="Ej: 300 123 4567"
                                class="{{ $inputClass }}"
                            />
                        </div>

                        {{-- Municipio + Barrio en fila --}}
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="{{ $labelClass }}">Municipio</label>
                                <input
                                    wire:model="detailCity"
                                    type="text"
                                    placeholder="Ej: Riohacha"
                                    class="{{ $inputClass }}"
                                />
                            </div>
                            <div>
                                <label class="{{ $labelClass }}">Barrio</label>
                                <input
                                    wire:model="detailNeighborhood"
                                    type="text"
                                    placeholder="Ej: El Centro"
                                    class="{{ $inputClass }}"
                                />
                            </div>
                        </div>

                        {{-- Dirección --}}
                        <div>
                            <label class="{{ $labelClass }}">Dirección</label>
                            <input
                                wire:model="detailAddress"
                                type="text"
                                placeholder="Ej: Cra. 7 # 11-28"
                                class="{{ $inputClass }}"
                            />
                        </div>

                        {{-- Grupo priorizado --}}
                        <div wire:key="field-detail-priority-group">
                            <label class="{{ $labelClass }}">
                                Grupo priorizado <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="detailPriorityGroup"
                                    wire:key="select-detail-priority-group"
                                    class="{{ $selectClass }} {{ $errors->has('detailPriorityGroup') ? 'border-red-400 focus:border-red-500 focus:ring-red-300/40' : '' }}">
                                <option value="">— Seleccionar —</option>
                                @foreach (\App\Livewire\Event\AttendanceRegistration::GRUPOS_PRIORIZADOS as $grupo)
                                    <option value="{{ $grupo }}">{{ $grupo }}</option>
                                @endforeach
                            </select>
                            <p wire:key="error-detail-priority-group"
                               class="mt-1 text-xs text-red-500 dark:text-red-400 {{ $errors->has('detailPriorityGroup') ? '' : 'hidden' }}">
                                {{ $errors->first('detailPriorityGroup') }}
                            </p>
                        </div>

                        {{-- Correo (solo si no tiene email registrado) --}}
                        @if (!($participantData['has_email'] ?? true))
                            <div>
                                <label class="{{ $labelClass }}">
                                    Correo electrónico
                                    <span class="ml-1 rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">
                                        Sin registrar
                                    </span>
                                </label>
                                <input
                                    wire:model="detailEmail"
                                    type="email"
                                    inputmode="email"
                                    placeholder="Opcional — se guardará en tu perfil"
                                    class="{{ $inputClass }} {{ $errors->has('detailEmail') ? 'border-amber-400 focus:ring-amber-300/40' : '' }}"
                                />
                                @error('detailEmail')
                                    <p class="mt-1 flex items-center gap-1 text-xs text-red-500 dark:text-red-400">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        @endif

                    </div>

                    @error('confirm')
                        <div class="mt-3 flex items-center gap-2 rounded-xl
                                    bg-red-50 px-4 py-3 dark:bg-red-900/20">
                            <svg class="h-4 w-4 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"
                                      clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        </div>
                    @enderror
                </div>

                <div class="space-y-2.5 border-t border-gray-100 px-5 py-4 dark:border-zinc-700">
                    <button
                        wire:click="confirmWithDetails"
                        wire:loading.attr="disabled"
                        wire:target="confirmWithDetails"
                        type="button"
                        class="w-full rounded-xl py-3.5 px-6 text-base font-bold text-white shadow
                               transition-all duration-200 hover:opacity-90 active:scale-[.98]
                               disabled:cursor-not-allowed disabled:opacity-60"
                        style="background: linear-gradient(90deg, #cc5e50 0%, #b84a3d 100%)">
                        <span wire:loading.remove wire:target="confirmWithDetails"
                              class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4.5 12.75 6 14.25l3.75-7.5m4.5 4.5 1.5 1.5 3.75-7.5"/>
                            </svg>
                            Confirmar asistencia
                        </span>
                        <span wire:loading wire:target="confirmWithDetails"
                              class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Registrando…
                        </span>
                    </button>

                    <button
                        wire:click="backToSearch"
                        type="button"
                        class="w-full rounded-xl border border-neutral-300 bg-white py-3 px-6
                               text-sm font-semibold text-gray-600 transition
                               hover:bg-gray-50 active:scale-[.98]
                               dark:border-zinc-600 dark:bg-zinc-700 dark:text-gray-300 dark:hover:bg-zinc-600">
                        ← Volver al inicio
                    </button>
                </div>
            </div>
        @endif

        {{-- ─────────────────────────────────────────────────────────────
             STEP: duplicate
        ──────────────────────────────────────────────────────────────── --}}
        @if ($step === 'duplicate' && $participantData)
            @php
                $arColors   = ['#cc5e50', '#e2a542', '#62a9b6'];
                $arBg       = $arColors[$participantData['id'] % 3];
                $arInitials = mb_strtoupper(
                    mb_substr($participantData['first_name'], 0, 1) .
                    mb_substr($participantData['last_name'],  0, 1)
                );
            @endphp

            <div wire:transition
                 wire:key="step-duplicate"
                 class="overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-sm
                        dark:border-amber-900/40 dark:bg-zinc-800">

                <div class="flex items-start gap-3 px-5 py-4 text-white"
                     style="background-color: #e2a542">
                    <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0
                                 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898
                                 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                    </svg>
                    <div>
                        <p class="font-bold text-sm leading-tight">Ya tienes asistencia registrada</p>
                        @if ($duplicateRegisteredAt)
                            <p class="mt-0.5 text-xs text-white/90">
                                Registrada a las <strong>{{ $duplicateRegisteredAt }}</strong>
                            </p>
                        @endif
                    </div>
                </div>

                <div class="px-6 py-6">
                    <div class="mb-5 flex items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center
                                    rounded-full text-base font-extrabold text-white shadow"
                             style="background-color: {{ $arBg }}">
                            {{ $arInitials }}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-base font-bold text-gray-800 dark:text-gray-100">
                                {{ $participantData['first_name'] }} {{ $participantData['last_name'] }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-zinc-400">
                                {{ $participantData['document'] }}
                            </p>
                        </div>
                    </div>

                    @if (!empty($participantData['program']))
                        <p class="mb-5 text-center text-sm text-gray-500 dark:text-zinc-400">
                            {{ $participantData['program'] }}
                        </p>
                    @endif

                    <button
                        wire:click="backToSearch"
                        type="button"
                        class="w-full rounded-xl py-3 px-6 text-sm font-bold text-white
                               transition-all hover:opacity-90 active:scale-[.98]"
                        style="background-color: #e2a542">
                        ← Volver al inicio
                    </button>
                </div>
            </div>
        @endif

        {{-- ─────────────────────────────────────────────────────────────
             STEP: success
        ──────────────────────────────────────────────────────────────── --}}
        @if ($step === 'success' && $participantData)
            @php
                $arColors   = ['#cc5e50', '#e2a542', '#62a9b6'];
                $arBg       = $arColors[$participantData['id'] % 3];
                $arInitials = mb_strtoupper(
                    mb_substr($participantData['first_name'], 0, 1) .
                    mb_substr($participantData['last_name'],  0, 1)
                );
            @endphp

            <div wire:transition
                 wire:key="step-success"
                 class="rounded-2xl border border-neutral-200 bg-white shadow-sm
                        dark:border-zinc-700 dark:bg-zinc-800"
                 x-data="{
                     segundos: 5,
                     _timer: null,
                     init() {
                         this._timer = setInterval(() => {
                             if (--this.segundos <= 0) {
                                 clearInterval(this._timer);
                                 $wire.backToSearch();
                             }
                         }, 1000);
                     },
                     stop() { clearInterval(this._timer); }
                 }">

                <div class="px-6 py-9 text-center">

                    <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full"
                         style="background-color: #62a9b6">
                        <svg class="ar-draw-check h-10 w-10 text-white"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4.5 12.75 6 14.25l3.75-7.5m4.5 4.5 1.5 1.5 3.75-7.5"/>
                        </svg>
                    </div>

                    <h2 class="text-2xl font-extrabold text-gray-800 dark:text-gray-100">
                        ¡Asistencia registrada!
                    </h2>

                    <div class="mt-4 flex items-center justify-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center
                                    rounded-full text-sm font-bold text-white shadow"
                             style="background-color: {{ $arBg }}">
                            {{ $arInitials }}
                        </div>
                        <p class="text-base font-semibold text-gray-700 dark:text-gray-200">
                            {{ $participantData['first_name'] }} {{ $participantData['last_name'] }}
                        </p>
                    </div>

                    <div class="mt-4 space-y-1 text-sm text-gray-500 dark:text-zinc-400">
                        @if ($successRegisteredAt)
                            <p>
                                Registrado a las
                                <span class="font-semibold text-gray-700 dark:text-gray-200">
                                    {{ $successRegisteredAt }}
                                </span>
                            </p>
                        @endif
                        @if ($totalAttendances > 0)
                            <p>
                                Evento
                                <span class="font-semibold" style="color: #62a9b6">
                                    N.° {{ $totalAttendances }}
                                </span>
                                en tu historial
                            </p>
                        @endif
                    </div>

                    <div class="mt-5">
                        <p class="text-sm text-gray-400 dark:text-zinc-500">
                            Volviendo al inicio en
                            <span class="font-bold" style="color: #62a9b6" x-text="segundos"></span>
                            <span x-text="segundos === 1 ? ' segundo' : ' segundos'"></span>…
                        </p>
                        <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full
                                    bg-gray-200 dark:bg-zinc-700">
                            <div class="ar-shrink-bar h-full rounded-full"
                                 style="background-color: #62a9b6; width: 100%"></div>
                        </div>
                    </div>

                    <button
                        wire:click="backToSearch"
                        x-on:click="stop()"
                        type="button"
                        class="mt-5 w-full rounded-xl py-3 px-6 text-sm font-bold text-white
                               transition-all hover:opacity-90 active:scale-[.98]"
                        style="background: linear-gradient(90deg, #62a9b6 0%, #4d94a0 100%)">
                        Registrar otra persona
                    </button>
                </div>
            </div>
        @endif

    @endif {{-- fin tab asistencia --}}


</div>
