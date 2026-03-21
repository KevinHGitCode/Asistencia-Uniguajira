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
function mount(eventId) {
  const container = document.getElementById('event-charts-react-root');
  if (!container) return;

  // Si el contenedor cambió (wire:navigate reemplazó el DOM), recrear el root
  if (!root || mountedContainer !== container) {
    if (root) root.unmount();
    root             = createRoot(container);
    mountedContainer = container;
  }

  root.render(<EventChartsApp eventId={eventId} />);
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

// Extract event ID from data attribute
const eventId = document.getElementById('event-charts-react-root')?.dataset?.eventId;

// Mount on initial load
mount(eventId);

// Support Livewire 3 wire:navigate
document.addEventListener('livewire:navigated', () => {
  const container = document.getElementById('event-charts-react-root');
  const newEventId = container?.dataset?.eventId;

  if (container && newEventId) {
    mount(newEventId);
  } else {
    unmount();
  }
});