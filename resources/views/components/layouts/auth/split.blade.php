<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <style>
            /* ============================================================
               LOGIN — Estética sobria
               ============================================================ */

            html, body {
                margin: 0; padding: 0;
                width: 100%;
                background: #0b0b0d;
                overflow-x: hidden;
            }
            body {
                position: relative;
                min-height: 100dvh;
            }

            /* Capas de fondo (foto y grain) cubren todo el viewport */
            .auth-bg-image,
            .auth-bg-grain {
                position: fixed;
                inset: 0;
                pointer-events: none;
            }
            .auth-bg-veil,
            .auth-bg-veil-right { pointer-events: none; }

            .auth-bg-image {
                background-image: url('{{ asset('images/fondo-uniguajira.jpeg') }}');
                background-size: cover;
                background-position: center;
                z-index: 0;
            }

            /* Velo del lado del logo — negro plano, solo en desktop, mitad izquierda */
            .auth-bg-veil {
                position: fixed;
                top: 0; bottom: 0;
                left: 0;
                width: 50%;
                background: rgba(0, 0, 0, 0.75);
                z-index: 1;
                display: none;
            }
            @media (min-width: 1024px) {
                .auth-bg-veil { display: block; }
            }

            /* Velo del lado del formulario — negro plano, más claro.
               En móvil cubre todo el viewport. En desktop solo la mitad derecha. */
            .auth-bg-veil-right {
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.50);
                z-index: 1;
            }
            @media (min-width: 1024px) {
                .auth-bg-veil-right {
                    left: auto;
                    right: 0;
                    width: 50%;
                }
            }

            /* Grain sutil para textura cinematográfica */
            .auth-bg-grain {
                background-image:
                    radial-gradient(rgba(255,255,255,0.025) 1px, transparent 1px);
                background-size: 3px 3px;
                opacity: 0.6;
                z-index: 2;
                mix-blend-mode: overlay;
            }

            /* Un único acento dorado tenue, en una esquina */
            .accent-glow {
                position: fixed;
                top: -200px;
                right: -200px;
                width: 500px;
                height: 500px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(226, 165, 66, 0.15), transparent 60%);
                filter: blur(40px);
                pointer-events: none;
                z-index: 3;
                animation: accent-drift 20s ease-in-out infinite alternate;
            }
            @keyframes accent-drift {
                from { transform: translate(0, 0); }
                to   { transform: translate(-40px, 30px); }
            }

            /* Partículas muy sutiles */
            .particles {
                position: fixed;
                inset: 0;
                overflow: hidden;
                pointer-events: none;
                z-index: 4;
            }
            .particle {
                position: absolute;
                background: rgba(255,255,255,0.5);
                border-radius: 50%;
                animation: particle-rise linear infinite;
            }
            @keyframes particle-rise {
                0%   { transform: translateY(0)    scale(0.6); opacity: 0; }
                15%  { opacity: 0.7; }
                85%  { opacity: 0.6; }
                100% { transform: translateY(-110vh) scale(1); opacity: 0; }
            }

            /* Card del formulario — siempre glass oscuro, independiente del tema */
            .login-card {
                position: relative;
                background: rgba(18, 18, 22, 0.62);
                backdrop-filter: blur(16px) saturate(1.1);
                -webkit-backdrop-filter: blur(16px) saturate(1.1);
                border: 1px solid rgba(255,255,255,0.08);
                border-radius: 14px;
                box-shadow: 0 20px 50px -20px rgba(0,0,0,0.55);
                animation: card-in 700ms cubic-bezier(0.16, 1, 0.3, 1) both;
            }
            @keyframes card-in {
                from { opacity: 0; transform: translateY(14px); }
                to   { opacity: 1; transform: translateY(0); }
            }

            /* Panel izquierdo — entrada animada */
            .left-panel-in {
                animation: left-in 800ms cubic-bezier(0.16, 1, 0.3, 1) both;
            }
            @keyframes left-in {
                from { opacity: 0; transform: translateY(10px); }
                to   { opacity: 1; transform: translateY(0); }
            }

            /* Cita inferior — barra discreta */
            .quote-bar {
                width: 2px;
                background: rgba(255,255,255,0.25);
                border-radius: 1px;
            }

            /* Slider de features — la tira se desliza horizontalmente.
               Defino la transición en CSS porque Alpine sobreescribe el atributo style
               cuando hay :style binding, y se perdía la propiedad 'transition'. */
            .carousel-strip {
                display: flex;
                height: 100%;
                transition: transform 850ms cubic-bezier(0.22, 1, 0.36, 1);
                will-change: transform;
            }
            .carousel-item {
                transition: opacity 600ms ease, filter 600ms ease;
            }
            .carousel-item.is-inactive {
                opacity: 0.35;
                filter: blur(2px);
            }

            /* ============================================================
               PORTAL DE TRANSICIÓN (login → dashboard)
               ============================================================ */
            .portal-overlay {
                position: fixed;
                inset: 0;
                z-index: 9999;
                pointer-events: none;
                opacity: 0;
                background: transparent;
                transition: opacity 200ms ease;
            }
            .portal-overlay.is-active {
                pointer-events: all;
                opacity: 1;
            }

            /* Destello: elemento circular que escala desde el centro.
               Animar transform es GPU-accelerated, siempre fluido. */
            .portal-flash {
                position: absolute;
                top: 50%; left: 50%;
                width: 120px; height: 120px;
                margin-left: -60px; margin-top: -60px;
                border-radius: 50%;
                background: radial-gradient(circle, #ffffff 60%, #e2a542 100%);
                transform: scale(0);
                opacity: 0;
                will-change: transform, opacity;
            }
            html.dark .portal-flash {
                background: radial-gradient(circle, #06060a 60%, #1a0e0c 100%);
            }
            .portal-overlay.is-active .portal-flash {
                animation: flash-expand 1200ms cubic-bezier(0.65, 0, 0.35, 1) forwards;
            }
            @keyframes flash-expand {
                0%   { transform: scale(0);   opacity: 0; }
                15%  { opacity: 1; }
                100% { transform: scale(60);  opacity: 1; }
            }
            /* Anillo único que combina los 3 colores institucionales:
               border + dos box-shadow con spread → 3 aros concéntricos en un solo elemento.
               Una sola animación, totalmente fluida. */
            .portal-ring {
                position: absolute;
                top: 50%; left: 50%;
                width: 0; height: 0;
                border-radius: 50%;
                border: 10px solid #e2a542;           /* aro dorado interior (10px) */
                box-shadow:
                    0 0 0 10px #cc5e50,               /* aro coral medio (10px más) */
                    0 0 0 20px #62a9b6,               /* aro teal exterior (10px más) */
                    0 0 90px 14px rgba(226,165,66,0.6); /* halo dorado */
                transform: translate(-50%,-50%);
                opacity: 0;
                will-change: width, height, opacity;
            }
            .portal-overlay.is-active .portal-ring {
                animation: portal-ring 1200ms cubic-bezier(0.65, 0, 0.35, 1) forwards;
            }
            @keyframes portal-ring {
                0%   { width: 0;     height: 0;     opacity: 0; }
                15%  { opacity: 1; }
                70%  { opacity: 0.9; }
                100% { width: 220vmax; height: 220vmax; opacity: 0; }
            }
            .portal-logo {
                position: absolute;
                top: 50%; left: 50%;
                transform: translate(-50%,-50%) scale(0.6);
                opacity: 0;
                max-width: 240px;
                filter: drop-shadow(0 0 40px rgba(226,165,66,1));
            }
            /* Por defecto (tema claro): se ve el logo NEGRO sobre el destello blanco */
            .portal-logo-light { display: block; }
            .portal-logo-dark  { display: none; }
            /* En tema oscuro se invierte: logo BLANCO sobre destello negro */
            html.dark .portal-logo-light { display: none; }
            html.dark .portal-logo-dark  { display: block; }
            .portal-overlay.is-active .portal-logo {
                animation: portal-logo 1200ms cubic-bezier(0.65, 0, 0.35, 1) forwards;
            }
            @keyframes portal-logo {
                0%   { transform: translate(-50%,-50%) scale(0.4); opacity: 0; }
                40%  { transform: translate(-50%,-50%) scale(1.0); opacity: 1; }
                85%  { transform: translate(-50%,-50%) scale(1.4); opacity: 1; }
                100% { transform: translate(-50%,-50%) scale(8);   opacity: 0; }
            }

            /* Contenido del split — fade out cuando entra el portal */
            .split-content { transition: opacity 350ms ease, transform 600ms ease, filter 600ms ease; }
            .split-content.fading {
                opacity: 0;
                transform: scale(0.97);
                filter: blur(8px);
            }

            /* ============================================================
               RESPONSIVE — corrige los huecos negros en móvil
               ============================================================ */
            .split-wrap {
                position: relative;
                z-index: 10;
                min-height: 100dvh;
                width: 100%;
                display: grid;
                grid-template-columns: 1fr;
                align-items: center;
                padding: 1.25rem;
            }
            @media (min-width: 1024px) {
                .split-wrap {
                    grid-template-columns: 1fr 1fr;
                    padding: 0;
                }
            }

            /* Reduce motion */
            @media (prefers-reduced-motion: reduce) {
                .accent-glow, .particle, .login-card, .left-panel-in { animation: none !important; }
            }
        </style>
    </head>
    <body class="antialiased dark"
          x-data="{
              portalActive: false,
              triggerPortal(target) {
                  this.portalActive = true;
                  try { sessionStorage.setItem('aura-from-login', '1'); } catch (e) {}
                  setTimeout(function () { window.location.href = target; }, 1100);
              }
          }"
          x-on:show-login-portal.window="triggerPortal($event.detail.target || '/dashboard')">

        <!-- Capas de fondo (fixed: cubren siempre todo el viewport) -->
        <div class="auth-bg-image"></div>
        <div class="auth-bg-veil-right"></div> {{-- velo claro del lado del formulario --}}
        <div class="auth-bg-veil"></div>       {{-- velo oscuro del lado del logo (encima, recortado al 50% izquierdo) --}}
        <div class="auth-bg-grain"></div>
        <div class="accent-glow"></div>

        <!-- Partículas suaves -->
        <div class="particles">
            @for ($i = 0; $i < 14; $i++)
                @php
                    $left = rand(0, 100);
                    $size = rand(1, 3);
                    $duration = rand(20, 40);
                    $delay = rand(0, 30);
                    $opacity = rand(20, 50) / 100;
                @endphp
                <span class="particle"
                      style="left: {{ $left }}%;
                             bottom: -10px;
                             width: {{ $size }}px;
                             height: {{ $size }}px;
                             animation-duration: {{ $duration }}s;
                             animation-delay: -{{ $delay }}s;
                             opacity: {{ $opacity }};"></span>
            @endfor
        </div>

        <!-- Contenido principal -->
        <div class="split-wrap split-content"
             :class="{ 'fading': portalActive }">

            <!-- ===================== LADO IZQUIERDO ===================== -->
            <div class="left-panel-in relative hidden h-full flex-col p-10 text-white lg:flex lg:border-r lg:border-white/10">
                <!-- Bienvenida y Features -->
                <div class="relative z-20 my-auto p-3 space-y-7">
                    <div class="space-y-3">
                        <div class="flex justify-center">
                            <x-app-logo-icon />
                        </div>
                        <p class="text-sm text-white/70 leading-relaxed text-center max-w-md mx-auto">
                            Accede a tu cuenta y descubre todas las herramientas que tenemos para ti.
                        </p>
                    </div>

                    <!-- ============ CARRUSEL DE FEATURES ============ -->
                    <div
                        x-data="{
                            current: 0,
                            features: [
                                { title: 'Crea Eventos',          desc: 'Organiza y administra tus actividades fácilmente',           icon: 'calendar' },
                                { title: 'Registra Asistencias',  desc: 'Controla la participación de tus asistentes en tiempo real', icon: 'check' },
                                { title: 'Obtén Estadísticas',    desc: 'Analiza el rendimiento y genera reportes completos',         icon: 'chart-bar' },
                                { title: 'Genera Códigos QR',     desc: 'Confirmación de asistencia con un solo escaneo',              icon: 'qr-code' },
                                { title: 'Exporta Reportes',      desc: 'Descarga informes en PDF y Excel listos para compartir',     icon: 'document-arrow-down' },
                            ],
                            timer: null,
                            start() {
                                this.timer = setInterval(() => {
                                    this.current = (this.current + 1) % this.features.length;
                                }, 4200);
                            },
                            go(i) {
                                clearInterval(this.timer);
                                this.current = i;
                                this.start();
                            }
                        }"
                        x-init="start()"
                        x-on:beforeunload.window="clearInterval(timer)"
                        class="relative max-w-md mx-auto"
                    >
                        <!-- Slider horizontal: la tira se desplaza, los items nunca se superponen -->
                        <div class="relative overflow-hidden h-24 rounded-xl bg-white/8 backdrop-blur-sm border border-white/10">
                            <div class="carousel-strip"
                                 :style="`transform: translateX(-${current * 100}%);`">
                                <template x-for="(feature, i) in features" :key="i">
                                    <div class="carousel-item w-full shrink-0 flex items-center gap-3 p-3"
                                         :class="{ 'is-inactive': current !== i }">
                                        <div class="w-12 h-12 rounded-lg flex items-center justify-center shrink-0 bg-white/10 border border-white/10">
                                            <flux:icon name="calendar"            x-show="feature.icon === 'calendar'"            class="w-6 h-6 text-white/85" />
                                            <flux:icon name="check"               x-show="feature.icon === 'check'"               class="w-6 h-6 text-white/85" />
                                            <flux:icon name="chart-bar"           x-show="feature.icon === 'chart-bar'"           class="w-6 h-6 text-white/85" />
                                            <flux:icon name="qr-code"             x-show="feature.icon === 'qr-code'"             class="w-6 h-6 text-white/85" />
                                            <flux:icon name="document-arrow-down" x-show="feature.icon === 'document-arrow-down'" class="w-6 h-6 text-white/85" />
                                        </div>
                                        <div class="min-w-0">
                                            <h3 class="font-medium text-white text-base" x-text="feature.title"></h3>
                                            <p class="text-white/60 text-sm leading-snug" x-text="feature.desc"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Indicadores de progreso -->
                        <div class="flex justify-center gap-1.5 mt-3">
                            <template x-for="(feature, i) in features" :key="i">
                                <button type="button"
                                        x-on:click="go(i)"
                                        class="h-1 rounded-full transition-all duration-500 cursor-pointer"
                                        :class="current === i ? 'w-6 bg-white/80' : 'w-1.5 bg-white/25 hover:bg-white/45'"
                                        :aria-label="`Ver ${feature.title}`">
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Cita inspiradora -->
                @php
                    [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                @endphp
                <div class="relative z-20 mt-auto flex items-stretch gap-3 max-w-md">
                    <div class="quote-bar"></div>
                    <blockquote class="space-y-0.5">
                        <flux:heading size="base" class="!text-white/85 !font-normal">&ldquo;{{ trim($message) }}&rdquo;</flux:heading>
                        <footer><flux:heading size="sm" class="!text-white/50">— {{ trim($author) }}</flux:heading></footer>
                    </blockquote>
                </div>
            </div>

            <!-- ===================== LADO DERECHO (LOGIN) ===================== -->
            <div class="flex items-center justify-center w-full">
                <div class="login-card w-full max-w-[420px] p-1 flex flex-col">
                    <div class="flex justify-center pt-4 lg:hidden">
                        <x-app-logo-icon />
                    </div>
                    {{ $slot }}
                </div>
            </div>
        </div>

        <!-- ===================== OVERLAY DE TRANSICIÓN ===================== -->
        <div class="portal-overlay" :class="{ 'is-active': portalActive }">
            <div class="portal-flash"></div>
            {{-- Anillo único que combina los 3 colores institucionales --}}
            <div class="portal-ring"></div>
            {{-- Tema claro: destello blanco → logo NEGRO (legible sobre blanco) --}}
            <img src="{{ asset('images/aura_negro.png') }}"  alt="" class="portal-logo portal-logo-light">
            {{-- Tema oscuro: destello negro → logo BLANCO (legible sobre negro) --}}
            <img src="{{ asset('images/aura_blanco.png') }}" alt="" class="portal-logo portal-logo-dark">
        </div>

        @fluxScripts
    </body>
</html>
