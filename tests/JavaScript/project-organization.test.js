import assert from 'node:assert/strict';
import { afterEach, beforeEach, test } from 'node:test';
import { JSDOM } from 'jsdom';
import { initializeProjectOrganization } from '../../resources/js/projects/organization.js';

let dom;
let originalFetch;
beforeEach(() => {
    dom = new JSDOM('<!doctype html><html><head><meta name="csrf-token" content="token"></head><body></body></html>', { url: 'https://portfolio.test/admin/projects/organize' });
    globalThis.window = dom.window; globalThis.document = window.document;
    originalFetch = globalThis.fetch;
});
afterEach(() => { globalThis.fetch = originalFetch; dom.window.close(); });

function mount() {
    document.body.innerHTML = `<form data-revision="original" action="/admin/projects/organize"><ol data-project-list>${[1, 2, 3].map((id) => `<li data-project-row data-project-id="${id}"><button type="button" data-project-handle>Drag</button><span data-project-position></span><input type="checkbox" data-project-featured><button type="button" data-project-up>Up</button><button type="button" data-project-down>Down</button></li>`).join('')}</ol><p data-organization-status></p><button data-organization-save disabled>Save</button></form>`;
    const form = document.querySelector('form'); initializeProjectOrganization(form);
    return { form, ids: () => [...document.querySelectorAll('[data-project-row]')].map((row) => Number(row.dataset.projectId)) };
}
const pointer = (type, y = 0, id = 1) => {
    const event = new window.Event(type, { bubbles: true, cancelable: true });
    Object.assign(event, { pointerId: id, button: 0, clientX: 50, clientY: y });
    return event;
};

test('buttons and keyboard change the complete order and preserve a reachable focus target', () => {
    const { ids } = mount();
    const first = document.querySelector('[data-project-row]');
    assert.equal(first.querySelector('[data-project-up]').disabled, true);
    first.querySelector('[data-project-down]').click();
    assert.deepEqual(ids(), [2, 1, 3]);
    assert.equal(document.activeElement, first.querySelector('[data-project-handle]'));
    first.querySelector('[data-project-handle]').dispatchEvent(new window.KeyboardEvent('keydown', { key: 'ArrowDown', cancelable: true }));
    assert.deepEqual(ids(), [2, 3, 1]);
    assert.equal(first.querySelector('[data-project-position]').textContent, '3');
    assert.equal(first.querySelector('[data-project-down]').disabled, true);
});

test('pointer dragging reorders from the handle and cancellation ends the gesture', () => {
    const { ids } = mount();
    const first = document.querySelector('[data-project-row]');
    const last = document.querySelector('[data-project-id="3"]');
    last.getBoundingClientRect = () => ({ top: 100, height: 100 });
    document.elementFromPoint = () => last;
    first.querySelector('[data-project-handle]').dispatchEvent(pointer('pointerdown'));
    document.dispatchEvent(pointer('pointermove', 190, 2));
    assert.deepEqual(ids(), [1, 2, 3]);
    document.dispatchEvent(pointer('pointermove', 190));
    assert.deepEqual(ids(), [2, 3, 1]);
    assert.equal(first.classList.contains('is-dragging'), true);
    document.dispatchEvent(pointer('pointercancel'));
    assert.equal(first.classList.contains('is-dragging'), false);
    document.dispatchEvent(pointer('pointermove', 100));
    assert.deepEqual(ids(), [2, 3, 1]);
});

test('save sends the displayed order and highlights, then uses the returned revision', async () => {
    const { form } = mount();
    const requests = [];
    globalThis.fetch = async (url, options) => { requests.push(options); return { ok: true, json: async () => ({ message: 'Saved', revision: 'new-revision' }) }; };
    document.querySelector('[data-project-down]').click();
    document.querySelector('[data-project-id="2"] [data-project-featured]').checked = true;
    form.dispatchEvent(new window.Event('submit', { cancelable: true }));
    await new Promise(setImmediate);
    assert.equal(requests[0].headers['X-CSRF-TOKEN'], 'token');
    assert.deepEqual(JSON.parse(requests[0].body), { revision: 'original', projects: [{ id: 2, is_featured: true }, { id: 1, is_featured: false }, { id: 3, is_featured: false }] });
    form.dispatchEvent(new window.Event('submit', { cancelable: true }));
    await new Promise(setImmediate);
    assert.equal(JSON.parse(requests[1].body).revision, 'new-revision');
    assert.equal(document.querySelector('[data-organization-status]').textContent, 'Saved');
});

test('conflicts preserve the edited order and display a reload message without claiming success', async () => {
    const { form, ids } = mount();
    globalThis.fetch = async () => ({ ok: false, json: async () => ({ message: 'Recarregue a página.' }) });
    document.querySelector('[data-project-down]').click();
    form.dispatchEvent(new window.Event('submit', { cancelable: true }));
    await new Promise(setImmediate);
    assert.deepEqual(ids(), [2, 1, 3]);
    assert.equal(document.querySelector('[data-organization-status]').textContent, 'Recarregue a página.');
    assert.equal(document.querySelector('[data-organization-save]').disabled, false);
});
