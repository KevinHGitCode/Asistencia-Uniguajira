document.addEventListener('alpine:init', () => {
    Alpine.data('dependenciesManager', () => ({
        search: '',
        showForm: false,
        editingId: null,
        formName: '',
        showDelete: false,
        deleteId: null,
        deleteName: '',

        openCreate() {
            this.editingId = null;
            this.formName = '';
            this.showForm = true;
        },

        openEdit(id, name) {
            this.editingId = id;
            this.formName = name;
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
