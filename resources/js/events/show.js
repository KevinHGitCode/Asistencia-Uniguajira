document.addEventListener('DOMContentLoaded', function () {
    // --- Copiar enlace ---
    const copyBtn = document.getElementById('copy-link-button');
    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            const link = copyBtn.getAttribute('data-link');
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard
                    .writeText(link)
                    .then(() => showCopied(copyBtn))
                    .catch(() => fallbackCopy(link, copyBtn));
            } else {
                fallbackCopy(link, copyBtn);
            }
        });
    }

    // --- Generar QR como PNG Blob ---
    function getQrPngBlob() {
        return new Promise(function (resolve, reject) {
            const container = document.getElementById('qr-code-container');
            if (!container) return reject(new Error('Contenedor no encontrado'));
            const svg = container.querySelector('svg');
            if (!svg) return reject(new Error('SVG no encontrado'));

            const clone = svg.cloneNode(true);
            if (!clone.getAttribute('xmlns')) clone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            if (!clone.getAttribute('xmlns:xlink'))
                clone.setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');

            let width = parseInt(clone.getAttribute('width'), 10);
            let height = parseInt(clone.getAttribute('height'), 10);
            if (!width || !height) {
                const rect = svg.getBoundingClientRect();
                width = Math.ceil(rect.width) || 400;
                height = Math.ceil(rect.height) || 400;
            }

            const scale = 2;
            const svgString = new XMLSerializer().serializeToString(clone);
            const svgBlob = new Blob([svgString], { type: 'image/svg+xml;charset=utf-8' });
            const url = URL.createObjectURL(svgBlob);

            const img = new Image();
            img.onload = function () {
                const canvas = document.createElement('canvas');
                canvas.width = width * scale;
                canvas.height = height * scale;
                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                URL.revokeObjectURL(url);
                canvas.toBlob(
                    function (blob) {
                        if (!blob) return reject(new Error('Blob vacío'));
                        resolve(blob);
                    },
                    'image/png',
                );
            };
            img.onerror = function () {
                URL.revokeObjectURL(url);
                reject(new Error('No se pudo cargar el SVG'));
            };
            img.src = url;
        });
    }

    function downloadBlob(blob, filename) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(() => URL.revokeObjectURL(url), 1000);
    }

    async function copyImageToClipboard(blob) {
        if (!navigator.clipboard || !window.ClipboardItem) return false;
        try {
            await navigator.clipboard.write([new ClipboardItem({ 'image/png': blob })]);
            return true;
        } catch (e) {
            return false;
        }
    }

    // Devuelve true si Web Share API puede compartir archivos
    function canShareFiles(file) {
        return !!(navigator.canShare && navigator.canShare({ files: [file] }) && navigator.share);
    }

    // Obtiene el contenedor con los atributos de datos
    const shareContainer = document.querySelector('[data-whatsapp-url]');
    const qrFilename = shareContainer ? shareContainer.getAttribute('data-qr-filename') : 'qr-evento.png';
    const shareText = shareContainer ? shareContainer.getAttribute('data-share-text') : '';
    const whatsappUrl = shareContainer ? shareContainer.getAttribute('data-whatsapp-url') : '#';
    const gmailUrl = shareContainer ? shareContainer.getAttribute('data-gmail-url') : '#';

    // Handler genérico: intenta compartir archivo nativamente; si no, descarga + copia + abre servicio
    async function shareWithQr(options) {
        const { serviceUrl, serviceName, fallbackMessage, button } = options;
        setButtonLoading(button, true);

        let blob;
        try {
            blob = await getQrPngBlob();
        } catch (err) {
            setButtonLoading(button, false);
            // Sin QR no podemos hacer nada mejor que abrir solo la URL
            window.open(serviceUrl, '_blank', 'noopener,noreferrer');
            return;
        }

        const file = new File([blob], qrFilename, { type: 'image/png' });

        // 1) Intentar Web Share API con archivo (ideal para móvil)
        if (canShareFiles(file)) {
            try {
                await navigator.share({
                    text: shareText,
                    files: [file],
                });
                setButtonLoading(button, false);
                return;
            } catch (err) {
                if (err && err.name === 'AbortError') {
                    setButtonLoading(button, false);
                    return;
                }
                // Si falla (ej. permiso), caemos al fallback
            }
        }

        // 2) Fallback: descargar QR + copiar al portapapeles + abrir el servicio
        downloadBlob(blob, qrFilename);
        const copied = await copyImageToClipboard(blob);

        const popup = window.open(serviceUrl, '_blank', 'noopener,noreferrer');
        if (!popup) {
            // Bloqueado por popup blocker
            showToast(
                'Permite ventanas emergentes para abrir ' + serviceName + '. El QR ya fue descargado.',
                5000,
            );
        } else if (copied) {
            showToast(
                'QR descargado y copiado. Pégalo (Ctrl+V) o adjúntalo en ' + serviceName + '.',
                5000,
            );
        } else {
            showToast(fallbackMessage, 5000);
        }

        setButtonLoading(button, false);
    }

    // --- WhatsApp ---
    const whatsappBtn = document.getElementById('share-whatsapp-button');
    if (whatsappBtn) {
        whatsappBtn.addEventListener('click', function () {
            shareWithQr({
                serviceUrl: whatsappUrl,
                serviceName: 'WhatsApp',
                fallbackMessage: 'QR descargado. Adjúntalo en WhatsApp con el ícono de clip.',
                button: whatsappBtn,
            });
        });
    }

    // --- Gmail ---
    const gmailBtn = document.getElementById('share-email-button');
    if (gmailBtn) {
        gmailBtn.addEventListener('click', async function () {
            setButtonLoading(gmailBtn, true);

            try {
                const blob = await getQrPngBlob();
                downloadBlob(blob, qrFilename);
                showToast('QR descargado. Adjúntalo al correo con el ícono de clip.', 5000);
            } catch (err) {
                // Si falla la generación del QR, abrimos Gmail de todos modos
            }

            window.open(gmailUrl, '_blank', 'noopener,noreferrer');
            setButtonLoading(gmailBtn, false);
        });
    }

    // --- Descargar QR ---
    const downloadBtn = document.getElementById('download-qr-button');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', async function () {
            setButtonLoading(downloadBtn, true);
            try {
                const blob = await getQrPngBlob();
                downloadBlob(blob, downloadBtn.getAttribute('data-filename') || qrFilename);
                showDownloaded(downloadBtn);
            } catch (err) {
                alert('No se pudo generar la imagen del QR.');
            } finally {
                setButtonLoading(downloadBtn, false);
            }
        });
    }
});

