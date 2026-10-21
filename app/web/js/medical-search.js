(function () {
    'use strict';
    const form = document.querySelector('[data-prescription-form]');
    if (!form) return;
    let timer, request;
    const format = new Intl.NumberFormat('fa-IR');
    function close(row) {
        const list = row.querySelector('.catalog-results');
        list.hidden = true; list.replaceChildren();
        row.querySelector('[data-catalog-search]').setAttribute('aria-expanded', 'false');
    }
    function clear(row) {
        if (request) request.abort(); clearTimeout(timer);
        row.querySelector('[data-line-medical_item_id]').value = '';
        const input = row.querySelector('[data-catalog-search]');
        input.value = ''; input.readOnly = false;
        row.querySelector('[data-line-unit_price]').value = '0';
        row.querySelector('[data-catalog-clear]').hidden = true;
        row.querySelector('.catalog-status').textContent = '';
        close(row); input.dispatchEvent(new Event('input', {bubbles: true})); input.focus();
    }
    form.addEventListener('input', event => {
        const input = event.target.closest('[data-catalog-search]');
        if (!input || input.readOnly) return;
        const row = input.closest('[data-prescription-item]');
        close(row); clearTimeout(timer); if (request) request.abort();
        const term = input.value.trim();
        const status = row.querySelector('.catalog-status');
        status.textContent = term.length < 2 ? '' : 'در حال جست‌وجو…';
        if (term.length < 2) return;
        timer = setTimeout(async () => {
            request = new AbortController();
            const url = new URL(row.querySelector('[data-catalog-url]').dataset.catalogUrl, location.origin);
            url.searchParams.set('q', term); url.searchParams.set('kind', row.querySelector('select').value);
            try {
                const response = await fetch(url, {signal: request.signal, headers: {'Accept': 'application/json'}});
                if (!response.ok) throw new Error('search');
                const items = await response.json();
                if (!Array.isArray(items)) throw new Error('search');
                const list = row.querySelector('.catalog-results');
                close(row);
                for (const item of items) {
                    const option = document.createElement('button'); option.type = 'button';
                    option.className = 'dropdown-item text-end'; option.setAttribute('role', 'option');
                    const title = document.createElement('strong'); title.textContent = item.title;
                    const price = document.createElement('small'); price.textContent = format.format(item.price) + ' تومان';
                    option.append(title, price);
                    option.addEventListener('click', () => {
                        const original = String(item.id) === row.dataset.originalId && item.kind === row.dataset.originalKind;
                        const selectedPrice = original ? row.dataset.originalPrice : item.price;
                        row.querySelector('[data-line-medical_item_id]').value = item.id;
                        row.querySelector('[data-line-unit_price]').value = selectedPrice;
                        input.value = original ? row.dataset.originalTitle : item.title; input.readOnly = true;
                        row.querySelector('[data-catalog-clear]').hidden = false;
                        status.textContent = original ? 'قلم انتخاب شد؛ قیمت ثبت‌شدهٔ این ردیف حفظ می‌شود.' : 'قلم انتخاب شد.'; close(row);
                        form.dataset.dirty = '1'; input.dispatchEvent(new Event('input', {bubbles: true}));
                        row.querySelector('[data-line-quantity]').focus();
                    });
                    list.append(option);
                }
                list.hidden = items.length === 0; list.classList.add('show');
                input.setAttribute('aria-expanded', String(items.length > 0));
                status.textContent = items.length ? 'یک قلم را از نتایج انتخاب کنید.' : 'قلمی پیدا نشد؛ مدیر می‌تواند آن را به فهرست اضافه کند.';
            } catch (error) { if (error.name !== 'AbortError') status.textContent = 'جست‌وجو انجام نشد؛ دوباره تلاش کنید.'; }
        }, 250);
    });
    form.addEventListener('change', event => {
        if (event.target.matches('[data-prescription-item] select')) clear(event.target.closest('[data-prescription-item]'));
    });
    form.addEventListener('click', event => {
        const button = event.target.closest('[data-catalog-clear]');
        if (button) clear(button.closest('[data-prescription-item]'));
    });
    form.addEventListener('keydown', event => {
        const row = event.target.closest('[data-prescription-item]'); if (!row) return;
        const list = row.querySelector('.catalog-results');
        if (event.key === 'Escape') { close(row); return; }
        if (list.hidden || !['ArrowDown', 'ArrowUp'].includes(event.key)) return;
        const options = [...list.querySelectorAll('button')]; if (!options.length) return;
        event.preventDefault();
        const current = options.indexOf(document.activeElement);
        options[(current + (event.key === 'ArrowDown' ? 1 : -1) + options.length) % options.length].focus();
    });
    document.addEventListener('click', event => {
        form.querySelectorAll('[data-prescription-item]').forEach(row => { if (!row.contains(event.target)) close(row); });
    });
})();
