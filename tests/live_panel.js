'use strict';

const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../app/web/js/live-panel.js'), 'utf8');

function harness({ hidden = false, online = true } = {}) {
    let timerId = 0;
    const timers = new Map();
    const events = {};
    const requests = [];
    const content = {
        innerHTML: 'previous cases', attributes: {},
        contains(element) { return element === this.focusedLink; },
        setAttribute(name, value) { this.attributes[name] = value; },
    };
    const status = { textContent: 'last update', dataset: { liveUpdated: '۱۴۰۵/۰۷/۲۹ ۱۲:۰۰' } };
    const label = { textContent: 'به‌روزرسانی' };
    const button = {
        disabled: false, events: {},
        querySelector() { return label; },
        addEventListener(name, fn) { this.events[name] = fn; },
    };
    const login = { hidden: true };
    const nodes = { '[data-live-content]': content, '[data-live-status]': status, '[data-live-refresh]': button, '[data-live-login]': login };
    const panel = { dataset: { liveUrl: '/discharge/queue' }, querySelector: selector => nodes[selector] };
    const document = {
        hidden, activeElement: null,
        querySelectorAll: () => [panel],
        addEventListener: (name, fn) => { events[name] = fn; },
    };
    const navigator = { onLine: online };
    let responder = async () => ({ ok: true, status: 200, json: async () => ({ html: 'fresh cases', updatedAt: '۱۴۰۵/۰۷/۲۹ ۱۲:۰۱' }) });
    const window = {
        setTimeout(fn, delay) { timers.set(++timerId, { fn, delay }); return timerId; },
        clearTimeout(id) { timers.delete(id); },
        addEventListener(name, fn) { events[name] = fn; },
    };
    vm.runInNewContext(source, {
        window, document, navigator, AbortController,
        fetch(url, options) { requests.push({ url, options }); return responder(options); },
    });
    return {
        timers, events, requests, content, status, label, button, login, panel, document, navigator,
        respond(fn) { responder = fn; },
        async tick(delay) {
            const entry = [...timers.entries()].find(([, timer]) => timer.delay === delay);
            assert.ok(entry, 'expected a scheduled timer at ' + delay);
            timers.delete(entry[0]);
            entry[1].fn();
            await settle();
        },
    };
}

async function settle() {
    for (let i = 0; i < 12; i++) await Promise.resolve();
}

(async function () {
    const h = harness();
    assert.equal(h.requests.length, 0, 'initial HTML does not trigger a redundant fetch');
    await h.tick(30000);
    assert.equal(h.content.innerHTML, 'fresh cases');
    assert.match(h.status.textContent, /۱۲:۰۱/);
    assert.equal(h.requests[0].options.cache, 'no-store');
    assert.equal(h.requests[0].options.credentials, 'same-origin');
    assert.equal(h.content.attributes['aria-busy'], 'false');
    console.log('PASS automatic refresh replaces only panel content and updates the timestamp');

    const focused = harness();
    focused.document.activeElement = focused.content.focusedLink = {};
    await focused.tick(30000);
    assert.equal(focused.requests.length, 0);
    focused.document.activeElement = null;
    await focused.button.events.click();
    assert.equal(focused.requests.length, 1);
    console.log('PASS automatic refresh preserves a focused case link; manual refresh remains available');

    const paused = harness({ hidden: true });
    assert.equal(paused.timers.size, 0);
    paused.document.hidden = false;
    paused.events.visibilitychange();
    await settle();
    assert.equal(paused.requests.length, 1);
    paused.document.hidden = true;
    paused.events.visibilitychange();
    assert.equal(paused.timers.size, 0);
    console.log('PASS hidden pages stop polling and returning to the page refreshes it');

    const concurrent = harness();
    let resolveRequest;
    concurrent.respond(() => new Promise(resolve => { resolveRequest = resolve; }));
    const running = concurrent.button.events.click();
    await concurrent.button.events.click();
    assert.equal(concurrent.requests.length, 1);
    assert.equal(concurrent.button.disabled, true);
    resolveRequest({ ok: true, status: 200, json: async () => ({ html: 'done', updatedAt: 'now' }) });
    await running;
    assert.equal(concurrent.button.disabled, false);
    console.log('PASS refresh requests never overlap and loading state is cleared');

    const failure = harness();
    failure.respond(async () => ({ ok: false, status: 500 }));
    await failure.button.events.click();
    assert.equal(failure.content.innerHTML, 'previous cases');
    assert.equal(failure.panel.dataset.liveState, 'error');
    await failure.tick(60000);
    assert.ok([...failure.timers.values()].some(timer => timer.delay === 120000));
    failure.navigator.onLine = false;
    failure.events.offline();
    assert.equal(failure.timers.size, 0);
    failure.respond(async () => ({ ok: true, status: 200, json: async () => ({ html: 'recovered', updatedAt: 'later' }) }));
    failure.navigator.onLine = true;
    failure.events.online();
    await settle();
    assert.equal(failure.content.innerHTML, 'recovered');
    console.log('PASS server errors keep old data, retries back off, and reconnecting refreshes');

    const timeout = harness();
    timeout.respond(options => new Promise((resolve, reject) => {
        options.signal.addEventListener('abort', () => reject(new Error('aborted')));
    }));
    timeout.button.events.click();
    await timeout.tick(10000);
    assert.equal(timeout.requests[0].options.signal.aborted, true);
    assert.equal(timeout.panel.dataset.liveState, 'error');
    assert.equal(timeout.button.disabled, false);
    console.log('PASS slow requests time out and leave existing data usable');

    for (const response of [{ ok: false, status: 403 }, { ok: true, status: 200, redirected: true }]) {
        const auth = harness();
        auth.respond(async () => response);
        await auth.button.events.click();
        assert.equal(auth.timers.size, 0);
        assert.equal(auth.login.hidden, false);
        assert.equal(auth.panel.dataset.liveState, 'stopped');
        await auth.button.events.click();
        assert.equal(auth.requests.length, 1);
    }
    console.log('PASS denied or expired sessions stop polling and offer login');

    const restored = harness();
    restored.events.pagehide();
    assert.equal(restored.timers.size, 0);
    restored.events.pageshow({ persisted: true });
    await settle();
    assert.equal(restored.requests.length, 1);
    console.log('PASS restoring a cached page resumes refreshing');
}()).catch(error => { console.error(error); process.exitCode = 1; });