function setButtonLoading(button, loading) {
    if (!button) return;
    if (loading) {
        button.disabled = true;
        button.dataset.originalOpacity = button.style.opacity || '';
        button.style.opacity = '0.6';
        button.style.cursor = 'wait';
    } else {
        button.disabled = false;
        button.style.opacity = button.dataset.originalOpacity || '';
        button.style.cursor = '';
    }
}

function showToast(message, duration) {
    const toast = document.getElementById('share-toast');
    const msg = document.getElementById('share-toast-message');
    if (!toast || !msg) return;
    msg.textContent = message;
    toast.classList.remove('hidden');
    clearTimeout(window.__shareToastTimer);
    window.__shareToastTimer = setTimeout(function () {
        toast.classList.add('hidden');
    }, duration || 3500);
}

function showDownloaded(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '✓ Descargado';
    button.classList.add('bg-green-100', 'dark:bg-green-900', 'border-green-400');
    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('bg-green-100', 'dark:bg-green-900', 'border-green-400');
    }, 2000);
}

function fallbackCopy(text, button) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    try {
        document.execCommand('copy');
        showCopied(button);
    } catch (err) {
        alert('No se pudo copiar: ' + text);
    }
    document.body.removeChild(textarea);
}

function showCopied(button) {
    const originalText = button.innerHTML;
    button.innerHTML = '✓ Copiado';
    button.classList.add('bg-green-100', 'dark:bg-green-900', 'border-green-400');
    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('bg-green-100', 'dark:bg-green-900', 'border-green-400');
    }, 2000);
}
