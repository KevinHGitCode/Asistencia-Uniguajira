<x-layouts.app :title="__('Dashboard')">
    @include('calendar.modal')
    
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <!-- Header de bienvenida -->
        <div class="mb-2">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                ¡Bienvenido, {{ $username }}! 👋
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Gestiona tus eventos y consulta estadísticas de asistencia
            </p>
        </div>

        <!-- Cards de estadísticas -->
        <div class="grid gap-6 md:grid-cols-3">
            <livewire:card-stat title="Eventos creados" :value="$eventosCount">
                <x-slot name="icon">
                    <flux:icon.calendar-check class="size-8" />
                </x-slot>
            </livewire:card-stat>

            <livewire:card-stat title="Asistencias totales" :value="$asistenciasCount">
                <x-slot name="icon">
                    <flux:icon.list-checks class="size-8" />
                </x-slot>
            </livewire:card-stat>

            <livewire:card-stat title="Participantes totales" :value="$participantesCount">
                <x-slot name="icon">
                    <flux:icon.users class="size-8" />
                </x-slot>
            </livewire:card-stat>
        </div>

        <!-- Contenedor del calendario -->
        <div class="relative border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-xl shadow-md overflow-hidden">
            <!-- Header del calendario -->
            <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900 text-center">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    📅 Calendario de Eventos
                </h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Visualiza tu actividad de eventos a lo largo del año
                </p>
            </div>

            <!-- Contenedor con scroll -->
            <div class="relative overflow-x-auto overflow-y-hidden px-6 py-8 bg-white dark:bg-zinc-800 border dark:border-neutral-700" style="scroll-behavior: smooth;">
                <div class="flex justify-center min-w-max" id="cal-heatmap"></div>
            </div>

            <!-- Leyenda -->
            <div class="px-6 py-4 border-t border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-zinc-900">
                <div class="flex items-center justify-center gap-8">
                    <!-- Tus eventos -->
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-sm bg-[#e2a542] dark:bg-[#62a9b6]"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Tus eventos</span>
                    </div>

                    <!-- Eventos de otros -->
                    <div class="flex items-center gap-2">
                        <div class="w-4 h-4 rounded-sm" style="background-color: #cc5e50;"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Eventos de otros</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>