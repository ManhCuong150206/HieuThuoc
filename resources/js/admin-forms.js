import { setupProductSelection, setupPromotionProducts } from './admin-product-selection';
import { showFeedback } from './feedback';

const numberFormat = new Intl.NumberFormat('vi-VN');

const saveError = 'Chưa nhận được kết quả. Vui lòng kiểm tra danh sách trước khi gửi lại.';

async function requestAdmin(url, options = {}) {
    try {
        const response = await fetch(url, {
            ...options,
            headers: { Accept: 'application/json' },
            redirect: 'error',
        });
        const data = response.headers.get('content-type')?.includes('application/json') ? await response.json() : null;

        if (response.status === 422 && data?.errors) return { errors: data.errors };
        if (response.ok && typeof data?.html === 'string' && data?.url) return { data };
        if (response.status === 401 || response.status === 419) {
            return { errors: { form: ['Phiên đăng nhập đã hết hạn. Vui lòng tải lại trang.'] } };
        }
        if (response.status === 403) {
            return { errors: { form: ['Bạn không có quyền thực hiện thao tác này.'] } };
        }
        if (response.status === 404) {
            return { errors: { form: ['Mục này không còn tồn tại.'] } };
        }
        if (response.status === 413) {
            return { errors: { form: ['Tệp tải lên quá lớn. Vui lòng chọn tệp nhỏ hơn.'] } };
        }
    } catch {
        // Do not retry a save automatically when its result is unknown.
    }

    const message = options.method === 'POST' ? saveError : 'Chưa tải được thông tin. Vui lòng đóng hộp thoại và thử lại.';
    return { errors: { form: [message] } };
}

const sendAdminForm = (form) => requestAdmin(form.action, { method: 'POST', body: new FormData(form) });

function parseResults(html) {
    const template = document.createElement('template');
    template.innerHTML = html;
    return template.content;
}

