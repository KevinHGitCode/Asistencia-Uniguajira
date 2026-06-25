import { PencilIcon, TrashIcon } from '../icons.jsx';

const TH = 'px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-zinc-400 uppercase tracking-wider';

/** Celda con valor principal + "+N" (lista completa en el title). */
function MultiCell({ items, badgeClass = 'bg-blue-600' }) {
    if (!items || items.length === 0) {
        return <span className="text-xs text-gray-400 dark:text-zinc-500">—</span>;
    }
    const [first, ...rest] = items;
    return (
        <div className="flex items-center gap-1.5">
            <span className="text-xs text-gray-700 dark:text-zinc-300 truncate max-w-[12rem]" title={first}>{first}</span>
            {rest.length > 0 && (
                <span className={`inline-flex items-center rounded-full ${badgeClass} px-2 py-0.5 text-xs font-semibold text-white`}
                    title={items.join('\n')}>
                    +{rest.length}
                </span>
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
                                    {p.types?.length > 0 ? (
                                        <div className="flex items-center gap-1.5">
                                            <span className="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300 max-w-[10rem] truncate" title={p.types[0]}>{p.types[0]}</span>
                                            {p.types.length > 1 && (
                                                <span className="inline-flex items-center rounded-full bg-teal-600 px-2 py-0.5 text-xs font-semibold text-white" title={p.types.join('\n')}>+{p.types.length - 1}</span>
                                            )}
                                        </div>
                                    ) : <span className="text-xs text-gray-400 dark:text-zinc-500">—</span>}
                                </td>
                                <td className="px-4 py-3"><MultiCell items={p.programs} badgeClass="bg-blue-600" /></td>
                                <td className="px-4 py-3"><MultiCell items={p.dependencies} badgeClass="bg-indigo-600" /></td>
                                <td className="px-4 py-3"><MultiCell items={p.affiliations} badgeClass="bg-amber-600" /></td>
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
