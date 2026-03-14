document.addEventListener('alpine:init', () => {
    Alpine.data('formatsManager', () => ({
        search: '',
        showForm: false,
        editingId: null,
        formName: '',
        formSlug: '',
        selectedDependencies: [],
        showDelete: false,
        deleteId: null,
        deleteName: '',

        openCreate() {
            this.editingId = null;
            this.formName = '';
            this.formSlug = '';
            this.selectedDependencies = [];
            this.showForm = true;
        },

        openEdit(id, name, slug, dependencies) {
            this.editingId = id;
            this.formName = name;
            this.formSlug = slug;
            this.selectedDependencies = dependencies || [];
            this.showForm = true;
        },

        closeForm() {
            this.showForm = false;
            this.editingId = null;
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