<x-layouts.app :title="__('Evento')">

    <nav class="text-sm mb-4 text-gray-500 dark:text-gray-300" aria-label="Breadcrumb">
        <ol class="list-reset flex">
            <li><a href="{{ route('events.list') }}" class="hover:underline"> Eventos </a></li>
            <li><span class="mx-2">/</span></li>
            <li class="font-bold text-gray-900 dark:text-white"> Información </li>
        </ol>
    </nav>

    <div class="relative flex w-full mb-3 flex-1 flex-col gap-4 p-1 rounded-2xl border border-neutral-200 dark:border-neutral-700">

        <div class="p-4 overflow-hidden rounded-2xl">

            <h1 class="text-3xl font-bold mb-4">Detalles del Evento</h1>   

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Información del evento --}}
                <div class="border border-neutral-200 rounded-lg px-6 pt-10 pb-10 bg-white dark:border-neutral-700 dark:bg-zinc-800">
            
                    <div class="space-y-3">
                        {{-- Título --}}
                        <div class="flex items-center justify-between border-b border-gray-300 dark:border-neutral-700 pb-2">
                            <div class="flex items-center gap-2 text-black dark:text-white">
                                <flux:icon name="bars-3-center-left" class="w-5 h-5 text-blue-500" />
                                <span class="font-medium">Título:</span>
                            </div>
                            <span class="font-bold text-black dark:text-white text-right">{{ $event->title }}</span>
                        </div>
                    
                        {{-- Fecha --}}
                        <div class="flex items-center justify-between border-b border-gray-300 dark:border-neutral-700 pb-2">
                            <div class="flex items-center gap-2 text-black dark:text-white">
                                <flux:icon name="calendar" class="w-5 h-5 text-blue-500" />
                                <span class="font-medium">Fecha:</span>
                            </div>
                            <span class="font-bold text-black dark:text-white text-right">{{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}</span>
                        </div>
                    
                        {{-- Hora de inicio --}}
                        <div class="flex items-center justify-between border-b border-gray-300 dark:border-neutral-700 pb-2">
                            <div class="flex items-center gap-2 text-black dark:text-white">
                                <flux:icon name="clock" class="w-5 h-5 text-blue-500" />
                                <span class="font-medium">Hora de Inicio:</span>
                            </div>
                            <span class="font-bold text-black dark:text-white text-right">{{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}</span>
                        </div>
                    
                        {{-- Hora de fin --}}
                        <div class="flex items-center justify-between border-b border-gray-300 dark:border-neutral-700 pb-2">
                            <div class="flex items-center gap-2 text-black dark:text-white">
                                <flux:icon name="clock" class="w-5 h-5 text-blue-500" />
                                <span class="font-medium">Hora de Fin:</span>
                            </div>
                            <span class="font-bold text-black dark:text-white text-right">{{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }}</span>
                        </div>
                    
                        {{-- Ubicación --}}
                        <div class="flex items-center justify-between border-b border-gray-300 dark:border-neutral-700 pb-2">
                            <div class="flex items-center gap-2 text-black dark:text-white">
                                <flux:icon name="map-pin" class="w-5 h-5 text-blue-500" />
                                <span class="font-medium">Ubicación:</span>
                            </div>
                            <span class="font-bold text-black dark:text-white text-right truncate max-w-[60%]">
                                {{ $event->location ?? 'Sin ubicación' }}
                            </span>
                        </div>

                        {{-- Dependencia --}}
                        <div class="flex items-center justify-between border-b border-gray-300 dark:border-neutral-700 pb-2">
                            <div class="flex items-center gap-2 text-black dark:text-white">
                                <flux:icon name="building-office" class="w-5 h-5 text-blue-500" />
                                <span class="font-medium">Dependencia:</span>
                            </div>
                            <span class="font-bold text-black dark:text-white text-right truncate max-w-[60%]">
                                {{ $event->dependency?->name ?? 'Sin dependencia' }}
                            </span>
                        </div>

                        {{-- Área --}}
                        <div class="flex items-center justify-between border-b border-gray-300 dark:border-neutral-700 pb-2">
                            <div class="flex items-center gap-2 text-black dark:text-white">
                                <flux:icon name="squares-2x2" class="w-5 h-5 text-blue-500" />
                                <span class="font-medium">Área:</span>
                            </div>
                            <span class="font-bold text-black dark:text-white text-right truncate max-w-[60%]">
                                {{ $event->area?->name ?? 'Sin área' }}
                            </span>
                        </div>

                    
                        {{-- Descripción --}}
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-black dark:text-white">
                                <flux:icon name="book-open" class="w-5 h-5 text-blue-500" />
                                <span class="font-medium">Descripción:</span>
                            </div>
                            <span class="font-bold text-black dark:text-white text-right truncate max-w-[60%]">
                                {{ $event->description ?? 'Sin descripción' }}
                            </span>
                        </div>
                    </div>
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
                        <div class="flex items-center justify-center">
                            <h2 class="text-2xl font-semibold">Enlaces del Evento</h2>
                        </div>
                        
                        <div class="flex items-center justify-center mt-8 mb-4">
                            <div class="border border-neutral-200 dark:border-neutral-700 p-4 rounded-2xl bg-white">
                                {!! QrCode::size(200)->generate(route('events.access', $event->link)) !!}
                            </div>  
                        </div>

                        <div wire:ignore class="flex items-center justify-center">
                            <button
                                id="copy-link-button"
                                data-link="{{ route('events.access', $event->link) }}"
                                class="px-3 py-2 border-1 border-gray-200 dark:border-white dark:text-white rounded-md transition-all duration-300 cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-400 text-sm font-medium">
                                🔗 Copiar Enlace
                            </button>
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

                        @php
                            // Combinar fecha y hora de inicio del evento
                            $eventStartDateTime = \Carbon\Carbon::parse($event->date . ' ' . $event->start_time);
                            $eventEndDateTime = \Carbon\Carbon::parse($event->date . ' ' . $event->end_time);
                            $eventHasStarted = now()->greaterThanOrEqualTo($eventStartDateTime);
                            $eventHasEnded = now()->greaterThan($eventEndDateTime);
                        @endphp

                        @if(!$eventHasStarted)
                            {{-- El evento aún no ha iniciado --}}
                            <div class="flex flex-col items-center justify-center py-12">
                                <div class="text-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <h3 class="text-xl font-semibold mb-2">El evento aún no ha iniciado</h3>
                                    <p class="text-sm">Las estadísticas se mostrarán cuando el evento comience.</p>
                                </div>
                            </div>
                        @elseif($asistenciasCount > 0)
                            {{-- El evento ha iniciado y hay asistentes --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                <!-- Gráfica circular - Programa -->
                                <div>
                                    <h3 class="text-lg font-medium mb-2">Distribución por Programa</h3>
                                    <div id="chart_program_pie" class="relative aspect-video overflow-hidden rounded-2xl border border-neutral-200 dark:border-neutral-700"></div>
                                </div>

                                <!-- Gráfica de barras - Programa -->
                                <div>
                                    <h3 class="text-lg font-medium mb-2">Participación por Programa</h3>
                                    <div id="chart_program_bar" class="relative aspect-video overflow-hidden rounded-2xl border border-neutral-200 dark:border-neutral-700"></div>
                                </div>

                                <!-- Gráfica circular - Rol -->
                                <div>
                                    <h3 class="text-lg font-medium mb-2">Distribución por Rol</h3>
                                    <div id="chart_role_pie" class="relative aspect-video overflow-hidden rounded-2xl border border-neutral-200 dark:border-neutral-700"></div>
                                </div>

                                <!-- Gráfica de barras - Rol -->
                                <div>
                                    <h3 class="text-lg font-medium mb-2">Participación por Rol</h3>
                                    <div id="chart_role_bar" class="relative aspect-video overflow-hidden rounded-2xl border border-neutral-200 dark:border-neutral-700"></div>
                                </div>
                            </div>
                        @else
                            {{-- El evento ha iniciado/finalizado pero no hay asistentes --}}
                            <div class="flex flex-col items-center justify-center py-12">
                                <div class="text-center text-gray-500 dark:text-gray-400">
                                    <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    <h3 class="text-xl font-semibold mb-2">
                                        @if($eventHasEnded)
                                            No hay estadísticas que registrar
                                        @else
                                            Esperando asistentes
                                        @endif
                                    </h3>
                                    <p class="text-sm">
                                        @if($eventHasEnded)
                                            El evento finalizó sin asistentes registrados.
                                        @else
                                            Las estadísticas aparecerán cuando se registren asistentes.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

    </div>

    {{-- Script para copiar el enlace al portapapeles --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const button = document.getElementById('copy-link-button');
            if (!button) return;

            button.addEventListener('click', function() {
                const link = button.getAttribute('data-link');
                
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(link).then(() => {
                        showCopied(button);
                    }).catch(() => {
                        fallbackCopy(link, button);
                    });
                } else {
                    fallbackCopy(link, button);
                }
            });
        });

        function fallbackCopy(text, button) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                showCopied(button);
            } catch (err) {
                alert('No se pudo copiar: ' + text);
            }
            document.body.removeChild(textarea);
        }

        function showCopied(button) {
            const originalText = button.innerHTML;
            button.innerHTML = '✓ Copiado';
            button.classList.add('bg-green-100', 'dark:bg-green-900', 'border-green-400');
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('bg-green-100', 'dark:bg-green-900', 'border-green-400');
            }, 2000);
        }
    </script>

    
</x-layouts.app>