<x-layouts.app-nosidebar :title="$event->title ?? __('Registro de asistencia')">

    {{-- ══════════════════════════════════════════════════════════════════
         ESTILOS — decoraciones y animaciones suaves
    ══════════════════════════════════════════════════════════════════ --}}
    <style>
        /* Fondo decorativo del panel del formulario */
        .ev-access-main-bg {
            background-color: #f6f7fb;
            background-image:
                radial-gradient(circle at 15% 15%, rgba(98, 169, 182, .10), transparent 55%),
                radial-gradient(circle at 85% 85%, rgba(204, 94, 80, .08),  transparent 55%);
        }
        .dark .ev-access-main-bg {
            background-color: #0f1115;
            background-image:
                radial-gradient(circle at 15% 15%, rgba(98, 169, 182, .15), transparent 55%),
                radial-gradient(circle at 85% 85%, rgba(204, 94, 80, .10), transparent 55%);
        }

        /* Grid sutil como textura en el panel del formulario */
        .ev-access-grid::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background-image:
                linear-gradient(to right, rgba(0, 0, 0, .035) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(0, 0, 0, .035) 1px, transparent 1px);
            background-size: 32px 32px;
            -webkit-mask-image: radial-gradient(ellipse 70% 70% at 50% 40%, #000 40%, transparent 85%);
                    mask-image: radial-gradient(ellipse 70% 70% at 50% 40%, #000 40%, transparent 85%);
        }
        .dark .ev-access-grid::before {
            background-image:
                linear-gradient(to right,  rgba(255, 255, 255, .05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, .05) 1px, transparent 1px);
        }

        /* Barra decorativa superior del panel móvil (acento de marca) */
        .ev-access-accent-bar {
            height: 3px;
            background: linear-gradient(90deg, #62a9b6 0%, #4d94a0 45%, #cc5e50 100%);
        }

        /* Pulso del indicador "en vivo" */
        @@keyframes ev-access-pulse {
            0%, 100% { opacity: .9; transform: scale(1); }
            50%      { opacity: .4; transform: scale(1.25); }
        }
        .ev-access-live-dot {
            animation: ev-access-pulse 2s ease-in-out infinite;
        }
    </style>

    {{-- Cancelar el p-6 del layout → tomar control total del viewport --}}
    <div class="-m-6 flex min-h-dvh flex-col overflow-hidden lg:h-dvh lg:flex-row">

        {{-- ═══════════════════════════════════════════════════════════════
             PANEL DE EVENTO
             · Mobile : franja compacta superior
             · Desktop: columna izquierda a pantalla completa (lg:w-[44%])
        ══════════════════════════════════════════════════════════════════ --}}
        <aside
            class="relative shrink-0 overflow-hidden text-white
                   px-5 pt-6 pb-5
                   bg-cover bg-center bg-no-repeat
                   lg:flex lg:h-dvh lg:w-[44%] lg:flex-col lg:self-stretch lg:px-10 lg:pt-10 lg:pb-10"
            style="background-image: url('{{ asset('images/fondo-uniguajira.jpeg') }}');">

            {{-- Overlay con degradado que refuerza la identidad de marca --}}
            <div class="absolute inset-0"
                 style="background:
                    linear-gradient(135deg,
                        rgba(23, 23, 23, .88) 0%,
                        rgba(23, 23, 23, .72) 45%,
                        rgba(77, 148, 160, .72) 100%);"></div>

            {{-- Círculos decorativos --}}
            <span class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/10"></span>
            <span class="pointer-events-none absolute -left-14 bottom-8 h-52 w-52 rounded-full bg-white/10"></span>
            <span class="pointer-events-none absolute right-10 bottom-32 h-32 w-32 rounded-full bg-white/[.06]"></span>

            {{-- Barra de acento en borde inferior (móvil) / borde derecho (desktop) --}}
            <span class="pointer-events-none absolute inset-x-0 bottom-0 ev-access-accent-bar lg:inset-y-0 lg:left-auto lg:right-0 lg:h-full lg:w-[3px]"></span>

            {{-- ─────────────────────────────────────────────────────────
                 VISTA MOBILE: layout horizontal compacto
                 Logo | Título + detalles
            ──────────────────────────────────────────────────────────── --}}
            <div class="relative lg:hidden">

                {{-- Fila superior: logos en su propia línea --}}
                <div class="flex items-center justify-between gap-2">
                    <div class="shrink-0 flex items-center gap-1.5 rounded-xl bg-white/15 px-2 py-1.5 ring-1 ring-white/20 backdrop-blur-sm">
                        <img
                            src="{{ asset('images/logo-uniguajira-blanco.webp') }}"
                            alt="Uniguajira"
                            class="h-7 sm:h-8 w-auto object-contain">
                        <span class="block h-6 sm:h-7 w-px bg-white/30"></span>
                        <img
                            src="{{ asset('images/aura_blanco.png') }}"
                            alt="AURA"
                            class="h-7 sm:h-8 w-auto object-contain">
                    </div>

                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-400/15 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-emerald-300 ring-1 ring-emerald-400/30">
                        <span class="ev-access-live-dot h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        En vivo
                    </span>
                </div>

                {{-- Salto de línea: Control de asistencia + título del evento --}}
                <div class="mt-3">
                    <p class="text-[10px] font-bold uppercase tracking-[.2em] text-white/70">
                        Control de asistencia
                    </p>
                    <h1 class="mt-1 text-[17px] font-extrabold leading-snug text-white drop-shadow">
                        {{ $event->title }}
                    </h1>
                </div>

                {{-- Fila de detalles compacta --}}
                <div class="mt-3 flex flex-wrap gap-x-3.5 gap-y-1.5 text-[11px] text-white/90">
                    @if ($event->date)
                        <span class="flex items-center gap-1.5 rounded-full bg-white/10 px-2 py-0.5 ring-1 ring-white/10">
                            <svg class="h-3 w-3 opacity-80" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8"  y1="2" x2="8"  y2="6"/>
                                <line x1="3"  y1="10" x2="21" y2="10"/>
                            </svg>
                            {{ \Carbon\Carbon::parse($event->date)->isoFormat('D [de] MMM') }}
                        </span>
                    @endif

                    @if ($event->start_time && $event->end_time)
                        <span class="flex items-center gap-1.5 rounded-full bg-white/10 px-2 py-0.5 ring-1 ring-white/10">
                            <svg class="h-3 w-3 opacity-80" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                            </svg>
                            {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}
                            &ndash;
                            {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }}
                        </span>
                    @endif

                    @if ($event->location)
                        <span class="flex items-center gap-1.5 rounded-full bg-white/10 px-2 py-0.5 ring-1 ring-white/10 max-w-full truncate">
                            <svg class="h-3 w-3 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span class="truncate">{{ $event->location }}</span>
                        </span>
                    @endif
                </div>
            </div>

            {{-- ─────────────────────────────────────────────────────────
                 VISTA DESKTOP: columna completa con logo, título y detalles
            ──────────────────────────────────────────────────────────── --}}
            <div class="relative hidden lg:flex lg:h-full lg:flex-col pb-10">

                {{-- Logo fijo arriba --}}
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex min-w-0 max-w-full items-center gap-3 lg:gap-4 xl:gap-5 rounded-2xl bg-white/15 px-4 py-3 lg:px-5 xl:px-6 xl:py-4 ring-1 ring-white/20 backdrop-blur-sm">
                        <img
                            src="{{ asset('images/logo-uniguajira-blanco.webp') }}"
                            alt="Universidad de La Guajira"
                            class="h-10 lg:h-12 xl:h-16 2xl:h-20 w-auto object-contain">
                        <span class="block h-8 lg:h-10 xl:h-14 2xl:h-16 w-px bg-white/30"></span>
                        <img
                            src="{{ asset('images/aura_blanco.png') }}"
                            alt="AURA"
                            class="h-10 lg:h-12 xl:h-16 2xl:h-20 w-auto object-contain">
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/15 px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest text-emerald-300 ring-1 ring-emerald-400/30">
                        <span class="ev-access-live-dot h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Evento en vivo
                    </span>
                </div>

                {{-- Bloque central --}}
                <div class="flex flex-1 flex-col justify-center">

                    <p class="text-[10px] font-bold uppercase tracking-[.22em] text-white/70">
                        Control de asistencia
                    </p>
                    <h1 class="mt-2 text-3xl font-extrabold leading-snug text-white drop-shadow xl:text-[2rem]">
                        {{ $event->title }}
                    </h1>

                    {{-- Línea decorativa --}}
                    <span class="mt-5 block h-1 w-16 rounded-full"
                          style="background: linear-gradient(90deg, #62a9b6 0%, #cc5e50 100%);"></span>

                    <ul class="mt-6 space-y-3 text-sm text-white/90">
                        @if ($event->date)
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/10 ring-1 ring-white/15">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                                        <line x1="16" y1="2" x2="16" y2="6"/>
                                        <line x1="8"  y1="2" x2="8"  y2="6"/>
                                        <line x1="3"  y1="10" x2="21" y2="10"/>
                                    </svg>
                                </span>
                                <span class="pt-0.5">{{ \Carbon\Carbon::parse($event->date)->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</span>
                            </li>
                        @endif

                        @if ($event->start_time && $event->end_time)
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/10 ring-1 ring-white/15">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                    </svg>
                                </span>
                                <span class="pt-0.5">
                                    {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}
                                    &ndash;
                                    {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }}
                                </span>
                            </li>
                        @endif

                        @if ($event->location)
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/10 ring-1 ring-white/15">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                </span>
                                <span class="pt-0.5">{{ $event->location }}</span>
                            </li>
                        @endif
                    </ul>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between text-xs text-white/50">
                    <p>&copy; {{ date('Y') }} Universidad de La Guajira</p>
                    <p class="font-mono tracking-wider">AURA</p>
                </div>
            </div>
        </aside>

        {{-- ═══════════════════════════════════════════════════════════════
             PANEL DEL FORMULARIO
             · flex-1 ocupa todo el espacio restante
             · overflow-y-auto permite scroll solo si el card no cabe
             · ev-access-main-bg crea un fondo sutil con color
        ══════════════════════════════════════════════════════════════════ --}}
        <main class="relative ev-access-main-bg ev-access-grid
                     flex flex-1 flex-col items-center overflow-y-auto
                     px-4 py-6 sm:px-6 lg:py-10">

            <div class="relative z-10 my-auto w-full max-w-sm">
                <livewire:event.attendance-registration :slug="$event->link" />
            </div>

            {{-- Créditos inferiores (solo móvil — en desktop están en la aside) --}}
            <p class="relative z-10 mt-6 text-center text-[11px] text-gray-400 dark:text-zinc-500 lg:hidden">
                &copy; {{ date('Y') }} Universidad de La Guajira
            </p>
        </main>

    </div>
</x-layouts.app-nosidebar>
