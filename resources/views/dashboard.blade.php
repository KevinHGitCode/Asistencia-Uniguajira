<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl p-6">
        <!-- Header de bienvenida -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">¡Bienvenido, Daniel!</h1>
            <p class="text-gray-400">Gestiona tus eventos y consulta estadísticas de asistencia</p>
        </div>

        <!-- Cards de estadísticas principales -->
        <div class="grid auto-rows-min gap-6 md:grid-cols-3">
            <!-- Eventos creados -->
            <div class="relative overflow-hidden rounded-xl bg-gray-800 border border-gray-700 p-6 hover:bg-gray-750 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-200 mb-2">Eventos creados</h3>
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-blue-600 rounded-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4h6m-6 4h6m-9-8h12a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2z"/>
                                </svg>
                            </div>
                            <span class="text-4xl font-bold text-white">5</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asistencias totales -->
            <div class="relative overflow-hidden rounded-xl bg-gray-800 border border-gray-700 p-6 hover:bg-gray-750 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-200 mb-2">Asistencias totales</h3>
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-orange-600 rounded-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <span class="text-4xl font-bold text-white">325</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asistentes totales -->
            <div class="relative overflow-hidden rounded-xl bg-gray-800 border border-gray-700 p-6 hover:bg-gray-750 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-200 mb-2">Asistentes totales</h3>
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-yellow-600 rounded-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="text-4xl font-bold text-white">108</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="cal-heatmap-container" class="relative py-4 overflow-hidden overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div>
                <h2 class="text-center text-lg font-bold text-white">Calendario de Eventos</h2>
            </div>
            <div class="h-full w-full flex justify-center" id="cal-heatmap"></div>
            {{-- <img src="https://editorial.uefa.com/resources/027f-17a3eb7de39a-460b563d750c-1000/manchester_united_v_chelsea_-_uefa_champions_league_final.jpeg" alt="Imagen de fondo" class="w-full h-full object-cover"> --}}
        </div>
    </div>
</x-layouts.app>
