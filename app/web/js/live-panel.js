(function () {
    'use strict';

    document.querySelectorAll('[data-live-panel]').forEach(function (panel) {
        const content = panel.querySelector('[data-live-content]');
        const status = panel.querySelector('[data-live-status]');
        const button = panel.querySelector('[data-live-refresh]');
        const label = button?.querySelector('[data-live-button-label]');
        const login = panel.querySelector('[data-live-login]');
        if (!content || !status || !button || !label) return;

        const interval = 30000;
        let timer;
        let request = null;
        let failures = 0;
        let stopped = false;
        let suspended = false;
        let resumePending = false;
        let lastUpdated = status.dataset.liveUpdated;

        function schedule(delay = Math.min(interval * (2 ** failures), 120000)) {
            window.clearTimeout(timer);
            if (!stopped && !suspended && !document.hidden && navigator.onLine) {
                timer = window.setTimeout(() => refresh(), delay);
            }
        }

        function showFailure() {
            panel.dataset.liveState = 'error';
            status.textContent = 'به‌روزرسانی انجام نشد؛ داده‌های قبلی نمایش داده می‌شوند. آخرین به‌روزرسانی: ' + lastUpdated;
        }

        function resume() {
            if (request) resumePending = true;
            else refresh();
        }

        async function refresh(manual = false) {
            if (stopped || suspended || document.hidden || request) return;
            if (!navigator.onLine) {
                showFailure();
                return;
            }
            // Leave a keyboard user's focused case link in place until they move away.
            if (!manual && content.contains(document.activeElement)) {
                schedule();
                return;
            }

            window.clearTimeout(timer);
            request = new AbortController();
            const timeout = window.setTimeout(() => request?.abort(), 10000);
            button.disabled = true;
            label.textContent = 'در حال به‌روزرسانی…';
            content.setAttribute('aria-busy', 'true');
            panel.dataset.liveState = 'loading';

            try {
                const response = await fetch(panel.dataset.liveUrl, {
                    method: 'GET',
                    credentials: 'same-origin',
                    cache: 'no-store',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: request.signal,
                });

                if (response.redirected || response.status === 401 || response.status === 403) {
                    stopped = true;
                    panel.dataset.liveState = 'stopped';
                    status.textContent = 'نشست شما پایان یافته یا دسترسی تغییر کرده است؛ دوباره وارد شوید.';
                    if (login) login.hidden = false;
                    return;
                }
                if (!response.ok) throw new Error('Refresh failed');

                const data = await response.json();
                if (typeof data.html !== 'string' || typeof data.updatedAt !== 'string') {
                    throw new Error('Invalid refresh response');
                }
                if (document.hidden || suspended || (!manual && content.contains(document.activeElement))) return;

                // HTML is rendered by the same role-checked Yii view as the initial page.
                content.innerHTML = data.html;
                lastUpdated = data.updatedAt;
                status.textContent = 'آخرین به‌روزرسانی: ' + lastUpdated;
                panel.dataset.liveState = 'ready';
                failures = 0;
            } catch (error) {
                if (!document.hidden && !suspended) {
                    failures++;
                    showFailure();
                }
            } finally {
                window.clearTimeout(timeout);
                request = null;
                button.disabled = stopped;
                label.textContent = 'به‌روزرسانی';
                content.setAttribute('aria-busy', 'false');
                if (resumePending) {
                    resumePending = false;
                    schedule(0);
                } else {
                    schedule();
                }
            }
        }

        button.addEventListener('click', () => refresh(true));
        document.addEventListener('visibilitychange', function () {
            window.clearTimeout(timer);
            if (document.hidden) {
                request?.abort();
            } else {
                resume();
            }
        });
        window.addEventListener('offline', function () {
            window.clearTimeout(timer);
            request?.abort();
            if (!stopped) showFailure();
        });
        window.addEventListener('online', function () {
            failures = 0;
            resume();
        });
        window.addEventListener('pagehide', function () {
            suspended = true;
            window.clearTimeout(timer);
            request?.abort();
        });
        window.addEventListener('pageshow', function (event) {
            suspended = false;
            if (event.persisted) resume();
        });
        schedule();
    });
}());
