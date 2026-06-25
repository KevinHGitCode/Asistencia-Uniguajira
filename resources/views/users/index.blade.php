<x-layouts.app :title="__('Users')">
    <div class="p-1 pb-8 sm:p-4 sm:pb-10 md:p-6 md:pb-12">
        <div class="flex h-max w-full flex-1 flex-col gap-4 rounded-2xl">

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2" x-data="{ infoOpen: false }">
                    <flux:heading size="xl" level="1">
                        {{ __('Users list') }}
                    </flux:heading>
                    {{-- Referencias de color (dropdown informativo) --}}
                    <div class="relative" @click.outside="infoOpen = false" @keydown.escape="infoOpen = false">
                        <button type="button" @click="infoOpen = !infoOpen"
                            :class="infoOpen ? 'text-[#3b82f6] bg-blue-50 dark:bg-blue-900/30' : 'text-gray-400 hover:text-[#3b82f6] hover:bg-blue-50 dark:hover:bg-blue-900/30'"
                            class="p-1 rounded-lg transition-colors cursor-pointer"
                            aria-label="Referencias de color">
                            <flux:icon.information-circle class="size-5" />
                        </button>
                        <div x-show="infoOpen" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                             class="absolute left-0 top-full mt-2 w-64 z-30 rounded-xl border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-4 shadow-lg">
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-200 mb-2">Referencias de color</p>
                            <ul class="space-y-1.5 text-xs text-gray-600 dark:text-gray-300">
                                <li class="flex items-center gap-2"><span class="size-3 rounded-sm bg-[#e2a542]"></span> Rol del usuario</li>
                                <li class="flex items-center gap-2"><span class="size-3 rounded-sm bg-[#cc5e50]"></span> Dependencias asignadas</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <livewire:user.online-count />
            </div>

            @if(session('success'))
                <div id="users-success-alert"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 text-sm transition-opacity duration-500">
                    <flux:icon.check-circle class="size-5 shrink-0" />
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <div class="flex w-full flex-col sm:flex-row sm:items-center gap-3">
                <form class="relative w-full sm:w-72" method="GET" action="{{ route('users.index') }}" x-data>
                    <svg class="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-gray-400"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.3-4.3M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z" />
                    </svg>
                    <input
                        id="users-search-input"
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="Buscar usuario…"
                        x-on:input.debounce.600ms="$el.closest('form').submit()"
                        x-on:keydown.enter.prevent="$el.closest('form').submit()"
                        class="w-full rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 py-1.5 pl-9 pr-3 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </form>
                <div class="sm:ml-auto">
                    <flux:modal.trigger name="create-user-modal">
                        <flux:button icon="user-plus" square
                            class="cursor-pointer !bg-[#3b82f6] hover:!bg-blue-700 !text-white !border-transparent"
                            :aria-label="__('Add User')" :title="__('Add User')" />
                    </flux:modal.trigger>
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
                                    <th class="px-4 py-3">Sede</th>
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
                                                <div class="relative shrink-0">
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
                                                    @if(in_array($user->id, $onlineUserIds))
                                                        <span title="En línea" class="absolute -bottom-0.5 -right-0.5 size-3 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-zinc-900"></span>
                                                    @endif
                                                </div>
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
                                        <td class="px-4 py-3">
                                            <span class="text-sm text-zinc-700 dark:text-zinc-300">
                                                {{ $user->campus?->name ?? 'Global' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 max-w-sm">
                                            @if(isset($user->role) && $user->role === 'user')
                                                @if($user->dependencies->isNotEmpty())
                                                    <div class="flex items-center gap-2">
                                                        <flux:badge class="!bg-[#cc5e50] !text-white max-w-[14rem] truncate" :color="null" :title="$primaryDependency">
                                                            {{ \Illuminate\Support\Str::limit($primaryDependency, 30, '...') }}
                                                        </flux:badge>
                                                        @if($extraDependenciesCount > 0)
                                                            <div x-data="{ open:false, x:0, y:0, _t:null, show(el){ clearTimeout(this._t); const r=el.getBoundingClientRect(); let xx=r.left, yy=r.bottom+6; if(xx+240>window.innerWidth) xx=r.right-240; if(xx<8) xx=8; if(yy+150>window.innerHeight) yy=r.top-6; this.x=xx; this.y=yy; this.open=true; }, keep(){ clearTimeout(this._t); }, hide(){ this._t=setTimeout(()=>this.open=false,150); } }">
                                                                <button type="button" x-ref="trigger"
                                                                    @mouseenter="show($refs.trigger)"
                                                                    @mouseleave="hide()"
                                                                    class="inline-flex items-center rounded-full bg-[#cc5e50] px-2 py-0.5 text-xs font-semibold text-white hover:brightness-110 focus:outline-none cursor-pointer"
                                                                    aria-label="Mostrar dependencias adicionales">
                                                                    +{{ $extraDependenciesCount }}
                                                                </button>
                                                                <template x-teleport="body">
                                                                    <div x-show="open" x-transition.opacity.duration.150ms
                                                                         :style="`position:fixed;top:${y}px;left:${x}px;`"
                                                                         class="z-[9999] min-w-56 max-w-xs rounded-lg border border-neutral-200 bg-white p-3 text-xs text-zinc-700 shadow-lg dark:border-neutral-700 dark:bg-zinc-900 dark:text-zinc-200"
                                                                         @mouseenter="keep()" @mouseleave="hide()">
                                                                        <p class="mb-2 font-semibold">Dependencias</p>
                                                                        <ul class="space-y-1">
                                                                            @foreach ($dependencyNames as $dependencyName)
                                                                                <li class="truncate" title="{{ $dependencyName }}">{{ $dependencyName }}</li>
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                </template>
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
                                            <div class="flex flex-col items-start gap-1">
                                                @if($user->is_active)
                                                    <span class="text-sm font-semibold text-green-600 dark:text-green-400">Activo</span>
                                                @else
                                                    <span class="text-sm font-semibold text-red-600 dark:text-red-400">Inactivo</span>
                                                @endif
                                            </div>
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
                                        <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-600 dark:text-zinc-400">
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
                                <div class="relative shrink-0">
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
                                    @if(in_array($user->id, $onlineUserIds))
                                        <span title="En línea" class="absolute -bottom-0.5 -right-0.5 size-3 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-zinc-800"></span>
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
                                                    <div x-data="{ open:false, x:0, y:0, _t:null, show(el){ clearTimeout(this._t); const r=el.getBoundingClientRect(); let xx=r.left, yy=r.bottom+6; if(xx+240>window.innerWidth) xx=r.right-240; if(xx<8) xx=8; if(yy+150>window.innerHeight) yy=r.top-6; this.x=xx; this.y=yy; this.open=true; }, keep(){ clearTimeout(this._t); }, hide(){ this._t=setTimeout(()=>this.open=false,150); } }">
                                                        <button type="button" x-ref="trigger"
                                                            @mouseenter="show($refs.trigger)"
                                                            @mouseleave="hide()"
                                                            class="inline-flex items-center rounded-full bg-[#cc5e50] px-2 py-0.5 text-xs font-semibold text-white hover:brightness-110 focus:outline-none cursor-pointer">
                                                            +{{ $mobileExtraDeps->count() }}
                                                        </button>
                                                        <template x-teleport="body">
                                                            <div x-show="open" x-transition.opacity.duration.150ms
                                                                 :style="`position:fixed;top:${y}px;left:${x}px;`"
                                                                 class="z-[9999] min-w-56 max-w-xs rounded-lg border border-neutral-200 bg-white p-3 text-xs text-zinc-700 shadow-lg dark:border-neutral-700 dark:bg-zinc-900 dark:text-zinc-200"
                                                                 @mouseenter="keep()" @mouseleave="hide()">
                                                                <p class="mb-2 font-semibold">Dependencias</p>
                                                                <ul class="space-y-1">
                                                                    @foreach ($mobileDepNames as $mobileDepName)
                                                                        <li class="truncate" title="{{ $mobileDepName }}">{{ $mobileDepName }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        </template>
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

    @livewire('user.create-user-modal', ['dependencies' => $dependencies, 'campuses' => $campuses, 'roles' => $roles])
    @livewire('user.edit-user-modal', ['dependencies' => $dependencies, 'campuses' => $campuses, 'roles' => $roles])

</x-layouts.app>
