<div class="mb-6 p-4 border rounded-lg bg-white dark:bg-neutral-800 shadow-md">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Filtros</h3>
        <div class="flex gap-2">
            <flux:button variant="ghost" wire:click="clearFilters" size="sm">
                Limpiar filtros
            </flux:button>
            <flux:button variant="primary" wire:click="applyFilters" size="sm">
                Aplicar filtros
            </flux:button>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Filtro: Fecha desde --}}
        <div>
            <flux:input 
                type="date" 
                wire:model="dateFrom"
                label="Fecha desde"
            />
        </div>

        {{-- Filtro: Fecha hasta --}}
        <div>
            <flux:input 
                type="date" 
                wire:model="dateTo"
                label="Fecha hasta"
            />
        </div>

        {{-- Filtro: Dependencias (múltiple) --}}
        {{-- <div>
            <flux:dropdown>
                <flux:button 
                    variant="outline" 
                    icon:trailing="chevron-down"
                    class="w-full justify-between"
                >
                    {{ count($dependencyIds) > 0 ? count($dependencyIds) . ' seleccionadas' : 'Dependencias' }}
                </flux:button>

                <flux:menu keep-open class="max-h-64 overflow-y-auto">
                    @if(count($dependencies) === 0)
                        <flux:menu.item disabled>No hay dependencias</flux:menu.item>
                    @else
                        @foreach($dependencies as $dependency)
                            <flux:menu.checkbox 
                                wire:click="toggleDependency({{ $dependency->id }})"
                                :checked="in_array($dependency->id, $dependencyIds)"
                            >
                                {{ $dependency->name }}
                            </flux:menu.checkbox>
                        @endforeach
                    @endif
                </flux:menu>
            </flux:dropdown>
        </div> --}}

        {{-- Filtro: Usuarios (múltiple) --}}
        {{-- <div>
            <flux:dropdown>
                <flux:button 
                    variant="outline" 
                    icon:trailing="chevron-down"
                    class="w-full justify-between"
                >
                    {{ count($userIds) > 0 ? count($userIds) . ' seleccionados' : 'Usuarios' }}
                </flux:button>

                <flux:menu keep-open class="max-h-64 overflow-y-auto">
                    @if(count($users) === 0)
                        <flux:menu.item disabled>No hay usuarios</flux:menu.item>
                    @else
                        @foreach($users as $user)
                            <flux:menu.checkbox 
                                wire:click="toggleUser({{ $user->id }})"
                                :checked="in_array($user->id, $userIds)"
                            >
                                {{ $user->name }}
                            </flux:menu.checkbox>
                        @endforeach
                    @endif
                </flux:menu>
            </flux:dropdown>
        </div> --}}
    </div>
</div>
