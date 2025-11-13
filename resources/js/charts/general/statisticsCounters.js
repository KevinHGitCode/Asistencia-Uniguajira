// statisticsCounters.js
// Actualiza los contadores de estadísticas cada 3 minutos

import { getApiUrl } from './filtersManager.js';

let countersInterval = null;

/**
 * Actualiza los contadores de estadísticas desde la API
 */
function updateStatisticsCounters() {
  // Función auxiliar para formatear números con separadores de miles
  const formatNumber = (num) => {
    return new Intl.NumberFormat('es-ES').format(num);
  };

  // Función auxiliar para actualizar un contador con animación
  const updateCounter = (elementId, value) => {
    const element = document.getElementById(elementId);
    if (!element) return;

    const currentValue = parseInt(element.textContent.replace(/\./g, '')) || 0;
    const targetValue = value;

    // Si el valor no ha cambiado, no hacer nada
    if (currentValue === targetValue) return;

    // Animación suave del contador
    const duration = 800; // ms
    const startTime = Date.now();
    const startValue = currentValue;

    const animate = () => {
      const elapsed = Date.now() - startTime;
      const progress = Math.min(elapsed / duration, 1);
      
      // Función de easing (ease-out)
      const easeOut = 1 - Math.pow(1 - progress, 3);
      
      const current = Math.floor(startValue + (targetValue - startValue) * easeOut);
      element.textContent = formatNumber(current);

      if (progress < 1) {
        requestAnimationFrame(animate);
      } else {
        element.textContent = formatNumber(targetValue);
      }
    };

    animate();
  };

  // Obtener datos de la API con filtros aplicados
  Promise.all([
    fetch(getApiUrl('/api/statistics/total-events')).then(res => res.json()),
    fetch(getApiUrl('/api/statistics/total-attendances')).then(res => res.json()),
    fetch('/api/statistics/total-participants').then(res => res.json()) // Participantes no se filtra por fecha
  ])
    .then(([events, attendances, participants]) => {
      updateCounter('total-events', events);
      updateCounter('total-attendances', attendances);
      updateCounter('total-participants', participants);
    })
    .catch(error => {
      console.error('Error actualizando contadores:', error);
    });
}

/**
 * Inicializa la actualización periódica de los contadores
 */
export function initStatisticsCounters() {
  // Limpiar intervalo anterior si existe
  if (countersInterval) {
    clearInterval(countersInterval);
  }

  // Actualizar inmediatamente al cargar
  updateStatisticsCounters();

  // Actualizar cada 3 minutos (180000 ms)
  countersInterval = setInterval(updateStatisticsCounters, 180000);
}

/**
 * Limpia el intervalo de actualización
 */
export function cleanupStatisticsCounters() {
  if (countersInterval) {
    clearInterval(countersInterval);
    countersInterval = null;
  }
}

