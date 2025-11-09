<x-layouts.app :title="__('Users')">

    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <h2>Usuarios</h2>
        {{-- <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
                <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
            </div>
        </div> --}}
        
        <div class="flex justi">
            <a>
                <flux:button 
                href="{{ route('user.form') }}" 
                variant="primary" 
                type="submit" 
                class="border hover:scale-105 transition-transform"> Crear User</flux:button>
            </a>
        </div>

        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <div class=" inset-0 overflow-auto p-4 md:grid md:grid-cols-2 gap-4">

                @foreach ($users as $user)
                {{-- <a href="{{ route('users.information', $user->id) }}" class="block hover:shadow-lg hover:scale-105 transition-shadow"> --}}
                    @livewire('user.card', ['title' => $user->name, 'user' => $user], key($user->id))
                @endforeach 
            </div>   
        <div>
    </div>

</x-layouts.app>
