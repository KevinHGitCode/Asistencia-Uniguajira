<x-layouts.app :title="__('Evento')">

    <nav class="text-sm mb-4 text-gray-500 dark:text-gray-300" aria-label="Breadcrumb">
        <ol class="list-reset flex">
            <li><a href="{{ route('events.list') }}" class="hover:underline"> Eventos </a></li>
            <li><span class="mx-2">/</span></li>
            <li class="font-bold text-gray-900 dark:text-white"> Información </li>
        </ol>
    </nav>

    <div class="relative flex w-full mb-3 flex-1 flex-col gap-4 p-1 rounded-xl border border-neutral-200 dark:border-neutral-700">

        <div class="p-4 overflow-hidden rounded-xl">

            <h1 class="text-3xl font-bold mb-4">Detalles del Evento</h1>   

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                {{-- Contenedor para la informacion del evento --}}
                <div class="border border-neutral-200 dark:border-neutral-700 p-4 rounded-lg">
                    <p class="text-lg mb-2"><strong>Título:</strong> {{ $event->title }}</p>
                    <p class="text-lg mb-2"><strong>Fecha:</strong> {{ $event->date }}</p>
                    <p class="text-lg mb-2"><strong>Hora de Inicio:</strong> {{ $event->start_time }}</p>
                    <p class="text-lg mb-2"><strong>Hora de Fin:</strong> {{ $event->end_time }}</p>
                    <p class="text-lg mb-2"><strong>Ubicación:</strong> {{ $event->location ?? 'Sin ubicación' }}</p>
                    <p class="text-lg mb-2"><strong>Descripción:</strong> {{ $event->description ?? 'Sin descripción' }}</p>

                    <a href="{{ route('events.download', $event->id) }}"
                        class="inline-block px-4 py-2 rounded-xl bg-green-600 text-white font-medium shadow-md hover:bg-green-700 hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        Descargar listado de asistencia
                    </a>

                </div>

                {{-- Contenedor de los Enlaces del Evento --}}
                <div class="border border-neutral-200 dark:border-neutral-700 p-4 rounded-lg">
                    
                    @php
                        // Combinar fecha y hora de finalización del evento
                        $eventEndDateTime = \Carbon\Carbon::parse($event->date . ' ' . $event->end_time);
                        $eventHasEnded = now()->greaterThan($eventEndDateTime);
                    @endphp

                    @if(!$eventHasEnded)
                        {{-- Mostrar enlaces solo si el evento NO ha terminado --}}
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-semibold">Enlaces del Evento</h2>

                            <button
                                id="copy-link-button"
                                data-link="{{ route('events.access', $event->link) }}"
                                class="px-3 py-2 border-2 border-blue-300 rounded-md transition-all duration-300 cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-400 text-sm font-medium"
                            >
                                🔗 Copiar Enlace
                            </button>
                        </div>
                        
                        <div class="flex items-center justify-center mt-8">
                            <div class="border border-neutral-200 dark:border-neutral-700 p-4 rounded-xl bg-white">
                                {!! QrCode::size(200)->generate(route('events.access', $event->link)) !!}
                            </div>  
                        </div>
                    @else
                        {{-- Mostrar mensaje cuando el evento ha terminado --}}
                        <div class="flex flex-col items-center justify-center h-full py-8">
                            <div class="text-center text-gray-500 dark:text-gray-400">
                                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <h3 class="text-xl font-semibold mb-2">Evento Finalizado</h3>
                                <p class="text-sm">Este evento ya ha terminado.</p>
                                <p class="text-xs mt-2">El enlace de registro ya no está disponible.</p>
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="md:col-span-2 flex flex-col gap-4">
                    {{-- Componente con modal de asistentes --}}
                    @livewire('event.attendees-modal', ['eventId' => $event->id])
                    
                    {{-- Contenedor para las estadísticas --}}
                    <div class="border border-neutral-200 dark:border-neutral-700 p-4 rounded-lg">
                        <h2 class="text-2xl font-semibold mb-4">Estadísticas del Evento</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <!-- Gráfica circular - Programa -->
                            <div>
                                <h3 class="text-lg font-medium mb-2">Distribución por Programa</h3>
                                <div id="chart_program_pie" class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700"></div>
                            </div>

                            <!-- Gráfica de barras - Programa -->
                            <div>
                                <h3 class="text-lg font-medium mb-2">Participación por Programa</h3>
                                <div id="chart_program_bar" class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700"></div>
                            </div>

                            <!-- Gráfica circular - Rol -->
                            <div>
                                <h3 class="text-lg font-medium mb-2">Distribución por Rol</h3>
                                <div id="chart_role_pie" class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700"></div>
                            </div>

                            <!-- Gráfica de barras - Rol -->
                            <div>
                                <h3 class="text-lg font-medium mb-2">Participación por Rol</h3>
                                <div id="chart_role_bar" class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    {{-- Script para copiar el enlace al portapapeles --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const copyButton = document.getElementById('copy-link-button');
            
            if (copyButton) {
                copyButton.addEventListener('click', function() {
                    const link = this.getAttribute('data-link');
                    
                    // Copiar al portapapeles
                    navigator.clipboard.writeText(link).then(() => {
                        // Cambiar el texto del botón temporalmente
                        const originalText = this.innerHTML;
                        this.innerHTML = '✓ Copiado';
                        this.classList.add('bg-green-100', 'dark:bg-green-900', 'border-green-400');
                        
                        // Restaurar después de 2 segundos
                        setTimeout(() => {
                            this.innerHTML = originalText;
                            this.classList.remove('bg-green-100', 'dark:bg-green-900', 'border-green-400');
                        }, 2000);
                    }).catch(err => {
                        console.error('Error al copiar:', err);
                        alert('No se pudo copiar el enlace');
                    });
                });
            }
        });
    </script>
    
</x-layouts.app>