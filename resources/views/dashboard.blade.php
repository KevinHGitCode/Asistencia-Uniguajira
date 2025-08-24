<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-6">
        <!-- Header de bienvenida -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold mb-2">¡Bienvenido, {{ $username }}!</h1>
            <p>Gestiona tus eventos y consulta estadísticas de asistencia</p>
        </div>

        <!-- Cards de estadísticas principales -->
        <div class="grid auto-rows-min gap-6 md:grid-cols-3">
            <!-- Eventos creados -->
            <div class="relative overflow-hidden rounded-xl border border-gray-700 p-6 hover:bg-gray-750 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium mb-2">Eventos creados</h3>
                        <div class="flex items-center gap-3">
                            <div class="p-3 rounded-lg">
                                <flux:icon.calendar-check class="size-8" />
                            </div>
                            <span class="text-4xl font-bold">{{ $eventosCount }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asistencias totales -->
            <div class="relative overflow-hidden rounded-xl border border-gray-700 p-6 hover:bg-gray-750 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium mb-2">Asistencias totales</h3>
                        <div class="flex items-center gap-3">
                            <div class="p-3 rounded-lg">
                                <flux:icon.list-checks class="size-8" />
                            </div>
                            <span class="text-4xl font-bold">{{ $asistenciasCount }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asistentes totales -->
            <div class="relative overflow-hidden rounded-xl border border-gray-700 p-6 hover:bg-gray-750 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium mb-2">Asistentes totales</h3>
                        <div class="flex items-center gap-3">
                            <div class="p-3 rounded-lg">
                                <flux:icon.users class="size-8" />
                            </div>
                            <span class="text-4xl font-bold">{{ $participantesCount }}</span>
                        </div>
                    </div>
                </div>
            </div>
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
