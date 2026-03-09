import React, { useState, useEffect, useRef, useMemo } from 'react';
import { ChevronDownIcon } from './AdminEventosIcons.jsx';

export default function ChecklistDropdown({ label, options, selected, onChange }) {
    const [open, setOpen] = useState(false);
    const [filter, setFilter] = useState("");
    const ref = useRef(null);

    useEffect(() => {
        function handleClickOutside(e) {
            if (ref.current && !ref.current.contains(e.target)) setOpen(false);
        }
        document.addEventListener("mousedown", handleClickOutside);
        return () => document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    const filtered = useMemo(() => {
        if (!filter) return options;
        const lower = filter.toLowerCase();
        return options.filter((o) => o.name.toLowerCase().includes(lower));
    }, [options, filter]);

    const toggle = (id) => {
        const next = selected.includes(id)
            ? selected.filter((s) => s !== id)
            : [...selected, id];
        onChange(next);
    };

    const clearAll = () => onChange([]);
    const selectedCount = selected.length;

    return (
        <div className="relative" ref={ref}>
            <button
                type="button"
                onClick={() => setOpen(!open)}
                className={`
                    flex items-center justify-between gap-2 w-full px-3 py-2.5 text-sm rounded-xl border
                    bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100
                    transition focus:outline-none focus:ring-2 focus:ring-[#e2a542]/50 focus:border-[#e2a542]
                    ${selectedCount > 0
                        ? "border-[#e2a542] dark:border-[#e2a542]"
                        : "border-neutral-200 dark:border-neutral-700"}
                `}
            >
                <span className="truncate">
                    {selectedCount === 0 ? label : `${label} (${selectedCount})`}
                </span>
                <ChevronDownIcon />
            </button>

            {open && (
                <div className="absolute z-50 mt-1 w-full min-w-[240px] max-h-64 bg-white dark:bg-zinc-800 border border-neutral-200 dark:border-neutral-700 rounded-xl shadow-lg overflow-hidden flex flex-col">
                    {options.length > 5 && (
                        <div className="p-2 border-b border-neutral-100 dark:border-neutral-700">
                            <input
                                type="text"
                                placeholder="Buscar…"
                                value={filter}
                                onChange={(e) => setFilter(e.target.value)}
                                className="w-full px-2.5 py-1.5 text-xs rounded-lg border border-neutral-200 dark:border-neutral-600 bg-white dark:bg-zinc-900 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none"
                            />
                        </div>
                    )}

                    <div className="overflow-y-auto flex-1">
                        {filtered.length === 0 && (
                            <p className="text-xs text-gray-400 p-3 text-center">Sin resultados</p>
                        )}
                        {filtered.map((opt) => (
                            <label
                                key={opt.id}
                                className="flex items-center gap-2.5 px-3 py-2 text-sm cursor-pointer hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition"
                            >
                                <input
                                    type="checkbox"
                                    checked={selected.includes(opt.id)}
                                    onChange={() => toggle(opt.id)}
                                    className="rounded border-gray-300 dark:border-neutral-600 text-[#e2a542] focus:ring-[#e2a542]"
                                />
                                <span className="text-gray-700 dark:text-gray-200 truncate">{opt.name}</span>
                            </label>
                        ))}
                    </div>

                    {selectedCount > 0 && (
                        <div className="p-2 border-t border-neutral-100 dark:border-neutral-700">
                            <button
                                type="button"
                                onClick={clearAll}
                                className="w-full text-xs text-center text-red-500 hover:text-red-600 py-1 transition"
                            >
                                Limpiar selección
                            </button>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}