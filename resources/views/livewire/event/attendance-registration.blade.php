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
         STEP: search
    ══════════════════════════════════════════════════════════════════ --}}
    @if ($step === 'search')
        <div wire:transition
             class="rounded-2xl border border-neutral-200 bg-white shadow-sm
                    dark:border-zinc-700 dark:bg-zinc-800">
            <div class="px-6 pt-8 pb-6">

                <div class="mb-6 text-center">
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full"
                         style="background: rgba(98,169,182,.12)">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="1.8" style="color: #62a9b6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">Ingresa tu documento</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-zinc-400">
                        Buscaremos tu perfil para confirmar la asistencia
                    </p>
                </div>

                <div class="mb-4">
                    <label for="ar-identification"
                           class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Número de documento
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
                        class="w-full rounded-xl border px-4 py-3.5 text-xl font-semibold tracking-wider
                               bg-white dark:bg-zinc-700
                               text-gray-900 dark:text-gray-100
                               placeholder-gray-300 dark:placeholder-zinc-500
                               transition focus:outline-none focus:ring-2
                               {{ $errors->has('identification')
                                    ? 'border-red-400 focus:border-red-400 focus:ring-red-300/40 dark:border-red-600'
                                    : 'border-neutral-300 dark:border-zinc-600 focus:border-[#62a9b6] focus:ring-[#62a9b6]/25' }}"
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

                <button
                    wire:click="search"
                    wire:loading.attr="disabled"
                    wire:target="search"
                    type="button"
                    class="w-full rounded-xl py-3.5 px-6 text-base font-bold text-white shadow
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
                        Buscar
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

            <p class="border-t border-neutral-100 px-6 py-3 text-center text-xs
                       text-gray-400 dark:border-zinc-700 dark:text-zinc-500">
                Ingresa el documento exactamente como aparece en tu cédula
            </p>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════
         STEP: not_found
    ══════════════════════════════════════════════════════════════════ --}}
    @if ($step === 'not_found')
        <div wire:transition
             class="rounded-2xl border border-red-200 bg-white shadow-sm
                    dark:border-red-900/40 dark:bg-zinc-800">
            <div class="px-6 py-10 text-center">

                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center
                            rounded-full bg-red-50 dark:bg-red-900/20">
                    <svg class="h-8 w-8 text-red-500 dark:text-red-400"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                    </svg>
                </div>

                <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100">Documento no encontrado</h2>
                <p class="mt-2 text-sm text-gray-500 dark:text-zinc-400">
                    El número
                    <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $identification }}</span>
                    no está registrado en el sistema.
                </p>
                <p class="mt-1 text-xs text-gray-400 dark:text-zinc-500">
                    Verifica que lo hayas escrito correctamente o contacta al organizador.
                </p>

                <button
                    wire:click="backToSearch"
                    type="button"
                    class="mt-7 inline-flex items-center gap-2 rounded-xl border border-neutral-300
                           bg-white px-6 py-3 text-sm font-semibold text-gray-700
                           transition hover:bg-gray-50 active:scale-[.98]
                           dark:border-zinc-600 dark:bg-zinc-700 dark:text-gray-200 dark:hover:bg-zinc-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                    </svg>
                    Intentar de nuevo
                </button>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════
         STEP: found
    ══════════════════════════════════════════════════════════════════ --}}
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
                    ['Documento',  $participantData['document'] ?? null],
                    ['Programa',   $participantData['program'] ?? null],
                    ['Rol',        $participantData['role'] ?? null],
                    ['Afiliación', $participantData['affiliation'] ?? null],
                    ['Correo',     $participantData['email'] ?? null],
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
            </div>

            @error('confirm')
                <div class="mx-6 mb-3 flex items-center gap-2 rounded-xl
                            bg-red-50 px-4 py-3 dark:bg-red-900/20">
                    <svg class="h-4 w-4 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                              d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"
                              clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                </div>
            @enderror

            <div class="space-y-2.5 px-6 pb-6">
                <button
                    wire:click="confirm"
                    wire:loading.attr="disabled"
                    wire:target="confirm"
                    type="button"
                    class="w-full rounded-xl py-3.5 px-6 text-base font-bold text-white shadow
                           transition-all duration-200 hover:opacity-90 active:scale-[.98]
                           disabled:cursor-not-allowed disabled:opacity-60"
                    style="background: linear-gradient(90deg, #cc5e50 0%, #b84a3d 100%)">
                    <span wire:loading.remove wire:target="confirm"
                          class="flex items-center justify-center gap-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M4.5 12.75 6 14.25l3.75-7.5m4.5 4.5 1.5 1.5 3.75-7.5"/>
                        </svg>
                        Sí, registrar mi asistencia
                    </span>
                    <span wire:loading wire:target="confirm"
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
                    No soy yo
                </button>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════
         STEP: duplicate
    ══════════════════════════════════════════════════════════════════ --}}
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

    {{-- ══════════════════════════════════════════════════════════════════
         STEP: success
    ══════════════════════════════════════════════════════════════════ --}}
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
</div>
