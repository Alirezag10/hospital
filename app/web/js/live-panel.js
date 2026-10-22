(function () {
    'use strict';

    document.querySelectorAll('[data-live-panel]').forEach(function (panel) {
        const content = panel.querySelector('[data-live-content]');
        if (!content) return;

        const interval = 30000;
        let timer;
        let request = null;
        let failures = 0;
        let stopped = false;
        let suspended = false;
        let resumePending = false;

        function schedule(delay = Math.min(interval * (2 ** failures), 120000)) {
            window.clearTimeout(timer);
            if (!stopped && !suspended && !document.hidden && navigator.onLine) {
                timer = window.setTimeout(() => refresh(), delay);
            }
        }

        function showFailure() {
            panel.dataset.liveState = 'error';
        }

        function resume() {
            if (request) resumePending = true;
            else refresh();
        }

        async function refresh() {
            if (stopped || suspended || document.hidden || request) return;
            if (!navigator.onLine) {
                showFailure();
                return;
            }
            // Leave a keyboard user's focused case link in place until they move away.
            if (content.contains(document.activeElement)) {
                schedule();
                return;
            }

            window.clearTimeout(timer);
            request = new AbortController();
            const timeout = window.setTimeout(() => request?.abort(), 10000);
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
                    return;
                }
                if (!response.ok) throw new Error('Refresh failed');

                const data = await response.json();
                if (typeof data.html !== 'string') {
                    throw new Error('Invalid refresh response');
                }
                if (document.hidden || suspended || content.contains(document.activeElement)) return;

                // HTML is rendered by the same role-checked Yii view as the initial page.
                content.innerHTML = data.html;
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
                content.setAttribute('aria-busy', 'false');
                if (resumePending) {
                    resumePending = false;
                    schedule(0);
                } else {
                    schedule();
                }
            }
        }

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
