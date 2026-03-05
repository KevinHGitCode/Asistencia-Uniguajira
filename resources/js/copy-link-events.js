function initCopyLink() {
    const copyButton = document.getElementById('copy-link-button');
    if (!copyButton || copyButton.dataset.initialized) return;
    copyButton.dataset.initialized = 'true';

    copyButton.addEventListener('click', function() {
        const link = this.getAttribute('data-link');

        navigator.clipboard.writeText(link).then(() => {
            const originalText = this.innerText;
            this.innerText = '✅Listo';
            setTimeout(() => this.innerText = originalText, 2000);
        }).catch(err => {
            console.error('Error al copiar:', err);
            alert('No se pudo copiar el enlace');
        });
    });
}

if (document.readyState !== 'loading') {
    initCopyLink();
} else {
    document.addEventListener('DOMContentLoaded', initCopyLink);
}

document.addEventListener('livewire:navigated', initCopyLink);
