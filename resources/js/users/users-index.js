
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('users-search-input');
    if (!input) return;

    const desktopRows = Array.from(document.querySelectorAll('tr[data-user-search]'));
    const mobileCards = Array.from(document.querySelectorAll('div[data-user-search]'));
    const desktopEmpty = document.getElementById('users-empty-desktop');
    const mobileEmpty = document.getElementById('users-empty-mobile');
    const paginationContainer = document.getElementById('users-table-pagination');
    const prevButton = document.getElementById('users-page-prev');
    const nextButton = document.getElementById('users-page-next');
    const pageInfo = document.getElementById('users-table-page-info');
    const pageSizeSelect = document.getElementById('users-page-size');

    let timer = null;
    let currentPage = 1;
    let rowsPerPage = Number(pageSizeSelect?.value || 10);

    const normalize = (value) => (value || '').toLowerCase().trim();

    const applyFilter = () => {
        const query = normalize(input.value);
        let visibleMobile = 0;
        const filteredDesktopRows = desktopRows.filter((row) => {
            const text = normalize(row.getAttribute('data-user-search'));
            return query === '' || text.includes(query);
        });
        const totalPages = Math.max(1, Math.ceil(filteredDesktopRows.length / rowsPerPage));
        currentPage = Math.min(currentPage, totalPages);
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const visibleDesktopRows = filteredDesktopRows.slice(start, end);

        desktopRows.forEach((row) => {
            const show = visibleDesktopRows.includes(row);
            row.classList.toggle('hidden', !show);
        });

        visibleDesktopRows.forEach((row, index) => {
            const tooltips = Array.from(row.querySelectorAll('.dependency-tooltip'));
            const showUpward = index === visibleDesktopRows.length - 1;
            tooltips.forEach((tooltip) => {
                tooltip.classList.toggle('top-full', !showUpward);
                tooltip.classList.toggle('mt-2', !showUpward);
                tooltip.classList.toggle('bottom-full', showUpward);
                tooltip.classList.toggle('mb-2', showUpward);
            });
        });

        mobileCards.forEach((card) => {
            const text = normalize(card.getAttribute('data-user-search'));
            const show = query === '' || text.includes(query);
            card.classList.toggle('hidden', !show);
            if (show) visibleMobile++;
        });

        if (desktopEmpty) desktopEmpty.classList.toggle('hidden', filteredDesktopRows.length > 0);
        if (mobileEmpty) mobileEmpty.classList.toggle('hidden', visibleMobile > 0);

        if (paginationContainer) {
            paginationContainer.classList.toggle('hidden', filteredDesktopRows.length === 0);
        }
        if (pageInfo) {
            pageInfo.textContent = `Página ${currentPage} de ${totalPages} (${filteredDesktopRows.length} usuarios)`;
        }
        if (prevButton) prevButton.disabled = currentPage <= 1;
        if (nextButton) nextButton.disabled = currentPage >= totalPages;
    };

    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            currentPage = 1;
            applyFilter();
        }, 600);
    });

    if (prevButton) {
        prevButton.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                applyFilter();
            }
        });
    }

    if (nextButton) {
        nextButton.addEventListener('click', () => {
            currentPage++;
            applyFilter();
        });
    }

    if (pageSizeSelect) {
        pageSizeSelect.addEventListener('change', () => {
            rowsPerPage = Number(pageSizeSelect.value || 10);
            currentPage = 1;
            applyFilter();
        });
    }

    applyFilter();
});
