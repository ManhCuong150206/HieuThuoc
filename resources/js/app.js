import './bootstrap';

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
