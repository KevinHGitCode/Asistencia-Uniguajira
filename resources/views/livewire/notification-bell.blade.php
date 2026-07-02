<div wire:poll.30s="refresh" class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">

    {{-- Botón campana --}}
    <button type="button" @click="open = !open; if (open && !$wire.loaded) { $wire.loadItems() }"
            aria-label="Notificaciones"
            class="relative inline-flex items-center justify-center size-9 cursor-pointer rounded-lg border border-neutral-200 bg-zinc-50 text-gray-500 transition-colors hover:border-blue-400 hover:text-gray-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:text-white">
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
         class="fixed left-3 right-3 top-14 mt-0 w-auto max-w-none rounded-xl border border-neutral-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900 z-50 overflow-hidden sm:absolute sm:left-auto sm:right-0 sm:top-auto sm:mt-2 sm:w-80 sm:max-w-[calc(100vw-2rem)]">

        <div class="flex items-center justify-between px-4 py-2.5 border-b border-neutral-100 dark:border-zinc-800">
            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Notificaciones</span>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 cursor-pointer">
                    Marcar todas como leídas
                </button>
            @endif
        </div>

        <div class="max-h-[calc(100vh-8rem)] overflow-y-auto divide-y divide-neutral-100 dark:divide-zinc-800 sm:max-h-96">
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
                <div class="flex items-start gap-2 px-4 py-3 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/60 {{ $item['read'] ? '' : 'bg-blue-50/60 dark:bg-blue-900/10' }}">
                    <button type="button" wire:click="markAsRead('{{ $item['id'] }}')"
                            class="flex min-w-0 flex-1 cursor-pointer gap-3 text-left">
                        <span class="shrink-0 mt-0.5 flex size-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300">
                            <flux:icon :name="$item['icono']" class="size-4" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center gap-1.5">
                                <span class="truncate text-sm font-medium text-gray-800 dark:text-gray-100">{{ $item['titulo'] }}</span>
                                @unless($item['read'])
                                    <span class="shrink-0 size-2 rounded-full bg-blue-500"></span>
                                @endunless
                            </span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2">{{ $item['mensaje'] }}</span>
                            <span class="block text-[11px] text-gray-400 dark:text-zinc-500 mt-1">{{ $item['fecha'] }}</span>
                        </span>
                    </button>

                    <button
                        type="button"
                        wire:click="deleteNotification('{{ $item['id'] }}')"
                        aria-label="Eliminar notificacion"
                        title="Eliminar notificacion"
                        class="mt-0.5 inline-flex size-8 shrink-0 cursor-pointer items-center justify-center rounded-lg text-gray-400 transition-colors hover:bg-red-50 hover:text-red-600 dark:text-zinc-500 dark:hover:bg-red-950/30 dark:hover:text-red-300"
                    >
                        <flux:icon.x-mark class="size-4" />
                    </button>
                </div>
            @empty
                <div class="px-4 py-10 text-center text-sm text-gray-400 dark:text-zinc-500">
                    Sin notificaciones por ahora.
                </div>
            @endforelse
            @endif
        </div>
    </div>
</div>
