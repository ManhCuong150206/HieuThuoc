const currency = new Intl.NumberFormat('vi-VN');

export function formatMoney(amount) {
    return currency.format(amount) + 'đ';
}
