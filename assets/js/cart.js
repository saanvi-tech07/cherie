/**
 * CHÉRIE — Cart Management Script
 */

document.addEventListener('DOMContentLoaded', function () {
    // Quantity increment/decrement buttons
    const qtyWrappers = document.querySelectorAll('.qty-control');
    qtyWrappers.forEach(wrap => {
        const minusBtn = wrap.querySelector('.qty-minus');
        const plusBtn = wrap.querySelector('.qty-plus');
        const input = wrap.querySelector('.qty-input');
        const form = wrap.closest('form');

        if (minusBtn && input) {
            minusBtn.addEventListener('click', function () {
                let current = parseInt(input.value) || 1;
                if (current > 1) {
                    input.value = current - 1;
                    if (form && form.classList.contains('auto-submit')) {
                        form.submit();
                    }
                }
            });
        }

        if (plusBtn && input) {
            plusBtn.addEventListener('click', function () {
                let current = parseInt(input.value) || 1;
                const max = parseInt(input.getAttribute('max')) || 99;
                if (current < max) {
                    input.value = current + 1;
                    if (form && form.classList.contains('auto-submit')) {
                        form.submit();
                    }
                } else {
                    window.showToast?.('Maximum stock reached for this item', 'error');
                }
            });
        }
    });
});
