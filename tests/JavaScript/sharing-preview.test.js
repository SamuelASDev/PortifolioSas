import assert from 'node:assert/strict';
import { afterEach, beforeEach, test } from 'node:test';
import { JSDOM } from 'jsdom';
import { initializeSharingPreview } from '../../resources/js/sharing/preview.js';

let dom;
let originalCreate;
let originalRevoke;
let revoked;
beforeEach(() => {
    dom = new JSDOM('<!doctype html><html><body><section><input data-share-title><textarea data-share-description></textarea><input type="file" data-share-image><input type="checkbox" data-share-remove><img data-share-preview-image><p data-share-preview-title></p><p data-share-preview-description></p><script type="application/json" data-sharing-defaults></script></section></body></html>', { url: 'https://portfolio.test' });
    globalThis.window = dom.window; globalThis.document = window.document;
    document.querySelector('script').textContent = JSON.stringify({ fallback_title: 'Original', fallback_description: 'Resumo original', image: 'https://portfolio.test/custom.png', fallback_image: 'https://portfolio.test/cover.png' });
    originalCreate = URL.createObjectURL; originalRevoke = URL.revokeObjectURL; revoked = [];
    URL.createObjectURL = () => 'blob:preview'; URL.revokeObjectURL = (url) => revoked.push(url);
    initializeSharingPreview(document.querySelector('section'));
});
afterEach(() => { URL.createObjectURL = originalCreate; URL.revokeObjectURL = originalRevoke; dom.window.close(); });

test('text updates are escaped and clearing fields restores original metadata', () => {
    const input = document.querySelector('[data-share-title]');
    input.value = '<script>unsafe</script>'; input.dispatchEvent(new window.Event('input'));
    const title = document.querySelector('[data-share-preview-title]');
    assert.equal(title.textContent, '<script>unsafe</script>'); assert.equal(title.querySelector('script'), null);
    input.value = ''; input.dispatchEvent(new window.Event('input'));
    assert.equal(title.textContent, 'Original');
});

test('image selection previews a local blob and removal restores the fallback and revokes it', () => {
    const input = document.querySelector('[data-share-image]');
    Object.defineProperty(input, 'files', { value: [new window.File(['png'], 'image.png', { type: 'image/png' })] });
    input.dispatchEvent(new window.Event('change'));
    const image = document.querySelector('img');
    assert.equal(image.src, 'blob:preview');
    const remove = document.querySelector('[data-share-remove]'); remove.checked = true; remove.dispatchEvent(new window.Event('change'));
    assert.equal(image.src, 'https://portfolio.test/cover.png');
    assert.deepEqual(revoked, ['blob:preview']);
});

test('unsupported image formats are not inserted in the preview', () => {
    const input = document.querySelector('[data-share-image]');
    Object.defineProperty(input, 'files', { value: [new window.File(['<svg/>'], 'image.svg', { type: 'image/svg+xml' })] });
    input.dispatchEvent(new window.Event('change'));
    assert.equal(document.querySelector('img').src, 'https://portfolio.test/custom.png');
});
