<div class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900 p-6 shadow-md">
    <div class="flex items-start justify-between mb-4">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white uppercase tracking-wider">{{ $title }}</h3>
        
        @isset($icon)
            <div class="p-2.5 rounded-xl bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20">
                <div class="text-blue-600 dark:text-blue-400">
                    {{ $icon }}
                </div>
            </div>
        @endisset
    </div>
    
    <div>
        <span class="text-5xl font-bold text-gray-900 dark:text-white">
            {{ $value }}
        </span>
    </div>
</div>