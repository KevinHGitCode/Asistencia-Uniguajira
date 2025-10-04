<x-layouts.app :title="__('Users')">

    <div class="flex flex-col gap-4 rounded-xl w-full h-full">

        <h2 class="text-xl font-bold">Usuarios</h2>

        <div class="flex">
            <a href="{{ route('user.form') }}">
                <flux:button variant="primary" type="button" class="border hover:scale-105 transition-transform">
                    Crear usuario
                </flux:button>
            </a>
        </div>

        <div class="relative h-full w-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($users as $user)
                    <div class="transform transition-all hover:scale-105 hover:shadow-lg rounded-xl">
                        <a href="{{ route('users.information', $user->id) }}" class="block hover:shadow-lg hover:scale-105 transition-transform">
                            @livewire('user.card', ['title' => $user->name, 'user' => $user], key($user->id))
                    </div>
                @endforeach
            </div>
        </div>
    </div>

</x-layouts.app>
