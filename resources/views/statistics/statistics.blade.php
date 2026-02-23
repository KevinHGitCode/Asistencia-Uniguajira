<x-layouts.app :title="__('Estadísticas Generales')">
    <flux:heading size="xl" level="1" class="mb-3">Estadísticas Generales</flux:heading>
    <flux:subheading size="lg" class="mb-6">
        {{ __('Visualiza un resumen general de la actividad, usuarios y eventos del sistema.') }}
    </flux:subheading>

    {{-- Filtros --}}
    @livewire('statistics.statistics-filters')

    {{-- Sección: General --}}
    <section class="mb-8">
        <flux:heading size="lg" level="2" class="text-lg mb-2">General</flux:heading>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            {{-- Area de contadores --}}
            <div class="flex items-center p-4 border rounded-lg bg-white dark:bg-neutral-800 shadow-md">
                <x-icon name="calendar" class="w-6 h-6 text-blue-500 dark:text-blue-300 mr-3" />
                <div>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100" id="total-events">0</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Número de Eventos</p>
                </div>
            </div>
            <div class="flex items-center p-4 border rounded-lg bg-white dark:bg-neutral-800 shadow-md">
                <x-icon name="users" class="w-6 h-6 text-green-500 dark:text-green-300 mr-3" />
                <div>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100" id="total-attendances">0</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Número de Asistencias</p>
                </div>
            </div>
            <div class="flex items-center p-4 border rounded-lg bg-white dark:bg-neutral-800 shadow-md">
                <x-icon name="user" class="w-6 h-6 text-purple-500 dark:text-purple-300 mr-3" />
                <div>
                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100" id="total-participants">0</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Número de Participantes</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Sección: Graficas y estadisticas --}}
    <section class="mb-8">
        @php
            $chartStyles = "relative aspect-video overflow-hidden rounded-2xl border border-neutral-200 dark:border-neutral-700";
        @endphp

        {{-- Gráficas: Programa --}}
        <section class="mb-8">
            <flux:heading size="lg" level="2" class="text-lg mb-2">Programa</flux:heading>
            <div class="grid grid-cols-1 gap-3">
                {{-- Asistencias por Programa --}}
                @livewire('chart-container', ['id' => 'chart_program_attendances_bar', 'class' => $chartStyles])
                {{-- Participantes por Programa --}}
                @livewire('chart-container', ['id' => 'chart_program_participants_pie', 'class' => $chartStyles.' md:h-[500px]'])
            </div>
        </section>

        {{-- Gráficas: Tiempo --}}
        {{-- <section class="mb-8">
            <flux:heading size="lg" level="2" class="text-lg mb-2">Tiempo</flux:heading>
            <div class="grid grid-cols-1 gap-3">
                @livewire('chart-container', ['id' => 'chart_events_time', 'class' => $chartStyles])
                @livewire('chart-container', ['id' => 'chart_attendances_time', 'class' => $chartStyles])
            </div>
        </section> --}}

        {{-- Gráficas: Tops --}}
        <section class="mb-8">
            <flux:heading size="lg" level="2" class="text-lg mb-2">Tops</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @livewire('chart-container', ['id' => 'chart_top_events', 'class' => $chartStyles])
                @livewire('chart-container', ['id' => 'chart_top_participants', 'class' => $chartStyles])
                @livewire('chart-container', ['id' => 'chart_top_users', 'class' => $chartStyles])
            </div>
        </section>

        {{-- Gráficas: Eventos por Usuarios --}}
        <section class="mb-8">
            <flux:heading size="lg" level="2" class="text-lg mb-2">Eventos creados por usuarios</flux:heading>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @livewire('chart-container', ['id' => 'chart_events_by_role', 'class' => $chartStyles])
                @livewire('chart-container', ['id' => 'chart_events_by_user', 'class' => $chartStyles])
            </div>
        </section>
    </section>
</x-layouts.app>
