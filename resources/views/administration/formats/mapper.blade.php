{{-- resources/views/administration/formats/mapper.blade.php --}}
<x-layouts.app :title="'Mapear Formato: ' . $format->name">
    <div class="flex h-full w-full flex-1 flex-col gap-4 p-1 sm:p-4 md:p-6">
        <div class="flex items-center justify-between">
            <div>
                <x-breadcrumb class="mb-1" :items="[
                    ['label' => 'Administración', 'route' => 'administracion.index'],
                    ['label' => 'Formatos', 'route' => 'formats.index'],
                    ['label' => 'Mapear: ' . $format->name],
                ]" />
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Mapear Formato: {{ $format->name }}
                </h1>
            </div>
        </div>

        <div id="pdf-mapper-root"
            data-format-id="{{ $format->id }}"
            data-format-slug="{{ $format->slug }}"
            data-format-name="{{ $format->name }}"
            data-format-file="{{ $format->file }}"
            data-format-mapping='@json($existingMapping ?? [])'
            data-save-url="{{ route('formats.save-mapping', $format) }}"
            data-pdf-url="{{ $format->file ? asset('formats/' . $format->file) : '' }}"
            data-csrf-token="{{ csrf_token() }}"
            style="height: 80vh; border-radius: 16px; overflow: hidden; border: 1px solid var(--color-zinc-700);">
        </div>
    </div>

    {{-- Cargar el mapper --}}
    @vite('resources/js/administration/formats/pdf-mapper.jsx')
</x-layouts.app>