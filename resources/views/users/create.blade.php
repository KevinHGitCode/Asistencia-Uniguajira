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

        <div class="border border-white rounded-lg p-6  dark:bg-black">

            <form action="{{ route('users.store') }}" method="POST" class="flex flex-col gap-6">
                @csrf
                <flux:input id="name" name="name" :label="__('Nombre')" type="text" required autofocus placeholder="Nombre completo" value="{{ old('name') }}" />
                @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror

                <flux:input id="email" name="email" :label="__('Correo electrónico')" type="email" required placeholder="ejemplo@correo.com" value="{{ old('email') }}" />
                @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror

                <flux:select id="role" name="role" :label="__('Rol')" required placeholder="Seleccione un rol">
                    @foreach($roles as $value => $label)
                        <option value="{{ $value }}" @selected(old('role') == $value)>{{ $label }}</option>
                    @endforeach
                </flux:select>
                @error('role') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror

                <div id="dependency-wrapper">
                    <flux:select id="dependency_id" name="dependency_id" :label="__('Dependencia')" placeholder="Seleccione una dependencia">
                        <option value="">--</option>
                        @foreach($dependencies as $value => $label)
                            <option value="{{ $value }}" @selected(old('dependency_id') == $value)>{{ $label }}</option>
                        @endforeach
                    </flux:select>
                    @error('dependency_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    <p id="dependency-note" class="text-sm text-gray-500 mt-1" style="display:none;">No aplica para administradores</p>
                </div>

                <flux:input id="password" name="password" :label="__('Contraseña')" type="password" required placeholder="Contraseña" />
                @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                <div class="flex justify-start">
                    <flux:button variant="primary" type="submit" class="px-3 py-6 rounded-lg hover:scale-105 transition-transform">
                        {{ __('Crear usuario') }}
                    </flux:button>
                </div>
            </form>
        </div>

    </div>
   
    <script src="{{ asset('js/users-create.js') }}" defer></script>

</x-layouts.app>