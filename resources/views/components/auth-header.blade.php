@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <flux:heading size="xl">{{ $title }}</flux:heading>
    <flux:subheading class="hidden sm:block">{{ $description }}</flux:subheading>
</div>
