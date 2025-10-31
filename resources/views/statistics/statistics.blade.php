<x-layouts.app :title="__('Estadísticas Generales')">
    <h1 class="text-3xl font-bold mb-6">Estadísticas Generales</h1>

    {{-- Sección: General --}}
    <section class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">General</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="p-4 border rounded-lg">Número de Eventos: <span id="total-events">0</span></div>
            <div class="p-4 border rounded-lg">Número de Asistencias: <span id="total-attendances">0</span></div>
            <div class="p-4 border rounded-lg">Número de Participantes: <span id="total-participants">0</span></div>
            <div class="p-4 border rounded-lg">Número de Eventos por Rol</div>
            <div class="p-4 border rounded-lg">Número de Eventos por Usuario</div>
        </div>
    </section>

    {{-- Sección: Programa --}}
    <section class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Programa</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h3 class="text-lg font-medium mb-2">Asistencias por Programa</h3>
                <div id="chart_program_attendances_bar" class="h-64"></div>
            </div>
            <div>
                <h3 class="text-lg font-medium mb-2">Participantes por Programa</h3>
                <div id="chart_program_participants_pie" class="h-64"></div>
            </div>
        </div>
    </section>

    {{-- Sección: Tiempo --}}
    <section class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Tiempo</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h3 class="text-lg font-medium mb-2">Eventos vs Tiempo</h3>
                <div id="chart_events_time" class="h-64"></div>
            </div>
            <div>
                <h3 class="text-lg font-medium mb-2">Asistencias vs Tiempo</h3>
                <div id="chart_attendances_time" class="h-64"></div>
            </div>
        </div>
    </section>

    {{-- Sección: Tops --}}
    <section class="mb-8">
        <h2 class="text-2xl font-semibold mb-4">Tops</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="p-4 border rounded-lg">Eventos con más Asistencias</div>
            <div class="p-4 border rounded-lg">Participantes con más Asistencias</div>
            <div class="p-4 border rounded-lg">Usuarios con más Asistencias</div>
        </div>
    </section>

    {{-- @push('scripts')
    <script type="module" src="/resources/js/statistics-general.js"></script>
    @endpush --}}
</x-layouts.app>
