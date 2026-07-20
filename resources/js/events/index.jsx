import { createRoot } from 'react-dom/client';
import EventChartsApp from './EventChartsApp.jsx';

let root             = null;
let mountedContainer = null; // referencia al nodo DOM actual

/**
 * Mount the event charts React app.
 *
 * Detecta si wire:navigate reemplazó el contenedor (nuevo nodo DOM)
 * y en ese caso crea un root nuevo en lugar de reutilizar el anterior.
 */
function mount(eventId, open) {
  const container = document.getElementById('event-charts-react-root');
  if (!container) return;

  // Si el contenedor cambió (wire:navigate reemplazó el DOM), recrear el root
  if (!root || mountedContainer !== container) {
    if (root) root.unmount();
    root             = createRoot(container);
    mountedContainer = container;
  }

  root.render(<EventChartsApp eventId={eventId} open={open} />);
}

/**
 * Unmount the app and clean up
 */
function unmount() {
  if (root) {
    root.unmount();
    root             = null;
    mountedContainer = null;
  }
}

// Extract event ID + estado (abierto) del data attribute
const rootEl = document.getElementById('event-charts-react-root');

// Mount on initial load
mount(rootEl?.dataset?.eventId, rootEl?.dataset?.eventOpen === '1');

// Support Livewire 3 wire:navigate
document.addEventListener('livewire:navigated', () => {
  const container = document.getElementById('event-charts-react-root');
  const newEventId = container?.dataset?.eventId;

  if (container && newEventId) {
    mount(newEventId, container.dataset.eventOpen === '1');
  } else {
    unmount();
  }
});