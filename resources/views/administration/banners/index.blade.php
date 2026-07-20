<x-layouts.app :title="__('Banners')">

<div class="flex min-h-full w-full flex-1 flex-col gap-6 p-1 pb-8 sm:p-4 sm:pb-10 md:p-6 md:pb-12" x-data="bannersManager()">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <x-breadcrumb class="mb-1" :items="[
                ['label' => 'Administración', 'route' => 'administracion.index'],
                ['label' => 'Banners'],
            ]" />
            <h1 class="flex items-center gap-2 text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                <flux:icon name="megaphone" class="size-16 text-[#f97316]" />
                <span>Banners de anuncios</span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $banners->count() }} {{ Str::plural('banner', $banners->count()) }} registrado{{ $banners->count() !== 1 ? 's' : '' }}
            </p>
        </div>
        <button @click="openCreate()"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-[#f97316] text-white text-sm font-medium transition-colors shadow-sm self-start sm:self-auto cursor-pointer">
            <flux:icon.plus class="size-4" />
            Nuevo Banner
        </button>
    </div>

    <x-administration.info-note color="#f97316">
        Los <strong>banners</strong> son anuncios de patrocinadores que se muestran de forma discreta en la parte inferior de la página pública de registro de asistencia (acceso por QR). Se elige al azar un banner <strong>activo y vigente</strong> por visita, y se cuentan sus impresiones y clics para rendir cuentas al patrocinador.
    </x-administration.info-note>

    {{-- Alertas --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-center gap-3 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 text-sm">
            <flux:icon.check-circle class="size-5 shrink-0" />
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div x-data="{ show: true }" x-show="show"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-start gap-3 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
            <flux:icon.x-circle class="size-5 shrink-0 mt-0.5" />
            <div>
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
            <button @click="show = false" class="ml-auto p-1 rounded hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors cursor-pointer">
                <flux:icon.x-mark class="size-4" />
            </button>
        </div>
    @endif

    {{-- Tabla --}}
    <div class="border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm overflow-hidden">

        <div class="px-4 sm:px-6 py-4 border-b border-neutral-200 dark:border-neutral-700 bg-zinc-50 dark:bg-zinc-900 flex items-center justify-between gap-4">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">Listado de Banners</h2>
            <div class="relative">
                <flux:icon.magnifying-glass class="size-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                <input type="text" x-model="search" placeholder="Buscar..."
                    class="pl-9 pr-4 py-1.5 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition w-40 sm:w-56" />
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-100 dark:border-zinc-800 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                        <th class="px-4 sm:px-6 py-3 text-left font-medium">#</th>
                        <th class="px-4 sm:px-6 py-3 text-left font-medium">Banner</th>
                        <th class="px-4 sm:px-6 py-3 text-left font-medium">Vista previa</th>
                        <th class="px-4 sm:px-6 py-3 text-center font-medium">Vigencia</th>
                        <th class="px-4 sm:px-6 py-3 text-center font-medium">Estado</th>
                        <th class="px-4 sm:px-6 py-3 text-center font-medium" title="Peso de la rotación: 3 aparece el triple que 1">Peso</th>
                        <th class="px-4 sm:px-6 py-3 text-center font-medium">Impresiones</th>
                        <th class="px-4 sm:px-6 py-3 text-center font-medium">Clics</th>
                        <th class="px-4 sm:px-6 py-3 text-right font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-zinc-800">
                    @forelse($banners as $banner)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors"
                            x-show="search === '' || {{ Js::from(strtolower($banner->name)) }}.includes(search.toLowerCase())"
                            x-transition>
                            <td class="px-4 sm:px-6 py-4 text-gray-400 dark:text-zinc-500 font-mono text-xs">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-4 sm:px-6 py-4">
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $banner->name }}</span>
                                    @if($banner->target_url)
                                        <a href="{{ $banner->target_url }}" target="_blank" rel="noopener"
                                           class="text-xs text-blue-600 dark:text-blue-400 hover:underline truncate max-w-[16rem]"
                                           title="{{ $banner->target_url }}">
                                            {{ Str::limit($banner->target_url, 40) }}
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400">Sin enlace</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 sm:px-6 py-4">
                                <img src="{{ route('banners.image', $banner) }}?v={{ $banner->fileRecord?->hash }}"
                                     alt="Vista previa de {{ $banner->name }}"
                                     loading="lazy"
                                     class="h-10 max-w-[10rem] rounded-lg border border-neutral-200 dark:border-zinc-700 object-contain bg-white" />
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-center text-xs text-gray-500 dark:text-gray-400">
                                @if($banner->starts_at || $banner->ends_at)
                                    {{ $banner->starts_at?->format('d/m/Y') ?? '∞' }} &ndash; {{ $banner->ends_at?->format('d/m/Y') ?? '∞' }}
                                @else
                                    Sin límite
                                @endif
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-center">
                                @if($banner->isVigente())
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 dark:bg-emerald-900/40 px-2 py-0.5 text-[11px] font-medium text-emerald-700 dark:text-emerald-300">
                                        Vigente
                                    </span>
                                @elseif(!$banner->active)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-zinc-800 px-2 py-0.5 text-[11px] font-medium text-gray-500 dark:text-gray-400">
                                        Inactivo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 dark:bg-amber-900/40 px-2 py-0.5 text-[11px] font-medium text-amber-700 dark:text-amber-300"
                                          title="Está activo pero fuera de su ventana de fechas.">
                                        Fuera de fechas
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-center font-mono text-xs text-gray-600 dark:text-gray-300">
                                ×{{ $banner->weight }}
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-center font-mono text-xs text-gray-600 dark:text-gray-300">
                                {{ number_format($banner->impressions, 0, ',', '.') }}
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-center font-mono text-xs text-gray-600 dark:text-gray-300">
                                {{ number_format($banner->clicks, 0, ',', '.') }}
                            </td>
                            <td class="px-4 sm:px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('banners.report', $banner) }}"
                                       class="p-1.5 rounded-lg text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 dark:hover:text-emerald-400 transition-colors cursor-pointer"
                                       title="Reporte de impresiones y clics">
                                        <flux:icon name="chart-bar" class="size-4" />
                                    </a>
                                    <button
                                        @click="openEdit({{ $banner->id }}, {{ Js::from($banner->name) }}, {{ Js::from($banner->target_url) }}, {{ Js::from($banner->starts_at?->format('Y-m-d')) }}, {{ Js::from($banner->ends_at?->format('Y-m-d')) }}, {{ $banner->active ? 'true' : 'false' }}, {{ Js::from(route('banners.image', $banner)) }}, {{ $banner->weight }})"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 dark:hover:text-blue-400 transition-colors cursor-pointer"
                                        title="Editar">
                                        <flux:icon.pencil-square class="size-4" />
                                    </button>
                                    <button
                                        @click="openDelete({{ $banner->id }}, {{ Js::from($banner->name) }})"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 dark:hover:text-red-400 transition-colors cursor-pointer"
                                        title="Eliminar">
                                        <flux:icon.trash class="size-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400 dark:text-zinc-500">
                                    <flux:icon name="megaphone" class="size-12 opacity-30" />
                                    <p class="text-sm">No hay banners registrados aún.</p>
                                    <button @click="openCreate()"
                                        class="text-sm text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                                        Crear el primer banner
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal crear / editar --}}
    <div x-show="showForm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">

        <div class="absolute inset-0 bg-black/50 dark:bg-black/70" @click="closeForm()"></div>

        <div x-show="showForm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-neutral-200 dark:border-zinc-700 z-10 max-h-[90dvh] overflow-y-auto">

            <div class="px-6 py-4 border-b border-neutral-200 dark:border-zinc-700 flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white" x-text="editId ? 'Editar banner' : 'Nuevo banner'"></h3>
                <button @click="closeForm()" class="p-1 rounded-lg text-gray-400 hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                    <flux:icon.x-mark class="size-5" />
                </button>
            </div>

            <form :action="editId ? '{{ route('banners.update', '__id__') }}'.replace('__id__', editId) : '{{ route('banners.store') }}'"
                  method="POST" enctype="multipart/form-data" class="px-6 py-5 flex flex-col gap-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre / patrocinador <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="formName" required maxlength="255"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                        placeholder="Ej: Librería El Saber" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Enlace del anuncio</label>
                    <input type="url" name="target_url" x-model="formUrl" maxlength="2048"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                        placeholder="https://..." />
                    <p class="mt-1 text-xs text-gray-400">Opcional. Si se deja vacío, el banner no será clicable.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Imagen <span class="text-red-500" x-show="!editId">*</span>
                    </label>
                    <img x-show="editId && editImageUrl" :src="editImageUrl" alt="Imagen actual"
                         class="mb-2 h-12 rounded-lg border border-neutral-200 dark:border-zinc-700 object-contain bg-white" />
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp" :required="!editId"
                        class="w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-[#f97316]/10 file:px-3 file:py-2 file:text-sm file:font-medium file:text-[#f97316] file:cursor-pointer cursor-pointer" />
                    <p class="mt-1 text-xs text-gray-400">
                        JPG, PNG o WebP, máx. 2MB. Recomendado: horizontal y ligera (≈ 600×80 px), se muestra con ~50 px de alto.
                        <span x-show="editId">Si no eliges archivo, se conserva la imagen actual.</span>
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vigente desde</label>
                        <input type="date" name="starts_at" x-model="formStartsAt"
                            class="w-full px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vigente hasta</label>
                        <input type="date" name="ends_at" x-model="formEndsAt"
                            class="w-full px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                    </div>
                </div>
                <p class="-mt-2 text-xs text-gray-400">Fechas opcionales: sin fechas, el banner rota mientras esté activo.</p>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Peso en la rotación</label>
                    <input type="number" name="weight" x-model="formWeight" min="1" max="100" step="1"
                        class="w-24 px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                    <p class="mt-1 text-xs text-gray-400">Un banner con peso 3 aparece el triple de veces que uno con peso 1.</p>
                </div>

                <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                    <input type="checkbox" name="active" value="1" x-model="formActive"
                        class="rounded border-neutral-300 dark:border-zinc-600 text-[#f97316] focus:ring-[#f97316]" />
                    Activo (participa en la rotación)
                </label>

                <div class="flex items-center gap-3 pt-2">
                    <button type="button" @click="closeForm()"
                        class="flex-1 px-4 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm rounded-lg bg-[#f97316] hover:brightness-110 text-white font-medium transition-colors cursor-pointer"
                        x-text="editId ? 'Guardar cambios' : 'Crear banner'">
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal eliminar --}}
    <div x-show="showDelete"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">

        <div class="absolute inset-0 bg-black/50 dark:bg-black/70" @click="closeDelete()"></div>

        <div x-show="showDelete"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-sm bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-neutral-200 dark:border-zinc-700 z-10">

            <div class="px-6 py-5 flex flex-col items-center gap-4 text-center">
                <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <flux:icon.exclamation-triangle class="size-6 text-red-600 dark:text-red-400" />
                </div>

                <div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">Eliminar banner</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        ¿Estás seguro de eliminar <strong x-text="deleteName" class="text-gray-900 dark:text-white"></strong>?
                        Se perderán sus contadores de impresiones y clics.
                    </p>
                </div>

                <div class="flex items-center gap-3 w-full pt-2">
                    <button @click="closeDelete()"
                        class="flex-1 px-4 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                        Cancelar
                    </button>
                    <form
                        :action="'{{ route('banners.destroy', '__id__') }}'.replace('__id__', deleteId)"
                        method="POST"
                        class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full px-4 py-2 text-sm rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium transition-colors cursor-pointer">
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function bannersManager() {
        return {
            search: '',

            showForm: false,
            editId: null,
            editImageUrl: null,
            formName: '',
            formUrl: '',
            formStartsAt: '',
            formEndsAt: '',
            formActive: true,
            formWeight: 1,

            showDelete: false,
            deleteId: null,
            deleteName: '',

            openCreate() {
                this.editId = null;
                this.editImageUrl = null;
                this.formName = '';
                this.formUrl = '';
                this.formStartsAt = '';
                this.formEndsAt = '';
                this.formActive = true;
                this.formWeight = 1;
                this.showForm = true;
            },
            openEdit(id, name, url, startsAt, endsAt, active, imageUrl, weight) {
                this.editId = id;
                this.editImageUrl = imageUrl;
                this.formName = name;
                this.formUrl = url ?? '';
                this.formStartsAt = startsAt ?? '';
                this.formEndsAt = endsAt ?? '';
                this.formActive = active;
                this.formWeight = weight ?? 1;
                this.showForm = true;
            },
            closeForm() { this.showForm = false; },

            openDelete(id, name) {
                this.deleteId = id;
                this.deleteName = name;
                this.showDelete = true;
            },
            closeDelete() { this.showDelete = false; },
        };
    }
</script>

</x-layouts.app>
