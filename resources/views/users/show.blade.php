<x-layouts.app :title="__('Detalle de Usuario')">

    <nav class="text-sm text-black dark:text-gray-200 mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2">
            <li><a href="{{ route('users.index') }}" class="hover:underline">Usuarios</a></li>
            <li><span class="mx-2">/</span></li>
            <li class="font-medium">Detalle</li>
        </ol>
    </nav>

    <div class="relative flex w-full flex-1 flex-col gap-4 p-6 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
        <flux:heading size="xl" level="1">{{ $user->name }}</flux:heading>

        <div class="space-y-2 text-sm text-gray-700 dark:text-gray-200">
            <p><strong>Correo:</strong> {{ $user->email }}</p>
            <p><strong>Rol:</strong> {{ $user->role }}</p>
        </div>

        <div class="flex gap-3 pt-4">
            <a href="{{ route('user.edit', $user->id) }}">
                <flux:button variant="primary">Editar</flux:button>
            </a>
            <a href="{{ route('users.information', $user->id) }}">
                <flux:button variant="ghost">Informacion completa</flux:button>
            </a>
        </div>
    </div>
</x-layouts.app>
