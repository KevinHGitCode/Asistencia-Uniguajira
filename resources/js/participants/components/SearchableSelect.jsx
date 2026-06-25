import { useState, useRef, useEffect, useMemo } from 'react';
import { ChevronDownIcon, CheckIcon } from '../icons.jsx';

/**
 * Selector único con búsqueda. Equivalente React de x-ui.searchable-select.
 * options: [{ id, name }]. value es el id (string) o '' para "todos".
 */
export default function SearchableSelect({ value, onChange, options, placeholder = 'Todos', emptyLabel = 'Todos', searchPlaceholder = 'Buscar…' }) {
    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState('');
    const rootRef = useRef(null);
    const searchRef = useRef(null);

    useEffect(() => {
        if (!open) return;
        const onClick = (e) => {
            if (rootRef.current && !rootRef.current.contains(e.target)) setOpen(false);
        };
        document.addEventListener('mousedown', onClick);
        return () => document.removeEventListener('mousedown', onClick);
    }, [open]);

    useEffect(() => {
        if (open && searchRef.current) searchRef.current.focus();
        if (!open) setSearch('');
    }, [open]);

    const selectedLabel = useMemo(() => {
        const found = options.find((o) => String(o.id) === String(value));
        return found ? found.name : '';
    }, [options, value]);

    const filtered = useMemo(() => {
        const term = search.trim().toLowerCase();
        if (!term) return options;
        return options.filter((o) => o.name.toLowerCase().includes(term));
    }, [options, search]);

    const pick = (id) => {
        onChange(id);
        setOpen(false);
    };

    return (
        <div ref={rootRef} className="relative">
            <button type="button" onClick={() => setOpen((o) => !o)}
                className={`flex w-full items-center justify-between gap-2 px-3 py-2 rounded-lg border bg-white dark:bg-zinc-800 text-left text-sm text-gray-900 dark:text-white transition focus:outline-none focus:ring-2 focus:ring-blue-500 ${open ? 'border-blue-500 ring-2 ring-blue-500' : 'border-neutral-200 dark:border-zinc-700'}`}>
                <span className={`truncate ${selectedLabel ? '' : 'text-gray-400'}`}>{selectedLabel || placeholder}</span>
                <ChevronDownIcon className={`size-4 shrink-0 text-gray-400 transition-transform ${open ? 'rotate-180' : ''}`} />
            </button>

            {open && (
                <div className="absolute z-30 mt-1 w-full rounded-lg border border-neutral-200 dark:border-zinc-600 bg-white dark:bg-zinc-800 shadow-lg">
                    {options.length > 6 && (
                        <div className="p-2 border-b border-neutral-100 dark:border-zinc-700">
                            <input ref={searchRef} type="text" value={search} onChange={(e) => setSearch(e.target.value)}
                                placeholder={searchPlaceholder} autoComplete="off"
                                className="w-full px-2.5 py-1.5 rounded-md border border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                    )}
                    <ul className="max-h-52 overflow-y-auto py-1 text-sm">
                        {!search && (
                            <li>
                                <button type="button" onClick={() => pick('')}
                                    className={`w-full px-3 py-2 text-left hover:bg-blue-50 dark:hover:bg-zinc-700 transition-colors ${value === '' ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-gray-400'}`}>
                                    {emptyLabel}
                                </button>
                            </li>
                        )}
                        {filtered.map((opt) => (
                            <li key={opt.id}>
                                <button type="button" onClick={() => pick(String(opt.id))}
                                    className={`flex w-full items-center justify-between gap-2 px-3 py-2 text-left hover:bg-blue-50 dark:hover:bg-zinc-700 transition-colors ${String(opt.id) === String(value) ? 'font-semibold text-blue-600 dark:text-blue-400' : 'text-gray-800 dark:text-gray-200'}`}>
                                    <span className="truncate">{opt.name}</span>
                                    {String(opt.id) === String(value) && <CheckIcon className="size-4 shrink-0 text-blue-600 dark:text-blue-400" />}
                                </button>
                            </li>
                        ))}
                        {filtered.length === 0 && (
                            <li className="px-3 py-3 text-center text-gray-400 dark:text-zinc-500">Sin resultados</li>
                        )}
                    </ul>
                </div>
            )}
        </div>
    );
}
