document.addEventListener('DOMContentLoaded', function () {
    const roleEl = document.getElementById('role');
    const depEl = document.getElementById('dependency_id');
    const wrapper = document.getElementById('dependency-wrapper');
    const note = document.getElementById('dependency-note');

    function toggleDependency() {
        if (!roleEl || !depEl || !wrapper || !note) return;
        if (roleEl.value === 'admin') {
            depEl.value = '';
            depEl.disabled = true;
            wrapper.style.display = 'none';
            note.style.display = '';
        } else {
            depEl.disabled = false;
            wrapper.style.display = '';
            note.style.display = 'none';
        }
    }

    roleEl?.addEventListener('change', toggleDependency);
    toggleDependency();
});
