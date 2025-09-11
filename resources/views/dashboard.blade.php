<x-layouts.app :title="__('Dashboard')">
    @include('calendar.modal')
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-6">
        <!-- Header de bienvenida -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold mb-2">¡Bienvenido, {{ $username }}!</h1>
            <p>Gestiona tus eventos y consulta estadísticas de asistencia</p>
        </div>

        <div class="grid auto-rows-min gap-6 md:grid-cols-3">
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




        <!-- Contenedor del calendario mejorado -->
        <div id="cal-heatmap-container" class="relative border border-gray-700 rounded-xl select-none">
            <!-- Header del calendario -->
            <div class="p-4 border-b border-gray-700">
                <h2 class="text-lg font-bold text-center">Calendario de Eventos</h2>
            </div>

            <!-- Contenedor con scroll mejorado -->
            <div class="relative overflow-x-auto overflow-y-hidden px-4 py-6" style="scroll-behavior: smooth;">
                <!-- Calendario -->
                <div class="flex justify-center min-w-max" id="cal-heatmap"></div>
            </div>
        </div>
    </div>
</x-layouts.app>
