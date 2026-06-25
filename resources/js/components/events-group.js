/**
 * Contenedor de grupo de eventos con búsqueda y filtros de cliente (ADR-0012).
 *
 * Filtra las tarjetas (`[data-event-card]`) de su grupo por texto (`data-search`,
 * sin acentos) y por filtros estructurados (rango de fechas, estado, dependencia,
 * área, creador) leídos de los `data-*` de cada tarjeta. Todo del lado del cliente,
 * sobre la colección ya cargada del grupo.
 *
 * Lo usa el componente Blade resources/views/components/events/group.blade.php.
 */

import { normalizar } from './text-filter';

window.eventsGroup = function () {
    return {
        q: '',
        dateFrom: '',
        dateTo: '',
        status: '',
        dependency: '',
        area: '',
        creator: '',
        filtersOpen: false,
        cards: [],
        totalCount: 0,
        visibleCount: 0,

        init() {
            this.cards = this.$refs.grid
                ? Array.from(this.$refs.grid.querySelectorAll('[data-event-card]'))
                : [];
            this.totalCount = this.cards.length;
            this.visibleCount = this.totalCount;

            // Cualquier cambio de búsqueda o filtro re-aplica el filtrado.
            ['q', 'dateFrom', 'dateTo', 'status', 'dependency', 'area', 'creator']
                .forEach((key) => this.$watch(key, () => this.apply()));
        },

        get countLabel() {
            return `${this.visibleCount} ${this.visibleCount === 1 ? 'evento' : 'eventos'}`;
        },

        get activeFilterCount() {
            return [this.dateFrom, this.dateTo, this.status, this.dependency, this.area, this.creator]
                .filter(Boolean).length;
        },

        resetFilters() {
            this.q = '';
            this.dateFrom = '';
            this.dateTo = '';
            this.status = '';
            this.dependency = '';
            this.area = '';
            this.creator = '';
        },

        apply() {
            const query = normalizar(this.q);
            let visible = 0;

            this.cards.forEach((card) => {
                const d = card.dataset;
                let show = !query || normalizar(d.search).includes(query);

                if (show && this.dateFrom) show = (d.date || '') >= this.dateFrom;
                if (show && this.dateTo) show = (d.date || '') <= this.dateTo;
                if (show && this.status) show = d.status === this.status;
                if (show && this.dependency) show = d.dependency === this.dependency;
                if (show && this.area) show = d.area === this.area;
                if (show && this.creator) show = d.creator === this.creator;

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
