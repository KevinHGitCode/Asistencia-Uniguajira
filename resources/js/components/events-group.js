/**
 * Contenedor de grupo de eventos con búsqueda de cliente (ADR-0012).
 *
 * Filtra las tarjetas (`[data-event-card]`) de su grupo por el texto de
 * `data-search`, sin acentos, reutilizando `normalizar` de text-filter.
 * Lo usa el componente Blade resources/views/components/events/group.blade.php.
 */

import { normalizar } from './text-filter';

window.eventsGroup = function () {
    return {
        q: '',
        cards: [],
        totalCount: 0,
        visibleCount: 0,

        init() {
            this.cards = this.$refs.grid
                ? Array.from(this.$refs.grid.querySelectorAll('[data-event-card]'))
                : [];
            this.totalCount = this.cards.length;
            this.visibleCount = this.totalCount;
        },

        get countLabel() {
            return `${this.visibleCount} ${this.visibleCount === 1 ? 'evento' : 'eventos'}`;
        },

        apply() {
            const query = normalizar(this.q);
            let visible = 0;

            this.cards.forEach((card) => {
                const show = !query || normalizar(card.dataset.search).includes(query);
                card.classList.toggle('hidden', !show);
                if (show) visible++;
            });

            this.visibleCount = visible;
        },
    };
};

document.addEventListener('alpine:init', () => {
    Alpine.data('eventsGroup', window.eventsGroup);
});
