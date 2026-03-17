<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('About')" :subheading="__('Information about this application')">

        <div class="space-y-6 py-2">

            {{-- App identity --}}
            <div class="flex items-center gap-4 p-5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
                <div class="flex-shrink-0">
                    <img src="{{ asset('images/favicon-uniguajira-32x32.webp') }}"
                         alt="Uniguajira"
                         class="h-14 w-14 rounded-xl object-cover">
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white leading-tight">
                        {{ config('app.name', 'Asistencia Uniguajira') }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        Universidad de La Guajira
                    </p>
                    <span class="inline-flex items-center gap-1.5 mt-2 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#e2a542]/15 text-[#c48a28] dark:bg-[#e2a542]/20 dark:text-[#e2a542]">
                        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        v{{ $version }}
                    </span>
                </div>
            </div>

            {{-- Description --}}
            <div class="p-5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                    <flux:icon.information-circle class="size-4 text-[#62a9b6]" />
                    {{ __('Description') }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                    Sistema web para la gestión y control de asistencias en la
                    <strong class="text-gray-800 dark:text-gray-200">Universidad de La Guajira</strong>.
                    Permite crear eventos, registrar participantes mediante código QR,
                    consultar estadísticas y generar reportes en PDF de asistencia.
                </p>
            </div>

            {{-- Creators --}}
            <div class="p-5 rounded-xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-900">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <flux:icon.users class="size-4 text-[#62a9b6]" />
                    {{ __('Developed by') }}
                </h3>
                <div class="space-y-4">

                    <!-- Kevin -->
                    <div class="flex items-start gap-3">
                        <div class="h-9 w-9 rounded-full bg-[#cc5e50]/15 dark:bg-[#cc5e50]/20 flex items-center justify-center flex-shrink-0">
                            <span class="text-sm font-bold text-[#cc5e50]">KD</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                Kevin Díaz
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                Desarrollador principal · Universidad de La Guajira
                            </p>

                            <div class="flex items-center gap-2 text-xs">

                                <!-- Instagram -->
                                <a href="https://instagram.com/kevinh.diaz23"
                                target="_blank"
                                class="flex items-center gap-1 px-2 py-1 rounded-md bg-pink-500/10 text-white hover:bg-pink-500/20 hover:scale-105 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M7.75 2C4.574 2 2 4.574 2 7.75v8.5C2 19.426 4.574 22 7.75 22h8.5C19.426 22 22 19.426 22 16.25v-8.5C22 4.574 19.426 2 16.25 2h-8.5zM12 7.5A4.5 4.5 0 1 1 7.5 12 4.505 4.505 0 0 1 12 7.5zm0 7.4A2.9 2.9 0 1 0 9.1 12 2.904 2.904 0 0 0 12 14.9zm4.8-7.9a1.05 1.05 0 1 1-1.05-1.05A1.051 1.051 0 0 1 16.8 7z"/>
                                    </svg>
                                    @kevinh.diaz23
                                </a>

                                <!-- GitHub -->
                                <a href="https://github.com/KevinHGitCode"
                                target="_blank"
                                class="flex items-center gap-1 px-2 py-1 rounded-md bg-gray-500/10 text-gray-700 dark:text-gray-300 hover:bg-gray-500/20 hover:scale-105 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2A10 10 0 0 0 9 21.5c.5.1.7-.2.7-.5v-1.8c-2.8.6-3.4-1.2-3.4-1.2-.4-1-.9-1.3-.9-1.3-.8-.5.1-.5.1-.5.9.1 1.4.9 1.4.9.8 1.3 2.2.9 2.7.7.1-.6.3-.9.6-1.1-2.2-.2-4.6-1.1-4.6-4.9 0-1.1.4-2 1-2.7-.1-.3-.4-1.3.1-2.7 0 0 .8-.3 2.8 1a9.7 9.7 0 0 1 5 0c2-1.3 2.8-1 2.8-1 .5 1.4.2 2.4.1 2.7.6.7 1 1.6 1 2.7 0 3.8-2.3 4.7-4.6 4.9.3.3.6.8.6 1.6V21c0 .3.2.6.7.5A10 10 0 0 0 12 2z"/>
                                    </svg>
                                    GitHub
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Daniel -->
                    <div class="flex items-start gap-3">
                        <div class="h-9 w-9 rounded-full bg-[#cc5e50]/15 dark:bg-[#cc5e50]/20 flex items-center justify-center flex-shrink-0">
                            <span class="text-sm font-bold text-[#cc5e50]">DS</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                Daniel Sierra
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                Desarrollador principal · Universidad de La Guajira
                            </p>

                            <div class="flex items-center gap-2 text-xs">

                                <!-- Instagram -->
                                <a href="https://instagram.com/danie1l6"
                                target="_blank"
                                class="flex items-center gap-1 px-2 py-1 rounded-md bg-pink-500/10 text-white hover:bg-pink-500/20 hover:scale-105 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M7.75 2C4.574 2 2 4.574 2 7.75v8.5C2 19.426 4.574 22 7.75 22h8.5C19.426 22 22 19.426 22 16.25v-8.5C22 4.574 19.426 2 16.25 2h-8.5zM12 7.5A4.5 4.5 0 1 1 7.5 12 4.505 4.505 0 0 1 12 7.5zm0 7.4A2.9 2.9 0 1 0 9.1 12 2.904 2.904 0 0 0 12 14.9zm4.8-7.9a1.05 1.05 0 1 1-1.05-1.05A1.051 1.051 0 0 1 16.8 7z"/>
                                    </svg>
                                    @danie1l6
                                </a>

                                <!-- GitHub -->
                                <a href="https://github.com/Danie1l6Dev"
                                target="_blank"
                                class="flex items-center gap-1 px-2 py-1 rounded-md bg-gray-500/10 text-gray-700 dark:text-gray-300 hover:bg-gray-500/20 hover:scale-105 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2A10 10 0 0 0 9 21.5c.5.1.7-.2.7-.5v-1.8c-2.8.6-3.4-1.2-3.4-1.2-.4-1-.9-1.3-.9-1.3-.8-.5.1-.5.1-.5.9.1 1.4.9 1.4.9.8 1.3 2.2.9 2.7.7.1-.6.3-.9.6-1.1-2.2-.2-4.6-1.1-4.6-4.9 0-1.1.4-2 1-2.7-.1-.3-.4-1.3.1-2.7 0 0 .8-.3 2.8 1a9.7 9.7 0 0 1 5 0c2-1.3 2.8-1 2.8-1 .5 1.4.2 2.4.1 2.7.6.7 1 1.6 1 2.7 0 3.8-2.3 4.7-4.6 4.9.3.3.6.8.6 1.6V21c0 .3.2.6.7.5A10 10 0 0 0 12 2z"/>
                                    </svg>
                                    GitHub
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer note --}}
            <p class="text-center text-xs pb-1">
                © {{ date('Y') }} Asistencia Uniguajira · Todos los derechos reservados
            </p>

        </div>

    </x-settings.layout>
</section>
