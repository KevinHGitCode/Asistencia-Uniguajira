function initAreasScript() {

    const dependencySelect = document.getElementById('dependencySelect');
    const dependencyHidden = document.getElementById('dependencyHidden');
    const areaSelect = document.getElementById('areaSelect');

    if (!areaSelect) return;

    function clearDisableArea() {
        areaSelect.innerHTML = '<option value="">Selecciona un área (opcional)</option>';
        areaSelect.disabled = true;
    }

    async function loadAreasFor(dependencyId) {
        if (!dependencyId) {
            clearDisableArea();
            return;
        }

        try {
            const response = await fetch(`/dependencies/${dependencyId}/areas`, {
                credentials: 'same-origin'
            });

            if (!response.ok) {
                clearDisableArea();
                return;
            }

            const areas = await response.json();

            if (!areas.length) {
                clearDisableArea();
                return;
            }

            let options = '<option value="">Selecciona un área (opcional)</option>';

            areas.forEach(area => {
                options += `<option value="${area.id}">${area.name}</option>`;
            });

            areaSelect.innerHTML = options;
            areaSelect.disabled = false;

        } catch {
            clearDisableArea();
        }
    }

    const initialDependency = dependencyHidden?.value || dependencySelect?.value;

    if (initialDependency) {
        loadAreasFor(initialDependency);
    }

    dependencySelect?.addEventListener('change', e => {
        loadAreasFor(e.target.value);
    });
}

document.addEventListener('DOMContentLoaded', initAreasScript);
document.addEventListener('livewire:navigated', initAreasScript);
