<x-layouts.app :title="__('Users')">
    <div class="mb-4">
        <div class="flex h-max w-full flex-1 flex-col gap-4 rounded-2xl">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <flux:heading size="xl" level="1">
                    {{ __('Users list') }}
                </flux:heading>

                <flux:modal.trigger name="create-user-modal">
                    <flux:button
                        variant="primary"
                        class="border hover:scale-105 transition-transform w-full sm:w-auto">
                        {{ __('Add User') }}
                    </flux:button>
                </flux:modal.trigger>
            </div>

            @if(session('success'))
                <div class="rounded-lg bg-green-100 border border-green-400 text-green-700 px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="relative h-full flex-1 rounded-2xl border bg-zinc-50 dark:bg-zinc-900 border-neutral-200 dark:border-neutral-700">
                <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($users as $user)
                        @livewire('user.card', [
                            'title' => $user->name,
                            'user' => $user,
                            'showDependenciesUpward' => $loop->last,
                        ], key($user->id))
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    <!-- Leyenda -->
    <div class="z-10 flex w-full flex-1 flex-col gap-4 p-6 mb-4 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
        <div class="flex items-center justify-center gap-4 sm:gap-8 flex-wrap">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-sm bg-[#e2a542]"></div>
                <span class="text-xs sm:text-sm text-black dark:text-white">Rol</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-sm bg-[#cc5e50]"></div>
                <span class="text-xs sm:text-sm text-black dark:text-white">Dependencias</span>
            </div>
        </div>
    </div>

    @livewire('user.create-user-modal', ['dependencies' => $dependencies, 'roles' => $roles])

</x-layouts.app>
