<x-layouts.app :title="__('Estadísticas Generales')">
    <h1 class="text-3xl font-bold mb-6">Estadísticas Generales</h1>

    {{-- Sección: General --}}
    <section class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">General</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-4">
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
            $chartStyles = "relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700";
        @endphp

        {{-- Gráficas: Programa --}}
        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Programa</h2>
            <div class="grid grid-cols-1 gap-3 p-2">
                {{-- Asistencias por Programa --}}
                @livewire('chart-container', ['id' => 'chart_program_attendances_bar', 'class' => $chartStyles])
                {{-- Participantes por Programa --}}
                @livewire('chart-container', ['id' => 'chart_program_participants_pie', 'class' => $chartStyles.' md:h-[500px]'])
            </div>
        </section>

        {{-- Gráficas: Tiempo --}}
        {{-- <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Tiempo</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-2">
                @livewire('chart-container', ['id' => 'chart_events_time', 'class' => $chartStyles])
                @livewire('chart-container', ['id' => 'chart_attendances_time', 'class' => $chartStyles])
            </div>
        </section> --}}

        {{-- Gráficas: Tops --}}
        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Tops</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-2">
                @livewire('chart-container', ['id' => 'chart_top_events', 'class' => $chartStyles])
                @livewire('chart-container', ['id' => 'chart_top_participants', 'class' => $chartStyles])
                @livewire('chart-container', ['id' => 'chart_top_users', 'class' => $chartStyles])
            </div>
        </section>

        {{-- Gráficas: Eventos por Usuarios --}}
        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Eventos creados por usuarios</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-2">
                @livewire('chart-container', ['id' => 'chart_events_by_role', 'class' => $chartStyles])
                @livewire('chart-container', ['id' => 'chart_events_by_user', 'class' => $chartStyles])
            </div>
        </section>
    </section>
</x-layouts.app>
