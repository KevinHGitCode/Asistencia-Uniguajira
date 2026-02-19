<x-layouts.app :title="__('Crear Usuario')">

    <nav class="text-sm mb-4 text-gray-500 dark:text-gray-300" aria-label="Breadcrumb">
        <ol class="list-reset flex">
            <li><a href="{{ route('users.index') }}" class="hover:underline">Usuarios</a></li>
            <li><span class="mx-2">/</span></li>
            <li class="font-bold text-gray-900 dark:text-white">Nuevo usuario</li>
        </ol>
    </nav>

    <div>

        <p class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Nuevo usuario</p>

        <div class="border border-zinc-500 rounded-lg p-6 dark:bg-zinc-900">

            <form action="{{ route('users.store') }}" method="POST" class="flex flex-col gap-6">
                @csrf

                <flux:input 
                    name="name" 
                    :label="__('Nombre completo')" 
                    type="text" 
                    required 
                    autofocus 
                    placeholder="Nombre completo" 
                    :value="old('name')" 
                />

                <flux:input 
                    name="email" 
                    :label="__('Correo electrónico')" 
                    type="email" 
                    required 
                    placeholder="ejemplo@correo.com" 
                    :value="old('email')" 
                />

                {{-- ROL --}}
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Rol del usuario
                    </label>
                    <select name="role"
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

                        <option value="">Selecciona un rol</option>

                        @foreach($roles as $value => $label)
                            <option value="{{ $value }}" {{ old('role') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- DEPENDENCIA --}}
                <div class="flex flex-col gap-1">
                    <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        Dependencia <span class="text-gray-400">(opcional para admin)</span>
                    </label>
                    <select name="dependency_id"
                        class="w-full rounded-lg border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">

                        <option value="">— Ninguna —</option>

                        @foreach($dependencies as $value => $label)
                            <option value="{{ $value }}" {{ old('dependency_id') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    <p class="text-sm text-gray-500 mt-1">
                        Solo aplica si el usuario pertenece a una dependencia.
                    </p>
                </div>

                {{-- PASSWORD --}}
                <flux:input 
                    name="password" 
                    :label="__('Contraseña')" 
                    type="password" 
                    required 
                    placeholder="Contraseña segura" 
                />

                {{-- ERRORES GENERALES --}}
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- BOTÓN --}}
                <div class="flex justify-start gap-4 items-center">
                    <flux:button variant="primary" type="submit" class="px-3 py-6">
                        {{ __('Crear usuario') }}
                    </flux:button>

                    @if (session()->has('success'))
                        <div class="bg-green-300 border border-green-400 text-green-700 px-4 py-3 rounded">
                            <p>{{ session('success') }}</p>
                        </div>
                    @endif
                </div>

            </form>
        </div>


    </div>
   
    @vite('resources/js/users/users-create.js')

</x-layouts.app>