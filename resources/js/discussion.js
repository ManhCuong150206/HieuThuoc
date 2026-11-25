const updateComments = (section, data) => {
    const results = section.querySelector('[data-discussion-results]');
    const incoming = document.createElement('template');
    incoming.innerHTML = data.html;

    // Keep open reply forms and requests in progress for comments still on this page.
    for (const entry of results.querySelectorAll('.discussion-entry')) {
        if (entry.querySelector('details[open], form[aria-busy="true"]')) {
            incoming.content.getElementById(entry.id)?.replaceWith(entry);
        }
    }

    results.replaceChildren(incoming.content);
    const count = section.querySelector('[data-discussion-count]');
    count.textContent = '(' + data.total + ')';
    count.hidden = false;
    window.history.replaceState(window.history.state, '', data.url);
};

const discussionError = (status, data) => {
    if (status === 422) return Object.values(data?.errors || {}).flat()[0];
    if (status === 401 || status === 419) return 'Phiên đăng nhập đã hết hạn. Vui lòng tải lại trang.';
    if (status === 403) return 'Bạn không có quyền thực hiện thao tác này.';
    if (status === 404) return 'Nội dung này không còn hiển thị.';
    if (status === 429) return 'Bạn gửi quá nhanh. Vui lòng thử lại sau.';
    return 'Chưa thể lưu nội dung. Vui lòng thử lại.';
};

document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (event.defaultPrevented || !(form instanceof HTMLFormElement) || !form.matches('[data-discussion-form]')) return;
    event.preventDefault();
    if (form.getAttribute('aria-busy') === 'true') return;

    const isReply = form.dataset.discussionForm === 'reply';
    const section = form.closest(isReply ? '.discussion-entry' : '.health-comments');
    const status = section.querySelector(isReply ? '[data-discussion-reply-status]' : '[data-discussion-comment-status]');
    const button = form.querySelector('button[type="submit"]');
    const input = form.querySelector('textarea');
    const error = form.querySelector('[data-discussion-error]');
    const body = new FormData(form);
    let label = button.textContent;

    error.hidden = true;
    status.hidden = true;
    input.removeAttribute('aria-invalid');
    input.readOnly = true;
    button.disabled = true;
    button.textContent = 'Đang gửi…';
    form.setAttribute('aria-busy', 'true');

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body,
            headers: { Accept: 'application/json' },
            redirect: 'error',
        });
        const data = response.headers.get('content-type')?.includes('application/json') ? await response.json() : null;
        if (!response.ok) {
            error.textContent = discussionError(response.status, data) || 'Vui lòng kiểm tra lại nội dung.';
            error.hidden = false;
            if (response.status === 422) input.setAttribute('aria-invalid', 'true');
            return;
        }

        if (isReply) {
            const reply = section.querySelector('[data-discussion-reply]');
            const composer = form.closest('details');
            const summary = composer.querySelector('summary');
            reply.querySelector('[data-discussion-reply-text]').textContent = data.reply;
            reply.hidden = false;
            input.defaultValue = data.reply;
            input.value = data.reply;
            summary.textContent = 'Sửa câu trả lời';
            composer.open = false;
            summary.focus({ preventScroll: true });
            label = 'Lưu câu trả lời';
        } else {
            updateComments(section, data);
            input.value = '';
            input.focus({ preventScroll: true });
        }
        status.textContent = data.message;
        status.hidden = false;
    } catch {
        error.textContent = 'Chưa nhận được phản hồi. Vui lòng kiểm tra kết quả trước khi gửi lại.';
        error.hidden = false;
    } finally {
        form.removeAttribute('aria-busy');
        input.readOnly = false;
        button.disabled = false;
        button.textContent = label;
    }
});
