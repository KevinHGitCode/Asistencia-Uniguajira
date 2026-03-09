import React from 'react';
import { XIcon } from './AdminEventosIcons.jsx';

export default function ActiveFilters({ filters, depOptions, userOptions, onRemoveDep, onRemoveUser, onClearDates, onClearSearch }) {
    const badges = [];

    if (filters.from || filters.to) {
        const label = [filters.from, filters.to].filter(Boolean).join(" → ");
        badges.push(
            <span key="dates" className="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200">
                {label}
                <button onClick={onClearDates} className="ml-0.5 hover:text-red-500"><XIcon /></button>
            </span>,
        );
    }

    if (filters.search) {
        badges.push(
            <span key="search" className="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-800 dark:text-blue-200">
                "{filters.search}"
                <button onClick={onClearSearch} className="ml-0.5 hover:text-red-500"><XIcon /></button>
            </span>,
        );
    }

    filters.dependencies?.forEach((id) => {
        const dep = depOptions.find((d) => d.id === id);
        if (dep) {
            badges.push(
                <span key={`dep-${id}`} className="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-[#cc5e50]/20 text-[#cc5e50] dark:text-[#e8897d]">
                    {dep.name}
                    <button onClick={() => onRemoveDep(id)} className="ml-0.5 hover:text-red-500"><XIcon /></button>
                </span>,
            );
        }
    });

    filters.users?.forEach((id) => {
        const user = userOptions.find((u) => u.id === id);
        if (user) {
            badges.push(
                <span key={`user-${id}`} className="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-full bg-violet-100 dark:bg-violet-900/40 text-violet-800 dark:text-violet-200">
                    {user.name}
                    <button onClick={() => onRemoveUser(id)} className="ml-0.5 hover:text-red-500"><XIcon /></button>
                </span>,
            );
        }
    });

    if (badges.length === 0) return null;

    return (
        <div className="flex flex-wrap gap-2 mb-4">
            <span className="text-xs text-gray-400 dark:text-gray-500 self-center mr-1">Filtros activos:</span>
            {badges}
        </div>
    );
}