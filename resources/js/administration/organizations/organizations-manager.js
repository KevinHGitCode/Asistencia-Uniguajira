window.organizationsManager = function () {
    return {
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

        // Merge search
        mergeSearchQuery: '',
        mergeSearchResults: [],
        mergeSearchOpen: false,
        mergeShowAll: false,
        mergeSearchTimeout: null,
        allOrganizations: [],

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
            this.mergeSearchQuery = '';
            this.mergeSearchResults = [];
            this.mergeSearchOpen = false;
            this.mergeShowAll = false;
            this.showMerge = true;
        },

        searchMergeTargets() {
            clearTimeout(this.mergeSearchTimeout);
            this.mergeTargetId = null;

            const q = this.mergeSearchQuery.trim();
            if (q.length < 2) {
                this.mergeSearchResults = [];
                this.mergeSearchOpen = false;
                return;
            }

            this.mergeSearchTimeout = setTimeout(() => {
                fetch('/administracion/organizations/search?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(data => {
                        this.mergeSearchResults = data.filter(o => o.id !== this.mergeId);
                        this.mergeSearchOpen = this.mergeSearchResults.length > 0;
                    });
            }, 300);
        },

        selectMergeTarget(id, name) {
            this.mergeTargetId = id;
            this.mergeSearchQuery = name;
            this.mergeSearchResults = [];
            this.mergeSearchOpen = false;
        },

        closeMerge() {
            this.showMerge = false;
        },
    };
};

document.addEventListener('alpine:init', () => {
    Alpine.data('organizationsManager', window.organizationsManager);
});
