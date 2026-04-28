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

    {{-- Buscador --}}
    <div class="mb-4">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400 pointer-events-none"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
            </svg>
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Buscar por nombre, documento o correo…"
                class="w-full pl-9 pr-4 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700
                       bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-gray-100
                       placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
            />
        </div>
    </div>

    {{-- Tabla --}}
    <div class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-zinc-700">
        <table class="min-w-[1000px] w-full divide-y divide-neutral-200 dark:divide-zinc-700 text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Documento</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Nombre</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Estamento(s)</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Programa(s)</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Dependencia(s)</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Vinculación</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Correo</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-neutral-100 dark:divide-zinc-800">
                @forelse ($participants as $participant)
                    @php
                        $typeNames              = $participant->activeRoles->pluck('type.name')->filter()->unique()->values();
                        $primaryType            = $typeNames->first();
                        $extraTypesCount        = max(0, $typeNames->count() - 1);

                        $programNames           = $participant->activeRoles->pluck('program.name')->filter()->unique()->values();
                        $primaryProgram         = $programNames->first();
                        $extraProgramsCount     = max(0, $programNames->count() - 1);

                        $dependencyNames        = $participant->activeRoles->pluck('dependency.name')->filter()->unique()->values();
                        $primaryDependency      = $dependencyNames->first();
                        $extraDependenciesCount = max(0, $dependencyNames->count() - 1);

                        $affiliationNames       = $participant->activeRoles->pluck('affiliation.name')->filter()->unique()->values();
                        $primaryAffiliation     = $affiliationNames->first();
                        $extraAffiliationsCount = max(0, $affiliationNames->count() - 1);
                    @endphp
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                        <td class="px-4 py-3 font-mono text-xs text-gray-600 dark:text-zinc-400 whitespace-nowrap">
                            {{ $participant->document }}
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900 dark:text-gray-100">
                                {{ $participant->first_name }} {{ $participant->last_name }}
                            </p>
                            @if($participant->student_code)
                                <p class="text-xs text-gray-400 dark:text-zinc-500">Cód. {{ $participant->student_code }}</p>
                            @endif
                        </td>

                        {{-- Estamento(s) --}}
                        <td class="px-4 py-3">
                            @if($typeNames->isNotEmpty())
                                <div class="flex items-center gap-1.5" x-data="floatingTooltip()">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300 max-w-[10rem] truncate" title="{{ $primaryType }}">
                                        {{ $primaryType }}
                                    </span>
                                    @if($extraTypesCount > 0)
                                        <button type="button" x-ref="trigger"
                                            @mouseenter="show($refs.trigger)"
                                            @mouseleave="hide()"
                                            class="inline-flex items-center rounded-full bg-teal-600 px-2 py-0.5 text-xs font-semibold text-white hover:brightness-110 focus:outline-none cursor-pointer">
                                            +{{ $extraTypesCount }}
                                        </button>
                                        <template x-teleport="body">
                                            <div x-show="open" x-transition.opacity.duration.150ms
                                                 :style="`position:fixed;top:${y}px;left:${x}px;`"
                                                 class="z-[9999] min-w-48 max-w-xs rounded-lg border border-neutral-200 bg-white p-3 text-xs text-zinc-700 shadow-lg dark:border-neutral-700 dark:bg-zinc-900 dark:text-zinc-200"
                                                 @mouseenter="keep()" @mouseleave="hide()">
                                                <p class="mb-2 font-semibold">Estamentos activos</p>
                                                <ul class="space-y-1">
                                                    @foreach ($typeNames as $typeName)
                                                        <li>{{ $typeName }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </template>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400 dark:text-zinc-500">—</span>
                            @endif
                        </td>

                        {{-- Programa(s) --}}
                        <td class="px-4 py-3">
                            @if($programNames->isNotEmpty())
                                <div class="flex items-center gap-1.5" x-data="floatingTooltip()">
                                    <span class="text-xs text-gray-700 dark:text-zinc-300 truncate max-w-[12rem]" title="{{ $primaryProgram }}">
                                        {{ $primaryProgram }}
                                    </span>
                                    @if($extraProgramsCount > 0)
                                        <button type="button" x-ref="trigger"
                                            @mouseenter="show($refs.trigger)"
                                            @mouseleave="hide()"
                                            class="inline-flex items-center rounded-full bg-blue-600 px-2 py-0.5 text-xs font-semibold text-white hover:brightness-110 focus:outline-none cursor-pointer">
                                            +{{ $extraProgramsCount }}
                                        </button>
                                        <template x-teleport="body">
                                            <div x-show="open" x-transition.opacity.duration.150ms
                                                 :style="`position:fixed;top:${y}px;left:${x}px;`"
                                                 class="z-[9999] min-w-56 max-w-xs rounded-lg border border-neutral-200 bg-white p-3 text-xs text-zinc-700 shadow-lg dark:border-neutral-700 dark:bg-zinc-900 dark:text-zinc-200"
                                                 @mouseenter="keep()" @mouseleave="hide()">
                                                <p class="mb-2 font-semibold">Programas activos</p>
                                                <ul class="space-y-1">
                                                    @foreach ($programNames as $name)
                                                        <li>{{ $name }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </template>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400 dark:text-zinc-500">—</span>
                            @endif
                        </td>

                        {{-- Dependencia(s) --}}
                        <td class="px-4 py-3">
                            @if($dependencyNames->isNotEmpty())
                                <div class="flex items-center gap-1.5" x-data="floatingTooltip()">
                                    <span class="text-xs text-gray-700 dark:text-zinc-300 truncate max-w-[12rem]" title="{{ $primaryDependency }}">
                                        {{ $primaryDependency }}
                                    </span>
                                    @if($extraDependenciesCount > 0)
                                        <button type="button" x-ref="trigger"
                                            @mouseenter="show($refs.trigger)"
                                            @mouseleave="hide()"
                                            class="inline-flex items-center rounded-full bg-indigo-600 px-2 py-0.5 text-xs font-semibold text-white hover:brightness-110 focus:outline-none cursor-pointer">
                                            +{{ $extraDependenciesCount }}
                                        </button>
                                        <template x-teleport="body">
                                            <div x-show="open" x-transition.opacity.duration.150ms
                                                 :style="`position:fixed;top:${y}px;left:${x}px;`"
                                                 class="z-[9999] min-w-56 max-w-xs rounded-lg border border-neutral-200 bg-white p-3 text-xs text-zinc-700 shadow-lg dark:border-neutral-700 dark:bg-zinc-900 dark:text-zinc-200"
                                                 @mouseenter="keep()" @mouseleave="hide()">
                                                <p class="mb-2 font-semibold">Dependencias activas</p>
                                                <ul class="space-y-1">
                                                    @foreach ($dependencyNames as $name)
                                                        <li>{{ $name }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </template>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400 dark:text-zinc-500">—</span>
                            @endif
                        </td>

                        {{-- Vinculación --}}
                        <td class="px-4 py-3">
                            @if($affiliationNames->isNotEmpty())
                                <div class="flex items-center gap-1.5" x-data="floatingTooltip()">
                                    <span class="text-xs text-gray-600 dark:text-zinc-400 truncate max-w-[10rem]" title="{{ $primaryAffiliation }}">
                                        {{ $primaryAffiliation }}
                                    </span>
                                    @if($extraAffiliationsCount > 0)
                                        <button type="button" x-ref="trigger"
                                            @mouseenter="show($refs.trigger)"
                                            @mouseleave="hide()"
                                            class="inline-flex items-center rounded-full bg-amber-600 px-2 py-0.5 text-xs font-semibold text-white hover:brightness-110 focus:outline-none cursor-pointer">
                                            +{{ $extraAffiliationsCount }}
                                        </button>
                                        <template x-teleport="body">
                                            <div x-show="open" x-transition.opacity.duration.150ms
                                                 :style="`position:fixed;top:${y}px;left:${x}px;`"
                                                 class="z-[9999] min-w-48 max-w-xs rounded-lg border border-neutral-200 bg-white p-3 text-xs text-zinc-700 shadow-lg dark:border-neutral-700 dark:bg-zinc-900 dark:text-zinc-200"
                                                 @mouseenter="keep()" @mouseleave="hide()">
                                                <p class="mb-2 font-semibold">Vinculaciones activas</p>
                                                <ul class="space-y-1">
                                                    @foreach ($affiliationNames as $affiliationName)
                                                        <li>{{ $affiliationName }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </template>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400 dark:text-zinc-500">—</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-xs text-gray-500 dark:text-zinc-400 whitespace-nowrap">
                            {{ $participant->email ?? '—' }}
                        </td>

                        {{-- Acciones --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    wire:click="openEdit({{ $participant->id }})"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 dark:hover:text-blue-400 transition-colors cursor-pointer"
                                    title="Editar">
                                    <flux:icon.pencil-square class="size-4" />
                                </button>
                                <button
                                    wire:click="openDelete({{ $participant->id }}, '{{ addslashes($participant->first_name . ' ' . $participant->last_name) }}')"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 dark:hover:text-red-400 transition-colors cursor-pointer"
                                    title="Eliminar">
                                    <flux:icon.trash class="size-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-sm text-gray-400 dark:text-zinc-500">
                            @if($search !== '')
                                No se encontraron participantes con "<strong>{{ $search }}</strong>".
                            @else
                                Aún no hay participantes registrados.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    @if($participants->hasPages())
        <div class="mt-4">
            {{ $participants->links() }}
        </div>
    @endif

    {{-- Contador --}}
    <p class="mt-2 text-xs text-gray-400 dark:text-zinc-500 text-right">
        {{ $participants->total() }} participante(s) en total
    </p>

    {{-- ======================== MODAL: EDITAR ======================== --}}
    @if($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data x-trap.noscroll="true">

            <div class="absolute inset-0 bg-black/50 dark:bg-black/70" wire:click="closeEdit"></div>

            <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-neutral-200 dark:border-zinc-700 z-10"
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

                <form wire:submit="updateParticipant" class="px-6 py-5 flex flex-col gap-5">

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
                    <div class="border-t border-neutral-200 dark:border-zinc-700 pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Roles / Estamentos</h4>
                            <button type="button" wire:click="addRole"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors cursor-pointer">
                                <flux:icon.plus class="size-3.5" />
                                Agregar rol
                            </button>
                        </div>

                        <div class="flex flex-col gap-3">
                            @foreach($editRoles as $index => $role)
                                @php
                                    $typeCategory = $this->getTypeCategory($role['participant_type_id']);
                                @endphp
                                <div class="relative rounded-xl border border-neutral-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 p-4" wire:key="role-{{ $index }}">

                                    {{-- Botón eliminar rol --}}
                                    @if(count($editRoles) > 1)
                                        <button type="button" wire:click="removeRole({{ $index }})"
                                            class="absolute top-2 right-2 p-1 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 dark:hover:text-red-400 transition-colors cursor-pointer"
                                            title="Eliminar rol">
                                            <flux:icon.x-mark class="size-4" />
                                        </button>
                                    @endif

                                    <div class="flex flex-col gap-3">
                                        {{-- Estamento --}}
                                        <div class="flex flex-col gap-1.5">
                                            <label class="text-xs font-medium text-gray-600 dark:text-gray-400">
                                                Estamento <span class="text-red-500">*</span>
                                            </label>
                                            <select wire:model.live="editRoles.{{ $index }}.participant_type_id"
                                                class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                                <option value="">Selecciona un estamento…</option>
                                                @foreach($catalogTypes as $type)
                                                    <option value="{{ $type['id'] }}">{{ $type['name'] }}</option>
                                                @endforeach
                                            </select>
                                            @error("editRoles.{$index}.participant_type_id")
                                                <p class="text-xs text-red-500">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        {{-- Programa (Estudiante, Graduado, Docente) --}}
                                        @if($typeCategory === 'program')
                                            <div class="flex flex-col gap-1.5">
                                                <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Programa</label>
                                                <select wire:model="editRoles.{{ $index }}.program_id"
                                                    class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                                    <option value="">Sin programa</option>
                                                    @foreach($catalogPrograms as $program)
                                                        <option value="{{ $program['id'] }}">{{ $program['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        {{-- Dependencia (Administrativo) --}}
                                        @if($typeCategory === 'dependency')
                                            <div class="flex flex-col gap-1.5">
                                                <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Dependencia</label>
                                                <select wire:model="editRoles.{{ $index }}.dependency_id"
                                                    class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                                    <option value="">Sin dependencia</option>
                                                    @foreach($catalogDependencies as $dep)
                                                        <option value="{{ $dep['id'] }}">{{ $dep['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif

                                        {{-- Vinculación (Estudiante, Graduado, Docente, Administrativo) --}}
                                        @if(in_array($typeCategory, ['program', 'dependency']))
                                            <div class="flex flex-col gap-1.5">
                                                <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Vinculación</label>
                                                <select wire:model="editRoles.{{ $index }}.affiliation_id"
                                                    class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                                    <option value="">Sin vinculación</option>
                                                    @foreach($catalogAffiliations as $aff)
                                                        <option value="{{ $aff['id'] }}">{{ $aff['name'] }}</option>
                                                    @endforeach
                                                </select>
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

@script
<script>
    Alpine.data('floatingTooltip', () => ({
        open: false,
        x: 0,
        y: 0,
        _timer: null,

        show(el) {
            clearTimeout(this._timer);
            const rect = el.getBoundingClientRect();
            let x = rect.left;
            let y = rect.bottom + 6;

            // Si se sale por la derecha, alinear al borde derecho del botón
            if (x + 240 > window.innerWidth) {
                x = rect.right - 240;
            }
            // Si queda negativo, pegarlo al borde izquierdo
            if (x < 8) {
                x = 8;
            }
            // Si se sale por abajo, mostrar encima del botón
            if (y + 150 > window.innerHeight) {
                y = rect.top - 6;
                // El tooltip se posicionará con transform para subir
                this._above = true;
            } else {
                this._above = false;
            }

            this.x = x;
            this.y = y;
            this.open = true;
        },

        keep() {
            clearTimeout(this._timer);
        },

        hide() {
            this._timer = setTimeout(() => { this.open = false; }, 150);
        },
    }));
</script>
@endscript
