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
                <flux:heading class="text-lg font-semibold mb-2">Estadísticas de actividad</flux:heading>

                <ul class="list-disc pl-6 text-gray-700 dark:text-gray-200">
                    <li>Eventos creados: <span>{{ $eventsCount }}</span></li>
                    <li>Eventos próximos: <span>{{ $upcomingEvents }}</span></li>
                    <li>Eventos pasados: <span>{{ $pastEvents }}</span></li>
                    <li>Último acceso: <span>{{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i') : 'N/A' }}</span>
                    </li>
                </ul>
            </div>

            <div>
                <flux:heading class="text-lg font-semibold mb-2">Detalles</flux:heading>

                <ul class="list-disc pl-6 text-gray-700 dark:text-gray-200">
                    <li>Nombre: <span>{{ $user->name }}</span></li>
                    <li>Correo: <span>{{ $user->email }}</span></li>
                    <li>Rol: <span>{{ $user->role }}</span></li>
                    @if($user->role === 'user')
                        <li>Dependencia: <span>{{ $user->dependency->name ?? 'N/A' }}</span></li>
                    @endif
                    <li>Fecha de creación: <span>{{ $user->created_at->format('d/m/Y H:i') }}</span></li>
                    <li>Última actualización: <span>{{ $user->updated_at->format('d/m/Y H:i') }}</span></li>
                </ul>
            </div>
        </div>



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
                            <flux:button type="submit" variant="primary" class="dark:hover:scale-105 transition-transform">Save changes</flux:button>
                        </div>
                    </form>
                </flux:modal>

            </div> --}}

 

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

    
    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">

        <flux:heading class="font-semibold text-xl mb-4">
            Eventos creados por {{ $user->name }} ({{ $eventsCount }})
        </flux:heading>

        @if ($events->count() > 0)
              <!-- GRID de eventos -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($events as $event)
                <div class="p-4 border rounded-lg shadow-sm dark:border-zinc-700 bg-white dark:bg-zinc-800">
                    <h4 class="text-md font-bold mb-1">{{ $event->title }}</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                        {{ $event->description }}
                    </p>
                    <p class="text-xs text-gray-500">
                        Creado el {{ $event->created_at->format('d/m/Y') }}
                    </p>
                </div>
            @endforeach
        </div>

        <!-- Paginación -->
        <div class="mt-6">
            {{ $events->links('pagination::tailwind') }}
        </div>
        @else
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