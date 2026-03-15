<x-layouts.app :title="__('Participantes')">

<div class="flex h-full w-full flex-1 flex-col gap-6 p-1 sm:p-4 md:p-6"
     x-data="{
         activeTab: '{{ session('active_tab', 'bulk') }}',
         role: '',
         showRoleDependent() { return ['Estudiante', 'Graduado'].includes(this.role); },
         showAffiliation()   { return this.role === 'Docente'; },
     }">

    {{-- Header --}}
    <div>
        <x-breadcrumb class="mb-1" :items="[
            ['label' => 'Administración', 'route' => 'administracion.index'],
            ['label' => 'Participantes'],
        ]" />
        <h1 class="flex items-center gap-2 text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
            <flux:icon name="users" class="size-16 text-[#3b82f6]" />
            <span>Gestión de Participantes</span>
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Importa participantes desde Excel o registra uno individualmente.
        </p>
    </div>

    {{-- Alertas globales --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-center gap-3 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 text-sm">
            <flux:icon.check-circle class="size-5 shrink-0" />
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-center gap-3 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
            <flux:icon.x-circle class="size-5 shrink-0" />
            {{ session('error') }}
        </div>
    @endif

    {{-- Resultado de importación --}}
    @if(session('import_result'))
        @php $result = session('import_result'); @endphp
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 flex items-center gap-3 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 text-sm">
                <flux:icon.check-circle class="size-5 shrink-0" />
                <span>
                    <strong>{{ $result['saved'] }}</strong> {{ $result['saved'] === 1 ? 'participante nuevo guardado' : 'participantes nuevos guardados' }}.
                    @if(($result['programs_attached'] ?? 0) > 0)
                        <strong>{{ $result['programs_attached'] }}</strong> {{ $result['programs_attached'] === 1 ? 'carrera nueva adjuntada' : 'carreras nuevas adjuntadas' }} a participantes existentes.
                    @endif
                </span>
            </div>
            @if($result['skipped'] > 0)
                <div class="flex-1 flex items-center justify-between gap-3 px-4 py-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400 text-sm">
                    <div class="flex items-center gap-3">
                        <flux:icon.exclamation-triangle class="size-5 shrink-0" />
                        <span><strong>{{ $result['skipped'] }}</strong> {{ $result['skipped'] === 1 ? 'fila omitida' : 'filas omitidas' }} (sin datos nuevos que agregar).</span>
                    </div>
                    <a href="{{ route('participants-import.download-skipped') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-medium transition-colors shrink-0">
                        <flux:icon.arrow-down-tray class="size-3.5" />
                        Descargar omitidos
                    </a>
                </div>
            @endif
        </div>
    @endif

    {{-- Tabs --}}
    <div class="border-b border-neutral-200 dark:border-zinc-700">
        <nav class="flex gap-1">
            <button
                @click="activeTab = 'bulk'"
                :class="activeTab === 'bulk'
                    ? 'border-b-2 border-[#3b82f6] text-[#3b82f6] dark:text-blue-400'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                class="flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors cursor-pointer">
                <flux:icon.document-arrow-up class="size-4" />
                Carga masiva Excel
            </button>
            <button
                @click="activeTab = 'single'"
                :class="activeTab === 'single'
                    ? 'border-b-2 border-[#3b82f6] text-[#3b82f6] dark:text-blue-400'
                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                class="flex items-center gap-2 px-4 py-3 text-sm font-medium transition-colors cursor-pointer">
                <flux:icon.user-plus class="size-4" />
                Registro individual
            </button>
        </nav>
    </div>

    {{-- ===================== TAB: CARGA MASIVA ===================== --}}
    <div x-show="activeTab === 'bulk'" x-transition>
        <div class="border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm overflow-hidden">

            <div class="px-4 sm:px-6 py-4 border-b border-neutral-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Importar desde Excel</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Usa el mismo formato del archivo <code class="font-mono bg-zinc-100 dark:bg-zinc-800 px-1 rounded">seed.xlsx</code>.
                    Las filas con documentos o correos duplicados serán omitidas y podrás descargarlas al final.
                </p>
            </div>

            <div class="px-4 sm:px-6 py-6">

                {{-- Formato esperado --}}
                <div class="mb-6 rounded-xl border border-blue-100 dark:border-blue-900/40 bg-blue-50 dark:bg-blue-900/20 p-4">
                    <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2 flex items-center gap-2">
                        <flux:icon.information-circle class="size-4" />
                        Formato esperado del Excel
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="text-xs text-blue-700 dark:text-blue-300 w-full">
                            <thead>
                                <tr class="border-b border-blue-200 dark:border-blue-800">
                                    @foreach(['Documento', 'Nombres', 'Apellidos', 'Rol', 'Correo', 'Programa - Sede', 'Tipo Programa', 'Afiliación'] as $col)
                                        <th class="pb-1 pr-4 text-left font-semibold">{{ $col }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-blue-600 dark:text-blue-400 opacity-70">
                                    <td class="pt-1 pr-4">1234567890</td>
                                    <td class="pt-1 pr-4">Juan</td>
                                    <td class="pt-1 pr-4">Pérez</td>
                                    <td class="pt-1 pr-4">Estudiante</td>
                                    <td class="pt-1 pr-4">juan@correo.co</td>
                                    <td class="pt-1 pr-4">Ingeniería - Riohacha</td>
                                    <td class="pt-1 pr-4">Pregrado</td>
                                    <td class="pt-1 pr-4">—</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Formulario de carga --}}
                <form action="{{ route('participants-import.import') }}" method="POST" enctype="multipart/form-data"
                      x-data="{ fileName: '', dragging: false }"
                      class="flex flex-col gap-4">
                    @csrf

                    {{-- Drop zone --}}
                    <div
                        @dragover.prevent="dragging = true"
                        @dragleave.prevent="dragging = false"
                        @drop.prevent="
                            dragging = false;
                            const file = $event.dataTransfer.files[0];
                            if (file) { fileName = file.name; $refs.fileInput.files = $event.dataTransfer.files; }
                        "
                        :class="dragging ? 'border-[#3b82f6] bg-blue-50 dark:bg-blue-900/20' : 'border-neutral-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50'"
                        class="relative flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed p-8 transition-colors text-center cursor-pointer"
                        @click="$refs.fileInput.click()">

                        <flux:icon.document-arrow-up class="size-10 text-gray-400 dark:text-zinc-500" />
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                <span x-show="!fileName">Arrastra tu archivo aquí o <span class="text-[#3b82f6]">selecciona uno</span></span>
                                <span x-show="fileName" class="text-[#3b82f6]" x-text="fileName"></span>
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                Formatos: .xlsx, .xls, .csv · Máximo 10 MB
                            </p>
                        </div>
                        <input
                            x-ref="fileInput"
                            type="file"
                            name="excel_file"
                            accept=".xlsx,.xls,.csv"
                            class="absolute inset-0 opacity-0 cursor-pointer"
                            @change="fileName = $event.target.files[0]?.name ?? ''" />
                    </div>

                    @error('excel_file')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror

                    <div class="flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-[#3b82f6] hover:bg-blue-700 text-white text-sm font-medium transition-colors shadow-sm cursor-pointer">
                            <flux:icon.arrow-up-tray class="size-4" />
                            Importar participantes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================== TAB: REGISTRO INDIVIDUAL ===================== --}}
    <div x-show="activeTab === 'single'" x-transition>
        <div class="border border-neutral-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm overflow-hidden">

            <div class="px-4 sm:px-6 py-4 border-b border-neutral-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white">Nuevo Participante</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Registra un participante de forma manual.
                </p>
            </div>

            <form action="{{ route('participants-import.store') }}" method="POST"
                  class="px-4 sm:px-6 py-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                @csrf

                {{-- Documento --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Documento <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="document" value="{{ old('document') }}" required maxlength="20"
                        placeholder="Número de documento"
                        class="px-3 py-2 rounded-lg border @error('document') border-red-400 @else border-neutral-200 dark:border-zinc-700 @enderror bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                    @error('document')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Rol --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Rol <span class="text-red-500">*</span>
                    </label>
                    <select name="role" x-model="role" required
                        class="px-3 py-2 rounded-lg border @error('role') border-red-400 @else border-neutral-200 dark:border-zinc-700 @enderror bg-white dark:bg-zinc-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="">Selecciona un rol…</option>
                        @foreach(['Estudiante', 'Docente', 'Administrativo', 'Graduado', 'Comunidad Externa'] as $rol)
                            <option value="{{ $rol }}" {{ old('role') === $rol ? 'selected' : '' }}>{{ $rol }}</option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nombres --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nombres <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required maxlength="100"
                        placeholder="Nombres"
                        class="px-3 py-2 rounded-lg border @error('first_name') border-red-400 @else border-neutral-200 dark:border-zinc-700 @enderror bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                    @error('first_name')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Apellidos --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Apellidos <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required maxlength="100"
                        placeholder="Apellidos"
                        class="px-3 py-2 rounded-lg border @error('last_name') border-red-400 @else border-neutral-200 dark:border-zinc-700 @enderror bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                    @error('last_name')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Correo --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Correo electrónico
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" maxlength="255"
                        placeholder="correo@ejemplo.com"
                        class="px-3 py-2 rounded-lg border @error('email') border-red-400 @else border-neutral-200 dark:border-zinc-700 @enderror bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                    @error('email')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Sexo --}}
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sexo</label>
                    <select name="sexo"
                        class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="">Sin especificar</option>
                        @foreach(['Masculino', 'Femenino', 'No binario'] as $s)
                            <option value="{{ $s }}" {{ old('sexo') === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Código estudiantil (Estudiante / Graduado) --}}
                <div class="flex flex-col gap-1.5 sm:col-span-2" x-show="showRoleDependent()" x-transition>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Código estudiantil
                    </label>
                    <input type="text" name="student_code" value="{{ old('student_code') }}" maxlength="20"
                        placeholder="Ej: 1243210019"
                        class="px-3 py-2 rounded-lg border @error('student_code') border-red-400 @else border-neutral-200 dark:border-zinc-700 @enderror bg-white dark:bg-zinc-800 text-gray-900 dark:text-white placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition" />
                    @error('student_code')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Programa (Estudiante / Graduado) --}}
                <div class="flex flex-col gap-1.5 sm:col-span-2" x-show="showRoleDependent()" x-transition>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Programa académico</label>
                    <select name="program_id"
                        class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="">Sin programa</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                {{ $program->name }}{{ $program->campus ? ' - ' . $program->campus : '' }}
                            </option>
                        @endforeach
                    </select>
                    @error('program_id')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Afiliación (Docente) --}}
                <div class="flex flex-col gap-1.5 sm:col-span-2" x-show="showAffiliation()" x-transition>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tipo de afiliación</label>
                    <select name="affiliation_id"
                        class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="">Sin afiliación</option>
                        @foreach($affiliations as $affiliation)
                            <option value="{{ $affiliation->id }}" {{ old('affiliation_id') == $affiliation->id ? 'selected' : '' }}>
                                {{ $affiliation->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Grupo priorizado --}}
                <div class="flex flex-col gap-1.5 sm:col-span-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Grupo priorizado</label>
                    <select name="grupo_priorizado"
                        class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        <option value="">Ninguno</option>
                        @foreach(['Comunidades indígenas', 'Comunidades afrodescendientes', 'Población con discapacidad', 'Víctimas del conflicto armado', 'Jóvenes rurales', 'LGBTIQ+'] as $grupo)
                            <option value="{{ $grupo }}" {{ old('grupo_priorizado') === $grupo ? 'selected' : '' }}>{{ $grupo }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Errores generales --}}
                @if($errors->any() && !$errors->has('excel_file'))
                    <div class="sm:col-span-2 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
                        <p class="font-medium mb-1">Por favor corrige los siguientes errores:</p>
                        <ul class="list-disc list-inside space-y-0.5 text-xs">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Botones --}}
                <div class="sm:col-span-2 flex justify-end gap-3 pt-2">
                    <button type="reset"
                        class="px-4 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                        Limpiar
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-[#3b82f6] hover:bg-blue-700 text-white text-sm font-medium transition-colors shadow-sm cursor-pointer">
                        <flux:icon.user-plus class="size-4" />
                        Registrar participante
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

</x-layouts.app>
