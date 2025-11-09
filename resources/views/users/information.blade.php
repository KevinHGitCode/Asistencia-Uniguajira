<x-layouts.app :title="__('Información del usuario')">
    
    <nav class="text-sm text-gray-400 dark:text-gray-500 mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2">
            <li><a href="{{ route('users.index') }}" class="hover:underline">Usuarios</a></li>
            <li><span class="mx-2">/</span></li>
            <li class="text-gray-300 dark:text-gray-200 font-medium">Información</li>
        </ol>
    </nav>

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 dark:bg-zinc-800 p-6 shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 p-6 dark:bg-black-800">

    <div class="flex flex-col items-center md:items-start text-center md:text-left mb-4 md:mb-0">
        @livewire('user.avatar', [
            'user' => $user,
            'size' => 'h-60 w-60 md:h-96 md:w-96',
            'textSize' => 'text-[12rem] md:text-[16rem]',
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
                <span class="font-bold text-base text-red-600 dark:text-red-400">{{ $user->role }}</span>
            </li>
            @if($user->role === 'user')
                <li class="py-2 flex justify-between items-center">
                    <span class="text-sm">Dependencia:</span> 
                    <span class="font-bold text-base text-gray-900 dark:text-white">{{ $user->dependency->name ?? 'N/A' }}</span>
                </li>
            @endif
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

 

        {{-- <div>
            <flux:heading class="text-lg font-semibold mb-2">Detalles</flux:heading>
            <div class="flex flex-col gap-2">
                <div>
                    <span class="font-semibold">ID:</span> {{ $user->id }}
                </div>
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
    </div> --}}

    
    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800 p-6 shadow-sm">

       <div class="flex items-center justify-between mb-4">
            <flux:heading class="text-xl font-semibold text-gray-800 dark:text-gray-100">
                @if($user->role === 'user' && $viewDependency)
                    Eventos de la dependencia: {{ $user->dependency->name ?? 'N/A' }}
                @else
                    Eventos creados por {{ $user->name }}
                @endif
                ({{ $events->total() }})
            </flux:heading>

            @if($user->role === 'user' && $user->dependency)
                @if($viewDependency)
                    <a href="{{ route('users.information', $user->id) }}"
                    class="px-4 py-2 text-sm font-medium bg-black text-white rounded-lg transition-all">
                        Eventos Propios
                    </a>
                @else
                    <a href="{{ route('users.information', ['id' => $user->id, 'dependency' => 1]) }}"
                    class="px-4 py-2 text-sm font-medium bg-black text-white rounded-lg transition-all">
                        Eventos De Dependencia
                    </a>
                @endif
            @endif
     </div>

        @if ($events->count() > 0)
              <!-- GRID de eventos -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($events as $event)
                <div class="p-4 border border-2 border-gray-200 rounded-xl shadow-sm hover-shadow-md transition-shadow dark:border-zinc-700 bg-gray-50 dark:bg-zinc-900">
                    <h4 class="font-semibold text-gray-800 dark:text-gray-100 mb-1"><span class="text-sm text-gray-600 dark:text-gray-400 mb-2"> Evento: </span>{{ $event->title }}</h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                       <span class="text-sm text-gray-600 dark:text-gray-400 mb-2">Descripcion: </span> {{ $event->description }}
                    </p>
                    <p class="text-xs text-gray-500">
                        <span>Dia Creado: </span> {{ $event->created_at->format('d/m/Y') }}
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

    </div>
</x-layouts.app>