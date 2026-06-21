{{--
    Selector con búsqueda (selección única).

    Uso con Livewire:
        <x-ui.searchable-select :options="$catalogPrograms" wire:model="editRoles.0.program_id"
            placeholder="Sin programa" empty-label="Sin programa" />

    Uso sin Livewire (form clásico): pasar `name` + `:value`.

    Props:
      - options       array de ['id'=>, 'name'=>] | ['value'=>, 'label'=>] | objetos | escalares
      - value-key     clave del valor en cada opción (def. 'id')
      - label-key     clave de la etiqueta (def. 'name')
      - placeholder   texto cuando no hay selección
      - empty-label   etiqueta de la opción "sin selección"
      - allow-empty   muestra la opción vacía (def. true)
      - name          (opcional) genera <input hidden> para formularios sin Livewire
--}}
@props([
    'options' => [],
    'valueKey' => 'id',
    'labelKey' => 'name',
    'placeholder' => 'Selecciona una opción…',
    'searchPlaceholder' => 'Escribe para buscar…',
    'allowEmpty' => true,
    'emptyLabel' => 'Sin selección',
    'value' => null,
    'name' => null,
])
@php
    $wireDirective = $attributes->wire('model');
    $wireModel = $wireDirective->value();
    $hasWire = filled($wireModel);
    $isLive = $hasWire && $wireDirective->hasModifier('live');

    $normalized = \App\Support\SelectOptions::normalize($options, $valueKey, $labelKey);

    $config = [
        'options' => $normalized,
        'model' => $hasWire ? $wireModel : null,
        'hasWire' => $hasWire,
        'live' => $isLive,
        'allowEmpty' => (bool) $allowEmpty,
        'emptyLabel' => $emptyLabel,
        'placeholder' => $placeholder,
        'initialValue' => $value,
    ];
@endphp

<div
    x-data="searchableSelect(@js($config))"
    x-modelable="value"
    x-on:keydown.escape.stop="close()"
    @click.outside="close()"
    {{ $attributes->whereDoesntStartWith('wire:model')->merge(['class' => 'relative']) }}
>
    {{-- Input oculto para formularios sin Livewire --}}
    @if($name && !$hasWire)
        <input type="hidden" name="{{ $name }}" :value="value">
    @endif

    {{-- Disparador --}}
    <button type="button"
        x-on:click="toggle()"
        x-on:keydown.arrow-down.prevent="move(1)"
        x-on:keydown.arrow-up.prevent="move(-1)"
        x-on:keydown.enter.prevent="open ? onEnter() : openPanel()"
        :class="open ? 'ring-2 ring-blue-500 border-blue-500' : ''"
        class="flex w-full items-center justify-between gap-2 px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-left text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
        <span class="truncate" :class="selectedLabel ? '' : 'text-gray-400'"
              x-text="selectedLabel || @js($placeholder)"></span>
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
            {{-- Opción "vacía" --}}
            @if($allowEmpty)
                <li x-show="!search">
                    <button type="button" x-on:click="clear()"
                        :class="value === '' ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-gray-400'"
                        class="w-full px-3 py-2 text-left hover:bg-blue-50 dark:hover:bg-zinc-700 transition-colors cursor-pointer">
                        {{ $emptyLabel }}
                    </button>
                </li>
            @endif

            <template x-for="(opt, i) in filtered" :key="opt.value">
                <li :data-index="i">
                    <button type="button"
                        x-on:click="select(opt)"
                        x-on:mouseenter="highlighted = i"
                        :class="{
                            'bg-blue-50 dark:bg-zinc-700': highlighted === i,
                            'font-semibold text-blue-600 dark:text-blue-400': opt.value === value,
                            'text-gray-800 dark:text-gray-200': opt.value !== value
                        }"
                        class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left hover:bg-blue-50 dark:hover:bg-zinc-700 transition-colors cursor-pointer">
                        <span class="truncate" x-text="opt.label"></span>
                        <svg x-show="opt.value === value" class="size-4 shrink-0 text-blue-600 dark:text-blue-400"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </button>
                </li>
            </template>

            <li x-show="filtered.length === 0" class="px-3 py-3 text-center text-gray-400 dark:text-zinc-500">
                Sin resultados
            </li>
        </ul>
    </div>
</div>
