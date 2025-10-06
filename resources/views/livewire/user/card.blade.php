<div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800 ">

    <div class="flex items-center gap-4">
       <div>
         @livewire('user.avatar', [
          'user' => $user,
          'size' => 'h-8 w-8',
          'textSize' => 'text-sm',
          'showUpload' => false
         ], key('avatar-'.$user->id))
     </div>

        <div class="flex-1">
            <flux:heading class="flex items-center gap-2 mb-1">
                        {{ $title }}
                        @if(isset($user->role) && ($user->role === 'admin'))
                            <flux:badge color="lime">{{ $user->role }}</flux:badge>
                        @endif
            </flux:heading>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ $user->email }}
            </p>
            <!-- Mostrar cantidad de eventos -->
            <p class="text-xs text-blue-600 dark:text-blue-400">
                {{ $user->events->count() }} evento(s) creado(s)
            </p>
        </div>

        <div class="ml-auto">
            {{-- botón detalles a la derecha --}}
            <a href="{{ route('users.information', ['id' => $user->id]) }}">
                <flux:button square variant="ghost" size="sm" title="Ver información">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </flux:button>
            </a>
        </div>
    </div>

</div>