document.addEventListener('alpine:init', () => {
    Alpine.data('programsManager', () => ({
        search: '',
        showForm: false,
        editingId: null,
        formName: '',
        formCampus: '',
        formType: '',
        showDelete: false,
        deleteId: null,
        deleteName: '',

        openCreate() {
            this.editingId = null;
            this.formName = '';
            this.formCampus = '';
            this.formType = '';
            this.showForm = true;
        },

        openEdit(id, name, campus, type) {
            this.editingId = id;
            this.formName = name ?? '';
            this.formCampus = campus ?? '';
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
