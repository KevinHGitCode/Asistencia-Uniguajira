<x-layouts.app :title="__('Lista de Eventos')">

{{-- *
* =============================================
* Indices superiores de la página
* =============================================
* --}}

    <nav class="text-sm mb-4 text-gray-500 dark:text-gray-300" aria-label="Breadcrumb">
        <ol class="list-reset flex">
            <li><a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a></li>
            <li><span class="mx-2">/</span></li>
            <li class="font-bold text-gray-900 dark:text-white">Eventos</li>
        </ol>
    </nav>

    <div class="space-y-6">
        
{{-- *
* =============================================
* Muestra los eventos propios del usuario autenticado
* =============================================
* --}}

        <div class="relative flex w-full flex-1 flex-col gap-4 p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    <svg class="inline-block w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Mis Eventos
                </h2>
                <span class="px-3 py-1 text-sm font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full">
                    {{ $myEvents->count() }} {{ $myEvents->count() === 1 ? 'evento' : 'eventos' }}
                </span>
            </div>

{{-- *
* =============================================
* Muestra un mensaje si el usuario no ha creado ningún evento
* =============================================
* --}}

            @if($myEvents->isEmpty())
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-lg font-medium">No has creado eventos aún</p>
                    <p class="text-sm mt-1">Crea tu primer evento para comenzar</p>
                </div>

{{-- *
* =============================================
* Muestra los eventos propios del usuario autenticado
* =============================================
* --}}

            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($myEvents as $event)
                        <a href="{{ route('events.show', $event->id) }}" 
                            class="block p-4 rounded-lg border border-neutral-200 dark:border-neutral-600 hover:border-blue-500 dark:hover:border-blue-400 hover:shadow-lg transition-all duration-200 bg-white dark:bg-neutral-900">
                            
                            {{-- Muestra el titulo del evento --}}
                            <div class="flex items-start justify-between mb-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-1">
                                    {{ $event->title }}
                                </h3>
                                <span class="ml-2 flex-shrink-0 px-2 py-1 text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded">
                                    Propio
                                </span>
                            </div>

                            {{-- Muestra la fecha del evento --}}
                            <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>{{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}</span>
                                </div>
                                
                                {{-- Muestra la hora de inicio del evento --}}
                                @if($event->start_time)
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>{{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}</span>
                                    </div>
                                @endif
                                
                                {{-- Muestra la ubicación del evento --}}
                                @if($event->location)
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="line-clamp-1">{{ $event->location }}</span>
                                    </div>
                                @endif
                            </div>
                            
                            {{-- Muestra la descripción del evento --}}
                            @if($event->description)
                                <p class="mt-3 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                    {{ $event->description }}
                                </p>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

{{-- *
* =============================================
* Muestra los eventos de la dependencia del usuario autenticado
* =============================================
* --}}

        @if(Auth::user()->dependency_id)
            <div class="relative flex w-full flex-1 flex-col gap-4 p-6 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        <svg class="inline-block w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Eventos de {{ Auth::user()->dependency->name ?? 'mi Dependencia' }}
                    </h2>
                    <span class="px-3 py-1 text-sm font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full">
                        {{ $dependencyEvents->count() }} {{ $dependencyEvents->count() === 1 ? 'evento' : 'eventos' }}
                    </span>
                </div>

                {{-- Muestra un mensaje si no hay eventos de la dependencia --}}
                @if($dependencyEvents->isEmpty())
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-lg font-medium">No hay eventos de la dependencia</p>
                        <p class="text-sm mt-1">Otros usuarios de tu dependencia aún no han creado eventos</p>
                    </div>

{{-- *
* =============================================
* Muestra los eventos de la dependencia del usuario autenticado
* =============================================
* --}}

                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($dependencyEvents as $event)
                            <a href="{{ route('events.show', $event->id) }}"
                                class="block p-4 rounded-lg border border-neutral-200 dark:border-neutral-600 hover:border-green-500 dark:hover:border-green-400 hover:shadow-lg transition-all duration-200 bg-white dark:bg-neutral-900">
                                
                                {{-- Muestra el titulo del evento --}}
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-1">
                                        {{ $event->title }}
                                    </h3>

                                    {{-- Muestra el tipo de evento --}}
                                    <span class="ml-2 flex-shrink-0 px-2 py-1 text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded">
                                        Dependencia
                                    </span>
                                </div>
                                
                                {{-- Muestra el creador del evento --}}
                                <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">
                                    Creado por: {{ $event->user->name }}
                                </div>
                                
                                {{-- Muestra la fecha del evento --}}
                                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>{{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}</span>
                                    </div>
                                    
                                    {{-- Muestra la hora de inicio del evento --}}
                                    @if($event->start_time)
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>{{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}</span>
                                        </div>
                                    @endif
                                    
                                    {{-- Muestra la ubicación del evento --}}
                                    @if($event->location)
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span class="line-clamp-1">{{ $event->location }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                {{-- Muestra la descripción del evento --}}
                                @if($event->description)
                                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                        {{ $event->description }}
                                    </p>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

    </div>

</x-layouts.app>