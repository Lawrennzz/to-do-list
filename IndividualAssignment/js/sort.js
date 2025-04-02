function toggleSortOrder() {
    const sortOrderBtn = document.querySelector('.sort-order-btn');
    const sortIcon = sortOrderBtn.querySelector('i');
    const form = sortOrderBtn.closest('form');
    const sortOrder = form.querySelector('input[name="order"]');

    if (!sortOrder) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'order';
        input.value = 'ASC';
        form.appendChild(input);
    }

    if (sortOrder.value === 'ASC') {
        sortOrder.value = 'DESC';
        sortIcon.classList.remove('fa-sort-amount-up');
        sortIcon.classList.add('fa-sort-amount-down');
    } else {
        sortOrder.value = 'ASC';
        sortIcon.classList.remove('fa-sort-amount-down');
        sortIcon.classList.add('fa-sort-amount-up');
    }

    form.submit();
}
