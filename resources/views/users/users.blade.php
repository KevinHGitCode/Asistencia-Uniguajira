<x-layouts.app :title="__('Users')">

    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">
                {{ __('Users list') }}
            </h1>

            <flux:button
                href="{{ route('user.form') }}"
                variant="primary"
                type="submit"
                class="border hover:scale-105 transition-transform">
                {{ __('Add User') }}
            </flux:button>
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
