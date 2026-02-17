<div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-md hover:shadow-lg transition-shadow dark:border-zinc-700 dark:bg-zinc-800">

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

        {{-- Información principal --}}
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <flux:heading class="truncate text-base font-semibold text-zinc-800 dark:text-zinc-100">
                    {{ $title }}
                </flux:heading>

                {{-- Rol --}}
                @if(isset($user->role))
                    <flux:badge color="{{ $user->role === 'admin' ? 'lime' : 'blue' }}">
                        {{ __(ucfirst($user->role)) }}
                    </flux:badge>
                @endif

                {{-- Dependencia: solo para usuarios normales --}}
                @if(isset($user->role) && $user->role === 'user')
                    @if($user->dependencies->isNotEmpty())
                        @foreach ($user->dependencies as $dependency)
                            <flux:badge color="violet">
                                {{ $dependency->name }}
                            </flux:badge>
                        @endforeach
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

        {{-- Botón de detalles --}}
        <div class="ml-auto">
            <a href="{{ route('users.information', ['id' => $user->id]) }}">
                <flux:button
                    square
                    variant="ghost"
                    size="sm"
                    title="{{ __('View information') }}"
                    class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </flux:button>
            </a>
        </div>
    </div>
</div>
