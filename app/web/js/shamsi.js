/* Local Persian calendar. Gregorian values are submitted to Yii/MySQL. */
(function () {
    'use strict';
    const DAY = 86400000;
    const months = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    const formatter = new Intl.DateTimeFormat('en-US-u-ca-persian-nu-latn', {
        timeZone: 'UTC', year: 'numeric', month: 'numeric', day: 'numeric'
    });
    const digits = v => String(v).replace(/[۰-۹]/g, c => '۰۱۲۳۴۵۶۷۸۹'.indexOf(c)).replace(/[٠-٩]/g, c => '٠١٢٣٤٥٦٧٨٩'.indexOf(c));
    const pad = n => String(n).padStart(2, '0');
    const persian = v => String(v).replace(/\d/g, c => '۰۱۲۳۴۵۶۷۸۹'[Number(c)]);
    function parts(date) {
        const p = {};
        for (const x of formatter.formatToParts(date)) p[x.type] = x.value;
        return [Number(p.year), Number(p.month), Number(p.day)];
    }
    function gregorian(jy, jm, jd) {
        if (!Number.isInteger(jy) || jy < 1200 || jy > 1600 || jm < 1 || jm > 12 || jd < 1 || jd > 31) return null;
        let lo = Date.UTC(jy + 621, 0, 1) / DAY;
        let hi = Date.UTC(jy + 622, 11, 31) / DAY;
        const target = jy * 10000 + jm * 100 + jd;
        while (lo <= hi) {
            const mid = Math.floor((lo + hi) / 2);
            const p = parts(new Date(mid * DAY));
            const key = p[0] * 10000 + p[1] * 100 + p[2];
            if (key === target) return new Date(mid * DAY);
            if (key < target) lo = mid + 1; else hi = mid - 1;
        }
        return null;
    }
    function display(value) {
        const m = String(value).match(/^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}:\d{2}(?::\d{2})?))?$/);
        if (!m || Number(m[1]) < 1821 || Number(m[1]) > 2222) return value;
        const d = new Date(Date.UTC(+m[1], +m[2] - 1, +m[3]));
        if (d.getUTCFullYear() !== +m[1] || d.getUTCMonth() + 1 !== +m[2] || d.getUTCDate() !== +m[3]) return value;
        const p = parts(d);
        return p[0] + '/' + pad(p[1]) + '/' + pad(p[2]) + (m[4] ? ' ' + m[4] : '');
    }
    function parse(value, time) {
        const v = digits(value).trim();
        if (!v) return '';
        const m = v.match(/^(\d{4})[\/-](\d{1,2})[\/-](\d{1,2})(?:\s+(\d{1,2}):(\d{2})(?::(\d{2}))?)?$/);
        if (!m || (!time && m[4])) return null;
        const d = gregorian(+m[1], +m[2], +m[3]);
        if (!d) return null;
        if (m[4] && (+m[4] > 23 || +m[5] > 59 || +(m[6] || 0) > 59)) return null;
        const date = d.getUTCFullYear() + '-' + pad(d.getUTCMonth() + 1) + '-' + pad(d.getUTCDate());
        return date + (time ? ' ' + pad(m[4] || 0) + ':' + pad(m[5] || 0) + ':' + pad(m[6] || 0) : '');
    }
    // Export conversion functions for regression tests and other local widgets.
    window.HospitalShamsi = {display, parse, parts, gregorian};
    const fields = new Set();
    let active = null;
    function sync(f, notify, showError = false) {
        const value = parse(f.visible.value, f.time);
        const error = value === null ? 'تاریخ شمسی معتبر وارد کنید؛ مانند ۱۴۰۵/۰۷/۰۸' + (f.time ? ' ۱۴:۳۰:۰۰' : '') : '';
        f.visible.setCustomValidity(showError ? error : '');
        f.visible.setAttribute('aria-invalid', showError && error ? 'true' : 'false');
        f.error.textContent = showError ? error : '';
        f.original.value = value === null ? '__invalid_date__' : value;
        if (notify && value !== null) f.original.dispatchEvent(new Event('change', {bubbles: true}));
        return value !== null;
    }
    function validate(form) {
        for (const f of fields) {
            if (f.original.isConnected && f.original.form === form && !sync(f, false, true)) {
                f.visible.reportValidity(); f.visible.focus(); return false;
            }
        }
        return true;
    }
    function button(text, action, className) {
        const b = document.createElement('button'); b.type = 'button'; b.textContent = text;
        b.className = className || 'btn btn-sm btn-outline-secondary'; b.addEventListener('click', action); return b;
    }
    function iconButton(label, path, action, className) {
        const b = button('', action, className);
        b.setAttribute('aria-label', label); b.title = label;
        b.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="' + path + '"/></svg>';
        return b;
    }
    function position(f) {
        const rect = f.visible.getBoundingClientRect();
        const pop = f.popup;
        const left = Math.max(12, Math.min(rect.right - pop.offsetWidth, window.innerWidth - pop.offsetWidth - 12));
        const below = rect.bottom + 6;
        const top = below + pop.offsetHeight <= window.innerHeight - 12 ? below : Math.max(12, rect.top - pop.offsetHeight - 6);
        pop.style.left = left + 'px'; pop.style.top = top + 'px';
    }
    function close() {
        if (!active) return;
        active.popup.hidden = true; active.toggle.setAttribute('aria-expanded', 'false'); active = null;
    }
    function render(f) {
        const pop = f.popup; pop.replaceChildren();
        const nav = document.createElement('div'); nav.className = 'shamsi-nav';
        function move(delta) {
            let y = f.year, m = f.month + delta;
            if (m < 1) {m = 12; y--;} if (m > 12) {m = 1; y++;}
            if (y < 1200 || y > 1600) return;
            f.year = y; f.month = m; render(f);
        }
        const month = document.createElement('select'); month.className = 'shamsi-month'; month.setAttribute('aria-label', 'ماه');
        months.forEach((name, i) => month.add(new Option(name, String(i + 1)))); month.value = f.month;
        const year = document.createElement('select'); year.className = 'shamsi-year'; year.setAttribute('aria-label', 'سال');
        for (let y = 1200; y <= 1600; y++) year.add(new Option(persian(y), String(y)));
        year.value = f.year;
        month.addEventListener('change', e => {e.stopPropagation();f.month = +month.value; render(f);pop.querySelector('.shamsi-month').focus();});
        year.addEventListener('change', e => {e.stopPropagation();f.year = +year.value; render(f);pop.querySelector('.shamsi-year').focus();});
        nav.append(month, year, iconButton('ماه قبل', 'M9 6l6 6-6 6', () => move(-1), 'shamsi-arrow'), iconButton('ماه بعد', 'M15 6l-6 6 6 6', () => move(1), 'shamsi-arrow')); pop.append(nav);
        const grid = document.createElement('div'); grid.className = 'shamsi-days';
        ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'].forEach(text => {const e = document.createElement('span');e.className = 'shamsi-weekday';e.textContent = text;grid.append(e);});
        const first = gregorian(f.year, f.month, 1);
        const offset = (first.getUTCDay() + 1) % 7;
        for (let i = 0; i < offset; i++) grid.append(document.createElement('span'));
        const selected = digits(f.visible.value).split(' ')[0];
        const now = new Date();
        const today = parts(new Date(Date.UTC(now.getFullYear(), now.getMonth(), now.getDate()))).join('/');
        for (let day = 1; day <= 31; day++) {
            const greg = gregorian(f.year, f.month, day);
            if (!greg) break;
            const date = f.year + '/' + pad(f.month) + '/' + pad(day);
            const b = button(persian(day), () => choose(f, date), 'shamsi-day');
            b.setAttribute('aria-label', persian(day) + ' ' + months[f.month - 1] + ' ' + persian(f.year));
            if (date === selected) {b.classList.add('selected'); b.setAttribute('aria-pressed', 'true');}
            if ([f.year, f.month, day].join('/') === today) {b.classList.add('today');b.setAttribute('aria-current', 'date');}
            const iso = greg.toISOString().slice(0, 10);
            b.disabled = (f.original.min && iso < f.original.min) || (f.original.max && iso > f.original.max);
            grid.append(b);
        }
        pop.append(grid);
        const actions = document.createElement('div'); actions.className = 'shamsi-actions';
        actions.append(button('امروز', () => {
            const now = new Date(); const p = parts(new Date(Date.UTC(now.getFullYear(), now.getMonth(), now.getDate())));
            choose(f, p[0] + '/' + pad(p[1]) + '/' + pad(p[2]));
        }, 'shamsi-action'), button('پاک کردن', () => {f.visible.value = '';sync(f, true);close();f.visible.focus();}, 'shamsi-action'));
        pop.append(actions);
    }
    function choose(f, date) {
        let clock = digits(f.visible.value).trim().split(/\s+/)[1];
        if (!clock) {const n = new Date();clock = pad(n.getHours()) + ':' + pad(n.getMinutes()) + ':00';}
        f.visible.value = date + (f.time ? ' ' + clock : '');
        sync(f, true);close();f.visible.focus();
    }
    function enhance(original) {
        if (original.dataset.shamsiReady || original.type === 'hidden') return;
        const match = original.name.match(/\[(birth_date|admission_date|discharge_date|created_at|dateFrom|dateTo)\]$/);
        if (!match) return;
        original.dataset.shamsiReady = '1';
        const time = !/Search\[/.test(original.name) && !['birth_date', 'dateFrom', 'dateTo'].includes(match[1]);
        const visible = original.cloneNode(false);
        visible.removeAttribute('name'); visible.removeAttribute('data-shamsi-ready'); visible.removeAttribute('min');visible.removeAttribute('max');
        visible.type = 'text'; visible.id = original.id + '-shamsi'; visible.dir = 'ltr';
        visible.autocomplete = 'off'; visible.placeholder = time ? '۱۴۰۵/۰۷/۰۸ ۱۴:۳۰:۰۰' : '۱۴۰۵/۰۷/۰۸';
        visible.value = display(original.value); visible.removeAttribute('value');
        const wrapper = document.createElement('div'); wrapper.className = 'shamsi-field';
        original.before(wrapper);wrapper.append(visible);
        original.type = 'hidden';
        const label = original.closest('.form-group, .mb-3')?.querySelector('label');
        if (label && label.htmlFor === original.id) label.htmlFor = visible.id;
        // Also handle GridView inputs and any custom ActiveField markup.
        document.querySelectorAll('label').forEach(l => {if (l.htmlFor === original.id) l.htmlFor = visible.id;});
        const toggle = iconButton('تقویم', 'M8 2v4m8-4v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zM7 14h2m4 0h2m-8 4h2', () => {
            if (active === f) {close();return;}
            close(); const parsed = parse(visible.value, time);
            const d = parsed ? new Date(parsed.slice(0, 10) + 'T00:00:00Z') : new Date();
            const p = parts(d); f.year = p[0];f.month = p[1];render(f);
            popup.hidden = false;active = f;toggle.setAttribute('aria-expanded', 'true');
            position(f);
            popup.querySelector('.selected:not(:disabled), .today:not(:disabled), .shamsi-day:not(:disabled)')?.focus({preventScroll:true});
        }, 'shamsi-toggle');
        toggle.setAttribute('aria-expanded', 'false');
        const popup = document.createElement('div');popup.className = 'shamsi-popup';popup.hidden = true;popup.dir = 'rtl';
        popup.id = visible.id + '-calendar';popup.setAttribute('role', 'dialog');popup.setAttribute('aria-label', 'تقویم شمسی');toggle.setAttribute('aria-controls', popup.id);
        wrapper.append(toggle, popup);
        const error = document.createElement('div');error.className = 'text-danger small';error.id = visible.id + '-error';error.setAttribute('aria-live', 'polite');wrapper.after(error);
        visible.setAttribute('aria-describedby', [visible.getAttribute('aria-describedby'), error.id].filter(Boolean).join(' '));
        const f = {original, visible, time, popup, toggle, error};fields.add(f);
        visible.addEventListener('input', () => sync(f, false));
        visible.addEventListener('change', e => {e.stopPropagation();sync(f, true);});
        visible.addEventListener('blur', () => sync(f, false));
        const form = original.form;
        if (form && !form.dataset.shamsiValidation) {
            form.dataset.shamsiValidation = '1';
            form.addEventListener('submit', e => {if (!validate(form)) {e.preventDefault();e.stopImmediatePropagation();}}, true);
            if (window.jQuery) window.jQuery(form).on('beforeValidate.shamsi beforeSubmit.shamsi', () => validate(form));
            form.addEventListener('reset', () => setTimeout(() => {
                for (const field of fields) if (field.original.form === form) {field.visible.value = display(field.original.value);sync(field, false);}
            }, 0));
        }
    }
    function init() {
        document.querySelectorAll('[data-shamsi-year]').forEach(node => {
            node.textContent = persian(display(node.dataset.shamsiYear).slice(0, 4));
        });
        const main = document.getElementById('main');if (!main) return;
        main.querySelectorAll('input[name]').forEach(enhance);
        const walk = document.createTreeWalker(main, NodeFilter.SHOW_TEXT, {acceptNode(node) {
            return node.parentElement.closest('script, style, input, textarea, .shamsi-popup') ? NodeFilter.FILTER_REJECT : NodeFilter.FILTER_ACCEPT;
        }});
        const nodes = [];while (walk.nextNode()) nodes.push(walk.currentNode);
        nodes.forEach(node => {node.nodeValue = node.nodeValue.replace(/\b\d{4}-\d{2}-\d{2}(?:[ T]\d{2}:\d{2}(?::\d{2})?)?\b/g, value => persian(display(value)));});
    }
    document.addEventListener('click', e => {if (active && !active.visible.parentElement.contains(e.target)) close();});
    document.addEventListener('keydown', e => {if (e.key === 'Escape' && active) {const f = active;close();f.visible.focus();}});
    window.addEventListener('resize', () => {if (active) position(active);});
    document.addEventListener('scroll', e => {if (active && !active.popup.contains(e.target)) position(active);}, true);
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);else init();
    if (window.jQuery) window.jQuery(document).on('pjax:end', init);
})();
