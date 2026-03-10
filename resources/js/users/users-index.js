
function initUsersAlertDismiss() {
    const alertBox = document.getElementById('users-success-alert');
    if (!alertBox || alertBox.dataset.dismissing) return;
    alertBox.dataset.dismissing = 'true';

    setTimeout(function () {
        alertBox.classList.add('opacity-0');
        setTimeout(function () { alertBox.remove(); }, 500);
    }, 5000);
}

if (document.readyState !== 'loading') {
    initUsersAlertDismiss();
} else {
    document.addEventListener('DOMContentLoaded', initUsersAlertDismiss);
}

// Soporte wire:navigate de Livewire 3 (SPA)
document.addEventListener('livewire:navigated', initUsersAlertDismiss);
