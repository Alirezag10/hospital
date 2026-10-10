(function () {
    'use strict';

    function enhance(input) {
        if (input.dataset.liveSearchReady) {
            return;
        }
        input.dataset.liveSearchReady = 'true';

        const selected = document.getElementById(input.dataset.targetInput);
        const wrapper = input.closest('.entity-search-wrapper');
        const results = wrapper && wrapper.querySelector('[role="listbox"]');
        const status = document.getElementById(input.id.replace('-search', '-search-status'));

        if (!selected || !results || !status || !wrapper || !input.dataset.searchUrl) return;

        const entity = input.labels[0].textContent.trim();
        const selectionMessage = 'یک ' + entity + ' را از فهرست نتایج انتخاب کنید.';
        const clearButton = document.createElement('button');
        clearButton.type = 'button';
        clearButton.className = 'entity-search-clear';
        clearButton.textContent = 'پاک‌کردن';
        clearButton.setAttribute('aria-label', 'پاک کردن انتخاب ' + entity);
        wrapper.insertBefore(clearButton, results);

        let timer;
        let request;

        function updateClearButton() {
            clearButton.hidden = !input.value;
            input.classList.toggle('has-clear-button', !!input.value);
        }

        function clearResults() {
            results.replaceChildren();
            results.hidden = true;
            input.setAttribute('aria-expanded', 'false');
        }

        function renderResults(items) {
            clearResults();

            if (items.length === 0) {
                status.textContent = entity + ' با این مشخصات پیدا نشد.';
                return;
            }

            items.forEach(function (item) {
                const option = document.createElement('button');
                option.type = 'button';
                option.className = 'dropdown-item text-wrap text-end';
                option.setAttribute('role', 'option');
                option.textContent = item.label;

                option.addEventListener('click', function () {
                    selected.value = item.id;
                    input.value = option.textContent;
                    if (input.closest('form').hasAttribute('data-unsaved-warning')) {
                        input.closest('form').dataset.dirty = '1';
                    }
                    const summary = input.closest('form').querySelector('[data-selected-admission-summary]');
                    if (summary && item.patientName) {
                        summary.querySelector('[data-summary-patient]').textContent = item.patientName;
                        summary.querySelector('[data-summary-code]').textContent = item.nationalCode || '';
                        summary.querySelector('[data-summary-doctor]').textContent = item.doctor || '';
                        summary.querySelector('[data-summary-ward]').textContent = item.ward || '';
                        summary.hidden = false;
                    }
                    input.setCustomValidity('');
                    updateClearButton();
                    status.textContent = entity + ' انتخاب شد.';
                    clearResults();
                    if (input.dataset.submitOnSelect === 'true') {
                        input.removeAttribute('name');
                        input.closest('form').requestSubmit();
                    } else {
                        input.focus();
                    }
                });

                results.append(option);
            });

            results.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            status.textContent = items.length === 10
                ? '۱۰ نتیجهٔ نخست نمایش داده شده؛ جست‌وجو را دقیق‌تر کنید.'
                : items.length + ' نتیجه پیدا شد.';
        }

        input.addEventListener('input', function () {
            selected.value = '';
            const summary = input.closest('form').querySelector('[data-selected-admission-summary]');
            if (summary) summary.hidden = true;
            clearResults();
            updateClearButton();
            input.setCustomValidity(input.value.trim() ? selectionMessage : '');
            status.textContent = '';
            window.clearTimeout(timer);
            if (request) {
                request.abort();
            }

            const term = input.value.trim();
            if (Array.from(term).length < 2) {
                if (term) {
                    status.textContent = 'برای جست‌وجو حداقل دو حرف یا رقم وارد کنید.';
                }
                return;
            }

            timer = window.setTimeout(async function () {
                request = new AbortController();
                status.textContent = 'در حال جست‌وجو…';

                try {
                    const url = new URL(input.dataset.searchUrl, window.location.href);
                    url.searchParams.set('q', term);
                    const response = await fetch(url, {
                        headers: {Accept: 'application/json'},
                        signal: request.signal,
                    });

                    if (!response.ok) {
                        throw new Error('Entity search failed');
                    }

                    renderResults(await response.json());
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        status.textContent = 'جست‌وجو انجام نشد؛ دوباره تلاش کنید.';
                    }
                }
            }, 250);
        });

        input.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                const options = results.querySelectorAll('button');
                const option = event.key === 'ArrowDown' ? options[0] : options[options.length - 1];
                if (!results.hidden && option) {
                    event.preventDefault();
                    option.focus();
                }
            } else if (event.key === 'Enter' && !results.hidden) {
                const firstResult = results.querySelector('button');
                if (firstResult) {
                    event.preventDefault();
                    firstResult.click();
                }
            } else if (event.key === 'Escape') {
                clearResults();
            }
        });

        results.addEventListener('keydown', function (event) {
            const options = Array.from(results.querySelectorAll('button'));
            const index = options.indexOf(document.activeElement);
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                const next = index + (event.key === 'ArrowDown' ? 1 : -1);
                (options[next] || input).focus();
            } else if (event.key === 'Escape') {
                event.preventDefault();
                clearResults();
                input.focus();
            }
        });

        clearButton.addEventListener('click', function () {
            window.clearTimeout(timer);
            if (request) request.abort();
            selected.value = '';
            const summary = input.closest('form').querySelector('[data-selected-admission-summary]');
            if (summary) summary.hidden = true;
            input.value = '';
            input.setCustomValidity('');
            status.textContent = '';
            clearResults();
            updateClearButton();
            if (input.dataset.submitOnSelect === 'true') {
                window.location.assign(input.closest('form').action);
            } else {
                input.focus();
            }
        });

        document.addEventListener('click', function (event) {
            if (!wrapper.contains(event.target)) {
                clearResults();
            }
        });

        const form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function (event) {
                if (!selected.value) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    input.setCustomValidity(selectionMessage);
                    input.reportValidity();
                }
            }, true);
        }
        updateClearButton();
    }

    function init() {
        document.querySelectorAll('[data-live-search="true"]').forEach(enhance);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
