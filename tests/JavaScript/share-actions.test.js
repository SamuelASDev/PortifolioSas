import assert from 'node:assert/strict';
import { afterEach, beforeEach, test } from 'node:test';
import { JSDOM } from 'jsdom';
import { initializeShareActions } from '../../resources/js/sharing/actions.js';

let dom;
const metadata = { title: 'Meu projeto', description: 'Resumo salvo', url: 'https://portfolio.test/dev/projects/42' };
beforeEach(() => {
    dom = new JSDOM('<!doctype html><html><body><div data-share-actions><script type="application/json" data-share-payload></script><button data-copy-link disabled>Copy</button><button data-share-link disabled>Share</button><label data-share-manual><input data-share-url readonly></label><p data-share-status></p></div></body></html>', { url: 'https://portfolio.test/admin/projects/42/edit' });
    globalThis.window = dom.window; globalThis.document = window.document;
    document.querySelector('script').textContent = JSON.stringify(metadata);
    document.querySelector('input').value = metadata.url;
    initializeShareActions(document.querySelector('[data-share-actions]'));
});
afterEach(() => dom.window.close());
const click = async (selector) => { document.querySelector(selector).click(); await new Promise(setImmediate); };
const status = () => document.querySelector('[data-share-status]').textContent;

test('copy uses the canonical public URL instead of the current admin page', async () => {
    let copied;
    window.navigator.clipboard = { writeText: async (value) => { copied = value; } };
    await click('[data-copy-link]');
    assert.equal(copied, metadata.url);
    assert.match(status(), /Link copiado/);
    assert.equal(document.querySelector('[data-share-manual]').hidden, true);
});

test('native share receives saved title, description and public URL', async () => {
    let payload;
    window.navigator.share = async (value) => { payload = value; };
    await click('[data-share-link]');
    assert.deepEqual(payload, { title: metadata.title, text: metadata.description, url: metadata.url });
    assert.match(status(), /menu de compartilhamento/);
});

test('native cancellation does not unexpectedly copy the link', async () => {
    let copied = false;
    window.navigator.share = async () => { throw new window.DOMException('Cancelled', 'AbortError'); };
    window.navigator.clipboard = { writeText: async () => { copied = true; } };
    await click('[data-share-link]');
    assert.equal(copied, false);
    assert.match(status(), /cancelado/);
    assert.equal(document.querySelector('[data-share-link]').disabled, false);
});

test('unsupported native sharing falls back to clipboard', async () => {
    let copied;
    window.navigator.clipboard = { writeText: async (value) => { copied = value; } };
    await click('[data-share-link]');
    assert.equal(copied, metadata.url);
});

test('unshareable payloads and denied native sharing fall back to copying', async () => {
    let copies = 0;
    window.navigator.share = async () => { throw new Error('Denied'); };
    window.navigator.canShare = () => false;
    window.navigator.clipboard = { writeText: async () => { copies++; } };
    await click('[data-share-link]');
    window.navigator.canShare = () => true;
    await click('[data-share-link]');
    assert.equal(copies, 2);
});

test('clipboard rejection reveals and selects a manual copy field', async () => {
    window.navigator.clipboard = { writeText: async () => { throw new Error('Denied'); } };
    await click('[data-copy-link]');
    const input = document.querySelector('input');
    assert.equal(document.querySelector('[data-share-manual]').hidden, false);
    assert.equal(document.activeElement, input);
    assert.equal(input.selectionStart, 0);
    assert.equal(input.selectionEnd, metadata.url.length);
    assert.match(status(), /link selecionado/);
});

test('missing clipboard support also offers manual copying', async () => {
    await click('[data-share-link]');
    assert.equal(document.querySelector('[data-share-manual]').hidden, false);
    assert.equal(document.querySelector('input').value, metadata.url);
});

test('initialization and rapid clicks cannot open multiple native dialogs', async () => {
    initializeShareActions(document.querySelector('[data-share-actions]'));
    let calls = 0;
    let complete;
    window.navigator.share = () => { calls++; return new Promise((resolve) => { complete = resolve; }); };
    document.querySelector('[data-share-link]').click();
    document.querySelector('[data-share-link]').click();
    assert.equal(calls, 1);
    assert.equal(document.querySelector('[data-copy-link]').disabled, true);
    complete(); await new Promise(setImmediate);
    assert.equal(document.querySelector('[data-copy-link]').disabled, false);
});
