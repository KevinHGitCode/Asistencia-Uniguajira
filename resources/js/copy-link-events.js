document.addEventListener('DOMContentLoaded', function() {
    const copyButton = document.getElementById('copy-link-button');
    
    if (copyButton) {
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
});
