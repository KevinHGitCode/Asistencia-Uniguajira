import React from 'react';
import { SearchIcon } from './AdminEventosIcons.jsx';
import ChecklistDropdown from './ChecklistDropdown.jsx';

export default function AdminFiltersPanel({
    searchInput, onSearchChange,
    from, onFromChange,
    to, onToChange,
    filterOptions,
    selectedDeps, onDepsChange,
    selectedUsers, onUsersChange,
    hasAnyFilter, onClearAll,
}) {
    return (
        <div className="mb-6 p-4 rounded-2xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-900">
            <div className="flex items-center justify-between mb-4">
                <h3 className="text-sm font-semibold">Filtros</h3>
                {hasAnyFilter && (
                    <button onClick={onClearAll} className="text-xs text-red-500 hover:text-red-600 transition">
                        Limpiar todo
                    </button>
                )}
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                {/* Búsqueda por nombre */}
                <div className="relative lg:col-span-1 h-fit">
                    <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <SearchIcon />
                    </div>
                    <input
                        type="search"
                        placeholder="Nombre del evento…"
                        value={searchInput}
                        onChange={onSearchChange}
                        className="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#e2a542]/50 focus:border-[#e2a542] transition"
                    />
                </div>

                {/* Fecha desde */}
                <div>
                    <input
                        type="date"
                        value={from}
                        onChange={(e) => onFromChange(e.target.value)}
                        className="w-full px-3 py-2.5 text-sm rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#e2a542]/50 focus:border-[#e2a542] transition"
                        title="Desde"
                    />
                    <span className="text-[10px] text-gray-400 ml-1">Desde</span>
                </div>

                {/* Fecha hasta */}
                <div>
                    <input
                        type="date"
                        value={to}
                        onChange={(e) => onToChange(e.target.value)}
                        className="w-full px-3 py-2.5 text-sm rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-[#e2a542]/50 focus:border-[#e2a542] transition"
                        title="Hasta"
                    />
                    <span className="text-[10px] text-gray-400 ml-1">Hasta</span>
                </div>

                {/* Dependencias */}
                <ChecklistDropdown
                    label="Dependencias"
                    options={filterOptions.dependencies}
                    selected={selectedDeps}
                    onChange={onDepsChange}
                />

                {/* Usuarios */}
                <ChecklistDropdown
                    label="Usuarios"
                    options={filterOptions.users}
                    selected={selectedUsers}
                    onChange={onUsersChange}
                />
            </div>
        </div>
    );
}