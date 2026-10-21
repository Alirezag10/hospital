'use strict';

const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

function setup(count) {
    const handlers = {};
    let focused;
    const container = {
        children: [],
        append(fragment) { this.children.push(fragment.row); },
        get lastElementChild() { return this.children.at(-1); },
        querySelectorAll() { return this.children; },
        querySelector(selector) { return this.children[0]?.querySelector(selector); },
        addEventListener(name, handler) { handlers[name] = handler; },
    };

    function row(index, selected = false) {
        function input(value, readOnly, attribute) {
            return { value, readOnly, hasAttribute: name => name === attribute, focus() { focused = this; } };
        }
        const fields = {
            '[data-line-title]': input(selected ? 'داروی انتخاب‌شده' : '', selected),
            '[data-line-quantity]': input('1', false, 'data-line-quantity'),
            '[data-line-unit_price]': input(selected ? '5000' : '0', true, 'data-line-unit_price'),
            '[data-line-medical_item_id]': input(selected ? '12' : '', false),
            '[data-line-total]': { textContent: '' },
        };
        const kind = { value: 'medicine' };
        return {
            index, fields,
            dataset: selected ? { originalId: '12', originalPrice: '5000' } : {},
            catalogVisible: selected,
            changeButtonHidden: !selected,
            querySelector(selector) { return selector === 'select' ? kind : fields[selector]; },
            querySelectorAll() { return Object.values(fields).filter(field => 'value' in field); },
            remove() { container.children.splice(container.children.indexOf(this), 1); },
            replaceWith(fragment) { container.children.splice(container.children.indexOf(this), 1, fragment.row); },
        };
    }

    for (let index = 0; index < count; index++) container.children.push(row(index, true));
    let addRow;
    const form = {
        dataset: {},
        querySelector(selector) {
            if (selector === '[data-prescription-items]') return container;
            if (selector === '[data-add-prescription-item]') return { addEventListener(name, handler) { addRow = handler; } };
            return { textContent: '' };
        },
    };
    const document = {
        querySelector() { return form; },
        getElementById() { return { innerHTML: '<fieldset data-index="__index__"></fieldset>' }; },
        createElement() {
            return {
                set innerHTML(html) { this.content = { row: row(Number(html.match(/data-index="(\d+)"/)[1])) }; },
            };
        },
    };
    vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../app/web/js/clinical.js'), 'utf8'), { document, Intl });

    return {
        container, form,
        get focused() { return focused; },
        add() { addRow(); },
        remove(target) {
            handlers.click({ target: { closest() { return { closest() { return target; } }; } } });
        },
    };
}

const one = setup(1);
const original = one.container.children[0];
one.remove(original);
const fresh = one.container.children[0];
assert.equal(one.container.children.length, 1);
assert.notEqual(fresh, original);
assert.equal(fresh.fields['[data-line-title]'].value, '');
assert.equal(fresh.fields['[data-line-title]'].readOnly, false);
assert.equal(fresh.fields['[data-line-medical_item_id]'].value, '');
assert.equal(fresh.fields['[data-line-unit_price]'].value, '0');
assert.equal(fresh.fields['[data-line-unit_price]'].readOnly, true);
assert.equal(fresh.changeButtonHidden, true);
assert.equal(fresh.catalogVisible, false);
assert.deepEqual(fresh.dataset, {});
assert.equal(one.focused, fresh.fields['[data-line-title]']);
assert.equal(one.form.dataset.dirty, '1');
one.add();
assert.equal(new Set(one.container.children.map(item => item.index)).size, 2);
one.remove(one.container.children[1]);
one.remove(fresh);
assert.equal(one.container.children.length, 1);
assert.equal(one.container.children[0].fields['[data-line-title]'].readOnly, false);
console.log('PASS removing the last selected row resets selection, editable title, results and focus; new indexes stay unique');

const many = setup(2);
const kept = many.container.children[0];
many.remove(many.container.children[1]);
assert.equal(many.container.children.length, 1);
assert.equal(many.container.children[0], kept);
assert.equal(kept.fields['[data-line-medical_item_id]'].value, '12');
assert.equal(kept.fields['[data-line-title]'].readOnly, true);
console.log('PASS removing one of several rows preserves the remaining selected item');
