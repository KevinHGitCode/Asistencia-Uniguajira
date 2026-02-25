import { createRoot } from 'react-dom/client';
import StatisticsApp from './StatisticsApp.jsx';

let root = null;

/**
 * Monta (o re-monta) la app React en el contenedor de estadísticas.
 * Compatible con wire:navigate de Livewire 3.
 */
function mount() {
  const container = document.getElementById('statistics-react-root');
  if (!container) return; // no estamos en la página de estadísticas

  if (!root) {
    root = createRoot(container);
  }
  root.render(<StatisticsApp />);
}

/**
 * Desmonta la app y libera recursos cuando se navega fuera de la página.
 * Se llama en `livewire:navigated` cuando el contenedor ya no existe.
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
// Después de cada navegación: montar si el contenedor existe, desmontar si no.
document.addEventListener('livewire:navigated', () => {
  const container = document.getElementById('statistics-react-root');
  if (container) {
    mount();
  } else {
    unmount();
  }
});
