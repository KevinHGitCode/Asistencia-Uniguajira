<div class="block p-5 rounded-2xl border border-neutral-200 dark:border-neutral-600 hover:border-[#e2a542] hover:shadow-lg transition-all duration-200 bg-white dark:bg-zinc-800">

    <div class="flex items-center gap-5">
        {{-- Avatar --}}
        <div class="shrink-0">
            @livewire('user.avatar', [
                'user' => $user,
                'size' => 'h-10 w-10',
                'textSize' => 'text-base',
                'showUpload' => false
            ], key('avatar-'.$user->id))
        </div>

        {{-- Informacion principal --}}
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <flux:heading class="truncate text-base font-semibold text-zinc-800 dark:text-zinc-100">
                    {{ $title }}
                </flux:heading>

                {{-- Rol --}}
                @if(isset($user->role))
                    <flux:badge class="!bg-[#e2a542] !text-white" :color="null">
                        {{ __(ucfirst($user->role)) }}
                    </flux:badge>
                @endif

                {{-- Dependencia: solo para usuarios normales --}}
                @if(isset($user->role) && $user->role === 'user')
                    @if($user->dependencies->isNotEmpty())
                        @php
                            $dependencyNames = $user->dependencies->pluck('name')->values();
                            $primaryDependency = $dependencyNames->first();
                            $extraDependencies = $dependencyNames->slice(1);
                        @endphp

                        <flux:badge class="!bg-[#cc5e50] !text-white max-w-[18rem] truncate" :color="null" :title="$primaryDependency">
                            {{ $primaryDependency }}
                        </flux:badge>

                        @if($extraDependencies->isNotEmpty())
                            <div class="relative group/dependencies">
                                <button
                                    type="button"
                                    class="inline-flex items-center rounded-full bg-[#cc5e50] px-2 py-0.5 text-xs font-semibold text-white hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-[#cc5e50]/40"
                                    aria-label="Mostrar dependencias adicionales">
                                    +{{ $extraDependencies->count() }}
                                </button>

                                <div class="pointer-events-none absolute left-0 z-10 hidden min-w-56 max-w-xs rounded-lg border border-neutral-200 bg-white p-3 text-xs text-zinc-700 shadow-lg dark:border-neutral-700 dark:bg-zinc-900 dark:text-zinc-200 group-hover/dependencies:block group-focus-within/dependencies:block {{ $showDependenciesUpward ? 'bottom-full mb-2' : 'top-full mt-2' }}">
                                    <p class="mb-2 font-semibold">Dependencias</p>
                                    <ul class="space-y-1">
                                        @foreach ($dependencyNames as $dependencyName)
                                            <li class="truncate" title="{{ $dependencyName }}">{{ $dependencyName }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                    @else
                        <flux:badge color="gray">
                            {{ __('Not assigned') }}
                        </flux:badge>
                    @endif
                @endif
            </div>

            {{-- Email --}}
            <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                {{ $user->email }}
            </p>

            {{-- Contador de eventos --}}
            <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">
                @if($user->events_count === 1)
                    {{ __(':count event created', ['count' => $user->events_count]) }}
                @else
                    {{ __(':count events created', ['count' => $user->events_count]) }}
                @endif
            </p>
        </div>

        {{-- Botones de acciones --}}
        <div class="ml-auto flex items-center gap-2">
            <a href="{{ route('user.edit', ['id' => $user->id]) }}">
                <flux:button
                    square
                    variant="ghost"
                    size="sm"
                    title="{{ __('Edit user') }}"
                    class="hover:text-[#62a9b6] transition-colors hover:cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L12 15l-4 1 1-4 8.586-8.586z" />
                    </svg>
                </flux:button>
            </a>
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
    </div>
</div>
