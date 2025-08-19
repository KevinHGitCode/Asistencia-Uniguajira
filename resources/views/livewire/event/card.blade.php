<div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700">
    <flux:heading class="flex items-center gap-2">
        {{ $title }}
    </flux:heading>

    <div class="mt-2 space-y-1 text-zinc-600 dark:text-zinc-300">
        <div class="flex items-center gap-2">
            <flux:icon name="calendar" variant="micro" />
            <span>{{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</span>
        </div>

        <div class="flex items-center gap-2">
            <flux:icon name="map-pin" variant="micro" />
            <span>{{ $location }}</span>
        </div>
    </div>
</div>

{{-- <div>
    <flux:card size="sm" class="hover:bg-zinc-50 dark:hover:bg-zinc-700">
        <flux:heading class="flex items-center gap-2">
            {{ $title }}
        </flux:heading>

        <div class="mt-2 space-y-1 text-zinc-600 dark:text-zinc-300">
            <div class="flex items-center gap-2">
                <flux:icon name="calendar" variant="micro" />
                <span>{{ \Carbon\Carbon::parse($date)->format('F d, Y') }}</span>
            </div>

            <div class="flex items-center gap-2">
                <flux:icon name="map-pin" variant="micro" />
                <span>{{ $location }}</span>
            </div>
        </div>
    </flux:card>
</div>
 --}}

 