<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="relative grid h-dvh flex-col items-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            
            <div class="absolute inset-0 z-[-10] bg-cover" style="background-image: url('{{ asset('images/fondo-uniguajira.jpeg') }}');"></div>
            
            <!-- Left side -->
            <div class="bg-muted relative hidden h-full flex-col p-10 text-white lg:flex dark:border-e dark:border-neutral-800">
                <div class="absolute inset-0 bg-neutral-900/45"></div>

                <!-- Bienvenida y Features -->
                <div class="relative z-20 my-auto p-3 space-y-4">
                    <div class="space-y-2">
                        <h1 class="flex items-center text-4xl font-bold leading-tight">
                                <x-app-logo-icon />
                                <span>Bienvenido a 
                                    <span class="text-[#ad3728] drop-shadow-lg">
                                        {{ config('app.name', 'Laravel') }}
                                    </span>
                                </span>
                        </h1>
                        <p class="text-sm text-gray-300 leading-relaxed">
                            Accede a tu cuenta y descubre todas las herramientas que tenemos para ti.
                        </p>
                    </div>

                    <!-- Feature highlights -->
                    <div class="space-y-4">
                        <x-feature-box 
                            title="Crea Eventos" 
                            description="Organiza y administra tus actividades fácilmente" 
                            icon-type="calendar"
                        />
                        <x-feature-box 
                            title="Registra Asistencias" 
                            description="Controla la participación de tus asistentes en tiempo real" 
                            icon-type="check"
                        />
                        <x-feature-box 
                            title="Obtén Estadísticas" 
                            description="Analiza el rendimiento y genera reportes completos" 
                            icon-type="chart-bar"
                        />
                    </div>

                </div>

                <!-- Cita -->
                @php
                    [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                @endphp
                <div class="absolute bottom-6 z-20 mt-auto">
                    <blockquote class="space-y-1">
                        <flux:heading size="base">&ldquo;{{ trim($message) }}&rdquo;</flux:heading>
                        <footer><flux:heading size="sm">{{ trim($author) }}</flux:heading></footer>
                    </blockquote>
                </div>
            </div>

            <!-- Right side (Login) -->
            <div class="h-full flex items-center">
                <div class="absolute inset-0 bg-neutral-900/35"></div>
                <div class="bg-neutral-900/70 mx-auto p-3 flex w-full flex-col justify-center space-y-6 sm:w-[400px] rounded-lg">
                    <a class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md">
                            <x-app-logo-icon />
                        </span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
