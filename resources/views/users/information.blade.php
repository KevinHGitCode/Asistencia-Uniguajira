<x-layouts.app :title="__('Información del usuario')">
    
    <x-breadcrumb class="mb-6" :items="[
        ['label' => 'Usuarios', 'route' => 'users.index'],
        ['label' => 'Información'],
    ]" />

    @if(session('success'))
        <div
            id="users-success-alert"
            class="rounded-lg bg-green-100 border border-green-400 text-green-700 px-4 py-3 text-sm transition-opacity duration-500">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-6 relative flex w-full flex-1 flex-col gap-4 p-6 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
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
                <flux:heading class="text-lg font-semibold mb-3 text-gray-700 dark:text-gray-200 border-b border-[#e2a542] pb-1">
                    Estadísticas de Actividad
                </flux:heading>

                <ul class="space-y-0 text-gray-700 dark:text-gray-300 divide-y divide-gray-200 dark:divide-gray-700">
                    <li class="py-2 flex justify-between items-center">
                        <span class="text-sm">Eventos creados:</span> 
                        <span class="font-bold text-base text-[#cc5e50]">{{ $eventsCount }}</span>
                    </li>
                    <li class="py-2 flex justify-between items-center">
                        <span class="text-sm">Eventos próximos:</span> 
                        <span class="font-bold text-base text-[#e2a542]">{{ $upcomingEvents }}</span>
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
                <flux:heading class="text-lg font-semibold mb-3 text-gray-700 dark:text-gray-200 border-b border-[#e2a542] pb-1">
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
                                    <span class="px-2 py-1 text-xs bg-[#cc5e50] text-white rounded">
                                        {{ $dependency->name }}
                                    </span>
                                @empty
                                    <span class="text-sm text-gray-500">N/A</span>
                                @endforelse
                            </div>
                        </li>
                    @endif
                    <li class="py-2 flex justify-between items-center">
                        <span class="text-sm">Estado:</span> 
                        @if($user->is_active)
                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 rounded">
                                Activo
                            </span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 rounded">
                                Inactivo
                            </span>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Eventos propios del usuario --}}
    <div class="mt-6">
        <x-events.group
            :events="$events"
            title="Eventos propios"
            empty="No hay eventos creados"
            :empty-hint="$user->name.' aún no ha creado ningún evento.'"
            from="usuario"
            :user-id="$user->id">
            <x-slot:icon>
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </x-slot:icon>
        </x-events.group>
    </div>

    {{-- Eventos de las dependencias del usuario --}}
    @foreach($dependencyEvents as $group)
        <div class="mt-6">
            <x-events.group
                :events="$group['events']"
                :title="'Eventos de '.$group['dependency']->name"
                empty="No hay eventos en esta dependencia."
                from="usuario"
                :user-id="$user->id"
                :show-creator="true">
                <x-slot:icon>
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </x-slot:icon>
            </x-events.group>
        </div>
    @endforeach

    {{-- Quita todo el div de los botones y modales, solo deja esto --}}
    <livewire:admin.toggle-user-active :user="$user" />
</x-layouts.app>