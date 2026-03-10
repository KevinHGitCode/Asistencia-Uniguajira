<x-layouts.app :title="__('Users')">
    <div class="mb-4">
        <div class="flex h-max w-full flex-1 flex-col gap-4 rounded-2xl">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <flux:heading size="xl" level="1">
                    {{ __('Users list') }}
                </flux:heading>
            </div>

            @if(session('success'))
                <div
                    id="users-success-alert"
                    class="rounded-lg bg-green-100 border border-green-400 text-green-700 px-4 py-3 text-sm transition-opacity duration-500">
                    {{ session('success') }}
                </div>
            @endif

            <div class="w-full">
                <div class="flex w-full flex-col sm:flex-row gap-3">
                    <form
                        class="flex-1"
                        method="GET"
                        action="{{ route('users.index') }}"
                        x-data>
                        <flux:input
                            id="users-search-input"
                            type="search"
                            name="q"
                            :label="__('Search users')"
                            :placeholder="__('Name, email, role or dependency')"
                            :value="$search"
                            x-on:input.debounce.600ms="$el.closest('form').submit()"
                            x-on:keydown.enter.prevent="$el.closest('form').submit()" />
                    </form>
                    <div class="sm:pt-7">
                        <flux:modal.trigger name="create-user-modal">
                            <flux:button
                                variant="primary"
                                class="border hover:scale-105 transition-transform w-full sm:w-auto cursor-pointer">
                                {{ __('Add User') }}
                            </flux:button>
                        </flux:modal.trigger>
                    </div>
                </div>
            </div>

            <div class="relative h-full flex-1 rounded-2xl border bg-zinc-50 dark:bg-zinc-900 border-neutral-200 dark:border-neutral-700">

                {{-- Desktop table --}}
                <div class="hidden md:block p-4">
                    <div class="overflow-x-auto overflow-y-visible rounded-xl border border-neutral-200 dark:border-neutral-700">
                        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700">
                            <thead class="bg-zinc-100 dark:bg-zinc-800">
                                <tr class="text-left text-xs uppercase tracking-wide text-zinc-600 dark:text-zinc-300">
                                    <th class="px-4 py-3">{{ __('User') }}</th>
                                    <th class="px-4 py-3">{{ __('Role') }}</th>
                                    <th class="px-4 py-3">{{ __('Dependencies') }}</th>
                                    <th class="px-4 py-3">{{ __('Status') }}</th>
                                    <th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700 bg-white dark:bg-zinc-900">
                                @forelse ($users as $user)
                                    @php
                                        $dependencyNames = $user->dependencies->pluck('name')->values();
                                        $primaryDependency = $dependencyNames->first();
                                        $extraDependenciesCount = max(0, $dependencyNames->count() - 1);
                                    @endphp
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/70 transition-colors">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3 min-w-[16rem]">
                                                @if($user->avatar)
                                                    <img
                                                        src="{{ Storage::url($user->avatar) }}"
                                                        alt="{{ $user->name }}"
                                                        class="h-9 w-9 rounded-full object-cover border border-neutral-200 dark:border-neutral-600">
                                                @else
                                                    <div class="h-9 w-9 rounded-full bg-gray-200 dark:bg-gray-700 border border-neutral-200 dark:border-neutral-600 flex items-center justify-center">
                                                        <span class="text-sm font-bold uppercase text-gray-800 dark:text-white">
                                                            {{ substr($user->name, 0, 1) }}
                                                        </span>
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <p class="truncate font-semibold text-zinc-900 dark:text-zinc-100">{{ $user->name }}</p>
                                                    <p class="truncate text-sm text-zinc-600 dark:text-zinc-400">{{ $user->email }}</p>
                                                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                                        @if($user->events_count === 1)
                                                            {{ __(':count event created', ['count' => $user->events_count]) }}
                                                        @else
                                                            {{ __(':count events created', ['count' => $user->events_count]) }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if(isset($user->role))
                                                <flux:badge class="!bg-[#e2a542] !text-white" :color="null">
                                                    {{ __(ucfirst($user->role)) }}
                                                </flux:badge>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 max-w-sm">
                                            @if(isset($user->role) && $user->role === 'user')
                                                @if($user->dependencies->isNotEmpty())
                                                    <div class="flex items-center gap-2">
                                                        <flux:badge class="!bg-[#cc5e50] !text-white max-w-[14rem] truncate" :color="null" :title="$primaryDependency">
                                                            {{ \Illuminate\Support\Str::limit($primaryDependency, 30, '...') }}
                                                        </flux:badge>
                                                        @if($extraDependenciesCount > 0)
                                                            <div class="relative group/dependencies">
                                                                <button
                                                                    type="button"
                                                                    class="inline-flex items-center rounded-full bg-[#cc5e50] px-2 py-0.5 text-xs font-semibold text-white hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-[#cc5e50]/40"
                                                                    aria-label="Mostrar dependencias adicionales">
                                                                    +{{ $extraDependenciesCount }}
                                                                </button>

                                                                <div class="dependency-tooltip pointer-events-none absolute right-0 top-full z-20 mt-2 hidden min-w-56 max-w-xs rounded-lg border border-neutral-200 bg-white p-3 text-xs text-zinc-700 shadow-lg dark:border-neutral-700 dark:bg-zinc-900 dark:text-zinc-200 group-hover/dependencies:block group-focus-within/dependencies:block">
                                                                    <p class="mb-2 font-semibold">Dependencias</p>
                                                                    <ul class="space-y-1">
                                                                        @foreach ($dependencyNames as $dependencyName)
                                                                            <li class="truncate" title="{{ $dependencyName }}">{{ $dependencyName }}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Not assigned') }}</span>
                                                @endif
                                            @else
                                                <span class="text-sm text-zinc-500 dark:text-zinc-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($user->is_active)
                                                <flux:badge class="!bg-green-600 !text-white" :color="null">
                                                    Activo
                                                </flux:badge>
                                            @else
                                                <flux:badge class="!bg-red-600 !text-white" :color="null">
                                                    Inactivo
                                                </flux:badge>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-1">
                                                <flux:modal.trigger name="edit-user-modal">
                                                    <flux:button
                                                        square
                                                        variant="ghost"
                                                        size="sm"
                                                        title="{{ __('Edit user') }}"
                                                        class="hover:text-[#62a9b6] transition-colors hover:cursor-pointer"
                                                        x-on:click="Livewire.dispatch('edit-user', { id: {{ $user->id }} })">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L12 15l-4 1 1-4 8.586-8.586z" />
                                                        </svg>
                                                    </flux:button>
                                                </flux:modal.trigger>
                                                <a href="{{ route('users.information', ['id' => $user->id]) }}">
                                                    <flux:button
                                                        square
                                                        variant="ghost"
                                                        size="sm"
                                                        title="{{ __('View information') }}"
                                                        class="hover:text-[#e2a542] transition-colors hover:cursor-pointer">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                    </flux:button>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-zinc-600 dark:text-zinc-400">
                                            {{ __('No users found') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Mobile cards --}}
                <div class="p-4 grid grid-cols-1 gap-4 md:hidden">
                    @forelse ($users as $user)
                        @php
                            $mobileDepNames  = $user->dependencies->pluck('name')->values();
                            $mobilePrimaryDep = $mobileDepNames->first();
                            $mobileExtraDeps  = $mobileDepNames->slice(1);
                        @endphp
                        <div class="block p-5 rounded-2xl border border-neutral-200 dark:border-neutral-600 hover:border-[#e2a542] hover:shadow-lg transition-all duration-200 bg-white dark:bg-zinc-800">
                            <div class="flex items-center gap-5">
                                {{-- Avatar --}}
                                <div class="shrink-0">
                                    @if($user->avatar)
                                        <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}"
                                             class="h-10 w-10 rounded-full object-cover border border-neutral-200 dark:border-neutral-600">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 border border-neutral-200 dark:border-neutral-600 flex items-center justify-center">
                                            <span class="text-base font-bold uppercase text-gray-800 dark:text-white">
                                                {{ substr($user->name, 0, 1) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Main info --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-1">
                                        <span class="truncate text-base font-semibold text-zinc-800 dark:text-zinc-100">{{ $user->name }}</span>

                                        @if(isset($user->role))
                                            <flux:badge class="!bg-[#e2a542] !text-white" :color="null">
                                                {{ __(ucfirst($user->role)) }}
                                            </flux:badge>
                                        @endif

                                        @if(isset($user->role) && $user->role === 'user')
                                            @if($mobileDepNames->isNotEmpty())
                                                <flux:badge class="!bg-[#cc5e50] !text-white max-w-[18rem] truncate" :color="null" :title="$mobilePrimaryDep">
                                                    {{ $mobilePrimaryDep }}
                                                </flux:badge>

                                                @if($mobileExtraDeps->isNotEmpty())
                                                    <div class="relative group/m-deps">
                                                        <button type="button"
                                                            class="inline-flex items-center rounded-full bg-[#cc5e50] px-2 py-0.5 text-xs font-semibold text-white hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-[#cc5e50]/40">
                                                            +{{ $mobileExtraDeps->count() }}
                                                        </button>
                                                        <div class="pointer-events-none absolute left-0 {{ $loop->last ? 'bottom-full mb-2' : 'top-full mt-2' }} z-10 hidden min-w-56 max-w-xs rounded-lg border border-neutral-200 bg-white p-3 text-xs text-zinc-700 shadow-lg dark:border-neutral-700 dark:bg-zinc-900 dark:text-zinc-200 group-hover/m-deps:block group-focus-within/m-deps:block">
                                                            <p class="mb-2 font-semibold">Dependencias</p>
                                                            <ul class="space-y-1">
                                                                @foreach ($mobileDepNames as $mobileDepName)
                                                                    <li class="truncate" title="{{ $mobileDepName }}">{{ $mobileDepName }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                @endif
                                            @else
                                                <flux:badge color="gray">{{ __('Not assigned') }}</flux:badge>
                                            @endif
                                        @endif
                                    </div>

                                    <p class="text-sm text-gray-600 dark:text-gray-400 truncate">{{ $user->email }}</p>
                                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">
                                        @if($user->events_count === 1)
                                            {{ __(':count event created', ['count' => $user->events_count]) }}
                                        @else
                                            {{ __(':count events created', ['count' => $user->events_count]) }}
                                        @endif
                                    </p>
                                </div>

                                {{-- Actions --}}
                                <div class="ml-auto flex items-center gap-2">
                                    <flux:modal.trigger name="edit-user-modal">
                                        <flux:button
                                            square variant="ghost" size="sm"
                                            title="{{ __('Edit user') }}"
                                            class="hover:text-[#62a9b6] transition-colors hover:cursor-pointer"
                                            x-on:click="Livewire.dispatch('edit-user', { id: {{ $user->id }} })">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L12 15l-4 1 1-4 8.586-8.586z" />
                                            </svg>
                                        </flux:button>
                                    </flux:modal.trigger>
                                    <a href="{{ route('users.information', ['id' => $user->id]) }}">
                                        <flux:button
                                            square variant="ghost" size="sm"
                                            title="{{ __('View information') }}"
                                            class="hover:text-[#e2a542] transition-colors hover:cursor-pointer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </flux:button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 text-center text-sm text-zinc-600 dark:text-zinc-400">
                            {{ __('No users found') }}
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if ($users->hasPages())
                    <div class="px-4 pb-4">
                        {{ $users->links() }}
                    </div>
                @endif

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
    @livewire('user.edit-user-modal', ['dependencies' => $dependencies, 'roles' => $roles])
</x-layouts.app>
