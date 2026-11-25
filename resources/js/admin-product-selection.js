const numberFormat = new Intl.NumberFormat('vi-VN');

function updateSelectAll(checkbox, total, checked) {
    checkbox.disabled = total === 0;
    checkbox.checked = total > 0 && checked === total;
    checkbox.indeterminate = checked > 0 && checked < total;
}

export function setupProductSelection(panel) {
    if (!panel) return;
    const search = panel.dataset.search;
    const total = Number(panel.dataset.total);
    const storageKey = 'admin.product-selection.' + panel.dataset.accountId;
    const boxes = [...panel.querySelectorAll('[data-bulk-product]')];
    const pageBox = panel.querySelector('[data-bulk-page]');
    const allButton = panel.querySelector('[data-bulk-all]');
    const clearButton = panel.querySelector('[data-bulk-clear]');
    const openButton = panel.querySelector('[data-bulk-open]');
    const dialog = panel.querySelector('[data-bulk-dialog]');
    const form = panel.querySelector('[data-bulk-form]');
    const submitButton = panel.querySelector('[data-bulk-submit]');
    const countLabel = panel.querySelector('[data-bulk-count]');
    const dialogCount = panel.querySelector('[data-bulk-dialog-count]');
    const fields = ['quantity', 'price', 'category_id', 'is_active'].map((name) => form.elements.namedItem(name));
    let scope = 'selected';
    let ids = new Set();

    function persist() {
        try {
            sessionStorage.setItem(storageKey, JSON.stringify({ scope, search, ids: [...ids] }));
        } catch {
            // Keep the current page usable if session storage is unavailable.
        }
    }

    function clear() {
        scope = 'selected';
        ids.clear();
        persist();
    }

    function restore() {
        try {
            const saved = JSON.parse(sessionStorage.getItem(storageKey));
            if (saved?.scope === 'all' && saved.search !== search) {
                clear();
                return;
            }
            scope = saved?.scope === 'all' ? 'all' : 'selected';
            ids = new Set(saved?.ids ?? []);
        } catch {
            // The current selection is still available in memory.
        }
    }

    function selectedCount() {
        return scope === 'all' ? Math.max(0, total - ids.size) : ids.size;
    }

    function updateSubmit() {
        submitButton.disabled = selectedCount() === 0 || fields.every((field) => field.value === '');
    }

    function render() {
        let checkedOnPage = 0;
        for (const box of boxes) {
            const included = ids.has(Number(box.value));
            box.checked = scope === 'all' ? !included : included;
            if (box.checked) checkedOnPage++;
        }
        updateSelectAll(pageBox, boxes.length, checkedOnPage);
        const count = selectedCount();
        const label = 'Đã chọn ' + numberFormat.format(count) + ' sản phẩm';
        countLabel.textContent = label;
        dialogCount.textContent = label;
        allButton.hidden = scope === 'all' || total <= boxes.length;
        clearButton.hidden = scope === 'selected' && count === 0;
        openButton.disabled = count === 0;
        updateSubmit();
    }

    function setChecked(box, checked) {
        const id = Number(box.value);
        if (checked === (scope === 'selected')) {
            ids.add(id);
        } else {
            ids.delete(id);
        }
    }

    panel.addEventListener('change', (event) => {
        if (event.target === pageBox) {
            boxes.forEach((box) => setChecked(box, pageBox.checked));
        } else if (event.target.matches('[data-bulk-product]')) {
            setChecked(event.target, event.target.checked);
        } else {
            return;
        }
        persist();
        render();
    });

    allButton.addEventListener('click', () => {
        scope = 'all';
        ids.clear();
        persist();
        render();
        openButton.focus();
    });
    clearButton.addEventListener('click', () => {
        clear();
        render();
        (pageBox.disabled ? document.getElementById('admin-search') : pageBox).focus();
    });
    openButton.addEventListener('click', () => dialog.showModal());
    panel.querySelector('[data-bulk-close]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => {
        fields.forEach((field) => { field.value = ''; });
        form.querySelector('[data-bulk-errors]')?.remove();
        updateSubmit();
    });
    form.addEventListener('input', updateSubmit);
    form.addEventListener('change', updateSubmit);
    form.addEventListener('submit', (event) => {
        updateSubmit();
        if (submitButton.disabled) {
            event.preventDefault();
            return;
        }
        form.elements.namedItem('scope').value = scope;
        form.elements.namedItem('ids').value = JSON.stringify([...ids]);
        form.elements.namedItem('selected_count').value = selectedCount();
        submitButton.disabled = true;
    });

    if (panel.hasAttribute('data-bulk-saved')) {
        clear();
    } else {
        restore();
        if (ids.delete(Number(panel.dataset.deletedProduct))) persist();
    }
    boxes.forEach((box) => { box.disabled = false; });
    panel.querySelector('[data-bulk-controls]').hidden = false;
    render();
    if (selectedCount() > 0 && form.querySelector('[data-bulk-errors]')) dialog.showModal();
    panel.addEventListener('admin:restore-selection', () => {
        if (dialog.open) dialog.close();
        restore();
        render();
    });
}

document.querySelectorAll('[data-admin-product-selection]').forEach(setupProductSelection);
window.addEventListener('pageshow', ({ persisted }) => {
    if (persisted) {
        document.querySelector('[data-admin-product-selection]')?.dispatchEvent(new Event('admin:restore-selection'));
    }
});

export function setupPromotionProducts(section) {
    if (!section) return;
    const scope = section.querySelector('[name="applies_to_all_products"]');
    const picker = section.querySelector('[data-promotion-picker]');
    const input = section.querySelector('[name="product_ids"]');
    const search = section.querySelector('[data-promotion-search]');
    const selectAll = section.querySelector('[data-promotion-all]');
    const selectedOnly = section.querySelector('[data-promotion-selected]');
    const count = section.querySelector('[data-promotion-count]');
    const empty = section.querySelector('[data-promotion-empty]');
    const normalize = (value) => value.toLocaleLowerCase('vi').normalize('NFD')
        .replace(/\p{Diacritic}/gu, '').replaceAll('đ', 'd').trim();
    const options = [...section.querySelectorAll('[data-promotion-option]')].map((row) => ({
        row,
        name: normalize(row.querySelector('[data-promotion-name]').textContent),
        box: row.querySelector('input'),
    }));
    let visible = [];

    function render() {
        const query = normalize(search.value);
        const selected = [];
        let checkedVisible = 0;
        visible = [];
        for (const { row, name, box } of options) {
            if (box.checked) selected.push(Number(box.value));
            row.hidden = !name.includes(query) || (selectedOnly.checked && !box.checked);
            if (!row.hidden) {
                visible.push(box);
                if (box.checked) checkedVisible++;
            }
        }
        input.value = JSON.stringify(selected);
        picker.hidden = scope.value === '1';
        input.disabled = picker.hidden;
        count.textContent = 'Đã chọn ' + numberFormat.format(selected.length) + ' sản phẩm';
        empty.hidden = visible.length > 0;
        updateSelectAll(selectAll, visible.length, checkedVisible);
    }

    search.addEventListener('input', render);
    search.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') event.preventDefault();
    });
    section.addEventListener('change', (event) => {
        if (event.target === selectAll) {
            visible.forEach((box) => { box.checked = selectAll.checked; });
        }
        render();
    });
    render();
}

document.querySelectorAll('[data-promotion-products]').forEach(setupPromotionProducts);
