document.querySelectorAll('[data-review-form]').forEach((form) => {
    const button = form.querySelector('button[type="submit"]');
    const error = form.querySelector('[data-review-error]');
    const status = form.parentElement.querySelector('[data-review-status]');
    const label = button.textContent;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (button.disabled) return;

        error.hidden = true;
        button.disabled = true;
        button.textContent = 'Đang gửi…';
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json' },
                redirect: 'error',
            });
            const data = await response.json();
            if (response.ok && typeof data.message === 'string') {
                status.textContent = data.message;
                status.hidden = false;
                form.remove();
                status.focus({ preventScroll: true });
                return;
            }

            const messages = Object.values(data.errors || {}).flat();
            error.textContent = messages[0] || (response.status === 401 || response.status === 419
                ? 'Phiên đăng nhập đã hết hạn. Vui lòng tải lại trang để tiếp tục.'
                : 'Chưa gửi được đánh giá. Vui lòng thử lại.');
            error.hidden = false;
        } catch {
            error.textContent = 'Chưa nhận được phản hồi. Vui lòng thử lại.';
            error.hidden = false;
        } finally {
            button.disabled = false;
            button.textContent = label;
        }
    });
});
