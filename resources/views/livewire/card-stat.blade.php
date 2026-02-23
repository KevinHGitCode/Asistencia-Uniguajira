<div class="relative w-full max-w-full overflow-hidden rounded-2xl border border-gray-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900 p-4 sm:p-6 shadow-md">
    <div class="flex items-start justify-between mb-3 gap-3">
        <h3 class="text-[10px] xs:text-[11px] sm:text-xs font-semibold text-gray-900 dark:text-white uppercase tracking-wider leading-tight break-words flex-1 min-w-0">
            {{ $title }}
        </h3>
        
        @isset($icon)
            <div class="p-2 rounded-2xl bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 shrink-0">
                <div class="text-blue-600 dark:text-blue-400">
                    {{ $icon }}
                </div>
            </div>
        @endisset
    </div>
    
    <div class="break-words">
        <span class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white break-all">
            {{ $value }}
        </span>
    </div>
</div>