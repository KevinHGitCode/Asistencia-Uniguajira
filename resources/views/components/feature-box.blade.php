@props([
    'title',
    'description',
    'iconType' => 'calendar', // valor por defecto
])
<div class="flex items-center space-x-2 bg-white/10 backdrop-blur-sm rounded-lg p-2.5">
    <div class="w-10 h-10 bg-[#ad3728] rounded-lg flex items-center justify-center">
        <flux:icon :name="$iconType" class="w-6 h-6 text-white" />
    </div>
    <div class="space-y-0">
        <h3 class="font-semibold">{{ $title }}</h3>
        <p class="text-gray-300 text-sm">{{ $description }}</p>
    </div>
</div>
