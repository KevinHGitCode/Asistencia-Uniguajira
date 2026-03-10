<x-layouts.app :title="__('Detalle de Usuario')">

    <x-breadcrumb class="mb-6" :items="[
        ['label' => 'Usuarios', 'route' => 'users.index'],
        ['label' => 'Detalle'],
    ]" />

    <div class="relative flex w-full flex-1 flex-col gap-4 p-6 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
        <flux:heading size="xl" level="1">{{ $user->name }}</flux:heading>

        <div class="space-y-2 text-sm text-gray-700 dark:text-gray-200">
            <p><strong>Correo:</strong> {{ $user->email }}</p>
            <p><strong>Rol:</strong> {{ $user->role }}</p>
        </div>

        <div class="flex gap-3 pt-4">
            <a href="{{ route('user.edit', $user->id) }}">
                <flux:button class="cursor-pointer" variant="primary">Editar</flux:button>
            </a>
            <a href="{{ route('users.information', $user->id) }}">
                <flux:button class="cursor-pointer" variant="ghost">Informacion completa</flux:button>
            </a>
        </div>
    </div>
</x-layouts.app>
