/**
 * Componentes de selector con búsqueda (Alpine).
 *
 *  - searchableSelect       → selección única
 *  - multiSearchableSelect  → selección múltiple (chips + sin duplicados),
 *                             hereda la misma lógica de búsqueda/teclado.
 *
 * Integración con Livewire: si el componente Blade recibe `wire:model`, se
 * sincroniza con esa propiedad vía `$wire.get` / `$wire.set(..., false)`
 * (diferido: el valor viaja en la siguiente petición / submit, sin round-trip
 * por cada tecla).
 */

import { filtrarOpciones } from './text-filter';

// El buscador escrito solo aparece cuando hay MÁS de esta cantidad de opciones.
// Cámbialo aquí para ajustar el umbral en todos los selectores a la vez.
const SEARCH_MIN_ITEMS = 3;

// ── Selección única ──────────────────────────────────────────────────────────
window.searchableSelect = function (config) {
    return {
        opciones: config.options || [],
        model: config.model || null,
        hasWire: !!config.hasWire,
        allowEmpty: !!config.allowEmpty,
        emptyLabel: config.emptyLabel || 'Sin selección',
        placeholder: config.placeholder || 'Selecciona una opción…',
        live: !!config.live,
        disabled: !!config.disabled,
        showSearch: (config.options || []).length > SEARCH_MIN_ITEMS,

        open: false,
        search: '',
        value: '',
        highlighted: 0,

        init() {
            this.value = this.leerValor();
        },

        leerValor() {
            if (this.hasWire && this.model) {
                const v = this.$wire.get(this.model);
                return v == null ? '' : String(v);
            }
            return config.initialValue != null ? String(config.initialValue) : '';
        },

        get filtered() {
            return filtrarOpciones(this.opciones, this.search);
        },

        get selectedLabel() {
            const opt = this.opciones.find((o) => o.value === this.value);
            return opt ? opt.label : '';
        },

        toggle() {
            if (this.disabled) return;
            this.open ? this.close() : this.openPanel();
        },

        openPanel() {
            if (this.disabled) return;
            this.open = true;
            this.search = '';
            this.highlighted = 0;
            this.$nextTick(() => this.$refs.search && this.$refs.search.focus());
        },

        close() {
            this.open = false;
            this.search = '';
        },

        commit(val) {
            this.value = val;
            if (this.hasWire && this.model) {
                this.$wire.set(this.model, val, this.live); // diferido salvo wire:model.live
            }
        },

        select(opt) {
            this.commit(opt.value);
            this.close();
        },

        clear() {
            this.commit('');
            this.close();
        },

        // Enter: si hay UN solo resultado se elige; si no, el resaltado.
        onEnter() {
            const lista = this.filtered;
            if (lista.length === 1) {
                this.select(lista[0]);
                return;
            }
            if (this.highlighted >= 0 && this.highlighted < lista.length) {
                this.select(lista[this.highlighted]);
            }
        },

        move(dir) {
            if (this.disabled) return;
            if (!this.open) {
                this.openPanel();
                return;
            }
            const len = this.filtered.length;
            if (len === 0) return;
            this.highlighted = (this.highlighted + dir + len) % len;
            this.scrollToHighlighted();
        },

        // Hace que el scroll del listado acompañe al elemento resaltado.
        // Ajusta solo el scrollTop del <ul> (no toca contenedores padres) por
        // el sobrante exacto, de modo que al bajar/subir avanza un solo ítem.
        scrollToHighlighted() {
            this.$nextTick(() => {
                const list = this.$refs.list;
                if (!list) return;
                const el = list.querySelector(`[data-index="${this.highlighted}"]`);
                if (!el) return;

                const elRect = el.getBoundingClientRect();
                const listRect = list.getBoundingClientRect();

                if (elRect.top < listRect.top) {
                    list.scrollTop -= listRect.top - elRect.top;
                } else if (elRect.bottom > listRect.bottom) {
                    list.scrollTop += elRect.bottom - listRect.bottom;
                }
            });
        },
    };
};

// ── Selección múltiple ───────────────────────────────────────────────────────
window.multiSearchableSelect = function (config) {
    return {
        opciones: config.options || [],
        model: config.model || null,
        hasWire: !!config.hasWire,
        placeholder: config.placeholder || 'Selecciona una o más opciones…',
        live: !!config.live,
        showSearch: (config.options || []).length > SEARCH_MIN_ITEMS,

        open: false,
        search: '',
        values: [],
        highlighted: 0,

        init() {
            this.values = this.leerValores();
        },

        leerValores() {
            if (this.hasWire && this.model) {
                const v = this.$wire.get(this.model);
                return Array.isArray(v) ? v.map(String) : [];
            }
            return Array.isArray(config.initialValue) ? config.initialValue.map(String) : [];
        },

        // Opciones ya elegidas, en el orden de selección.
        get selectedOptions() {
            return this.values
                .map((v) => this.opciones.find((o) => o.value === String(v)))
                .filter(Boolean);
        },

        // Disponibles = no seleccionadas + filtro de búsqueda.
        get available() {
            const elegidas = new Set(this.values.map(String));
            return filtrarOpciones(
                this.opciones.filter((o) => !elegidas.has(o.value)),
                this.search,
            );
        },

        has(val) {
            return this.values.map(String).includes(String(val));
        },

        commit(nuevos) {
            this.values = nuevos;
            if (this.hasWire && this.model) {
                this.$wire.set(this.model, nuevos, this.live); // diferido salvo wire:model.live
            }
        },

        add(opt) {
            if (!opt || this.has(opt.value)) return; // valida no duplicados
            this.commit([...this.values, opt.value]);
            this.highlighted = 0;
            this.$nextTick(() => this.$refs.search && this.$refs.search.focus());
        },

        remove(val) {
            this.commit(this.values.filter((v) => String(v) !== String(val)));
        },

        openPanel() {
            this.open = true;
            this.search = '';
            this.highlighted = 0;
            this.$nextTick(() => this.$refs.search && this.$refs.search.focus());
        },

        close() {
            this.open = false;
            this.search = '';
        },

        onEnter() {
            const lista = this.available;
            if (lista.length === 1) {
                this.add(lista[0]);
                return;
            }
            if (this.highlighted >= 0 && this.highlighted < lista.length) {
                this.add(lista[this.highlighted]);
            }
        },

        move(dir) {
            if (!this.open) {
                this.openPanel();
                return;
            }
            const len = this.available.length;
            if (len === 0) return;
            this.highlighted = (this.highlighted + dir + len) % len;
            this.scrollToHighlighted();
        },

        // Hace que el scroll del listado acompañe al elemento resaltado.
        // Ajusta solo el scrollTop del <ul> (no toca contenedores padres) por
        // el sobrante exacto, de modo que al bajar/subir avanza un solo ítem.
        scrollToHighlighted() {
            this.$nextTick(() => {
                const list = this.$refs.list;
                if (!list) return;
                const el = list.querySelector(`[data-index="${this.highlighted}"]`);
                if (!el) return;

                const elRect = el.getBoundingClientRect();
                const listRect = list.getBoundingClientRect();

                if (elRect.top < listRect.top) {
                    list.scrollTop -= listRect.top - elRect.top;
                } else if (elRect.bottom > listRect.bottom) {
                    list.scrollTop += elRect.bottom - listRect.bottom;
                }
            });
        },
    };
};

document.addEventListener('alpine:init', () => {
    Alpine.data('searchableSelect', window.searchableSelect);
    Alpine.data('multiSearchableSelect', window.multiSearchableSelect);
});
