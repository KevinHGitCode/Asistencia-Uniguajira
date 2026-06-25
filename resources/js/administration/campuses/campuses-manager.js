window.campusesManager = function () {
    return {
        showForm: false, editingId: null, formName: '', showDelete: false, deleteId: null, deleteName: '',
        openCreate() { this.editingId = null; this.formName = ''; this.showForm = true; },
        openEdit(id, name) { this.editingId = id; this.formName = name; this.showForm = true; },
        openDelete(id, name) { this.deleteId = id; this.deleteName = name; this.showDelete = true; },
        closeForm() { this.showForm = false; }, closeDelete() { this.showDelete = false; },
    };
};
document.addEventListener('alpine:init', () => Alpine.data('campusesManager', window.campusesManager));
