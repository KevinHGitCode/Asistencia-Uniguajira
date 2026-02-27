import { createRoot } from 'react-dom/client';
import EventChartsApp from './EventChartsApp.jsx';

let root = null;

/**
 * Mount the event charts React app
 */
function mount(eventId) {
  const container = document.getElementById('event-charts-react-root');
  if (!container) return;

  if (!root) {
    root = createRoot(container);
  }
  root.render(<EventChartsApp eventId={eventId} />);
}

/**
 * Unmount the app and clean up
 */
function unmount() {
  if (root) {
    root.unmount();
    root = null;
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
