import SearchableSelect from './SearchableSelect.jsx';
import { FunnelIcon, XIcon } from '../icons.jsx';

const EMAIL_OPTIONS = [
    { id: 'con', name: 'Con correo' },
    { id: 'sin', name: 'Sin correo' },
];

/**
 * Panel de filtros colapsable. Equivalente React del panel Livewire (ADR-0011).
 */
export default function FiltersPanel({ filters, options, onChange, onReset, showReset }) {
    const set = (key) => (value) => onChange(key, value);

    return (
        <div className="mt-2 rounded-xl border border-neutral-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/40 p-3">
            {showReset && (
                <div className="flex items-center justify-end mb-2.5">
                    <button type="button" onClick={onReset}
                        className="inline-flex items-center gap-1 text-xs font-medium text-gray-500 hover:text-red-600 dark:text-zinc-400 dark:hover:text-red-400 transition-colors">
                        <XIcon className="size-3.5" />
                        Limpiar filtros
                    </button>
                </div>
            )}

            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                <Field label="Estamento">
                    <SearchableSelect value={filters.estamento} onChange={set('estamento')} options={options.types}
                        placeholder="Todos" emptyLabel="Todos los estamentos" searchPlaceholder="Buscar estamento…" />
                </Field>
                <Field label="Programa">
                    <SearchableSelect value={filters.programa} onChange={set('programa')} options={options.programs}
                        placeholder="Todos" emptyLabel="Todos los programas" searchPlaceholder="Buscar programa…" />
                </Field>
                <Field label="Dependencia">
                    <SearchableSelect value={filters.dependencia} onChange={set('dependencia')} options={options.dependencies}
                        placeholder="Todas" emptyLabel="Todas las dependencias" searchPlaceholder="Buscar dependencia…" />
                </Field>
                <Field label="Vinculación">
                    <SearchableSelect value={filters.vinculacion} onChange={set('vinculacion')} options={options.affiliations}
                        placeholder="Todas" emptyLabel="Todas las vinculaciones" searchPlaceholder="Buscar vinculación…" />
                </Field>
                <Field label="Correo">
                    <SearchableSelect value={filters.correo} onChange={set('correo')} options={EMAIL_OPTIONS}
                        placeholder="Con o sin correo" emptyLabel="Con o sin correo" />
                </Field>
                <Field label="Clasificación">
                    <button type="button" onClick={() => onChange('sinClasificar', !filters.sinClasificar)}
                        className={`inline-flex items-center justify-between gap-2 px-3 py-2 rounded-lg border text-sm font-medium transition-colors ${filters.sinClasificar
                            ? 'border-amber-400 bg-amber-50 text-amber-700 dark:border-amber-600 dark:bg-amber-900/30 dark:text-amber-300'
                            : 'border-neutral-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-zinc-700'}`}>
                        <span className="inline-flex items-center gap-2">
                            <FunnelIcon className="size-4" />
                            Solo sin clasificar
                        </span>
                        {filters.sinClasificar && <XIcon className="size-3.5" />}
                    </button>
                </Field>
            </div>
        </div>
    );
}

function Field({ label, children }) {
    return (
        <div className="flex flex-col gap-1.5">
            <label className="text-xs font-medium text-gray-600 dark:text-gray-400">{label}</label>
            {children}
        </div>
    );
}
