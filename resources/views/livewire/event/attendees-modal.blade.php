<div>
    <flux:modal.trigger name="attendees-modal-{{ $eventId }}">
        <div class="cursor-pointer hover:scale-[1.008] transition-transform duration-200">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="rounded-lg bg-blue-100 p-3 dark:bg-blue-900">
                            <svg class="size-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">Asistencias totales</p>
                            <p class="text-3xl font-bold text-zinc-900 dark:text-white">{{ $totalAttendees }}</p>
                        </div>
                    </div>
                    <svg class="size-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>
        </div>
    </flux:modal.trigger>

    <flux:modal name="attendees-modal-{{ $eventId }}" variant="flyout" class="w-full max-w-2xl" x-data x-init="
        $nextTick(() => {
            const closeButton = $el.querySelector('[data-flux-modal-close]');
            if (closeButton) {
                closeButton.addEventListener('click', () => {
                    $dispatch('modal-close', { name: 'attendees-modal-{{ $eventId }}' });
                });
            }
        });
    ">
        <div class="space-y-6">
            <!-- Header -->
            <div class="border-b border-zinc-200 dark:border-zinc-700 pb-4">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <flux:heading size="lg">Lista de Asistentes</flux:heading>
                </div>
                <flux:text class="mt-2">
                    Total de participantes registrados: <span class="font-bold">{{ $totalAttendees }}</span>
                </flux:text>
            </div>

            <!-- Lista de Asistentes -->
            <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-2">
                @forelse($attendees as $index => $attendance)
                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 hover:bg-zinc-50 dark:hover:bg-zinc-900 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex items-start gap-3 flex-1">
                                <!-- Número de orden -->
                                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                    <span class="text-sm font-bold text-blue-600 dark:text-blue-400">
                                        {{ $index + 1 }}
                                    </span>
                                </div>

                                <!-- Información del participante -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                        <h4 class="font-semibold text-zinc-900 dark:text-white">
                                            {{ $attendance->participant->first_name }} {{ $attendance->participant->last_name }}
                                        </h4>
                                        @if($attendance->participant->role === 'Estudiante')
                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">
                                                Estudiante
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300">
                                                Docente
                                            </span>
                                        @endif
                                    </div>

                                    <div class="space-y-1 text-sm text-zinc-600 dark:text-zinc-400">
                                        <p class="flex items-center gap-1">
                                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                                            </svg>
                                            <span class="font-mono">{{ $attendance->participant->document }}</span>
                                        </p>
                                        
                                        @if($attendance->participant->email)
                                        <p class="flex items-center gap-1 truncate">
                                            <svg class="size-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="truncate">{{ $attendance->participant->email }}</span>
                                        </p>
                                        @endif

                                        @if($attendance->participant->program)
                                        <p class="flex items-center gap-1">
                                            <svg class="size-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                            </svg>
                                            <span>{{ $attendance->participant->program->name }}</span>
                                        </p>
                                        @endif

                                        @if($attendance->participant->affiliation)
                                        <p class="flex items-center gap-1">
                                            <svg class="size-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            <span>{{ $attendance->participant->affiliation }}</span>
                                        </p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Hora de registro -->
                            <div class="flex-shrink-0 text-right ml-4">
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-1">
                                    Registrado
                                </p>
                                <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($attendance->created_at)->format('h:i A') }}
                                </p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ \Carbon\Carbon::parse($attendance->created_at)->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-zinc-500 dark:text-zinc-400">
                        <svg class="size-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <p class="text-lg font-semibold">No hay asistentes registrados</p>
                        <p class="text-sm">Los participantes aparecerán aquí cuando se registren.</p>
                    </div>
                @endforelse
            </div>

            <!-- Footer con estadísticas -->
            @if($totalAttendees > 0)
            <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="text-center p-3 bg-green-50 dark:bg-green-950 rounded-lg">
                        <p class="text-xs text-green-600 dark:text-green-400 mb-1">Estudiantes</p>
                        <p class="text-2xl font-bold text-green-700 dark:text-green-300">
                            {{ $attendees->where('participant.role', 'Estudiante')->count() }}
                        </p>
                    </div>
                    <div class="text-center p-3 bg-purple-50 dark:bg-purple-950 rounded-lg">
                        <p class="text-xs text-purple-600 dark:text-purple-400 mb-1">Docentes</p>
                        <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">
                            {{ $attendees->where('participant.role', 'Docente')->count() }}
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Botones de acción -->
            <div class="flex items-center gap-2 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                @php
                    $event = \App\Models\Event::find($eventId);
                    $eventEndDateTime = \Carbon\Carbon::parse($event->date . ' ' . $event->end_time);
                    $eventHasEnded = now()->greaterThan($eventEndDateTime);
                @endphp

                @if($eventHasEnded)
                    <a href="{{ route('events.download', $eventId) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Descargar PDF
                    </a>
                @else
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-gray-400 text-white rounded-lg opacity-60 cursor-not-allowed">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Descargar PDF
                    </div>
                @endif

                <div class="flex-1"></div>
                <flux:button 
                    variant="primary" 
                    x-on:click="$dispatch('modal-close', { name: 'attendees-modal-{{ $eventId }}' })">
                    Cerrar
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>