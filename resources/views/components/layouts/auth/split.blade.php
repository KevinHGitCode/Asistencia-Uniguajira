<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
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
        <div class="auth-bg-image" style="background-image: url('{{ asset('images/fondo-uniguajira.jpeg') }}')"></div>
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
