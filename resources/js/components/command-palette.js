/**
 * Paleta de comandos para administradores (ADR-0007).
 *
 * Atajo global Cmd/Ctrl + K (no Ctrl+P, reservado a imprimir). Reutiliza el
 * núcleo de filtrado sin acentos de `text-filter` y la misma lógica de teclado
 * (↑/↓, Enter, scroll que acompaña) del selector con búsqueda. Navega con la
 * navegación SPA de Livewire (`Livewire.navigate`).
 *
 * Los comandos llegan desde Blade ya filtrados por rol (ver
 * resources/views/components/command-palette.blade.php).
 */

import { filtrarOpciones } from './text-filter';

window.commandPalette = function (config) {
    return {
        comandos: config.commands || [],

        open: false,
        search: '',
        highlighted: 0,

        init() {
            // Atajo global: Cmd/Ctrl + K abre/cierra la paleta.
            this._onKey = (e) => {
                if ((e.metaKey || e.ctrlKey) && (e.key || '').toLowerCase() === 'k') {
                    e.preventDefault();
                    this.toggle();
                }
            };
            window.addEventListener('keydown', this._onKey);
        },

        destroy() {
            window.removeEventListener('keydown', this._onKey);
        },

        // Filtra por el campo `search` (etiqueta + sinónimos) sin acentos.
        get filtered() {
            return filtrarOpciones(this.comandos, this.search, 'search');
        },

        toggle() {
            this.open ? this.close() : this.openPanel();
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

        move(dir) {
            const len = this.filtered.length;
            if (len === 0) return;
            this.highlighted = (this.highlighted + dir + len) % len;
            this.scrollToHighlighted();
        },

        onEnter() {
            const lista = this.filtered;
            if (lista.length === 0) return;
            const i = Math.min(Math.max(this.highlighted, 0), lista.length - 1);
            this.go(lista[i]);
        },

        go(cmd) {
            if (!cmd) return;
            this.close();

            // Navegación SPA si Livewire está presente; si no, recarga normal.
            if (window.Livewire && typeof window.Livewire.navigate === 'function') {
                window.Livewire.navigate(cmd.url);
            } else {
                window.location.href = cmd.url;
            }
        },

        // Hace que el scroll del listado acompañe al elemento resaltado.
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
    Alpine.data('commandPalette', window.commandPalette);
});
