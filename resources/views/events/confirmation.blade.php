confirmation.blade.php


<x-layouts.app-nosidebar :title="__('Asistencia Confirmada')">
    <div class="max-w-4xl mx-auto mt-10 p-6">
        
        <!-- Encabezado de éxito -->
        <div class="bg-green-100 dark:bg-green-900 border-2 border-green-500 rounded-lg p-6 mb-6 text-center">
            <div class="flex justify-center mb-4">
                <svg class="w-20 h-20 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-green-800 dark:text-green-200 mb-2">
                ¡Asistencia Registrada Exitosamente!
            </h1>
            <p class="text-green-700 dark:text-green-300 text-lg">
                {{ \Carbon\Carbon::parse($attendance->created_at)->format('d/m/Y h:i A') }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Información del Participante -->
            <div class="border border-gray-300 dark:border-gray-700 rounded-lg p-6 bg-white dark:bg-black shadow-lg">
                <div class="flex items-center mb-4">
                    <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full mr-4">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Tu Información</h2>
                </div>

                <div class="space-y-4">
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nombre Completo</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $participant->first_name }} {{ $participant->last_name }}
                        </p>
                    </div>

                    <div class="border-b border-gray-200 dark:border-gray-700 pb-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Documento de Identidad</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $participant->document }}
                        </p>
                    </div>

                    <div class="border-b border-gray-200 dark:border-gray-700 pb-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Correo Electrónico</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white break-all">
                            {{ $participant->email }}
                        </p>
                    </div>

                    <div class="border-b border-gray-200 dark:border-gray-700 pb-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Rol</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $participant->role }}
                        </p>
                    </div>

                    @if($participant->affiliation)
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tipo de Vinculación</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $participant->affiliation }}
                        </p>
                    </div>
                    @endif

                    @if($participant->program)
                    <div class="pb-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Programa Académico</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $participant->program->name }}
                        </p>
                    </div>
                    @endif
                </div>

                <!-- Estadística de asistencias -->
                <div class="mt-6 bg-blue-50 dark:bg-blue-950 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-blue-600 dark:text-blue-400">Total de Asistencias</p>
                            <p class="text-3xl font-bold text-blue-800 dark:text-blue-200">{{ $totalAttendances }}</p>
                        </div>
                        <svg class="w-12 h-12 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Información del Evento -->
            <div class="border border-gray-300 dark:border-gray-700 rounded-lg p-6 bg-white dark:bg-black shadow-lg">
                <div class="flex items-center mb-4">
                    <div class="bg-purple-100 dark:bg-purple-900 p-3 rounded-full mr-4">
                        <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Detalles del Evento</h2>
                </div>

                <div class="space-y-4">
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nombre del Evento</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $event->title }}
                        </p>
                    </div>

                    @if($event->description)
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Descripción</p>
                        <p class="text-base text-gray-700 dark:text-gray-300">
                            {{ $event->description }}
                        </p>
                    </div>
                    @endif

                    <div class="border-b border-gray-200 dark:border-gray-700 pb-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Fecha</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            📅 {{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}
                        </p>
                    </div>

                    @if($event->start_time && $event->end_time)
                    <div class="border-b border-gray-200 dark:border-gray-700 pb-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Horario</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            🕐 {{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }} - 
                            {{ \Carbon\Carbon::parse($event->end_time)->format('h:i A') }}
                        </p>
                    </div>
                    @endif

                    @if($event->location)
                    <div class="pb-3">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Ubicación</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            📍 {{ $event->location }}
                        </p>
                    </div>
                    @endif
                </div>

                <!-- Información de registro -->
                <div class="mt-6 bg-purple-50 dark:bg-purple-950 rounded-lg p-4">
                    <p class="text-sm text-purple-600 dark:text-purple-400 mb-2">Hora de Registro</p>
                    <p class="text-2xl font-bold text-purple-800 dark:text-purple-200">
                        {{ \Carbon\Carbon::parse($attendance->created_at)->format('h:i A') }}
                    </p>
                    <p class="text-xs text-purple-500 dark:text-purple-400 mt-1">
                        {{ \Carbon\Carbon::parse($attendance->created_at)->diffForHumans() }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Mensaje final y botón -->
        <div class="mt-8 text-center bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
            <p class="text-gray-700 dark:text-gray-300 mb-4">
                Gracias por registrar tu asistencia. ¡Disfruta del evento!
            </p>
            <a href="{{ route('events.access', $event->link) }}" 
                class="inline-block px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 hover:scale-105 transition-all duration-200 shadow-md">
                    Registrar otra asistencia
            </a>
        </div>
    </div>
</x-layouts.app-nosidebar>