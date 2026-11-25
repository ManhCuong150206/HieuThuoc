const form = document.querySelector('[data-password-email]');

if (form) {
    const email = form.elements.email;
    const error = form.querySelector('#reset-email-error');
    const submit = form.querySelector('button[type="submit"]');
    const defaultLabel = submit.textContent;
    const showError = (message) => {
        error.hidden = !message;
        error.textContent = message;
        if (message) email.setAttribute('aria-invalid', 'true');
        else email.removeAttribute('aria-invalid');
    };

    email.addEventListener('input', () => showError(''));
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (submit.disabled) return;

        const body = new FormData(form);
        showError('');
        submit.disabled = true;
        email.readOnly = true;
        submit.textContent = 'Đang gửi mã…';
        try {
            const response = await fetch(form.action, {
                method: 'POST', body, headers: { Accept: 'application/json' },
            });
            const data = await response.json();
            if (response.ok && data.html && data.url) {
                const panel = form.closest('.auth-panel');
                history.replaceState(history.state, '', data.url);
                document.title = 'Đặt lại mật khẩu | An Tâm Pharmacy';
                panel.innerHTML = data.html;
                setupOtp(panel.querySelector('[data-otp-entry]'));
                panel.querySelector('#reset-otp').focus();
                return;
            }

            showError(data.errors?.email?.[0] || (response.status === 429
                ? 'Bạn đã gửi quá nhiều yêu cầu. Vui lòng thử lại sau.'
                : 'Chưa gửi được mã OTP. Vui lòng thử lại.'));
            email.focus();
        } catch {
            showError('Chưa gửi được mã OTP. Vui lòng thử lại.');
            email.focus();
        } finally {
            submit.disabled = false;
            email.readOnly = false;
            submit.textContent = defaultLabel;
        }
    });
}
function setupOtp(entry) {
    const input = entry.querySelector('input');
    const slots = [...entry.querySelectorAll('.otp-slots span')];
    const render = () => {
        const current = Math.min(input.selectionStart, slots.length - 1);
        slots.forEach((slot, index) => {
            slot.textContent = input.value[index] || '';
            slot.classList.toggle('is-current', index === current);
        });
    };

    input.addEventListener('input', () => {
        const digits = input.value.replace(/[^0-9]/g, '').slice(0, slots.length);
        if (input.value !== digits) input.value = digits;
        render();
    });
    input.addEventListener('selectionchange', render);
    input.addEventListener('focus', render);
    input.addEventListener('click', (event) => {
        if (event.detail !== 1 || input.selectionStart !== input.selectionEnd) return;
        const clicked = slots.findIndex((slot) => event.clientX < slot.getBoundingClientRect().right);
        const position = clicked < 0 ? input.value.length : Math.min(clicked, input.value.length);
        input.setSelectionRange(position, position);
        render();
    });
    input.addEventListener('paste', (event) => {
        const digits = event.clipboardData?.getData('text').replace(/[^0-9]/g, '');
        if (!digits) return;
        event.preventDefault();
        const start = digits.length >= slots.length ? 0 : input.selectionStart;
        const end = digits.length >= slots.length ? input.value.length : input.selectionEnd;
        input.setRangeText(digits.slice(0, slots.length - input.value.length + end - start), start, end, 'end');
        render();
    });
    window.addEventListener('pageshow', render);
    render();
    entry.classList.add('is-enhanced');
}

document.querySelectorAll('[data-otp-entry]').forEach(setupOtp);

