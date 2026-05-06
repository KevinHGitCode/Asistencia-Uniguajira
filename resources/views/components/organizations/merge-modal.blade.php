@props(['organizations'])

<div x-show="showMerge"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display: none;">

        <div class="absolute inset-0 bg-black/50 dark:bg-black/70" @click="closeMerge()"></div>

        <div x-show="showMerge"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-neutral-200 dark:border-zinc-700 z-10">

            <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-200 dark:border-zinc-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Fusionar organización</h3>
                <button @click="closeMerge()"
                    class="p-1 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                    <flux:icon.x-mark class="size-5" />
                </button>
            </div>

            <form
                :action="'{{ route('organizations.merge', '__id__') }}'.replace('__id__', mergeId)"
                method="POST"
                class="px-6 py-5 flex flex-col gap-4">
                @csrf

                <div class="flex items-start gap-3 px-4 py-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400 text-sm">
                    <flux:icon.exclamation-triangle class="size-5 shrink-0 mt-0.5" />
                    <div>
                        <p>Se moverán todos los participantes de <strong x-text="mergeName"></strong> a la organización seleccionada y luego se eliminará la duplicada.</p>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Organización destino <span class="text-red-500">*</span>
                    </label>
                    <select name="canonical_id" x-model="mergeTargetId" required
                        class="px-3 py-2 rounded-lg border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-[#2563eb] transition">
                        <option value="">— Seleccionar —</option>
                        @foreach($organizations as $org)
                            <option value="{{ $org->id }}"
                                x-show="mergeId != {{ $org->id }}">
                                {{ $org->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="closeMerge()"
                        class="px-4 py-2 text-sm rounded-lg border border-neutral-200 dark:border-zinc-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-medium transition-colors shadow-sm cursor-pointer">
                        Fusionar
                    </button>
                </div>
            </form>
        </div>
    </div>
