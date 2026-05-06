document.addEventListener('alpine:init', () => {
    Alpine.data('organizationsManager', () => ({
        search: '',
        showForm: false,
        editingId: null,
        formName: '',
        showDelete: false,
        deleteId: null,
        deleteName: '',
        showMerge: false,
        mergeId: null,
        mergeName: '',
        mergeTargetId: null,

        openCreate() {
            this.editingId = null;
            this.formName = '';
            this.showForm = true;
        },

        openEdit(id, name) {
            this.editingId = id;
            this.formName = name ?? '';
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

        openMerge(id, name) {
            this.mergeId = id;
            this.mergeName = name;
            this.mergeTargetId = null;
            this.showMerge = true;
        },

        closeMerge() {
            this.showMerge = false;
        },
    }));
});
