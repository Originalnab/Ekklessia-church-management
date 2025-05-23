// pagination.js
// Handles pagination UI and logic for the events table

function renderPagination(total, page, pageSize, onPageChange) {
    const totalPages = Math.ceil(total / pageSize);
    const paginationEl = document.getElementById('eventsPagination');
    if (!paginationEl) return;
    paginationEl.innerHTML = '';
    if (totalPages <= 1) return;

    const createPageBtn = (p, label, active = false, disabled = false) => {
        const li = document.createElement('li');
        li.className = 'page-item' + (active ? ' active' : '') + (disabled ? ' disabled' : '');
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = label;
        a.addEventListener('click', function (e) {
            e.preventDefault();
            if (!disabled && p !== page) onPageChange(p);
        });
        li.appendChild(a);
        return li;
    };

    // Previous
    paginationEl.appendChild(createPageBtn(page - 1, '«', false, page === 1));
    // Page numbers
    for (let p = 1; p <= totalPages; p++) {
        if (p === 1 || p === totalPages || Math.abs(p - page) <= 2) {
            paginationEl.appendChild(createPageBtn(p, p, p === page));
        } else if (Math.abs(p - page) === 3) {
            const li = document.createElement('li');
            li.className = 'page-item disabled';
            li.innerHTML = '<span class="page-link">…</span>';
            paginationEl.appendChild(li);
        }
    }
    // Next
    paginationEl.appendChild(createPageBtn(page + 1, '»', false, page === totalPages));
}
