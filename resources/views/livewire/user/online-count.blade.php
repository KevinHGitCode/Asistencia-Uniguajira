<div wire:poll.30s
     class="inline-flex items-center gap-2 rounded-full border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 px-3 py-1.5 text-sm font-medium text-emerald-700 dark:text-emerald-300">
    <span class="relative flex size-2.5">
        @if($count > 0)
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-500 opacity-60"></span>
        @endif
        <span class="relative inline-flex size-2.5 rounded-full {{ $count > 0 ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
    </span>
    <span>
        <span class="font-semibold">{{ $count }}</span>
        {{ $count === 1 ? 'usuario en línea' : 'usuarios en línea' }}
    </span>
</div>
