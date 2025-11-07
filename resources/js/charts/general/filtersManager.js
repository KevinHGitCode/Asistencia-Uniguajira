// filtersManager.js
// Gestiona los filtros y actualiza los gráficos cuando cambian

let currentFilters = {
  dateFrom: '',
  dateTo: '',
  dependencyIds: [],
  userIds: []
};

/**
 * Construye los parámetros de query string desde los filtros
 */
function buildQueryParams(filters) {
  const params = new URLSearchParams();
  
  if (filters.dateFrom) params.append('dateFrom', filters.dateFrom);
  if (filters.dateTo) params.append('dateTo', filters.dateTo);
  
  // Agregar arrays como múltiples parámetros
  if (filters.dependencyIds && Array.isArray(filters.dependencyIds) && filters.dependencyIds.length > 0) {
    filters.dependencyIds.forEach(id => params.append('dependencyIds[]', id));
  }
  if (filters.userIds && Array.isArray(filters.userIds) && filters.userIds.length > 0) {
    filters.userIds.forEach(id => params.append('userIds[]', id));
  }
  
  return params.toString();
}

/**
 * Actualiza los filtros y recarga todos los gráficos
 */
export function updateFilters(filters) {
  currentFilters = { ...filters };
  
  // Disparar evento personalizado para que los gráficos se actualicen
  window.dispatchEvent(new CustomEvent('statistics-filters-changed', {
    detail: { filters: currentFilters }
  }));
}

/**
 * Obtiene los filtros actuales
 */
export function getCurrentFilters() {
  return { ...currentFilters };
}

/**
 * Obtiene la URL de la API con los filtros aplicados
 */
export function getApiUrl(endpoint) {
  const params = buildQueryParams(currentFilters);
  return params ? `${endpoint}?${params}` : endpoint;
}

// Escuchar eventos de Livewire
document.addEventListener('livewire:init', () => {
  Livewire.on('filters-changed', (filters) => {
    updateFilters(filters);
  });
});

