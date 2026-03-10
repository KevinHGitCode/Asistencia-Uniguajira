<x-layouts.app :title="__('Editar Usuario')">

    <x-breadcrumb class="mb-4" :items="[
        ['label' => 'Usuarios', 'route' => 'users.index'],
        ['label' => 'Editar usuario'],
    ]" />

    <div>
        <p class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Editar usuario</p>

        <div class="border border-zinc-500 rounded-lg p-6 dark:bg-zinc-900">
            <form action="{{ route('user.update', $user->id) }}" method="POST" class="flex flex-col gap-6">
                @csrf

                <flux:input
                    name="name"
                    :label="__('Nombre completo')"
                    type="text"
                    required
                    placeholder="Nombre completo"
                    :value="old('name', $user->name)"
                />

                <flux:input
                    name="email"
                    :label="__('Correo electronico')"
                    type="email"
                    required
                    placeholder="ejemplo@correo.com"
                    :value="old('email', $user->email)"
                />

                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <ul class="list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="flex justify-start gap-4 items-center">
                    <flux:button variant="primary" type="submit" class="px-3 py-6 cursor-pointer">
                        Guardar cambios
                    </flux:button>

                    <a href="{{ route('users.index') }}" class="text-sm text-gray-600 hover:underline">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
