<div class="relative overflow-hidden rounded-xl border border-gray-700 p-6 hover:bg-gray-750 transition-colors">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-medium mb-2">{{ $title }}</h3>
            <div class="flex items-center gap-3">
                @if ($icon)
                    <div class="p-3 rounded-lg">
                        {!! $icon !!}
                    </div>
                @endif
                <span class="text-4xl font-bold">{{ $value }}</span>
            </div>
        </div>
    </div>
</div>
