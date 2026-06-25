window.dependenciesManager = function () {
    return {
        showForm: false,
        editingId: null,
        formName: '',
        formCampusId: '',
        showDelete: false,
        deleteId: null,
        deleteName: '',

        openCreate() {
            this.editingId = null;
            this.formName = '';
            this.formCampusId = window.administrationActiveCampusId ?? '';
            this.showForm = true;
        },

        openEdit(id, name, campusId = '') {
            this.editingId = id;
            this.formName = name;
            this.formCampusId = campusId ?? '';
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
    };
};

document.addEventListener('alpine:init', () => {
    Alpine.data('dependenciesManager', window.dependenciesManager);
});
