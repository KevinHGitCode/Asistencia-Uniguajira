<x-layouts.app :title="__('Información del usuario')">
    
    <nav class="text-sm text-black dark:text-gray-200 mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2">
            <li><a href="{{ route('users.index') }}" class="hover:underline">Usuarios</a></li>
            <li><span class="mx-2">/</span></li>
            <li class="font-medium">Información</li>
        </ol>
    </nav>

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 p-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 p-6 dark:bg-black-800">

            <div class="flex flex-col items-center md:items-start text-center md:text-left mb-4 md:mb-0">
                @livewire('user.avatar', [
                    'user' => $user,
                    'size' => 'h-48 w-48 md:h-56 md:w-56',
                    'textSize' => 'text-6xl md:text-7xl',
                    'showUpload' => false
                ], key('avatar-'.$user->id))
            </div>

            <div class="md:col-span-1 border-t pt-4 md:border-t-0 md:pt-0 border-gray-200 dark:border-gray-700">
                <flux:heading class="text-lg font-semibold mb-3 text-gray-700 dark:text-gray-200 border-b border-indigo-200 dark:border-indigo-700 pb-1">
                    Estadísticas de Actividad
                </flux:heading>

                <ul class="space-y-0 text-gray-700 dark:text-gray-300 divide-y divide-gray-200 dark:divide-gray-700">
                    <li class="py-2 flex justify-between items-center">
                        <span class="text-sm">Eventos creados:</span> 
                        <span class="font-bold text-base text-indigo-600 dark:text-indigo-400">{{ $eventsCount }}</span>
                    </li>
                    <li class="py-2 flex justify-between items-center">
                        <span class="text-sm">Eventos próximos:</span> 
                        <span class="font-bold text-base text-green-600 dark:text-green-400">{{ $upcomingEvents }}</span>
                    </li>
                    <li class="py-2 flex justify-between items-center">
                        <span class="text-sm">Eventos pasados:</span> 
                        <span class="font-bold text-base">{{ $pastEvents }}</span>
                    </li>
                    <li class="py-2 flex justify-between items-center text-sm text-gray-500 dark:text-gray-400">
                        <span>Último acceso:</span> 
                        <span class="font-medium">
                            {{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i') : 'N/A' }}
                        </span>
                    </li>
                </ul>
            </div>

            <div class="md:col-span-1 border-t pt-4 md:border-t-0 md:pt-0 border-gray-200 dark:border-gray-700">
                <flux:heading class="text-lg font-semibold mb-3 text-gray-700 dark:text-gray-200 border-b border-indigo-200 dark:border-indigo-700 pb-1">
                    Detalles del Usuario
                </flux:heading>

                <ul class="space-y-0 text-gray-700 dark:text-gray-300 divide-y divide-gray-200 dark:divide-gray-700">
                    <li class="py-2 flex justify-between items-center">
                        <span class="text-sm">Nombre:</span> 
                        <span class="font-bold text-base text-gray-900 dark:text-white">{{ $user->name }}</span>
                    </li>
                    <li class="py-2 flex justify-between items-center">
                        <span class="text-sm">Correo:</span> 
                        <span class="font-bold text-base text-gray-900 dark:text-white">{{ $user->email }}</span>
                    </li>
                    <li class="py-2 flex justify-between items-center">
                        <span class="text-sm">Fecha de Creacion:</span> 
                        </span class="font-bold text-base text-gray-900 dark:text-white"> {{ $user->created_at->format('d/m/Y H:i') }}
                    </li>
                    <li class="py-2 flex justify-between items-center">
                        <span class="text-sm">Actualizado:</span> 
                        </span class="font-bold text-base text-gray-900 dark:text-white"> {{ $user->updated_at->format('d/m/Y H:i') }}
                    </li>
                    <li class="py-2 flex justify-between items-center">
                        <span class="text-sm">Rol:</span> 
                        <span class="font-bold text-base text-black dark:text-gray-200">{{ $user->role }}</span>
                    </li>
                    @if($user->role === 'user')
                        <li class="py-2">
                            <span class="text-sm">Dependencias:</span>

                            <div class="mt-2 flex flex-wrap gap-2">
                                @forelse($user->dependencies as $dependency)
                                    <span class="px-2 py-1 text-xs bg-violet-100 dark:bg-violet-900 text-violet-800 dark:text-violet-200 rounded">
                                        {{ $dependency->name }}
                                    </span>
                                @empty
                                    <span class="text-sm text-gray-500">N/A</span>
                                @endforelse
                            </div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    {{-- Contenedor de eventos propios --}}
    <div class="mt-6 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                <svg class="inline-block w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                Eventos propios
            </h2>
            <span class="px-3 py-1 text-sm font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full">
                {{ $events->count() }} {{ $events->count() === 1 ? 'evento' : 'eventos' }}
            </span>
        </div>

        @if ($events->total()==0)
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-lg font-medium">No hay eventos creados</p>
                <p class="text-sm mt-1">{{ $user->name }} aún no ha creado ningún evento.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($events as $event)
                    <a href="{{ route('events.show', $event->id) }}" 
                    class="block p-4 rounded-lg border border-neutral-200 dark:border-neutral-600 hover:border-blue-500 dark:hover:border-blue-400 hover:shadow-lg transition-all duration-200 bg-white dark:bg-neutral-900">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-1">
                                {{ $event->title }}
                            </h3>
                            <div class="ml-2 flex flex-col gap-1">
                                <span class="flex-shrink-0 px-2 py-1 text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded">
                                    Propio
                                </span>
                                @php
                                    $now = now();
                                    $startDateTime = \Carbon\Carbon::parse($event->date . ' ' . $event->start_time);
                                    $endDateTime = \Carbon\Carbon::parse($event->date . ' ' . $event->end_time);
                                    $status = '';
                                    $statusClass = '';
                                    
                                    if ($now->greaterThanOrEqualTo($startDateTime) && $now->lessThanOrEqualTo($endDateTime)) {
                                        $status = 'En proceso';
                                        $statusClass = 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200';
                                    } elseif ($now->lessThan($startDateTime)) {
                                        $status = 'Próximo';
                                        $statusClass = 'bg-cyan-100 dark:bg-cyan-900 text-cyan-800 dark:text-cyan-200';
                                    } else {
                                        $status = 'Finalizado';
                                        $statusClass = 'bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200';
                                    }
                                @endphp
                                <span class="flex-shrink-0 px-2 py-1 text-xs font-medium {{ $statusClass }} rounded">
                                    {{ $status }}
                                </span>
                            </div>
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
                                <span class="px-2 py-1 bg-violet-100 dark:bg-violet-900 text-violet-800 dark:text-violet-200 rounded">
                                    {{ $event->dependency->name }}
                                </span>

                                @if($event->area)
                                    <span class="ml-2 px-2 py-1 bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200 rounded">
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
            {{ $events->links() }}
        @endif
    </div>

    {{-- Contenedor de eventos de la dependencia --}}
    @foreach($dependencyEvents as $group)

        <div class="mt-6 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6 shadow-sm">
            
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Eventos de {{ $group['dependency']->name }}
                </h2>

                <span class="px-3 py-1 text-sm font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full">
                    {{ $group['events']->total() }}
                    {{ $group['events']->total() === 1 ? 'evento' : 'eventos' }}
                </span>
            </div>

            @if ($group['events']->total() === 0)

                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    No hay eventos en esta dependencia.
                </div>

            @else

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($group['events'] as $event)

                        <a href="{{ route('events.show', $event->id) }}"
                        class="block p-4 rounded-lg border border-neutral-200 dark:border-neutral-600 hover:border-green-500 dark:hover:border-green-400 hover:shadow-lg transition-all duration-200 bg-white dark:bg-neutral-900">
                            
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                {{ $event->title }}
                            </h3>

                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                {{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}
                            </div>

                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                Creado por: {{ $event->user->name }}
                            </div>

                        </a>

                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $group['events']->links() }}
                </div>

            @endif

        </div>

    @endforeach


    <div>
        <div class="flex justify-end mt-8">
            <flux:modal.trigger name="delete-profile">
                <flux:button 
                variant="danger" class="bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm transition-colors">
                Eliminar
            </flux:button>
            </flux:modal.trigger>
        </div>

        <flux:modal name="delete-profile" class="min-w-[22rem]">
            <form action="{{ route('users.delete', $user->id) }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <flux:heading size="lg">¿Eliminar usuario?</flux:heading>
                    <flux:text class="mt-2">
                        <p>Estás a punto de eliminar este usuario.</p>
                        <p>Esta acción no se puede revertir.</p>
                        <p class="text-red-600 font-semibold mt-2">Por favor ingresa tu contraseña para confirmar.</p>
                    </flux:text>
                </div>
                <div>
                    <flux:input label="Contraseña" name="password" type="password" placeholder="Ingresa tu contraseña" required />
                    @error('password')
                        <div class="text-red-600 text-sm mt-2">{{ $message }}</div>
                    @enderror
                </div>
                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancelar</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="danger">Eliminar usuario</flux:button>
                </div>
            </form>
        </flux:modal>
    </div>
</x-layouts.app>