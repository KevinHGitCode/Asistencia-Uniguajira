document.addEventListener('alpine:init', () => {
    Alpine.data('formatsManager', () => ({
        search: '',
        showForm: false,
        editingId: null,
        formName: '',
        formSlug: '',
        selectedDependencies: [],
        fileName: '',
        currentFile: '',
        showDelete: false,
        deleteId: null,
        deleteName: '',

        openCreate() {
            this.editingId = null;
            this.formName = '';
            this.formSlug = '';
            this.selectedDependencies = [];
            this.fileName = '';
            this.currentFile = '';
            this.showForm = true;
        },

        openEdit(id, name, slug, dependencies, file) {
            this.editingId = id;
            this.formName = name;
            this.formSlug = slug;
            this.selectedDependencies = dependencies || [];
            this.fileName = '';
            this.currentFile = file || '';
            this.showForm = true;
        },

        closeForm() {
            this.showForm = false;
            this.editingId = null;
            this.fileName = '';
            this.currentFile = '';
        },

        openDelete(id, name) {
            this.deleteId = id;
            this.deleteName = name;
            this.showDelete = true;
        },

        closeDelete() {
            this.showDelete = false;
            this.deleteId = null;
        },

        toggleDependency(id) {
            const index = this.selectedDependencies.indexOf(id);
            if (index === -1) {
                this.selectedDependencies.push(id);
            } else {
                this.selectedDependencies.splice(index, 1);
            }
        },
    }));
});