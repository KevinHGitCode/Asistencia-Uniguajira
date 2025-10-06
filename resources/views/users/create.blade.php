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

        <div class="border border-white rounded-lg p-6 bg-gray-800 dark:bg-black">

            <form action="{{ route('users.store') }}" method="POST" class="flex flex-col gap-6">
                @csrf
                <flux:input name="name" :label="__('Nombre')" type="text" required autofocus placeholder="Nombre completo" />
                <flux:input name="email" :label="__('Correo electrónico')" type="email" required placeholder="ejemplo@correo.com" />
                <flux:input name="password" :label="__('Contraseña')" type="password" required placeholder="Contraseña" />
                <div class="flex justify-start">
                    <flux:button variant="primary" type="submit" class="px-3 py-6 rounded-lg hover:scale-105 transition-transform">
                        {{ __('Crear usuario') }}
                    </flux:button>
                </div>
            </form>
        </div>

    </div>
</x-layouts.app>