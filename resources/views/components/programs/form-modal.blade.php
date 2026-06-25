@props([
    'campuses' => [],
    'activeCampusId' => null,
    'isSuperadmin' => false,
    'academicPrograms' => [],
])

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
             class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-neutral-200 dark:border-zinc-700 z-10">

            <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 dark:border-zinc-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white"
                    x-text="editingId ? 'Editar Programa' : 'Nuevo Programa'"></h3>
                <button @click="closeForm()"
                    class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                    <flux:icon.x-mark class="size-5" />
                </button>
            </div>

            <form
                :action="editingId
                    ? '{{ route('programs.update', '__id__') }}'.replace('__id__', editingId)
                    : '{{ route('programs.store') }}'"
                method="POST"
                class="px-6 py-5 flex flex-col gap-4">
                @csrf

                <div class="flex rounded-lg bg-zinc-100 p-1 dark:bg-zinc-800">
                    <button type="button"
                            @click="programMode = 'existing'"
                            :class="programMode === 'existing' ? 'bg-white text-[#2563eb] shadow-sm dark:bg-zinc-700 dark:text-blue-300' : 'text-gray-500 dark:text-gray-400'"
                            class="flex-1 rounded-md px-3 py-2 text-sm font-medium transition-colors">
                        Usar existente
                    </button>
                    <button type="button"
                            @click="programMode = 'new'; $nextTick(() => $refs.nameInput.focus())"
                            :class="programMode === 'new' ? 'bg-white text-[#2563eb] shadow-sm dark:bg-zinc-700 dark:text-blue-300' : 'text-gray-500 dark:text-gray-400'"
                            class="flex-1 rounded-md px-3 py-2 text-sm font-medium transition-colors">
                        Crear nuevo
                    </button>
                </div>

                <div x-show="programMode === 'existing'" x-cloak class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Programa académico existente
                    </label>
                    <input type="hidden" :name="programMode === 'existing' ? 'academic_program_id' : null" x-model="formAcademicProgramId">
                    <x-ui.searchable-select
                        x-model="formAcademicProgramId"
                        :options="$academicPrograms"
                        placeholder="Crear uno nuevo con el nombre"
                        empty-label="Crear uno nuevo"
                        :allow-empty="true" />
                    @error('academic_program_id')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="programMode === 'new'" x-cloak class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nombre del programa académico
                    </label>
                    <input type="text" :name="programMode === 'new' ? 'name' : null" x-model="formName" x-ref="nameInput"
                        x-init="$watch('showForm', v => v && programMode === 'new' && $nextTick(() => $refs.nameInput.focus()))"
                        maxlength="100" placeholder="Ej: Ingeniería de Sistemas"
                        class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-[#2563eb] transition" />
                    @error('name')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                @if($isSuperadmin)
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Sede <span class="text-red-500">*</span>
                        </label>
                        <input type="hidden" name="campus_id" x-model="formCampusId">
                        <x-ui.searchable-select
                            x-model="formCampusId"
                            :options="$campuses"
                            :value="$activeCampusId"
                            placeholder="Selecciona una sede"
                            empty-label="Selecciona una sede"
                            :allow-empty="true" />
                        @error('campus_id')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Lugar de oferta
                    </label>
                    <input type="text" name="offer_location" x-model="formOfferLocation" maxlength="100"
                        placeholder="Ej: Convenio Jorge Artel (opcional)"
                        class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-[#2563eb] transition" />
                    <p class="text-xs text-gray-400 dark:text-gray-500">Úsalo para convenios o extensiones gestionados desde una sede.</p>
                    @error('offer_location')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tipo de programa
                    </label>
                    <input type="hidden" name="program_type" x-model="formType">
                    <x-ui.searchable-select
                        x-model="formType"
                        :options="['Pregrado' => 'Pregrado', 'Posgrado' => 'Posgrado']"
                        placeholder="— Sin definir —"
                        empty-label="— Sin definir —" />
                    @error('program_type')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="closeForm()"
                        class="px-4 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm rounded-lg bg-[#2563eb] hover:bg-[#1d4ed8] text-white font-medium transition-colors shadow-sm cursor-pointer"
                        x-text="editingId ? 'Guardar cambios' : 'Crear programa'">
                    </button>
                </div>
            </form>
        </div>
    </div>
