import { createRoot } from 'react-dom/client';

import AsistenciasApp    from './AsistenciasApp.jsx';
import ParticipantesApp  from './ParticipantesApp.jsx';
import UsuariosApp       from './UsuariosApp.jsx';
import ComparaEventosApp from './ComparaEventosApp.jsx';
import AdminEventosApp   from './AdminEventosApp.jsx';

/**
 * Mapa módulo → componente.
 * El valor de data-module en el div#statistics-react-root determina qué app montar.
 */
const APPS = {
  asistencias:       AsistenciasApp,
  participantes:     ParticipantesApp,
  usuarios:          UsuariosApp,
  'compara-eventos': ComparaEventosApp,
  'admin-eventos':   AdminEventosApp,
};

let root             = null;
let mountedModule    = null;
let mountedContainer = null; // referencia al nodo DOM actual

/**
 * Monta (o re-monta) la sub-app correspondiente al módulo indicado en data-module.
 *
 * Detecta si wire:navigate reemplazó el contenedor (nuevo nodo DOM) incluso cuando
 * el módulo es el mismo (click en la página actual), y en ese caso re-monta.
 */
function mount() {
  const container = document.getElementById('statistics-react-root');
  if (!container) return;

  const module = container.dataset.module;
  const App    = APPS[module];
  if (!App) return;

  // Si el mismo módulo ya está montado Y el contenedor es el mismo nodo DOM, no remontar
  if (root && mountedModule === module && mountedContainer === container) return;

  // Desmontar root anterior (transición entre sub-módulos o DOM reemplazado)
  if (root) {
    root.unmount();
    root = null;
  }

  root             = createRoot(container);
  mountedModule    = module;
  mountedContainer = container;
  root.render(<App />);
}

/**
 * Desmonta la app y libera recursos cuando se navega fuera de estadísticas.
 */
function unmount() {
  if (root) {
    root.unmount();
    root             = null;
    mountedModule    = null;
    mountedContainer = null;
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