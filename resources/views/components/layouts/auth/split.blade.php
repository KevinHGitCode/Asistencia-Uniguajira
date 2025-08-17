<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
            
            <!-- Left side -->
            <div class="bg-muted relative hidden h-full flex-col p-10 text-white lg:flex dark:border-e dark:border-neutral-800">
                <div class="absolute inset-0 bg-neutral-900"></div>

                <!-- Bienvenida y Features -->
                <div class="relative z-20 mt-8 space-y-6">
                    <div class="space-y-4">
                        <h1 class="text-4xl font-bold leading-tight">
                            <span class="flex items-center justify-center rounded-md">
                                <x-app-logo-icon />
                                <span>Bienvenido a 
                                    <span class="text-[#ad3728] drop-shadow-lg">
                                        {{ config('app.name', 'Laravel') }}
                                    </span>
                                </span>
                            </span>
                        </h1>
                        <p class="text-lg text-gray-300 leading-relaxed">
                            Accede a tu cuenta y descubre todas las herramientas que tenemos para ti.
                        </p>
                    </div>

                    <!-- Feature highlights -->
                    <div class="space-y-4">
                        <!-- Crea eventos -->
                        <div class="flex items-center space-x-4 bg-white/10 backdrop-blur-sm rounded-lg p-4">
                            <div class="w-12 h-12 bg-[#ad3728] rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10m-11 4h12m-4 4h4M5 21h4m4 0h6"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-white">Crea Eventos</h3>
                                <p class="text-gray-300 text-sm">Organiza y administra tus actividades fácilmente</p>
                            </div>
                        </div>

                        <!-- Registra asistencias -->
                        <div class="flex items-center space-x-4 bg-white/10 backdrop-blur-sm rounded-lg p-4">
                            <div class="w-12 h-12 bg-[#ad3728] rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-white">Registra Asistencias</h3>
                                <p class="text-gray-300 text-sm">Controla la participación de tus asistentes en tiempo real</p>
                            </div>
                        </div>

                        <!-- Obtén estadísticas -->
                        <div class="flex items-center space-x-4 bg-white/10 backdrop-blur-sm rounded-lg p-4">
                            <div class="w-12 h-12 bg-[#ad3728] rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 11V3H5v18h6v-8h2v8h6V11h-6z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-white">Obtén Estadísticas</h3>
                                <p class="text-gray-300 text-sm">Analiza el rendimiento y genera reportes completos</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Cita -->
                {{-- @php
                    [$message, $author] = str(Illuminate\Foundation\Inspiring::quotes()->random())->explode('-');
                @endphp
                <div class="relative z-20 mt-auto">
                    <blockquote class="space-y-2">
                        <flux:heading size="lg">&ldquo;{{ trim($message) }}&rdquo;</flux:heading>
                        <footer><flux:heading>{{ trim($author) }}</flux:heading></footer>
                    </blockquote>
                </div> --}}
            </div>

            <!-- Right side (Login) -->
            <div class="w-full lg:p-8">
                <div class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]">
                    <a href="{{ route('home') }}" class="z-20 flex flex-col items-center gap-2 font-medium lg:hidden" wire:navigate>
                        <span class="flex h-9 w-9 items-center justify-center rounded-md">
                            <x-app-logo-icon class="size-9 fill-current text-black dark:text-white" />
                        </span>
                        <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                    </a>
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
