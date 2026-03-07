<x-layouts.app :title="__('Estadísticas')">

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-1 sm:p-4 md:p-6">

        {{-- Header --}}
        <div class="mb-2">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-2">
                <div class="flex items-center gap-2">
                    <flux:icon.chart-bar class="size-10 text-blue-500 dark:text-blue-400" />
                    <span class="text-gray-700 dark:text-gray-200">Módulo de Estadísticas</span>
                </div>
            </h1>
            <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">
                Analiza la actividad del sistema desde diferentes perspectivas
            </p>
        </div>

        {{-- Grid de tarjetas de módulos --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">

            {{-- Card: Por Asistencias --}}
            <a href="{{ route('statistics.asistencias') }}"
               class="group relative flex flex-col gap-4 p-5 sm:p-6 rounded-2xl border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 overflow-hidden"
               wire:navigate>

                <div class="flex items-center justify-between">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/50 group-hover:scale-110 transition-transform duration-200">
                        <flux:icon.chart-bar class="size-7 text-blue-500 dark:text-blue-400" />
                    </div>
                    <flux:icon.chevron-right class="size-5 text-gray-400 dark:text-gray-500 group-hover:translate-x-1 transition-transform duration-200" />
                </div>

                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                        Por Asistencias
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Incluye registros múltiples por persona. Una persona puede contar varias veces.
                    </p>
                </div>

                <div class="absolute bottom-0 left-0 w-0 h-0.5 rounded-b-xl bg-blue-500 group-hover:w-full transition-all duration-300"></div>
            </a>

            {{-- Card: Por Participantes --}}
            <a href="{{ route('statistics.participantes') }}"
               class="group relative flex flex-col gap-4 p-5 sm:p-6 rounded-2xl border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 overflow-hidden"
               wire:navigate>

                <div class="flex items-center justify-between">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 group-hover:scale-110 transition-transform duration-200">
                        <flux:icon.users class="size-7 text-emerald-500 dark:text-emerald-400" />
                    </div>
                    <flux:icon.chevron-right class="size-5 text-gray-400 dark:text-gray-500 group-hover:translate-x-1 transition-transform duration-200" />
                </div>

                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                        Por Participantes
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Cada persona cuenta una sola vez, sin importar cuántos eventos haya asistido.
                    </p>
                </div>

                <div class="absolute bottom-0 left-0 w-0 h-0.5 rounded-b-xl bg-emerald-500 group-hover:w-full transition-all duration-300"></div>
            </a>

            {{-- Card: Compara Eventos --}}
            <a href="{{ route('statistics.compara-eventos') }}"
               class="group relative flex flex-col gap-4 p-5 sm:p-6 rounded-2xl border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 overflow-hidden"
               wire:navigate>

                <div class="flex items-center justify-between">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/30 group-hover:scale-110 transition-transform duration-200">
                        <flux:icon.arrows-right-left class="size-7 text-amber-500 dark:text-amber-400" />
                    </div>
                    <flux:icon.chevron-right class="size-5 text-gray-400 dark:text-gray-500 group-hover:translate-x-1 transition-transform duration-200" />
                </div>

                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                        Compara Eventos
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Selecciona eventos específicos y compara asistencias y perfil demográfico.
                    </p>
                </div>

                <div class="absolute bottom-0 left-0 w-0 h-0.5 rounded-b-xl bg-amber-500 group-hover:w-full transition-all duration-300"></div>
            </a>

            {{-- Card: Por Usuarios --}}
            <a href="{{ route('statistics.usuarios') }}"
               class="group relative flex flex-col gap-4 p-5 sm:p-6 rounded-2xl border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 overflow-hidden"
               wire:navigate>

                <div class="flex items-center justify-between">
                    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-violet-50 dark:bg-violet-950/50 group-hover:scale-110 transition-transform duration-200">
                        <flux:icon.user class="size-7 text-violet-500 dark:text-violet-400" />
                    </div>
                    <flux:icon.chevron-right class="size-5 text-gray-400 dark:text-gray-500 group-hover:translate-x-1 transition-transform duration-200" />
                </div>

                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                        Por Usuarios
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Actividad y rendimiento de los usuarios que crean y gestionan eventos.
                    </p>
                </div>

                <div class="absolute bottom-0 left-0 w-0 h-0.5 rounded-b-xl bg-violet-500 group-hover:w-full transition-all duration-300"></div>
            </a>

        </div>

        {{-- Sección informativa --}}
        <div class="mt-2 border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
                <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">
                    💡 ¿Qué módulo usar?
                </h2>
            </div>
            <div class="px-4 sm:px-6 py-5 bg-white dark:bg-zinc-800">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-start gap-3">
                        <flux:icon.chart-bar class="size-5 text-blue-500 mt-0.5 shrink-0" />
                        <span>Usa <strong class="text-gray-800 dark:text-gray-200">Por Asistencias</strong> para medir el total de registros, incluyendo personas que asistieron a múltiples eventos.</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <flux:icon.users class="size-5 text-emerald-500 mt-0.5 shrink-0" />
                        <span>Usa <strong class="text-gray-800 dark:text-gray-200">Por Participantes</strong> para contar personas únicas, sin duplicar a quienes asistieron varias veces.</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <flux:icon.user class="size-5 text-violet-500 mt-0.5 shrink-0" />
                        <span>Usa <strong class="text-gray-800 dark:text-gray-200">Por Usuarios</strong> para analizar cuántos eventos ha creado cada usuario y su distribución por rol.</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <flux:icon.arrows-right-left class="size-5 text-amber-400 mt-0.5 shrink-0" />
                        <span>Usa <strong class="text-gray-800 dark:text-gray-200">Compara Eventos</strong> para contrastar eventos específicos en cuanto a asistencias y perfil demográfico.</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</x-layouts.app>
