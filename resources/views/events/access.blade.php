<x-layouts.app-nosidebar :title="$event->title ?? __('Registro de asistencia')">

    {{-- Cancelar el p-6 del layout → toma control total del viewport --}}
    <div class="-m-6 flex h-screen flex-col overflow-hidden lg:flex-row">

        {{-- ═══════════════════════════════════════════════════════════════
             PANEL DE EVENTO
             · Mobile : franja compacta superior (shrink-0, solo lo necesario)
             · Desktop: columna izquierda a pantalla completa (lg:w-[44%])
        ══════════════════════════════════════════════════════════════════ --}}
        <aside
            class="relative shrink-0 overflow-hidden text-white
                   px-5 pt-5 pb-5
                   lg:flex lg:w-[44%] lg:h-full lg:flex-col lg:px-10 lg:pt-10 lg:pb-10"
            style="background: linear-gradient(145deg, #cc5e50 0%, #e2a542 52%, #62a9b6 100%)">

            {{-- Círculos decorativos (solo notables en desktop) --}}
            <span class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/10"></span>
            <span class="pointer-events-none absolute -left-14 bottom-8   h-52 w-52 rounded-full bg-white/10"></span>
            <span class="pointer-events-none absolute  right-10 bottom-32 h-32 w-32 rounded-full bg-white/[.06]"></span>

            {{-- ─────────────────────────────────────────────────────────
                 VISTA MOBILE: layout horizontal compacto
                 Logo | Título + badge
                 Fecha · Hora · Lugar  (una sola línea)
            ──────────────────────────────────────────────────────────── --}}
            <div class="relative lg:hidden">

                {{-- Fila superior: logo + título --}}
                <div class="flex items-start gap-3">
                    <img
                        src="{{ asset('images/logo-uniguajira-blanco.webp') }}"
                        alt="Uniguajira"
                        class="mt-0.5 h-9 w-auto shrink-0 object-contain">

                    <div class="min-w-0 flex-1">
                        <p class="text-[9px] font-bold uppercase tracking-[.2em] text-white/60">
                            Control de asistencia
                        </p>
                        <h1 class="mt-0.5 text-base font-extrabold leading-snug text-white drop-shadow">
                            {{ $event->title }}
                        </h1>
                    </div>
                </div>

                {{-- Fila de detalles: compacta, una sola línea scrollable si fuera muy largo --}}
                <div class="mt-2.5 flex flex-wrap gap-x-4 gap-y-1 text-xs text-white/90">
                    @if ($event->date)
                        <span class="flex items-center gap-1">
                            <svg class="h-3 w-3 opacity-75" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8"  y1="2" x2="8"  y2="6"/>
                                <line x1="3"  y1="10" x2="21" y2="10"/>
                            </svg>
                            {{ \Carbon\Carbon::parse($event->date)->isoFormat('D [de] MMM, YYYY') }}
                        </span>
                    @endif

                    @if ($event->start_time && $event->end_time)
                        <span class="flex items-center gap-1">
                            <svg class="h-3 w-3 opacity-75" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                            </svg>
                            {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}
                            &ndash;
                            {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }}
                        </span>
                    @endif

                    @if ($event->location)
                        <span class="flex items-center gap-1">
                            <svg class="h-3 w-3 opacity-75" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            {{ $event->location }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- ─────────────────────────────────────────────────────────
                 VISTA DESKTOP: columna completa con logo, ícono, título
                 y detalles con espaciado generoso
            ──────────────────────────────────────────────────────────── --}}
            <div class="relative hidden lg:flex lg:h-full lg:flex-col">

                {{-- Logo --}}
                <img
                    src="{{ asset('images/logo-uniguajira-blanco.webp') }}"
                    alt="Universidad de La Guajira"
                    class="h-12 w-auto object-contain">

                {{-- Bloque central —centrado verticalmente --}}
                <div class="flex flex-1 flex-col justify-center">
                    <div class="mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20">
                        <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0
                                     2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0
                                     2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4"/>
                        </svg>
                    </div>

                    <p class="text-[10px] font-bold uppercase tracking-[.22em] text-white/60">
                        Control de asistencia
                    </p>
                    <h1 class="mt-2 text-3xl font-extrabold leading-snug text-white drop-shadow xl:text-[2rem]">
                        {{ $event->title }}
                    </h1>

                    <ul class="mt-6 space-y-2.5 text-sm text-white/90">
                        @if ($event->date)
                            <li class="flex items-start gap-2.5">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 opacity-75"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                                    <line x1="16" y1="2" x2="16" y2="6"/>
                                    <line x1="8"  y1="2" x2="8"  y2="6"/>
                                    <line x1="3"  y1="10" x2="21" y2="10"/>
                                </svg>
                                <span>{{ \Carbon\Carbon::parse($event->date)->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</span>
                            </li>
                        @endif

                        @if ($event->start_time && $event->end_time)
                            <li class="flex items-center gap-2.5">
                                <svg class="h-4 w-4 shrink-0 opacity-75"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                </svg>
                                <span>
                                    {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}
                                    &ndash;
                                    {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }}
                                </span>
                            </li>
                        @endif

                        @if ($event->location)
                            <li class="flex items-start gap-2.5">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 opacity-75"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                                <span>{{ $event->location }}</span>
                            </li>
                        @endif
                    </ul>
                </div>

                {{-- Footer --}}
                <p class="text-xs text-white/40">
                    &copy; {{ date('Y') }} Universidad de La Guajira
                </p>
            </div>
        </aside>

        {{-- ═══════════════════════════════════════════════════════════════
             PANEL DEL FORMULARIO
             · flex-1 ocupa todo el espacio restante
             · my-auto en el wrapper centra verticalmente cuando hay espacio
             · overflow-y-auto permite scroll solo si el card no cabe
        ══════════════════════════════════════════════════════════════════ --}}
        <main class="flex flex-1 flex-col items-center overflow-y-auto
                      bg-gray-50 dark:bg-zinc-900 px-4 py-5 lg:py-6">

            <div class="my-auto w-full max-w-sm">
                <livewire:event.attendance-registration :slug="$event->link" />
            </div>
        </main>

    </div>
</x-layouts.app-nosidebar>
