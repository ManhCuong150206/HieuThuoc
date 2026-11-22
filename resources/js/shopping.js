const feedback = document.querySelector('[data-ajax-feedback]');
let feedbackTimer;
let shoppingQueue = Promise.resolve();

const showFeedback = (message, { error = false, href, linkText } = {}) => {
    if (!feedback) return;
    window.clearTimeout(feedbackTimer);
    feedback.dataset.state = error ? 'error' : 'success';
    feedback.hidden = false;
    feedback.querySelector('[data-ajax-message]').textContent = message;
    const link = feedback.querySelector('[data-ajax-link]');
    link.hidden = !href;
    if (href) {
        link.href = href;
        link.textContent = linkText;
    }
    if (!error) feedbackTimer = window.setTimeout(() => { feedback.hidden = true; }, 6000);
};

feedback?.querySelector('[data-ajax-dismiss]').addEventListener('click', () => {
    window.clearTimeout(feedbackTimer);
    feedback.hidden = true;
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

const markFavoriteSaved = (productId) => {
    document.querySelectorAll('form[data-ajax-action="favorite"]').forEach((form) => {
        if (form.dataset.productId !== String(productId)) return;
        form.dataset.saved = 'true';
        form.querySelectorAll('button[type="submit"]').forEach((button) => {
            button.textContent = 'Đã lưu yêu thích';
            button.disabled = true;
        });
    });
};

const sendShoppingRequest = async (form, body) => {
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
        if (form.dataset.ajaxAction === 'cart') updateCartCount(data.cart_quantity);
        if (form.dataset.ajaxAction === 'favorite' && data.saved === true) markFavoriteSaved(data.product_id);
        showFeedback(data.message || 'Đã lưu thành công.');
    } catch {
        const isCart = form.dataset.ajaxAction === 'cart';
        showFeedback('Chưa nhận được phản hồi. Hãy kiểm tra kết quả trước khi thử lại.', {
            error: true,
            href: isCart ? feedback?.dataset.cartUrl : feedback?.dataset.favoritesUrl,
            linkText: isCart ? 'Kiểm tra giỏ hàng' : 'Kiểm tra yêu thích',
        });
    }
};

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.matches('[data-ajax-action]')) return;
    // Buy now keeps its normal navigation to checkout.
    if (event.submitter?.name === 'buy_now') return;
    event.preventDefault();
    if (form.dataset.busy === 'true' || form.dataset.saved === 'true') return;

    const body = new FormData(form);
    const buttons = Array.from(form.querySelectorAll('button[type="submit"]'), (button) => ({
        button, disabled: button.disabled, text: button.textContent,
    }));
    form.dataset.busy = 'true';
    form.setAttribute('aria-busy', 'true');
    buttons.forEach(({ button }) => { button.disabled = true; });
    const submitter = event.submitter || buttons[0]?.button;
    if (submitter) submitter.textContent = 'Đang lưu…';

    // Serialize shopping writes so responses cannot overwrite the badge out of order.
    shoppingQueue = shoppingQueue.then(() => sendShoppingRequest(form, body)).finally(() => {
        delete form.dataset.busy;
        form.removeAttribute('aria-busy');
        buttons.forEach(({ button, disabled, text }) => {
            button.disabled = form.dataset.saved === 'true' || disabled;
            button.textContent = form.dataset.saved === 'true' ? 'Đã lưu yêu thích' : text;
        });
    });
});
