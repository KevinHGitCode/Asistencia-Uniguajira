import { useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { PencilIcon, TrashIcon } from '../icons.jsx';

const TH = 'px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider';

function ExtraItemsBadge({ count, items, badgeClass = 'bg-blue-600', label = 'Elementos adicionales' }) {
    const buttonRef = useRef(null);
    const popoverRef = useRef(null);
    const hideTimerRef = useRef(null);
    const [open, setOpen] = useState(false);
    const [position, setPosition] = useState({ top: 0, left: 0, placement: 'bottom' });

    const clearHideTimer = () => {
        if (hideTimerRef.current) {
            clearTimeout(hideTimerRef.current);
            hideTimerRef.current = null;
        }
    };

    const updatePosition = () => {
        const trigger = buttonRef.current;
        if (!trigger) return;

        const rect = trigger.getBoundingClientRect();
        const width = 280;
        const maxHeight = Math.min(240, Math.max(160, window.innerHeight - 24));
        let left = rect.left;
        let top = rect.bottom + 8;
        let placement = 'bottom';

        if (left + width > window.innerWidth - 8) {
            left = rect.right - width;
        }

        if (top + maxHeight > window.innerHeight - 8) {
            top = rect.top - 8;
            placement = 'top';
        }

        setPosition({
            left: Math.max(8, left),
            top: placement === 'top' ? Math.max(8, top) : top,
            placement,
        });
    };

    const toggleFromClick = () => {
        clearHideTimer();
        if (open) {
            setOpen(false);
            buttonRef.current?.blur();
            return;
        }

        updatePosition();
        setOpen(true);
    };

    useEffect(() => () => clearHideTimer(), []);

    useEffect(() => {
        if (!open) return undefined;

        const closeOnOutsideInteraction = (event) => {
            if (buttonRef.current?.contains(event.target) || popoverRef.current?.contains(event.target)) {
                return;
            }

            setOpen(false);
        };
        const closeOnEscape = (event) => {
            if (event.key === 'Escape') setOpen(false);
        };
        const reposition = () => updatePosition();

        document.addEventListener('pointerdown', closeOnOutsideInteraction);
        document.addEventListener('keydown', closeOnEscape);
        window.addEventListener('resize', reposition);
        window.addEventListener('scroll', reposition, true);

        return () => {
            document.removeEventListener('pointerdown', closeOnOutsideInteraction);
            document.removeEventListener('keydown', closeOnEscape);
            window.removeEventListener('resize', reposition);
            window.removeEventListener('scroll', reposition, true);
        };
    }, [open]);

    return (
        <>
            <button
                ref={buttonRef}
                type="button"
                aria-expanded={open}
                aria-label={`Mostrar ${items.length} ${label.toLowerCase()}`}
                onClick={toggleFromClick}
                className={`inline-flex cursor-pointer items-center rounded-full ${badgeClass} px-2 py-0.5 text-xs font-semibold text-white shadow-sm transition hover:brightness-110 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1 dark:focus:ring-offset-zinc-900`}
            >
                +{count}
            </button>

            {open && createPortal(
                <div
                    ref={popoverRef}
                    role="tooltip"
                    onMouseEnter={clearHideTimer}
                    className="fixed z-[9999] w-[17.5rem] rounded-lg border border-neutral-200 bg-white p-3 text-xs text-gray-700 shadow-xl dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200"
                    style={{
                        left: `${position.left}px`,
                        top: `${position.top}px`,
                        transform: position.placement === 'top' ? 'translateY(-100%)' : 'none',
                    }}
                >
                    <p className="mb-2 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-zinc-400">
                        {label}
                    </p>
                    <ul className="max-h-56 space-y-1 overflow-y-auto pr-1">
                        {items.map((item, index) => (
                            <li key={`${item}-${index}`} className="rounded-md bg-zinc-50 px-2 py-1.5 leading-snug text-gray-700 dark:bg-zinc-800 dark:text-zinc-200" title={item}>
                                {item}
                            </li>
                        ))}
                    </ul>
                </div>,
                document.body
            )}
        </>
    );
}

function MultiCell({ items, badgeClass = 'bg-blue-600', firstClass = 'text-gray-700 dark:text-zinc-300', label }) {
    if (!items || items.length === 0) {
        return <span className="text-xs text-gray-400 dark:text-zinc-500">—</span>;
    }

    const [first, ...rest] = items;

    return (
        <div className="flex items-center gap-1.5">
            <span className={`text-xs truncate max-w-[12rem] ${firstClass}`} title={first}>{first}</span>
            {rest.length > 0 && (
                <ExtraItemsBadge count={rest.length} items={items} badgeClass={badgeClass} label={label} />
            )}
        </div>
    );
}

export default function ParticipantsTable({ rows, hasFilters, search, onEdit, onDelete }) {
    return (
        <div className="overflow-x-auto rounded-xl border border-neutral-200 dark:border-zinc-700">
            <table className="min-w-[1000px] w-full divide-y divide-neutral-200 dark:divide-zinc-700 text-sm">
                <thead className="bg-zinc-50 dark:bg-zinc-800/60">
                    <tr>
                        <th className={TH}>Documento</th>
                        <th className={TH}>Nombre</th>
                        <th className={TH}>Estamento(s)</th>
                        <th className={TH}>Programa(s)</th>
                        <th className={TH}>Dependencia(s)</th>
                        <th className={TH}>Vinculación</th>
                        <th className={TH}>Correo</th>
                        <th className={`${TH} text-right`}>Acciones</th>
                    </tr>
                </thead>
                <tbody className="bg-white dark:bg-zinc-900 divide-y divide-neutral-100 dark:divide-zinc-800">
                    {rows.length === 0 ? (
                        <tr>
                            <td colSpan={8} className="px-4 py-10 text-center text-sm text-gray-400 dark:text-zinc-500">
                                {search && hasFilters
                                    ? <>No se encontraron participantes con &quot;<strong>{search}</strong>&quot; y los filtros aplicados.</>
                                    : search
                                        ? <>No se encontraron participantes con &quot;<strong>{search}</strong>&quot;.</>
                                        : hasFilters
                                            ? 'No hay participantes que cumplan los filtros aplicados.'
                                            : 'Aún no hay participantes registrados.'}
                            </td>
                        </tr>
                    ) : (
                        rows.map((p) => (
                            <tr key={p.id} className="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                <td className="px-4 py-3 font-mono text-xs text-gray-600 dark:text-zinc-400 whitespace-nowrap">{p.document}</td>
                                <td className="px-4 py-3">
                                    <div className="flex items-center gap-2">
                                        <p className="font-semibold text-gray-900 dark:text-gray-100">{p.first_name} {p.last_name}</p>
                                        {p.has_unclassified_role && (
                                            <span className="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 whitespace-nowrap">
                                                Sin clasificar
                                            </span>
                                        )}
                                    </div>
                                    {p.student_code && <p className="text-xs text-gray-400 dark:text-zinc-500">Cód. {p.student_code}</p>}
                                </td>
                                <td className="px-4 py-3">
                                    <MultiCell
                                        items={p.types}
                                        badgeClass="bg-teal-600"
                                        firstClass="inline-flex items-center px-2 py-0.5 rounded-full font-medium bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300 max-w-[10rem]"
                                        label="Estamentos adicionales"
                                    />
                                </td>
                                <td className="px-4 py-3"><MultiCell items={p.programs} badgeClass="bg-blue-600" label="Programas adicionales" /></td>
                                <td className="px-4 py-3"><MultiCell items={p.dependencies} badgeClass="bg-indigo-600" label="Dependencias adicionales" /></td>
                                <td className="px-4 py-3"><MultiCell items={p.affiliations} badgeClass="bg-amber-600" label="Vinculaciones adicionales" /></td>
                                <td className="px-4 py-3 text-xs text-gray-500 dark:text-zinc-400 whitespace-nowrap">{p.email ?? '—'}</td>
                                <td className="px-4 py-3">
                                    <div className="flex items-center justify-end gap-2">
                                        <button type="button" onClick={() => onEdit(p.id)} title="Editar"
                                            className="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 dark:hover:text-blue-400 transition-colors">
                                            <PencilIcon className="size-4" />
                                        </button>
                                        <button type="button" onClick={() => onDelete(p.id, `${p.first_name} ${p.last_name}`)} title="Eliminar"
                                            className="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 dark:hover:text-red-400 transition-colors">
                                            <TrashIcon className="size-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        ))
                    )}
                </tbody>
            </table>
        </div>
    );
}
