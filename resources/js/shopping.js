import { showFeedback } from './feedback';

const feedback = document.querySelector('[data-ajax-feedback]');
let shoppingQueue = Promise.resolve();

document.querySelectorAll('[data-quantity-stepper]').forEach((stepper) => {
    const input = stepper.querySelector('input');
    const decrease = stepper.querySelector('[data-quantity-step="-1"]');
    const increase = stepper.querySelector('[data-quantity-step="1"]');
    const min = Number(input.min);
    const max = Number(input.max);

    const update = () => {
        decrease.setAttribute('aria-disabled', String(input.valueAsNumber <= min));
        increase.setAttribute('aria-disabled', String(input.valueAsNumber >= max));
    };

    const step = (direction) => {
        const value = input.valueAsNumber;
        input.value = Number.isFinite(value)
            ? Math.max(min, Math.min(max, Math.round(value) + direction))
            : min;
        input.dispatchEvent(new Event('input', { bubbles: true }));
    };

    decrease.addEventListener('click', () => {
        if (decrease.getAttribute('aria-disabled') === 'false') step(-1);
    });
    increase.addEventListener('click', () => {
        if (increase.getAttribute('aria-disabled') === 'false') step(1);
    });
    input.addEventListener('input', update);
    window.addEventListener('pageshow', update);
    update();
    decrease.hidden = false;
    increase.hidden = false;
    stepper.classList.add('is-enhanced');
});

const updateCartCount = (quantity) => {
    if (!Number.isSafeInteger(quantity) || quantity < 0) return;
    document.querySelectorAll('[data-cart-count]').forEach((badge) => {
        badge.textContent = String(quantity);
    });
    document.querySelectorAll('[data-cart-link]').forEach((link) => {
        link.setAttribute('aria-label', 'Giỏ hàng, ' + quantity + ' sản phẩm');
    });
};

const sendShoppingRequest = async (form, body, cart) => {
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body,
            credentials: 'same-origin',
            redirect: 'error',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (response.status === 401) {
            showFeedback('Vui lòng đăng nhập để tiếp tục.', {
                error: true, href: feedback?.dataset.loginUrl, linkText: 'Đăng nhập',
            });
            return;
        }
        if (response.status === 419) {
            showFeedback('Phiên làm việc đã hết hạn. Hãy tải lại trang rồi thử lại.', {
                error: true, href: window.location.pathname + window.location.search, linkText: 'Tải lại trang',
            });
            return;
        }
        if (!response.headers.get('content-type')?.includes('application/json')) {
            throw new Error('Unexpected response');
        }
        const data = await response.json();
        if (!response.ok) {
            const validation = response.status === 422
                ? Object.values(data.errors || {}).flat().filter((message) => typeof message === 'string').join(' ')
                : '';
            showFeedback(validation || (response.status === 404
                ? 'Sản phẩm không còn tồn tại hoặc đã ngừng bán.'
                : 'Chưa thể lưu thay đổi. Vui lòng thử lại sau.'), { error: true });
            return;
        }
        updateCartCount(data.cart_quantity);
        cart?.dispatchEvent(new CustomEvent('cart:updated', { detail: data }));
        showFeedback(data.message || 'Đã lưu thành công.');
    } catch {
        showFeedback('Chưa nhận được phản hồi. Hãy kiểm tra kết quả trước khi thử lại.', {
            error: true,
            href: feedback?.dataset.cartUrl,
            linkText: 'Kiểm tra giỏ hàng',
        });
    }
};

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (event.defaultPrevented || !(form instanceof HTMLFormElement) || !form.matches('[data-ajax-action="cart"]')) return;
    // Buy now keeps its normal navigation to checkout.
    if (event.submitter?.name === 'buy_now') return;
    event.preventDefault();
    if (form.dataset.busy === 'true') return;

    const cart = form.closest('[data-cart-selection]');
    const body = new FormData(form);
    const quantityInput = form.matches('.quantity-form') ? form.elements.quantity : null;
    const buttons = Array.from(form.querySelectorAll('button[type="submit"]'), (button) => ({
        button, disabled: button.disabled, text: button.textContent,
    }));
    form.dataset.busy = 'true';
    form.setAttribute('aria-busy', 'true');
    if (quantityInput) quantityInput.readOnly = true;
    cart?.dispatchEvent(new Event('cart:busy'));
    buttons.forEach(({ button }) => { button.disabled = true; });
    const submitter = event.submitter || buttons[0]?.button;
    if (submitter) submitter.textContent = 'Đang lưu…';

    // Serialize shopping writes so responses cannot overwrite the badge out of order.
    shoppingQueue = shoppingQueue.then(() => {
        if (form.isConnected && !cart?.hidden) return sendShoppingRequest(form, body, cart);
    }).finally(() => {
        delete form.dataset.busy;
        form.removeAttribute('aria-busy');
        if (quantityInput) {
            quantityInput.value = quantityInput.defaultValue;
            quantityInput.readOnly = false;
        }
        buttons.forEach(({ button, disabled, text }) => {
            button.disabled = disabled;
            button.textContent = text;
        });
        cart?.dispatchEvent(new Event('cart:busy'));
    });
});
