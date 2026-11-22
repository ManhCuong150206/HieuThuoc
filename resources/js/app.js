import './bootstrap';
import './shopping';

document.querySelectorAll('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});

document.querySelectorAll('[data-checkout]').forEach((form) => {
    const promotion = form.querySelector('[data-promotion]');
    const format = (amount) => new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
    const update = () => {
        const subtotal = Number(form.dataset.subtotal);
        const discount = Math.floor(subtotal * Number(promotion.selectedOptions[0]?.dataset.percent || 0) / 100);
        form.querySelector('[data-discount]').textContent = format(discount);
        form.querySelector('[data-total]').textContent = format(subtotal - discount + Number(form.dataset.shipping));
    };
    promotion.addEventListener('change', update);
    update();
    form.addEventListener('submit', () => {
        const button = form.querySelector('[data-place-order]');
        button.disabled = true;
        button.textContent = 'Đang lưu đơn hàng…';
    });
});

document.querySelectorAll('[data-category-menu]').forEach((root) => {
    const trigger = root.querySelector('.header-category-trigger');
    const menu = root.querySelector('.header-category-list');
    const setOpen = (open) => {
        trigger.setAttribute('aria-expanded', String(open));
        menu.hidden = !open;
    };

    root.addEventListener('pointerenter', () => {
        if (window.matchMedia('(hover: hover)').matches) setOpen(true);
    });
    root.addEventListener('pointerleave', () => {
        if (!root.contains(document.activeElement)) setOpen(false);
    });
    trigger.addEventListener('click', () => setOpen(menu.hidden));
    root.addEventListener('focusout', (event) => {
        if (!root.contains(event.relatedTarget)) setOpen(false);
    });
    root.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            trigger.focus();
            setOpen(false);
        } else if (event.key === 'ArrowDown' && event.target === trigger) {
            event.preventDefault();
            setOpen(true);
            menu.querySelector('a')?.focus();
        }
    });
    document.addEventListener('click', (event) => {
        if (!root.contains(event.target)) setOpen(false);
    });
});

document.addEventListener('error', (event) => {
    const element = event.target;
    if (element instanceof HTMLImageElement && element.matches('[data-product-image]')) {
        element.hidden = true;
        if (element.nextElementSibling) element.nextElementSibling.hidden = false;
    }
}, true);

document.querySelectorAll('[data-product-image]').forEach((image) => {
    if (image.complete && image.naturalWidth === 0) {
        image.hidden = true;
        if (image.nextElementSibling) image.nextElementSibling.hidden = false;
    }
});
