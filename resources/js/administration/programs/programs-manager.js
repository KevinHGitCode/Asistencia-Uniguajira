document.addEventListener('alpine:init', () => {
    Alpine.data('programsManager', () => ({
        search: '',
        showForm: false,
        editingId: null,
        formName: '',
        formType: '',
        showDelete: false,
        deleteId: null,
        deleteName: '',

        openCreate() {
            this.editingId = null;
            this.formName = '';
            this.formType = '';
            this.showForm = true;
        },

        openEdit(id, name, type) {
            this.editingId = id;
            this.formName = name ?? '';
            this.formType = type ?? '';
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
