const feedback = document.querySelector('[data-ajax-feedback]');
let feedbackTimer;

const hideFeedback = () => {
    window.clearTimeout(feedbackTimer);
    feedback.hidden = true;
};

const scheduleDismiss = () => {
    window.clearTimeout(feedbackTimer);
    if (feedback.dataset.state === 'success') {
        feedbackTimer = window.setTimeout(hideFeedback, 6000);
    }
};

export const showFeedback = (message, { error = false, href, linkText } = {}) => {
    if (!feedback) return;
    feedback.dataset.state = error ? 'error' : 'success';
    feedback.hidden = false;
    feedback.querySelector('[data-ajax-message]').textContent = message;
    const link = feedback.querySelector('[data-ajax-link]');
    link.hidden = !href;
    if (href) {
        link.href = href;
        link.textContent = linkText;
    }
    scheduleDismiss();
};

if (feedback) {
    feedback.querySelector('[data-ajax-dismiss]').addEventListener('click', hideFeedback);
    if (!feedback.hidden) scheduleDismiss();
}
