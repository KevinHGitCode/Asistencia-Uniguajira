/**
 * Núcleo de filtrado sin acentos, compartido por los componentes con búsqueda
 * por teclado: `searchableSelect`, `multiSearchableSelect` y `commandPalette`
 * (ADR-0007). Centralizar aquí evita duplicar la normalización.
 */

// Quita acentos/diacríticos y normaliza para comparar de forma amable.
export function normalizar(str) {
    return (str ?? '')
        .toString()
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .toLowerCase()
        .trim();
}

// Filtra una lista de opciones por coincidencia sin acentos en `key` (def. 'label').
export function filtrarOpciones(opciones, busqueda, key = 'label') {
    const q = normalizar(busqueda);
    if (!q) return opciones;

    return opciones.filter((o) => normalizar(o[key]).includes(q));
}
