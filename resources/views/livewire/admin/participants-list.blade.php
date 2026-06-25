<div>
    {{-- Flash de éxito --}}
    @if(session('participant-success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="mb-4 flex items-center gap-3 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 text-sm">
            <flux:icon.check-circle class="size-5 shrink-0" />
            {{ session('participant-success') }}
        </div>
    @endif

    {{-- Flash informativo (reactivación de roles) --}}
    @if(session('participant-info'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="mb-4 flex items-center gap-3 px-4 py-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-400 text-sm">
            <flux:icon.information-circle class="size-5 shrink-0" />
            {{ session('participant-info') }}
        </div>
    @endif

    {{-- Flash de error --}}
    @if(session('participant-error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="mb-4 flex items-center gap-3 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
            <flux:icon.x-circle class="size-5 shrink-0" />
            {{ session('participant-error') }}
        </div>
    @endif


    {{-- ======================== MODAL: EDITAR ======================== --}}
    @if($showEditModal)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4"
             x-data x-trap.noscroll="true">

            <div class="fixed inset-0 bg-black/50 dark:bg-black/70" wire:click="closeEdit"></div>

            <div class="relative z-10 my-6 w-full max-w-2xl overflow-visible rounded-2xl border border-neutral-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 dark:border-zinc-700 sticky top-0 bg-white dark:bg-zinc-900 z-10">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Editar Participante</h3>
                    <button wire:click="closeEdit"
                        class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>

                <form wire:submit="updateParticipant" class="relative z-20 flex flex-col gap-5 overflow-visible px-6 py-5">

                    {{-- ── Datos básicos ── --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Documento --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Documento <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="editDocument" required maxlength="20"
                                class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                            @error('editDocument')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Código estudiantil --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Código estudiantil</label>
                            <input type="text" wire:model="editStudentCode" maxlength="20"
                                class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                            @error('editStudentCode')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Nombres --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Nombres <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="editFirstName" required maxlength="100"
                                class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                            @error('editFirstName')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Apellidos --}}
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Apellidos <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="editLastName" required maxlength="100"
                                class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                            @error('editLastName')
                                <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Correo --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Correo electrónico</label>
                        <input type="email" wire:model="editEmail" maxlength="255"
                            placeholder="correo@ejemplo.com"
                            class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                        @error('editEmail')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- ── Roles / Estamentos ── --}}
                    <div class="relative z-20 overflow-visible border-t border-neutral-200 dark:border-zinc-700 pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Roles / Estamentos</h4>
                            <button type="button" wire:click="addRole"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors cursor-pointer">
                                <flux:icon.plus class="size-3.5" />
                                Agregar rol
                            </button>
                        </div>

                        <div class="relative flex flex-col gap-3 overflow-visible">
                            @foreach($editRoles as $index => $role)
                                @php
                                    $typeCategory = $this->getTypeCategory($role['participant_type_id']);
                                @endphp
                                <div class="relative overflow-visible rounded-xl border border-neutral-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 p-4"
                                     style="z-index: {{ count($editRoles) - $index + 20 }}"
                                     wire:key="role-{{ $index }}">

                                    {{-- Botón eliminar rol --}}
                                    @if(count($editRoles) > 1)
                                        <button type="button" wire:click="removeRole({{ $index }})"
                                            class="absolute top-2 right-2 p-1 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 dark:hover:text-red-400 transition-colors cursor-pointer"
                                            title="Eliminar rol">
                                            <flux:icon.x-mark class="size-4" />
                                        </button>
                                    @endif

                                    <div class="relative flex flex-col gap-3 overflow-visible">
                                        {{-- Estamento --}}
                                        <div class="relative z-40 flex flex-col gap-1.5">
                                            <label class="text-xs font-medium text-gray-600 dark:text-gray-400">
                                                Estamento <span class="text-red-500">*</span>
                                            </label>
                                            <x-ui.searchable-select
                                                wire:key="type-select-{{ $index }}"
                                                wire:model.live="editRoles.{{ $index }}.participant_type_id"
                                                :options="$catalogTypes"
                                                placeholder="Selecciona un estamento…"
                                                empty-label="Selecciona un estamento…"
                                                search-placeholder="Buscar estamento…" />
                                            @error("editRoles.{$index}.participant_type_id")
                                                <p class="text-xs text-red-500">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        {{-- Programa (Estudiante, Graduado, Docente) --}}
                                        @if($typeCategory === 'program')
                                            <div class="relative z-30 flex flex-col gap-1.5">
                                                <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Programa</label>
                                                <x-ui.searchable-select
                                                    wire:key="program-select-{{ $index }}"
                                                    wire:model="editRoles.{{ $index }}.program_id"
                                                    :options="$catalogPrograms"
                                                    placeholder="Sin programa"
                                                    empty-label="Sin programa"
                                                    search-placeholder="Buscar programa…" />
                                            </div>
                                        @endif

                                        {{-- Dependencia (Administrativo) --}}
                                        @if($typeCategory === 'dependency')
                                            <div class="relative z-30 flex flex-col gap-1.5">
                                                <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Dependencia</label>
                                                <x-ui.searchable-select
                                                    wire:key="dependency-select-{{ $index }}"
                                                    wire:model="editRoles.{{ $index }}.dependency_id"
                                                    :options="$catalogDependencies"
                                                    placeholder="Sin dependencia"
                                                    empty-label="Sin dependencia"
                                                    search-placeholder="Buscar dependencia…" />
                                            </div>
                                        @endif

                                        {{-- Organización (Comunidad Externa) --}}
                                        @if($typeCategory === 'organization')
                                            <div class="relative z-30 flex flex-col gap-1.5"
                                                 x-data="{ orgOpen: false }"
                                                 x-on:click.outside="orgOpen = false"
                                                 x-effect="if ($wire.organizationSearchIndex === {{ $index }} && $wire.organizationSuggestions.length > 0) orgOpen = true">
                                                <label class="text-xs font-medium text-gray-600 dark:text-gray-400">
                                                    Organización / Institución
                                                </label>
                                                <input type="text"
                                                    wire:model="editRoles.{{ $index }}.organization_name"
                                                    wire:input.debounce.300ms="searchOrganizations({{ $index }}, $event.target.value)"
                                                    autocomplete="off"
                                                    maxlength="150"
                                                    placeholder="Ej: Alcaldía de Riohacha"
                                                    x-on:focus="if ($wire.organizationSearchIndex === {{ $index }} && $wire.organizationSuggestions.length) orgOpen = true"
                                                    class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />

                                                <ul x-show="orgOpen && $wire.organizationSearchIndex === {{ $index }} && $wire.organizationSuggestions.length > 0"
                                                    x-transition x-cloak
                                                    class="absolute z-[70] top-full mt-1 w-full rounded-lg border border-neutral-200 bg-white shadow-lg dark:border-zinc-600 dark:bg-zinc-700 max-h-40 overflow-y-auto">
                                                    <template x-for="org in $wire.organizationSuggestions" :key="org.id">
                                                        <li>
                                                            <button type="button"
                                                                x-on:mousedown.prevent="
                                                                    $wire.selectRoleOrganization({{ $index }}, org.id, org.name);
                                                                    orgOpen = false;
                                                                "
                                                                class="w-full px-3 py-2 text-left text-sm text-gray-800 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-zinc-600 transition-colors cursor-pointer"
                                                                x-text="org.name">
                                                            </button>
                                                        </li>
                                                    </template>
                                                </ul>
                                            </div>
                                        @endif

                                        {{-- Vinculación (Estudiante, Graduado, Docente, Administrativo) --}}
                                        @if(in_array($typeCategory, ['program', 'dependency']))
                                            <div class="relative z-20 flex flex-col gap-1.5">
                                                <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Vinculación</label>
                                                <x-ui.searchable-select
                                                    wire:key="affiliation-select-{{ $index }}"
                                                    wire:model="editRoles.{{ $index }}.affiliation_id"
                                                    :options="$catalogAffiliations"
                                                    placeholder="Sin vinculación"
                                                    empty-label="Sin vinculación"
                                                    search-placeholder="Buscar vinculación…" />
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @error('editRoles')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Error de roles duplicados (inline dentro del modal) --}}
                    @if($roleError)
                        <div class="flex items-start gap-3 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
                            <flux:icon.exclamation-triangle class="size-5 shrink-0 mt-0.5" />
                            <span>{{ $roleError }}</span>
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="closeEdit"
                            class="px-4 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm rounded-lg bg-[#3b82f6] hover:bg-blue-700 text-white font-medium transition-colors shadow-sm cursor-pointer">
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ======================== MODAL: ELIMINAR ======================== --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data x-trap.noscroll="true">

            <div class="absolute inset-0 bg-black/50 dark:bg-black/70" wire:click="closeDelete"></div>

            <div class="relative w-full max-w-sm bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-neutral-200 dark:border-zinc-700 z-10 p-6 flex flex-col gap-4"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-50 dark:bg-red-900/30 mx-auto">
                    <flux:icon.exclamation-triangle class="size-6 text-red-500" />
                </div>

                <div class="text-center">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">¿Eliminar participante?</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Estás por eliminar a <strong class="text-gray-800 dark:text-gray-200">"{{ $deletingName }}"</strong>.
                        Se eliminarán también todos sus roles. Esta acción no se puede deshacer.
                    </p>
                </div>

                <div class="flex gap-3">
                    <button type="button" wire:click="closeDelete"
                        class="flex-1 px-4 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                        Cancelar
                    </button>
                    <button type="button" wire:click="deleteParticipant"
                        class="flex-1 px-4 py-2 text-sm rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium transition-colors shadow-sm cursor-pointer">
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
