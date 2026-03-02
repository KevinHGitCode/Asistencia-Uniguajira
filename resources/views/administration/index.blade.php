<x-layouts.app :title="__('Administración')">

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-1 sm:p-4 md:p-6">

        {{-- Header --}}
        <div class="mb-2">
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-2">
                <div class="flex items-center gap-2">
                    <flux:icon.cog class="size-16" />
                    <span class="text-gray-700 dark:text-gray-200">Módulo de Configuración</span>
                </div>
            </h1>
            <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">
                Administra las dependencias, áreas y otros recursos del sistema
            </p>
        </div>

        {{-- Grid de tarjetas de administración --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">

            {{-- Card: Dependencias --}}
            <a href="{{ route('dependencies.index') }}"
               class="group relative flex flex-col gap-4 p-5 sm:p-6 rounded-2xl border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 overflow-hidden">

                {{-- Ícono --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center justify-center w-12 h-12 rounded-lg group-hover:scale-110 transition-transform duration-200">
                        <flux:icon name="building-office" class="size-16 text-[#cc5e50]" />
                    </div>
                    <flux:icon.chevron-right class="size-5 text-gray-400 dark:text-gray-500 group-hover:translate-x-1 transition-transform duration-200" />
                </div>

                {{-- Texto --}}
                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                        Dependencias
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Crea, edita y elimina dependencias del sistema.
                    </p>
                </div>

                {{-- Indicador de color al hover --}}
                <div class="absolute bottom-0 left-0 w-0 h-0.5 rounded-b-xl bg-[#cc5e50] group-hover:w-full transition-all duration-300"></div>
            </a>

            {{-- Card: Áreas --}}
            <a href="{{ route('areas.index') }}"
               class="group relative flex flex-col gap-4 p-5 sm:p-6 rounded-2xl border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 overflow-hidden">

                <div class="flex items-center justify-between">
                    <div class="flex items-center justify-center w-12 h-12 rounded-lg group-hover:scale-110 transition-transform duration-200">
                        <flux:icon.squares-2x2 class="size-16 text-[#62a9b6]" />
                    </div>
                    <flux:icon.chevron-right class="size-5 text-gray-400 dark:text-gray-500 group-hover:translate-x-1 transition-transform duration-200" />
                </div>

                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                        Áreas
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Gestiona las áreas asociadas a cada dependencia.
                    </p>
                </div>

                <div class="absolute bottom-0 left-0 w-0 h-0.5 rounded-b-xl bg-[#62a9b6] group-hover:w-full transition-all duration-300"></div>
            </a>

            {{-- Card placeholder: Próximamente --}}
            <div class="relative flex flex-col gap-4 p-5 sm:p-6 rounded-2xl border border-dashed border-neutral-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50 opacity-60 cursor-not-allowed">

                <div class="flex items-center justify-between">
                    <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-gray-100 dark:bg-zinc-800 text-gray-400 dark:text-zinc-500">
                        <flux:icon.plus class="size-6" />
                    </div>
                    <span class="text-xs font-medium text-gray-400 dark:text-zinc-500 bg-gray-100 dark:bg-zinc-800 px-2 py-0.5 rounded-full">
                        Próximamente
                    </span>
                </div>

                <div>
                    <h3 class="text-base font-semibold text-gray-400 dark:text-zinc-500 mb-1">
                        Más opciones
                    </h3>
                    <p class="text-sm text-gray-400 dark:text-zinc-600">
                        Se añadirán nuevas secciones de administración aquí.
                    </p>
                </div>
            </div>

        </div>

        {{-- Sección informativa / tips --}}
        <div class="mt-2 border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
                <h2 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white">
                    💡 Guía rápida
                </h2>
            </div>
            <div class="px-4 sm:px-6 py-5 bg-white dark:bg-zinc-800">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-start gap-3">
                        <flux:icon.information-circle class="size-5 text-[#cc5e50] mt-0.5 shrink-0" />
                        <span>Las <strong class="text-gray-800 dark:text-gray-200">dependencias</strong> representan las unidades organizativas principales del sistema.</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <flux:icon.information-circle class="size-5 text-[#62a9b6] mt-0.5 shrink-0" />
                        <span>Las <strong class="text-gray-800 dark:text-gray-200">áreas</strong> se agrupan dentro de una dependencia para organizar mejor los participantes.</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</x-layouts.app>