// resources/js/charts/utils/shortName.js

/**
 * Abrevia nombres largos agregando "…" si exceden un límite.
 * @param {string} name - Nombre original del programa.
 * @param {number} maxLength - Longitud máxima permitida (por defecto 25).
 * @returns {string} Nombre abreviado.
 */
export function shortName(name, maxLength = 25) {
  if (!name) return '';
  return name.length > maxLength ? name.slice(0, maxLength - 3) + '…' : name;
}
