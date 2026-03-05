import { createRoot } from 'react-dom/client';

import AsistenciasApp   from './AsistenciasApp.jsx';
import ParticipantesApp from './ParticipantesApp.jsx';
import UsuariosApp      from './UsuariosApp.jsx';

/**
 * Mapa módulo → componente.
 * El valor de data-module en el div#statistics-react-root determina qué app montar.
 */
const APPS = {
  asistencias:   AsistenciasApp,
  participantes: ParticipantesApp,
  usuarios:      UsuariosApp,
};

let root = null;

/**
 * Monta (o re-monta) la sub-app correspondiente al módulo indicado en data-module.
 * Siempre crea un root nuevo para que la app arranque con estado limpio al navegar
 * entre sub-módulos vía wire:navigate.
 */
function mount() {
  const container = document.getElementById('statistics-react-root');
  if (!container) return;

  const App = APPS[container.dataset.module];
  if (!App) return;

  // Desmontar root anterior (transición entre sub-módulos vía wire:navigate)
  if (root) {
    root.unmount();
    root = null;
  }

  root = createRoot(container);
  root.render(<App />);
}

/**
 * Desmonta la app y libera recursos cuando se navega fuera de estadísticas.
 */
function unmount() {
  if (root) {
    root.unmount();
    root = null;
  }
}

// ── Carga inicial ──
mount();

// ── Soporte wire:navigate de Livewire 3 ──
// Después de cada navegación SPA: montar si el contenedor existe, desmontar si no.
document.addEventListener('livewire:navigated', () => {
  const container = document.getElementById('statistics-react-root');
  if (container) {
    mount();
  } else {
    unmount();
  }
});
