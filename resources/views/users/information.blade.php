<x-layouts.app :title="__('Información del usuario')">
    
    <x-breadcrumb class="mb-6" :items="[
        ['label' => 'Usuarios', 'route' => 'users.index'],
        ['label' => 'Información'],
    ]" />

    @if(session('success'))
        <div id="users-success-alert"
            class="flex items-center gap-3 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 text-sm transition-opacity duration-500">
            <flux:icon.check-circle class="size-5 shrink-0" />
            <span>{{ session('success') }}</span>
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
                            {{ $usage['last_seen'] ? $usage['last_seen']->format('d/m/Y H:i') : 'Sin registro' }}
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
                            <span class="text-sm font-semibold text-green-600 dark:text-green-400">Activo</span>
                        @else
                            <span class="text-sm font-semibold text-red-600 dark:text-red-400">Inactivo</span>
                        @endif
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Actividad y uso (ADR-0010, frente 3) --}}
    @php
        $secs = $usage['usage_seconds'] ?? 0;
        $h = intdiv($secs, 3600);
        $m = intdiv($secs % 3600, 60);
        $usageLabel = $secs > 0 ? ($h > 0 ? $h.' h '.$m.' min' : $m.' min') : '—';
    @endphp
    <div class="mt-6 flex w-full flex-col gap-4 p-6 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
        <div class="flex items-center justify-between gap-3">
            <flux:heading class="text-lg font-semibold text-gray-700 dark:text-gray-200">
                Actividad y uso
            </flux:heading>
            @if($usage['is_online'])
                <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 px-3 py-1 text-xs font-medium text-emerald-700 dark:text-emerald-300">
                    <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span> En línea ahora
                </span>
            @else
                <span class="inline-flex items-center gap-2 rounded-full border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-3 py-1 text-xs font-medium text-gray-500 dark:text-zinc-400">
                    <span class="size-2 rounded-full bg-gray-400"></span> Desconectado
                </span>
            @endif
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="rounded-xl border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-3">
                <p class="text-xs text-gray-500 dark:text-zinc-400">Última actividad</p>
                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                    {{ $usage['last_seen'] ? $usage['last_seen']->diffForHumans() : 'Sin registro' }}
                </p>
            </div>
            <div class="rounded-xl border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-3">
                <p class="text-xs text-gray-500 dark:text-zinc-400">Inicios de sesión</p>
                <p class="mt-1 text-sm font-semibold text-[#e2a542]">{{ $usage['login_count'] }}</p>
            </div>
            <div class="rounded-xl border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-3">
                <p class="text-xs text-gray-500 dark:text-zinc-400">Tiempo aprox. en la app</p>
                <p class="mt-1 text-sm font-semibold text-[#62a9b6]">{{ $usageLabel }}</p>
            </div>
            <div class="rounded-xl border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-3">
                <p class="text-xs text-gray-500 dark:text-zinc-400">Último inicio de sesión</p>
                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                    {{ $usage['last_login'] ? $usage['last_login']->format('d/m/Y H:i') : '—' }}
                </p>
            </div>
        </div>

        @if(!empty($usage['actions_by_module']))
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-zinc-400 mb-2">
                    Acciones por módulo
                </p>
                <div class="flex flex-wrap gap-2">
                    @foreach($usage['actions_by_module'] as $row)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800 border border-neutral-200 dark:border-zinc-700 px-3 py-1 text-xs text-gray-700 dark:text-zinc-300">
                            {{ ucfirst($row['module']) }}
                            <span class="font-semibold text-[#cc5e50]">{{ $row['count'] }}</span>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        <p class="text-xs text-gray-400 dark:text-zinc-500">
            El tiempo en la app es aproximado (suma de pares inicio/cierre de sesión; los cierres sin
            “cerrar sesión” no se contabilizan).
        </p>
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

</x-layouts.app>