import { createRoot } from 'react-dom/client';
import ParticipantsApp from './ParticipantsApp.jsx';

const ROOT_ID = 'participants-react-root';

let root = null;
let mountedContainer = null;

function mount() {
    const container = document.getElementById(ROOT_ID);
    if (!container) return;

    // Si ya está montado en el mismo nodo DOM, no remontar
    if (root && mountedContainer === container) return;

    if (root) {
        root.unmount();
        root = null;
    }

    root = createRoot(container);
    mountedContainer = container;
    root.render(<ParticipantsApp />);
}

function unmount() {
    if (root) {
        root.unmount();
        root = null;
        mountedContainer = null;
    }
}

mount();

// Soporte wire:navigate de Livewire 3
document.addEventListener('livewire:navigated', () => {
    if (document.getElementById(ROOT_ID)) mount();
    else unmount();
});
