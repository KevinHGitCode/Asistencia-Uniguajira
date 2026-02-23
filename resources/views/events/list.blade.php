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

        <!-- Leyenda -->
        <div class="relative flex w-full flex-1 flex-col gap-4 p-6 mb-4 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
            <div class="flex items-center justify-center gap-4 sm:gap-8 flex-wrap">
                <!-- Tus eventos -->
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded-sm bg-[#cc5e50]"></div>
                    <span class="text-xs sm:text-sm text-black dark:text-white">Dependencias</span>
                </div>

                <!-- Eventos de otros -->
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded-sm bg-[#62a9b6]"></div>
                    <span class="text-xs sm:text-sm text-black dark:text-white">Areas</span>
                </div>
            </div>
        </div>
    
        <div class="space-y-6">
            
            @php
                $now = now();
                
                $myEventsInProgress = $myEvents->filter(function($event) use ($now) {
                    $startDateTime = \Carbon\Carbon::parse($event->date . ' ' . $event->start_time);
                    $endDateTime = \Carbon\Carbon::parse($event->date . ' ' . $event->end_time);
                    return $now->greaterThanOrEqualTo($startDateTime) && $now->lessThanOrEqualTo($endDateTime);
                })->sortBy(fn($e) => $e->date . ' ' . $e->start_time);

                $myEventsUpcoming = $myEvents->filter(function($event) use ($now) {
                    $startDateTime = \Carbon\Carbon::parse($event->date . ' ' . $event->start_time);
                    return $now->lessThan($startDateTime);
                })->sortBy(fn($e) => $e->date . ' ' . $e->start_time);

                $myEventsFinished = $myEvents->filter(function($event) use ($now) {
                    $endDateTime = \Carbon\Carbon::parse($event->date . ' ' . $event->end_time);
                    return $now->greaterThan($endDateTime);
                })->sortByDesc(fn($e) => $e->date . ' ' . $e->end_time);
            @endphp
    
    {{-- *
    * =============================================
    * Eventos en Proceso
    * =============================================
    * --}}
    
            <div class="relative flex w-full flex-1 flex-col gap-4 p-6 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        <svg class="inline-block w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Eventos en Proceso
                    </h2>
                    <span class="px-3 py-1 text-sm font-medium bg-[#e2a542] text-white rounded-2xl">
                        {{ $myEventsInProgress->count() }} {{ $myEventsInProgress->count() === 1 ? 'evento' : 'eventos' }}
                    </span>
                </div>
    
                @if($myEventsInProgress->isEmpty())
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-lg font-medium">No hay eventos en proceso</p>
                        <p class="text-sm mt-1">Los eventos que estén en curso aparecerán aquí</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($myEventsInProgress as $event)
                            <a href="{{ route('events.show', $event->id) }}" 
                               class="block p-4 rounded-2xl border border-neutral-200 dark:border-neutral-600 hover:border-[#e2a542] hover:shadow-lg transition-all duration-200 bg-white dark:bg-zinc-800">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-1">
                                        {{ $event->title }}
                                    </h3>
                                    {{-- <div class="ml-2 flex flex-col gap-1">
                                        <span class="flex-shrink-0 px-2 py-1 text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded">
                                            Propio
                                        </span>
                                        <span class="flex-shrink-0 px-2 py-1 text-xs font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded">
                                            En proceso
                                        </span>
                                    </div> --}}
                                </div>
                                
                                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>{{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}</span>
                                    </div>
                                    
                                    @if($event->start_time)
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>{{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}</span>
                                        </div>
                                    @endif
                                    
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

                                @if($event->dependency)
                                    <div class="flex items-center text-xs mt-2">
                                        <span class="px-2 py-1 bg-[#cc5e50] text-white rounded">
                                            {{ $event->dependency->name }}
                                        </span>

                                        @if($event->area)
                                            <span class="ml-2 px-2 py-1 bg-[#62a9b6] text-white rounded">
                                                {{ $event->area->name }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                                
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
    * Eventos Próximos
    * =============================================
    * --}}
    
            <div class="relative flex w-full flex-1 flex-col gap-4 p-6 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        <svg class="inline-block w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        Eventos Próximos
                    </h2>
                    <span class="px-3 py-1 text-sm font-medium bg-[#e2a542] text-white rounded-2xl">
                        {{ $myEventsUpcoming->count() }} {{ $myEventsUpcoming->count() === 1 ? 'evento' : 'eventos' }}
                    </span>
                </div>
    
                @if($myEventsUpcoming->isEmpty())
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-lg font-medium">No hay eventos próximos</p>
                        <p class="text-sm mt-1">Los eventos futuros aparecerán aquí</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($myEventsUpcoming as $event)
                            <a href="{{ route('events.show', $event->id) }}" 
                               class="block p-4 rounded-2xl border border-neutral-200 dark:border-neutral-600 hover:border-[#e2a542] hover:shadow-lg transition-all duration-200 bg-white dark:bg-zinc-800">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-1">
                                        {{ $event->title }}
                                    </h3>
                                    {{-- <div class="ml-2 flex flex-col gap-1">
                                        <span class="flex-shrink-0 px-2 py-1 text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded">
                                            Propio
                                        </span>
                                        <span class="flex-shrink-0 px-2 py-1 text-xs font-medium bg-cyan-100 dark:bg-cyan-900 text-cyan-800 dark:text-cyan-200 rounded">
                                            Próximo
                                        </span>
                                    </div> --}}
                                </div>
                                
                                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>{{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}</span>
                                    </div>
                                    
                                    @if($event->start_time)
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>{{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}</span>
                                        </div>
                                    @endif
                                    
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

                                @if($event->dependency)
                                    <div class="flex items-center text-xs mt-2">
                                        <span class="px-2 py-1 bg-[#cc5e50] text-white rounded">
                                            {{ $event->dependency->name }}
                                        </span>

                                        @if($event->area)
                                            <span class="ml-2 px-2 py-1 bg-[#62a9b6] text-white rounded">
                                                {{ $event->area->name }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                                
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
    * Eventos Finalizados
    * =============================================
    * --}}
    
            <div class="relative flex w-full flex-1 flex-col gap-4 p-6 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        <svg class="inline-block w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Eventos Finalizados
                    </h2>
                    <span class="px-3 py-1 text-sm font-medium bg-[#e2a542] text-white rounded-2xl">
                        {{ $myEventsFinished->count() }} {{ $myEventsFinished->count() === 1 ? 'evento' : 'eventos' }}
                    </span>
                </div>
    
                @if($myEventsFinished->isEmpty())
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-lg font-medium">No hay eventos finalizados</p>
                        <p class="text-sm mt-1">Los eventos completados aparecerán aquí</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($myEventsFinished as $event)
                            <a href="{{ route('events.show', $event->id) }}" 
                               class="block p-4 rounded-2xl border border-neutral-200 dark:border-neutral-600 hover:border-[#e2a542] hover:shadow-lg transition-all duration-200 bg-white dark:bg-zinc-800">
                                <div class="flex items-start justify-between mb-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-1">
                                        {{ $event->title }}
                                    </h3>
                                    {{-- <div class="ml-2 flex flex-col gap-1">
                                        <span class="flex-shrink-0 px-2 py-1 text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded">
                                            Propio
                                        </span>
                                        <span class="flex-shrink-0 px-2 py-1 text-xs font-medium bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 rounded">
                                            Finalizado
                                        </span>
                                    </div> --}}
                                </div>
                                
                                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>{{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}</span>
                                    </div>
                                    
                                    @if($event->start_time)
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>{{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}</span>
                                        </div>
                                    @endif
                                    
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

                                @if($event->dependency)
                                    <div class="flex items-center text-xs mt-2">
                                        <span class="px-2 py-1 bg-[#cc5e50] text-white rounded">
                                            {{ $event->dependency->name }}
                                        </span>

                                        @if($event->area)
                                            <span class="ml-2 px-2 py-1 bg-[#62a9b6] text-white rounded">
                                                {{ $event->area->name }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                                
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
    
            @if(Auth::user()->dependencies()->exists())
                <div class="relative flex w-full flex-1 flex-col gap-4 p-6 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                            <svg class="inline-block w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Eventos de -> {{ $dependenciesNames ?: 'mis dependencias' }}
                        </h2>
                        <span class="px-3 py-1 text-sm font-medium bg-[#e2a542] text-white rounded-2xl">
                            {{ $dependencyEvents->count() }} {{ $dependencyEvents->count() === 1 ? 'evento' : 'eventos' }}
                        </span>
                    </div>
    
                    @if($dependencyEvents->isEmpty())
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="text-lg font-medium">No hay eventos de la dependencia</p>
                            <p class="text-sm mt-1">Otros usuarios de tu dependencia aún no han creado eventos</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($dependencyEvents->sortByDesc(fn($e) => $e->date . ' ' . $e->start_time) as $event)
                                <a href="{{ route('events.show', $event->id) }}"
                                    class="block p-4 rounded-2xl border border-neutral-200 dark:border-neutral-600 hover:border-[#e2a542] hover:shadow-lg transition-all duration-200 bg-white dark:bg-zinc-800">
                                    
                                    <div class="flex items-start justify-between mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-1">
                                            {{ $event->title }}
                                        </h3>
                                        <span class="ml-2 flex-shrink-0 px-2 py-1 text-xs font-medium bg-green-900 text-white rounded">
                                            Dependencia
                                        </span>
                                    </div>
                                    
                                    <div class="mb-2 text-xs text-gray-500 dark:text-gray-400">
                                        Creado por: {{ $event->user->name }}
                                    </div>
                                    
                                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span>{{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}</span>
                                        </div>
                                        
                                        @if($event->start_time)
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>{{ \Carbon\Carbon::parse($event->start_time)->format('h:i A') }}</span>
                                            </div>
                                        @endif
                                        
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

                                    @if($event->dependency)
                                        <div class="flex items-center text-xs mt-2">
                                            <span class="px-2 py-1 bg-[#cc5e50] text-white rounded">
                                                {{ $event->dependency->name }}
                                            </span>

                                            @if($event->area)
                                                <span class="ml-2 px-2 py-1 bg-[#62a9b6] text-white rounded">
                                                    {{ $event->area->name }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                    
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