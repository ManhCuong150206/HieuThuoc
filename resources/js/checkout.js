import { formatMoney } from './money';

document.querySelectorAll('[data-checkout]').forEach((form) => {
    const promotion = form.querySelector('[data-promotion]');
    const update = () => {
        const subtotal = Number(form.dataset.subtotal);
        const discount = Number(promotion?.selectedOptions[0]?.dataset.amount || 0);
        form.querySelector('[data-discount]').textContent = formatMoney(discount);
        form.querySelector('[data-total]').textContent = formatMoney(subtotal - discount + Number(form.dataset.shipping));
    };
    if (promotion) {
        promotion.addEventListener('change', update);
        update();
    }
    form.addEventListener('submit', () => {
        const button = form.querySelector('[data-place-order]');
        button.disabled = true;
        button.textContent = 'Đang lưu đơn hàng…';
    });
});
