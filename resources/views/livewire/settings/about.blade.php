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
                        v1.0.0
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
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <flux:icon.users class="size-4 text-[#62a9b6]" />
                    {{ __('Developed by') }}
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-full bg-[#cc5e50]/15 dark:bg-[#cc5e50]/20 flex items-center justify-center flex-shrink-0">
                            <span class="text-sm font-bold text-[#cc5e50]">KD</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Kevin Díaz</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Desarrollador principal · Universidad de La Guajira</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-full bg-[#cc5e50]/15 dark:bg-[#cc5e50]/20 flex items-center justify-center flex-shrink-0">
                            <span class="text-sm font-bold text-[#cc5e50]">DS</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Daniel Sierra</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Desarrollador principal · Universidad de La Guajira</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer note --}}
            <p class="text-center text-xs text-gray-400 dark:text-gray-600 pb-1">
                © {{ date('Y') }} Asistencia Uniguajira · Todos los derechos reservados
            </p>

        </div>

    </x-settings.layout>
</section>
