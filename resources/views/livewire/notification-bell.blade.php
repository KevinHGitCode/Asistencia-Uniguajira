<div wire:poll.30s="refresh" class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">

    {{-- Botón campana --}}
    <button type="button" @click="open = !open; if (open && !$wire.loaded) { $wire.loadItems() }"
            aria-label="Notificaciones"
            class="relative inline-flex items-center justify-center size-9 rounded-lg border border-neutral-200 bg-white text-gray-500 transition-colors hover:border-blue-400 hover:text-gray-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:text-white">
        <flux:icon.bell class="size-5" />
        @if($unreadCount > 0)
            <span class="absolute -top-1.5 -right-1.5 inline-flex min-w-[1.1rem] h-[1.1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    {{-- Desplegable --}}
    <div x-show="open" x-cloak @click.outside="open = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900 z-50 overflow-hidden">

        <div class="flex items-center justify-between px-4 py-2.5 border-b border-neutral-100 dark:border-zinc-800">
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Notificaciones</span>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 cursor-pointer">
                    Marcar todas como leídas
                </button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto divide-y divide-neutral-100 dark:divide-zinc-800">
            @if(! $loaded)
                <div class="flex items-center justify-center gap-2 px-4 py-10 text-sm text-gray-400 dark:text-zinc-500">
                    <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                    </svg>
                    Cargando…
                </div>
            @else
            @forelse($items as $item)
                <button type="button" wire:click="markAsRead('{{ $item['id'] }}')"
                        class="w-full text-left flex gap-3 px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/60 cursor-pointer {{ $item['read'] ? '' : 'bg-blue-50/60 dark:bg-blue-900/10' }}">
                    <span class="shrink-0 mt-0.5 flex size-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300">
                        <flux:icon :name="$item['icono']" class="size-4" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center gap-1.5">
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate">{{ $item['titulo'] }}</span>
                            @unless($item['read'])
                                <span class="shrink-0 size-2 rounded-full bg-blue-500"></span>
                            @endunless
                        </span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2">{{ $item['mensaje'] }}</span>
                        <span class="block text-[11px] text-gray-400 dark:text-zinc-500 mt-1">{{ $item['fecha'] }}</span>
                    </span>
                </button>
            @empty
                <div class="px-4 py-10 text-center text-sm text-gray-400 dark:text-zinc-500">
                    Sin notificaciones por ahora.
                </div>
            @endforelse
            @endif
        </div>
    </div>
</div>
