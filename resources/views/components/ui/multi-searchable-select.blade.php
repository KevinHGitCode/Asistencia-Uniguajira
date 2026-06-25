{{--
    Selector con búsqueda (selección múltiple). Hereda la lógica de búsqueda y
    teclado del selector único; añade chips arriba con "x" y valida que no se
    pueda agregar el mismo elemento dos veces.

    Uso con Livewire (la propiedad debe ser un array):
        <x-ui.multi-searchable-select :options="$catalogPrograms" wire:model="selectedProgramIds"
            placeholder="Agregar programas…" />

    Props: iguales a x-ui.searchable-select salvo que el valor es un array.
      - values   (opcional) valor inicial sin Livewire (array)
      - name     (opcional) genera <input hidden name="...[]"> por cada valor
--}}
@props([
    'options' => [],
    'valueKey' => 'id',
    'labelKey' => 'name',
    'placeholder' => 'Selecciona una o más opciones…',
    'searchPlaceholder' => 'Escribe para buscar…',
    'values' => [],
    'name' => null,
])
@php
    $wireDirective = $attributes->wire('model');
    $wireModel = $wireDirective->value();
    $hasWire = is_string($wireModel) && $wireModel !== '';
    $isLive = $hasWire && $wireDirective->hasModifier('live');

    $normalized = \App\Support\SelectOptions::normalize($options, $valueKey, $labelKey);

    $config = [
        'options' => $normalized,
        'model' => $hasWire ? $wireModel : null,
        'hasWire' => $hasWire,
        'live' => $isLive,
        'placeholder' => $placeholder,
        'initialValue' => array_values((array) $values),
    ];
@endphp

<div
    x-data="multiSearchableSelect(@js($config))"
    x-modelable="values"
    x-on:keydown.escape.stop="close()"
    @click.outside="close()"
    {{ $attributes->whereDoesntStartWith('wire:model')->merge(['class' => 'relative']) }}
>
    {{-- Inputs ocultos para formularios sin Livewire --}}
    @if($name && !$hasWire)
        <template x-for="v in values" :key="v">
            <input type="hidden" name="{{ $name }}[]" :value="v">
        </template>
    @endif

    {{-- Chips de seleccionados --}}
    <div x-show="selectedOptions.length" class="flex flex-wrap gap-1.5 mb-2">
        <template x-for="opt in selectedOptions" :key="opt.value">
            <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 pl-2.5 pr-1 py-0.5 text-xs font-medium">
                <span class="truncate max-w-[12rem]" x-text="opt.label"></span>
                <button type="button" x-on:click="remove(opt.value)"
                    class="inline-flex items-center justify-center rounded-full p-0.5 hover:bg-blue-200 dark:hover:bg-blue-800/60 transition-colors cursor-pointer"
                    :aria-label="'Quitar ' + opt.label">
                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </span>
        </template>
    </div>

    {{-- Disparador --}}
    <button type="button"
        x-on:click="open ? close() : openPanel()"
        x-on:keydown.arrow-down.prevent="move(1)"
        x-on:keydown.arrow-up.prevent="move(-1)"
        x-on:keydown.enter.prevent="open ? onEnter() : openPanel()"
        :class="open ? 'ring-2 ring-blue-500 border-blue-500' : ''"
        class="flex w-full items-center justify-between gap-2 px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-left text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
        <span class="truncate text-gray-400" x-text="@js($placeholder)"></span>
        <svg class="size-4 shrink-0 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''"
             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    {{-- Panel --}}
    <div x-show="open" x-cloak x-transition
         class="absolute z-[70] mt-1 w-full rounded-lg border border-neutral-200 dark:border-zinc-600 bg-white dark:bg-zinc-800 shadow-lg">

        {{-- Buscador (solo si hay más de SEARCH_MIN_ITEMS opciones) --}}
        <div x-show="showSearch" class="p-2 border-b border-neutral-100 dark:border-zinc-700">
            <input x-ref="search" type="text" x-model="search"
                placeholder="{{ $searchPlaceholder }}"
                autocomplete="off"
                x-on:input="highlighted = 0"
                x-on:keydown.enter.prevent.stop="onEnter()"
                x-on:keydown.arrow-down.prevent="move(1)"
                x-on:keydown.arrow-up.prevent="move(-1)"
                class="w-full px-2.5 py-1.5 rounded-md border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>

        <ul x-ref="list" class="max-h-52 overflow-y-auto py-1 text-sm">
            <template x-for="(opt, i) in available" :key="opt.value">
                <li :data-index="i">
                    <button type="button"
                        x-on:click="add(opt)"
                        x-on:mouseenter="highlighted = i"
                        :class="highlighted === i ? 'bg-blue-50 dark:bg-zinc-700' : ''"
                        class="flex w-full items-center gap-2 px-3 py-2 text-left text-gray-800 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-zinc-700 transition-colors cursor-pointer">
                        <svg class="size-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span class="truncate" x-text="opt.label"></span>
                    </button>
                </li>
            </template>

            <li x-show="available.length === 0" class="px-3 py-3 text-center text-gray-400 dark:text-zinc-500"
                x-text="search ? 'Sin resultados' : 'No quedan opciones por agregar'"></li>
        </ul>
    </div>
</div>
