@props(['eventId', 'formats', 'event'])

@if($formats->count() > 1)
<flux:modal name="format-select-{{ $eventId }}" class="max-w-md">
    <div class="space-y-4">
        <div class="border-b border-zinc-200 dark:border-zinc-700 pb-3">
            <flux:heading size="lg">Seleccionar formato</flux:heading>
            <flux:text class="mt-1">Elige el formato de asistencia que deseas descargar.</flux:text>
        </div>

        <div class="space-y-2">
            @foreach($formats as $format)
                <a href="{{ route('events.download', [$event->id, $format->slug]) }}"
                   class="flex items-center gap-3 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-green-50 dark:hover:bg-green-950 hover:border-green-300 dark:hover:border-green-700 transition-colors">
                    <div class="rounded-lg bg-green-100 dark:bg-green-900 p-2">
                        <svg class="size-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-zinc-900 dark:text-white">{{ $format->name }}</p>
                    </div>
                    <svg class="size-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </a>
            @endforeach
        </div>

        <div class="flex justify-end pt-2">
            <flux:button 
                variant="ghost"
                class="cursor-pointer"
                x-on:click="$dispatch('modal-close', { name: 'format-select-{{ $eventId }}' })">
                Cancelar
            </flux:button>
        </div>
    </div>
</flux:modal>
@endif