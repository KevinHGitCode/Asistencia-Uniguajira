@props(['color' => '#0d9488'])

<div
    {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-lg border px-4 py-3 text-sm text-gray-900 dark:text-white']) }}
    style="--note-color: {{ $color }}; border-color: color-mix(in srgb, var(--note-color) 35%, transparent); background-color: color-mix(in srgb, var(--note-color) 10%, transparent);"
>
    <flux:icon.information-circle class="mt-0.5 size-5 shrink-0" style="color: var(--note-color)" />
    <span>{{ $slot }}</span>
</div>
