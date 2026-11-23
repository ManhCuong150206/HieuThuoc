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

    // the panel opens after 150ms of hover intent, on wide screens only (the sheet below 1280 opens it by click)
    let intent;
    const wide = () => window.matchMedia('(hover: hover) and (min-width: 1280px)').matches;
    root.addEventListener('pointerenter', () => {
        if (wide()) intent = window.setTimeout(() => setOpen(true), 150);
    });
    root.addEventListener('pointerleave', () => {
        window.clearTimeout(intent);
        if (wide() && !root.contains(document.activeElement)) setOpen(false);
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

document.querySelectorAll('[data-site-header]').forEach((header) => {
    const toggle = header.querySelector('[data-nav-toggle]');
    const drawer = header.querySelector('#header-drawer');
    if (!toggle || !drawer) return;
    const setOpen = (open) => {
        header.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Đóng menu' : 'Mở menu');
        document.documentElement.classList.toggle('nav-locked', open);
    };
    setOpen(false);
    toggle.addEventListener('click', () => setOpen(!header.classList.contains('is-open')));
    drawer.addEventListener('click', (event) => {
        if (event.target.closest('a')) setOpen(false);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && header.classList.contains('is-open')) {
            setOpen(false);
            toggle.focus();
        }
    });
    window.matchMedia('(min-width: 1280px)').addEventListener('change', (query) => {
        if (query.matches) setOpen(false);
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
