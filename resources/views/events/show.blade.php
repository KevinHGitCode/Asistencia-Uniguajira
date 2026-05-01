<x-layouts.app :title="__('Evento')">

    <x-breadcrumb class="mb-4" :items="[
        ['label' => 'Eventos', 'route' => 'events.list'],
        ['label' => 'Información'],
    ]" />

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 8000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-start gap-3 mb-4 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
            <svg class="size-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="relative flex w-full mb-3 flex-1 flex-col gap-4 p-1 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">

        <div class="p-4 overflow-hidden rounded-2xl">

            <h1 class="text-3xl font-bold mb-4">Detalles del Evento</h1>   

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Información del evento --}}
                <div class="border border-neutral-200 rounded-lg px-6 pt-6 pb-6 bg-white dark:border-neutral-700 dark:bg-zinc-800">

                    {{-- Header con título y botón editar --}}
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-black dark:text-white">Información del evento</h3>
                        <div class="flex items-center gap-2">
                            {{-- Editar --}}
                            <flux:modal.trigger name="edit-event-modal">
                                <flux:button 
                                    variant="primary" 
                                    size="sm" 
                                    class="hover:scale-105 transition-transform cursor-pointer"
                                    x-on:click="Livewire.dispatch('edit-event', { id: {{ $event->id }} })">
                                    <svg class="size-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L12 15l-4 1 1-4 8.586-8.586z"/>
                                    </svg>
                                    {{ __('Editar') }}
                                </flux:button>
                            </flux:modal.trigger>

                            {{-- Eliminar --}}
                            <flux:modal.trigger name="delete-event-modal">
                                <flux:button 
                                    variant="danger" 
                                    size="sm" 
                                    class="hover:scale-105 transition-transform cursor-pointer">
                                    <svg class="size-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    {{ __('Eliminar') }}
                                </flux:button>
                            </flux:modal.trigger>
                        </div>
                    </div>

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
                <div class="border border-neutral-200 dark:border-neutral-700 p-4 bg-white dark:bg-zinc-800 rounded-lg">
                    
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
                            <div id="qr-code-container" class="border border-neutral-200 dark:border-neutral-700 p-4 rounded-2xl bg-white">
                                {!! QrCode::size(200)->generate(route('events.access', $event->link)) !!}
                            </div>
                        </div>

                        @php
                            $eventLink = route('events.access', $event->link);
                            $shareText = '¡Te invito al evento "' . $event->title . '"! Regístrate aquí: ' . $eventLink;
                            $emailSubject = 'Invitación al evento: ' . $event->title;
                            $emailBody = "Hola,\n\nTe invito a registrarte al evento \"" . $event->title . "\".\n\n"
                                . "Fecha: " . \Carbon\Carbon::parse($event->date)->format('d/m/Y') . "\n"
                                . "Hora: " . \Carbon\Carbon::parse($event->start_time)->format('h:i A') . "\n"
                                . ($event->location ? "Ubicación: " . $event->location . "\n" : '')
                                . "\nAccede con el siguiente enlace:\n" . $eventLink . "\n";

                            // Nombre de archivo del QR: usa el título del evento tal cual,
                            // removiendo solo los caracteres inválidos para nombres de archivo.
                            $qrFilename = trim(preg_replace('/\s+/', ' ', preg_replace('/[\/\\\\:*?"<>|]/', '', $event->title)));
                            $qrFilename = ($qrFilename !== '' ? $qrFilename : 'qr-evento') . '.png';
                        @endphp

                        <div wire:ignore class="flex flex-wrap items-center justify-center gap-2"
                             data-qr-filename="{{ $qrFilename }}"
                             data-share-text="{{ $shareText }}"
                             data-whatsapp-url="https://wa.me/?text={{ urlencode($shareText) }}"
                             data-gmail-url="https://mail.google.com/mail/?view=cm&fs=1&tf=1&su={{ rawurlencode($emailSubject) }}&body={{ rawurlencode($emailBody) }}">
                            <button
                                id="copy-link-button"
                                data-link="{{ $eventLink }}"
                                class="px-3 py-2 border border-gray-200 dark:border-white dark:text-white rounded-md transition-all duration-300 cursor-pointer hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:border-blue-400 text-sm font-medium inline-flex items-center gap-1">
                                🔗 Copiar Enlace
                            </button>

                            <button
                                id="share-whatsapp-button"
                                type="button"
                                class="px-3 py-2 border border-gray-200 dark:border-white dark:text-white rounded-md transition-all duration-300 cursor-pointer hover:bg-green-50 dark:hover:bg-green-900/20 hover:border-green-500 text-sm font-medium inline-flex items-center gap-1">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M20.52 3.48A11.93 11.93 0 0012.04 0C5.5 0 .2 5.3.2 11.84c0 2.09.55 4.13 1.6 5.93L0 24l6.39-1.68a11.83 11.83 0 005.65 1.44h.01c6.54 0 11.84-5.3 11.84-11.84 0-3.16-1.23-6.13-3.47-8.44zM12.05 21.6h-.01a9.82 9.82 0 01-5-1.37l-.36-.21-3.79 1 1.01-3.69-.23-.38a9.82 9.82 0 01-1.5-5.11c0-5.43 4.42-9.85 9.86-9.85 2.63 0 5.1 1.03 6.96 2.89a9.78 9.78 0 012.88 6.97c0 5.43-4.42 9.85-9.82 9.85zm5.4-7.37c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.4-1.48-.89-.79-1.49-1.77-1.66-2.07-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.51l-.57-.01c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.49 0 1.47 1.07 2.89 1.22 3.09.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.7.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.69.25-1.29.17-1.41-.07-.12-.27-.2-.57-.35z"/>
                                </svg>
                                WhatsApp
                            </button>

                            <button
                                id="share-email-button"
                                type="button"
                                class="px-3 py-2 border border-gray-200 dark:border-white dark:text-white rounded-md transition-all duration-300 cursor-pointer hover:bg-red-50 dark:hover:bg-red-900/20 hover:border-red-500 text-sm font-medium inline-flex items-center gap-1">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.273H1.636A1.636 1.636 0 0 1 0 19.366V5.457c0-2.023 2.309-3.178 3.927-1.964L5.455 4.64 12 9.548l6.545-4.91 1.528-1.145C21.69 2.28 24 3.434 24 5.457z"/>
                                </svg>
                                Gmail
                            </button>

                            <button
                                id="download-qr-button"
                                type="button"
                                data-filename="{{ $qrFilename }}"
                                class="px-3 py-2 border border-gray-200 dark:border-white dark:text-white rounded-md transition-all duration-300 cursor-pointer hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:border-purple-500 text-sm font-medium inline-flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                                </svg>
                                Descargar QR
                            </button>
                        </div>

                        {{-- Toast de aviso para compartir --}}
                        <div id="share-toast"
                             class="fixed top-6 left-1/2 -translate-x-1/2 z-50 hidden max-w-sm px-4 py-3 rounded-lg shadow-lg bg-zinc-900 text-white text-sm border border-zinc-700">
                            <span id="share-toast-message"></span>
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
                    <div class="border bg-white dark:bg-zinc-800 border-neutral-200 dark:border-neutral-700 p-4 rounded-lg">
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
                            {{-- Punto de montaje de la app React de gráficos de eventos --}}
                            <div id="event-charts-react-root" data-event-id="{{ $event->id }}"></div>
                            @vite(['resources/js/events/index.jsx'])
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

    @livewire('event.edit-event-modal')

    {{-- Modal de confirmación para eliminar --}}
    <x-events.delete-modal :event="$event" />

    {{-- Script para copiar el enlace, compartir y descargar el QR --}}
    @vite(['resources/js/events/show.js'])

    
</x-layouts.app>
