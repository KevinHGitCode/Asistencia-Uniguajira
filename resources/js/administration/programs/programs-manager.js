window.programsManager = function () {
    return {
        showForm: false,
        editingId: null,
        formName: '',
        formType: '',
        formCampusId: '',
        formAcademicProgramId: '',
        formOfferLocation: '',
        programMode: 'new',
        showDelete: false,
        deleteId: null,
        deleteName: '',

        openCreate() {
            this.editingId = null;
            this.formName = '';
            this.formType = '';
            this.formCampusId = window.administrationActiveCampusId ?? '';
            this.formAcademicProgramId = '';
            this.formOfferLocation = '';
            this.programMode = 'new';
            this.showForm = true;
        },

        openEdit(id, name, type, campusId = '', academicProgramId = '', offerLocation = '') {
            this.editingId = id;
            this.formName = name ?? '';
            this.formType = type ?? '';
            this.formCampusId = campusId ?? '';
            this.formAcademicProgramId = academicProgramId ?? '';
            this.formOfferLocation = offerLocation ?? '';
            this.programMode = academicProgramId ? 'existing' : 'new';
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
    Alpine.data('programsManager', window.programsManager);
});