const dialog = document.getElementById('resource-create-dialog');
if (dialog) {
    const trigger = document.querySelector('[data-admin-create-open]');
    const form = dialog.querySelector('[data-admin-create-form]');
    const fields = form.querySelector('[data-admin-create-fields]');
    const content = fields.querySelector('[data-admin-form-content]');
    const loading = fields.querySelector('[data-admin-form-loading]');
    const errorList = form.querySelector('[data-admin-create-errors]');
    const title = form.querySelector('[data-admin-form-title]');
    const method = form.elements.namedItem('_method');
    const submit = form.querySelector('button[type="submit"]');
    const closeButtons = [...dialog.querySelectorAll('[data-admin-create-close]')];
    const createFields = [...content.childNodes];
    const createTitle = title.textContent;
    let createAction = form.action;
    let editLink = null;
    let editRequest = null;

    function clearErrors() {
        errorList.hidden = true;
        errorList.replaceChildren();
    }

    function showErrors(errors) {
        for (const [name, messages] of Object.entries(errors)) {
            const field = form.elements.namedItem(name);
            if (field instanceof HTMLElement && field.type !== 'hidden') field.setAttribute('aria-invalid', 'true');
            for (const message of messages) {
                const item = document.createElement('li');
                item.textContent = message;
                errorList.append(item);
            }
        }
        errorList.hidden = false;
        errorList.focus();
    }

    trigger.addEventListener('click', () => dialog.showModal());
    closeButtons.forEach((button) => button.addEventListener('click', () => dialog.close()));
    dialog.addEventListener('close', () => {
        if (editLink) {
            editRequest?.abort();
            editRequest = null;
            const editedId = editLink.dataset.adminEditOpen;
            const opener = document.querySelector(`[data-admin-edit-open="${editedId}"]`);
            editLink = null;
            method.disabled = true;
            submit.disabled = false;
            loading.hidden = true;
            clearErrors();
            title.textContent = createTitle;
            form.action = createAction;
            content.replaceChildren(...createFields);
            fields.scrollTop = 0;
            (opener || trigger).focus({ preventScroll: true });
        } else {
            trigger.focus({ preventScroll: true });
        }
    });
    dialog.addEventListener('cancel', (event) => {
        if (form.getAttribute('aria-busy') === 'true') event.preventDefault();
    });

    if (dialog.hasAttribute('data-open-on-load')) {
        dialog.showModal();
        dialog.querySelector('[aria-invalid="true"]')?.focus();
    }

    document.addEventListener('click', async (event) => {
        const link = event.target.closest('[data-admin-edit-open]');
        if (!link || event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        if (dialog.open) return;

        editLink = link;
        const request = new AbortController();
        editRequest = request;
        clearErrors();
        title.textContent = dialog.dataset.editTitle;
        content.replaceChildren();
        fields.scrollTop = 0;
        submit.disabled = true;
        loading.hidden = false;
        dialog.showModal();

        const { data, errors } = await requestAdmin(link.href, { signal: request.signal });
        if (editRequest !== request || !dialog.open) return;
        editRequest = null;
        loading.hidden = true;
        if (errors) {
            showErrors(errors);
            return;
        }

        content.replaceChildren(parseResults(data.html));
        setupPromotionProducts(content.querySelector('[data-promotion-products]'));
        form.action = data.url;
        method.disabled = false;
        submit.disabled = false;
        content.querySelector('.ui-field :is(input, select, textarea)')?.focus();
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (submit.disabled) return;

        for (const field of form.querySelectorAll('[aria-invalid]')) field.removeAttribute('aria-invalid');
        clearErrors();
        form.setAttribute('aria-busy', 'true');
        const label = submit.textContent;
        submit.textContent = 'Đang lưu…';
        submit.disabled = true;
        closeButtons.forEach((button) => { button.disabled = true; });
        fields.inert = true;

        const { data, errors } = await sendAdminForm(form);
        form.removeAttribute('aria-busy');
        submit.textContent = label;
        submit.disabled = false;
        closeButtons.forEach((button) => { button.disabled = false; });
        fields.inert = false;

        if (errors) {
            showErrors(errors);
            return;
        }

        const nextResults = parseResults(data.html).querySelector('[data-admin-resource-results]');
        if (!nextResults) {
            showErrors({ form: [saveError] });
            return;
        }
        document.querySelector('[data-admin-resource-results]').replaceWith(nextResults);
        setupProductSelection(nextResults.matches('[data-admin-product-selection]') ? nextResults : null);
        window.history.replaceState(window.history.state, '', data.url);
        createAction = data.url;
        if (!editLink) {
            const search = document.getElementById('admin-search');
            search.value = search.defaultValue = '';
            form.action = createAction;
            form.reset();
            content.querySelector('[name="applies_to_all_products"]')?.dispatchEvent(new Event('change', { bubbles: true }));
            fields.scrollTop = 0;
        }
        dialog.close();
        showFeedback(data.message, { href: data.image_url, linkText: 'Xem ảnh vừa lưu' });
    });
}

const orderResults = document.querySelector('[data-admin-order-results]');
if (orderResults) {
    orderResults.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!form.matches('[data-admin-order-update]')) return;
        event.preventDefault();
        if (orderResults.getAttribute('aria-busy') === 'true') return;

        const button = form.querySelector('button[type="submit"]');
        const buttons = orderResults.querySelectorAll('[data-admin-order-update] button[type="submit"]');
        const label = button.textContent;
        const order = form.closest('[data-admin-order]');
        const orderId = order.dataset.adminOrder;
        orderResults.setAttribute('aria-busy', 'true');
        buttons.forEach((submit) => { submit.disabled = true; });
        button.textContent = 'Đang cập nhật…';

        const { data, errors } = await sendAdminForm(form);
        orderResults.removeAttribute('aria-busy');
        buttons.forEach((submit) => { submit.disabled = false; });
        button.textContent = label;

        if (errors) {
            showFeedback(Object.values(errors).flat()[0], { error: true });
            return;
        }

        const incoming = parseResults(data.html);
        for (const select of orderResults.querySelectorAll('select[name="status"]')) {
            if (form.contains(select) || select.selectedIndex === 0) continue;
            const next = incoming.getElementById(select.id);
            if (next && [...next.options].some((option) => option.value === select.value)) next.value = select.value;
        }
        const updated = incoming.querySelector(`[data-admin-order="${orderId}"] h2`);
        const restoreFocus = order.contains(document.activeElement) || document.activeElement === document.body;
        orderResults.replaceChildren(incoming);
        document.querySelector('[data-admin-order-count]').textContent = numberFormat.format(data.total);
        window.history.replaceState(window.history.state, '', data.url);
        if (restoreFocus) (updated || document.querySelector('[data-admin-order-heading]')).focus({ preventScroll: true });
        showFeedback(data.message);
    });
}
