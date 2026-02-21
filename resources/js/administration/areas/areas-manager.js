document.addEventListener('alpine:init', () => {
    Alpine.data('areasManager', () => ({
        search: '',
        showForm: false,
        editingId: null,
        formName: '',
        formDependencyId: '',
        showDelete: false,
        deleteId: null,
        deleteName: '',

        openCreate() {
            this.editingId = null;
            this.formName = '';
            this.formDependencyId = '';
            this.showForm = true;
        },
        openEdit(id, name, dependencyId) {
            this.editingId = id;
            this.formName = name;
            this.formDependencyId = dependencyId ? String(dependencyId) : '';
            this.showForm = true;
        },
        closeForm() {
            this.showForm = false;
        },
        openDelete(id, name) {
            this.deleteId = id;
            this.deleteName = name;
            this.showDelete = true;
        },
        closeDelete() {
            this.showDelete = false;
        },
    }));
});
