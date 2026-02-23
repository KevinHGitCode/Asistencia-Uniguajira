<x-layouts.app :title="__('Users')">
    <div class="mb-4">
        <div class="flex h-max w-full flex-1 flex-col gap-4 rounded-2xl">

            <div class="flex items-center justify-between">
                <flux:heading size="xl" level="1">
                    {{ __('Users list') }}
                </flux:heading>

                <flux:button
                    href="{{ route('user.form') }}"
                    variant="primary"
                    type="submit"
                    class="border hover:scale-105 transition-transform">
                    {{ __('Add User') }}
                </flux:button>
            </div>

            <div class="relative h-full flex-1 overflow-hidden rounded-2xl border bg-zinc-50 dark:bg-zinc-900 border-neutral-200 dark:border-neutral-700">
                <div class=" inset-0 overflow-auto p-4 md:grid md:grid-cols-2 gap-4">

                    @foreach ($users as $user)
                    {{-- <a href="{{ route('users.information', $user->id) }}" class="block hover:shadow-lg hover:scale-105 transition-shadow"> --}}
                        @livewire('user.card', ['title' => $user->name, 'user' => $user], key($user->id))
                    @endforeach
                </div>
            <div>
        </div>
    </div>
    
    <!-- Leyenda -->
    <div class="relative flex w-full flex-1 flex-col gap-4 p-6 mb-4 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900">
        <div class="flex items-center justify-center gap-4 sm:gap-8 flex-wrap">
            <!-- Rol -->
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-sm bg-[#e2a542]"></div>
                <span class="text-xs sm:text-sm text-black dark:text-white">Rol</span>
            </div>

            <!-- Dependencias -->
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-sm bg-[#cc5e50]"></div>
                <span class="text-xs sm:text-sm text-black dark:text-white">Dependencias</span>
            </div>
        </div>
    </div>

</x-layouts.app>
