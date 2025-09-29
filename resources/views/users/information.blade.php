<x-layouts.app :title="__('Información del usuario')">
    <nav class="text-sm mb-4 text-gray-500 dark:text-gray-300" aria-label="Breadcrumb">
        <ol class="list-reset flex">
            <li><a href="{{ route('users.index') }}" class="hover:underline">Usuarios</a></li>
            <li><span class="mx-2">/</span></li>
            <li class="font-bold text-gray-900 dark:text-white">Información</li>
        </ol>
    </nav>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 mb-6">
        <div class="flex items-center gap-6 mb-6">
            <div>
                @livewire('user.avatar', [
                    'user' => $user,
                    'size' => 'h-10 w-10',
                    'textSize' => 'text-2xl',
                    'showUpload' => false
                ], key('avatar-'.$user->id))
            </div>
            <div>
                <flux:heading class="text-2xl font-bold">{{ $user->name }}</flux:heading>
                <p class="text-gray-600 dark:text-gray-300">{{ $user->email }}</p>
                    @if($user->role === 'administrador')
                        <flux:badge color="lime">{{ $user->role }}</flux:badge>
                    @endif
            </div>
        </div>

        <div class="mb-6">
            <flux:heading class="text-lg font-semibold mb-2">Estadísticas de actividad</flux:heading>
            <ul class="list-disc pl-6 text-gray-700 dark:text-gray-200">
                <li>Eventos creados: <span class="font-bold text-blue-600">{{ $eventsCount }}</span></li>
                <li>Eventos próximos: <span class="font-bold text-green-600">{{ $upcomingEvents }}</span></li>
                <li>Eventos pasados: <span class="font-bold text-gray-600">{{ $pastEvents }}</span></li>
                <li>Último acceso: <span
                        class="font-bold">{{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i') : 'N/A' }}</span>
                </li>
            </ul>

            {{-- <div class="flex justify-end gap-2 mb-4">

                 boton editar user 

                <flux:modal.trigger name="edit-profile">
                    <flux:button>Editar perfil</flux:button>
                </flux:modal.trigger>

                <flux:modal name="edit-profile" class="md:w-96">
                    <form action="{{ route('user.update', $user->id) }}" method="POST" class="space-y-6">
                        @csrf
                        <div>
                            <flux:heading size="lg">Update profile</flux:heading>
                            <flux:text class="mt-2">Make changes to your personal details.</flux:text>
                        </div>
                        <flux:input label="Name" name="name" placeholder="Your name" />
                        <flux:input label="Email" name="email" type="email" placeholder="Your email" />
                        <div class="flex">
                            <flux:spacer />
                            <flux:button type="submit" variant="primary">Save changes</flux:button>
                        </div>
                    </form>
                </flux:modal>

            </div> --}}

        </div>

        <div>
            <flux:heading class="text-lg font-semibold mb-2">Detalles</flux:heading>
            <div class="flex flex-col gap-2">
                {{-- <div>
                    <span class="font-semibold">ID:</span> {{ $user->id }}
                </div> --}}
                <div>
                    <span class="font-semibold">Correo:</span> {{ $user->email }}
                </div>
                <div>
                    <span class="font-semibold">Fecha de creación:</span> {{ $user->created_at->format('d/m/Y H:i') }}
                </div>
                <div>
                    <span class="font-semibold">Actualizado:</span> {{ $user->updated_at->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
    </div>

    <!-- NUEVA SECCIÓN: EVENTOS DEL USUARIO -->
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <flux:heading class="text-lg font-semibold mb-4">
            Eventos creados por {{ $user->name }} ({{ $eventsCount }})
        </flux:heading>

        @if ($events->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($events as $event)
                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700">
                        <!-- Título del evento -->
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-2 truncate">
                            {{ $event->title }}
                        </h4>

                        <!-- Fecha -->
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-300 mb-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a1 1 0 011 1v8a1 1 0 01-1 1H5a1 1 0 01-1-1V8a1 1 0 011-1h3z">
                                </path>
                            </svg>
                            {{ \Carbon\Carbon::parse($event->date)->format('d/m/Y') }}

                            <!-- Indicador si es evento futuro o pasado -->
                            @if ($event->date >= now()->toDateString())
                                <span
                                    class="ml-2 px-2 py-1 text-xs bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100 rounded">
                                    Próximo
                                </span>
                            @else
                                <span
                                    class="ml-2 px-2 py-1 text-xs bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-300 rounded">
                                    Pasado
                                </span>
                            @endif
                        </div>

                        <!-- Horarios (si existen) -->
                        @if ($event->start_time || $event->end_time)
                            <div class="flex items-center text-sm text-gray-600 dark:text-gray-300 mb-2">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                                @if ($event->start_time && $event->end_time)
                                    {{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($event->end_time)->format('H:i') }}
                                @elseif($event->start_time)
                                    Desde {{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }}
                                @elseif($event->end_time)
                                    Hasta {{ \Carbon\Carbon::parse($event->end_time)->format('H:i') }}
                                @endif
                            </div>
                        @endif

                        <!-- Descripción (si existe) -->
                        @if ($event->description)
                            <div class="text-sm text-gray-600 dark:text-gray-300 mb-3">
                                <p class="line-clamp-2">{{ $event->description }}</p>
                            </div>
                        @endif

                        <!-- Fecha de creación -->
                        <div
                            class="text-xs text-gray-500 dark:text-gray-400 border-t border-gray-200 dark:border-gray-600 pt-2">
                            Creado: {{ $event->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Mensaje cuando el usuario no tiene eventos -->
            <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a1 1 0 011 1v8a1 1 0 01-1 1H5a1 1 0 01-1-1V8a1 1 0 011-1h3z">
                    </path>
                </svg>
                <h3 class="text-lg font-semibold mb-2">No hay eventos creados</h3>
                <p>{{ $user->name }} aún no ha creado ningún evento.</p>
            </div>
        @endif

            
        </div>
        <div>
            <div class="flex justify-end mt-8">
                {{-- boton eliminar user --}}
                <flux:modal.trigger name="delete-profile">
                    <flux:button variant="danger">Eliminar</flux:button>
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

    </div>
</x-layouts.app>