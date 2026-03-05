<x-layouts.app :title="__('Compara Eventos')">

    <div class="flex h-full w-full flex-1 flex-col gap-4 p-1 sm:p-4 md:p-6">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('statistics') }}"
               class="hover:text-gray-700 dark:hover:text-gray-200 transition-colors"
               wire:navigate>
                Estadísticas
            </a>
            <flux:icon.chevron-right class="size-4 shrink-0" />
            <span class="text-gray-700 dark:text-gray-200 font-medium">Compara Eventos</span>
        </nav>

        {{-- Contenido: Próximamente --}}
        <div class="flex flex-1 flex-col items-center justify-center gap-6 py-16">

            <div class="flex items-center justify-center w-20 h-20 rounded-2xl bg-amber-50 dark:bg-amber-950/30">
                <flux:icon.arrows-right-left class="size-10 text-amber-400 dark:text-amber-500" />
            </div>

            <div class="text-center max-w-sm">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                    Compara Eventos
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                    Próximamente podrás seleccionar eventos específicos y compararlos entre sí en gráficas interactivas.
                </p>
            </div>

            <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-medium bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                En desarrollo
            </span>

            <a href="{{ route('statistics') }}"
               class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors mt-2"
               wire:navigate>
                <flux:icon.arrow-left class="size-4" />
                Volver al módulo de estadísticas
            </a>

        </div>

    </div>

</x-layouts.app>
