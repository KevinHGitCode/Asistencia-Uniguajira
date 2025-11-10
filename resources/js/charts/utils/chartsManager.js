// resources/js/charts/chartsManager.js
// Gestor central para el ciclo de vida de los gráficos

import { isDarkMode } from './theme.js';

class ChartsManager {
  constructor() {
    this.charts = new Map();
    this.resizeTimeout = null;
    this.themeObserver = null;
    this.isInitialized = false;
  }

  /**
   * Registra una instancia de gráfico
   */
  register(id, instance) {
    if (instance) {
      this.charts.set(id, instance);
    }
  }

  /**
   * Obtiene una instancia de gráfico
   */
  get(id) {
    return this.charts.get(id);
  }

  /**
   * Elimina y limpia una instancia de gráfico
   */
  dispose(id) {
    const chart = this.charts.get(id);
    if (chart) {
      chart.dispose();
      this.charts.delete(id);
    }
  }

  /**
   * Redimensiona todos los gráficos registrados
   */
  resizeAll() {
    this.charts.forEach(chart => {
      if (chart && typeof chart.resize === 'function') {
        try {
          chart.resize();
        } catch (error) {
          console.warn('Error al redimensionar gráfico:', error);
        }
      }
    });
  }

  /**
   * Limpia todos los gráficos y libera recursos
   */
  disposeAll() {
    this.charts.forEach(chart => {
      if (chart && typeof chart.dispose === 'function') {
        try {
          chart.dispose();
        } catch (error) {
          console.warn('Error al eliminar gráfico:', error);
        }
      }
    });
    this.charts.clear();
  }

  /**
   * Inicializa los listeners globales (resize y theme)
   */
  initGlobalListeners() {
    if (this.isInitialized) return;

    // Listener para redimensionamiento de ventana
    this.initResizeListener();

    // Observer para cambios de tema
    this.initThemeObserver();

    // Cleanup al salir de la página
    this.initCleanupListener();

    this.isInitialized = true;
    console.log('✓ ChartsManager: Listeners globales inicializados');
  }

  /**
   * Configura el listener de redimensionamiento
   */
  initResizeListener() {
    window.addEventListener('resize', () => {
      clearTimeout(this.resizeTimeout);
      this.resizeTimeout = setTimeout(() => {
        this.resizeAll();
      }, 200);
    });
  }

  /**
   * Configura el observer para cambios de tema
   */
  initThemeObserver() {
    this.themeObserver = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
          // Pequeño delay para asegurar que Alpine.js termine sus actualizaciones
          setTimeout(() => {
            this.handleThemeChange();
          }, 50);
        }
      });
    });

    // Observar cambios en el elemento html
    this.themeObserver.observe(document.documentElement, {
      attributes: true,
      attributeFilter: ['class']
    });
  }

  /**
   * Configura el listener de limpieza al salir
   */
  initCleanupListener() {
    window.addEventListener('beforeunload', () => {
      this.cleanup();
    });
  }

  /**
   * Maneja el cambio de tema
   */
  handleThemeChange() {
    const isDark = isDarkMode();
    console.log(`🎨 Tema cambiado a: ${isDark ? 'oscuro' : 'claro'}`);

    // Disparar evento personalizado para que los gráficos se recarguen
    window.dispatchEvent(new CustomEvent('charts-theme-changed', {
      detail: { isDark }
    }));
  }

  /**
   * Limpia todos los recursos
   */
  cleanup() {
    // Desconectar observer
    if (this.themeObserver) {
      this.themeObserver.disconnect();
      this.themeObserver = null;
    }

    // Limpiar timeout de resize
    if (this.resizeTimeout) {
      clearTimeout(this.resizeTimeout);
      this.resizeTimeout = null;
    }

    // Eliminar todos los gráficos
    this.disposeAll();

    this.isInitialized = false;
    console.log('✓ ChartsManager: Recursos liberados');
  }

  /**
   * Reinicia el manager (útil para hot reload en desarrollo)
   */
  reset() {
    this.cleanup();
    this.charts.clear();
    this.isInitialized = false;
  }
}

// Exportar instancia singleton
export const chartsManager = new ChartsManager();

// Exponer globalmente para debugging
if (typeof window !== 'undefined') {
  window.chartsManager = chartsManager;
}
