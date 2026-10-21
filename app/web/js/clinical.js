(function () {
    'use strict';
    const form = document.querySelector('[data-prescription-form]');
    if (!form) return;
    const container = form.querySelector('[data-prescription-items]');
    const template = document.getElementById('prescription-item-template');
    const format = new Intl.NumberFormat('fa-IR');
    let index = container.children.length;
    function digits(value) {
        return value.replace(/[۰-۹٠-٩]/g, digit => String('۰۱۲۳۴۵۶۷۸۹'.includes(digit)
            ? '۰۱۲۳۴۵۶۷۸۹'.indexOf(digit) : '٠١٢٣٤٥٦٧٨٩'.indexOf(digit))).trim();
    }
    function updateTotal() {
        let sum = 0n;
        let valid = true;
        container.querySelectorAll('[data-prescription-item]').forEach(row => {
            const quantity = digits(row.querySelector('[data-line-quantity]').value);
            const price = digits(row.querySelector('[data-line-unit_price]').value);
            const isValid = /^\d+$/.test(quantity) && /^\d+$/.test(price);
            const amount = isValid ? BigInt(quantity) * BigInt(price) : 0n;
            row.querySelector('[data-line-total]').textContent = isValid ? format.format(amount) + ' تومان' : '—';
            valid = valid && isValid;
            sum += amount;
        });
        form.querySelector('[data-prescription-total]').textContent = valid ? format.format(sum) + ' تومان' : '—';
    }
    form.querySelector('[data-add-prescription-item]').addEventListener('click', () => {
        if (container.children.length >= 50) return;
        const fragment = document.createElement('template');
        fragment.innerHTML = template.innerHTML.replaceAll('__index__', String(index++));
        container.append(fragment.content);
        container.lastElementChild.querySelector('[data-line-title]').focus();
        form.dataset.dirty = '1';
        updateTotal();
    });
    container.addEventListener('click', event => {
        const remove = event.target.closest('[data-remove-prescription-item]');
        if (!remove) return;
        const row = remove.closest('[data-prescription-item]');
        if (container.children.length > 1) { row.remove(); }
        else {
            ['originalId','originalPrice','originalTitle','originalKind'].forEach(key => { delete row.dataset[key]; });
            row.querySelectorAll('input').forEach(input => { input.value = input.hasAttribute('data-line-quantity') ? '1' : input.hasAttribute('data-line-unit_price') ? '0' : ''; });
            row.querySelector('select').value = 'medicine';
        }
        form.dataset.dirty = '1';
        updateTotal();
        container.querySelector('[data-line-title]')?.focus();
    });
    container.addEventListener('input', updateTotal);
    updateTotal();
})();
