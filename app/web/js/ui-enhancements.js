(function () {
    'use strict';

    const persianDigits = new Intl.NumberFormat('fa-IR');

    document.addEventListener('click', function (event) {
        const printButton = event.target.closest('[data-print-trigger]');
        if (printButton) window.print();

    });

    document.querySelectorAll('[data-page-size]').forEach(function (select) {
        select.addEventListener('change', function () {
            const url = new URL(window.location.href);
            url.searchParams.set('per-page', select.value);
            url.searchParams.delete('page');
            window.location.assign(url.toString());
        });
    });

    const serviceSelect = document.querySelector('[data-service-price-select]');
    const quantityInput = document.querySelector('[data-service-quantity]');
    const serviceTotal = document.querySelector('[data-service-total]');
    function updateServiceTotal() {
        if (!serviceSelect || !quantityInput || !serviceTotal) return;
        if (!serviceSelect.value) {
            serviceTotal.textContent = 'ابتدا خدمت را انتخاب کنید';
            return;
        }
        const price = Number(serviceSelect.selectedOptions[0]?.dataset.price || 0);
        const quantity = Math.max(0, Number.parseInt(quantityInput.value, 10) || 0);
        serviceTotal.textContent = persianDigits.format(price * quantity) + ' تومان';
    }
    serviceSelect?.addEventListener('change', updateServiceTotal);
    quantityInput?.addEventListener('input', updateServiceTotal);
    updateServiceTotal();

    document.querySelectorAll('form[data-loading-form]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (form.dataset.submitting === '1') {
                event.preventDefault();
                return;
            }
            form.dataset.submitting = '1';
            const button = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
            if (!button) return;
            button.dataset.originalLabel = button.textContent;
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            button.textContent = button.dataset.loadingLabel || 'در حال ثبت…';
        });
    });

    const dirtyForms = new Set();
    document.querySelectorAll('form[data-unsaved-warning]').forEach(function (form) {
        const markDirty = event => {
            if (event.target.matches('[data-live-search]')) return;
            form.dataset.dirty = '1';
        };
        form.addEventListener('input', markDirty);
        form.addEventListener('change', markDirty);
        form.addEventListener('submit', () => { form.dataset.submitting = '1'; });
        dirtyForms.add(form);
    });
    function hasUnsavedChanges() {
        return Array.from(dirtyForms).some(form => form.isConnected && form.dataset.dirty === '1' && form.dataset.submitting !== '1');
    }
    window.addEventListener('beforeunload', function (event) {
        if (hasUnsavedChanges()) {
            event.preventDefault();
            event.returnValue = '';
        }
    });
    document.addEventListener('click', function (event) {
        const link = event.target.closest('a[href]');
        if (!link || link.target === '_blank' || link.hasAttribute('download') || link.hasAttribute('data-confirm') || !hasUnsavedChanges()) return;
        const destination = new URL(link.href, window.location.href);
        if (destination.origin !== window.location.origin || destination.href === window.location.href) return;
        event.preventDefault();
        const leave = function () {
            dirtyForms.forEach(form => { form.dataset.dirty = ''; form.dataset.submitting = '1'; });
            window.location.assign(destination.href);
        };
        if (typeof window.yii?.confirm === 'function') {
            window.yii.confirm('تغییراتی که وارد کرده‌اید ذخیره نشده است. از این صفحه خارج می‌شوید؟', leave);
        } else {
            leave();
        }
    }, true);

    const monthHeading = document.querySelector('[data-calendar-month]');
    if (monthHeading) {
        const date = new Date(monthHeading.dataset.calendarMonth + 'T00:00:00Z');
        monthHeading.textContent = new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
            year: 'numeric', month: 'long', timeZone: 'UTC'
        }).format(date);
        const dayFormatter = new Intl.DateTimeFormat('fa-IR-u-ca-persian', {day: 'numeric', timeZone: 'UTC'});
        document.querySelectorAll('[data-calendar-date]').forEach(function (day) {
            const persianDay = dayFormatter.format(new Date(day.dataset.calendarDate + 'T00:00:00Z'));
            day.textContent = persianDay;
            day.parentElement.setAttribute('aria-label', 'روز ' + persianDay);
        });
    }
})();
