import { formatMoney } from './money';

document.querySelectorAll('[data-cart-selection]').forEach((cart) => {
    const quantityLabel = cart.querySelector('[data-selected-quantity]');
    const totalLabel = cart.querySelector('[data-selected-total]');
    const button = cart.querySelector('#cart-checkout button');
    const hint = cart.querySelector('[data-selection-hint]');
    const fees = cart.querySelector('[data-selection-fees]');
    const format = new Intl.NumberFormat('vi-VN');
    const update = () => {
        let quantity = 0;
        let total = 0;
        let selectedDraft = false;
        for (const row of cart.querySelectorAll('[data-cart-row]')) {
            const item = row.querySelector('[data-cart-item]');
            const input = row.querySelector('.quantity-form input[name="quantity"]');
            const validDraft = input.value !== '' && Number.isInteger(input.valueAsNumber)
                && input.valueAsNumber >= Number(input.min) && input.valueAsNumber <= Number(input.max);
            const lineQuantity = validDraft ? input.valueAsNumber : Number(input.defaultValue);
            const lineTotal = lineQuantity * Number(item.dataset.unitPrice);
            row.querySelector('.cart-price').textContent = formatMoney(lineTotal);
            if (item.checked && !item.disabled) {
                quantity += lineQuantity;
                total += lineTotal;
                selectedDraft ||= input.value !== input.defaultValue;
            }
        }
        quantityLabel.textContent = format.format(quantity);
        totalLabel.textContent = formatMoney(total);
        button.disabled = quantity === 0 || selectedDraft || cart.querySelector('form[aria-busy="true"]') !== null;
        hint.hidden = quantity > 0;
        fees.hidden = quantity === 0;
    };
    cart.querySelectorAll('[data-cart-summary]').forEach((summary) => { summary.hidden = false; });
    cart.addEventListener('change', (event) => {
        if (event.target.matches('[data-cart-item]')) update();
    });
    cart.querySelectorAll('.quantity-form').forEach((form) => {
        const input = form.elements.quantity;
        let timer;
        input.addEventListener('input', () => {
            update();
            window.clearTimeout(timer);
            if (input.validity.valid && input.value !== input.defaultValue) {
                timer = window.setTimeout(() => {
                    if (form.isConnected && !input.readOnly) form.requestSubmit();
                }, 500);
            }
        });
        form.addEventListener('submit', () => window.clearTimeout(timer));
    });
    cart.addEventListener('cart:busy', update);
    cart.addEventListener('cart:updated', ({ detail }) => {
        if (detail.cart_item) {
            const { id, quantity, unit_price: unitPrice, regular_unit_price: regularPrice } = detail.cart_item;
            const row = cart.querySelector(`[data-cart-row="${id}"]`);
            if (row) {
                const checkbox = row.querySelector('[data-cart-item]');
                checkbox.disabled = false;
                checkbox.dataset.unitPrice = unitPrice;
                const input = row.querySelector('input[name="quantity"]');
                input.defaultValue = String(quantity);
                row.querySelector('[data-cart-line-quantity]').textContent = 'Số lượng: ' + quantity;
                row.querySelector('[data-cart-unit-price]').textContent = formatMoney(unitPrice);
                const originalPrice = row.querySelector('[data-cart-regular-price]');
                originalPrice.hidden = regularPrice <= unitPrice;
                originalPrice.textContent = formatMoney(regularPrice);
                row.querySelector('[data-cart-unavailable]')?.remove();
            }
        }
        if (detail.removed_item_id) {
            cart.querySelector(`[data-cart-row="${detail.removed_item_id}"]`)?.remove();
        }
        if (detail.cart_cleared) {
            cart.querySelectorAll('[data-cart-row]').forEach((row) => row.remove());
        }
        cart.querySelector('[data-cart-quantity]').textContent = detail.cart_quantity;
        if (!cart.querySelector('[data-cart-row]')) {
            cart.hidden = true;
            const empty = cart.parentElement.querySelector('[data-cart-empty]');
            empty.hidden = false;
            empty.querySelector('h2').focus();
        }
        update();
    });
    window.addEventListener('pageshow', update);
    update();
});
