import assert from 'node:assert/strict';
import { afterEach, beforeEach, test } from 'node:test';
import { JSDOM } from 'jsdom';
import { applySettings, contrast, normalizeSettings, presetSettings } from '../../resources/js/appearance/settings.js';
import { initializeAppearanceEditor } from '../../resources/js/appearance/editor.js';

const palette = { page: '#101216', ink: '#f3f4f6', title: '#f3f4f6', muted: '#b2b8c3', card: '#191c22', surface: '#191c22', header: '#101216', footer: '#101216', line: '#343943' };
const schema = {
    colors: Object.fromEntries(Object.keys(palette).map((key) => [key, key])),
    fonts: { system: { css: 'system-ui' }, serif: { css: 'Georgia, serif' } },
    palettes: { dark: palette, light: { ...palette, page: '#fafaf8', ink: '#18201d' } },
    presets: { light: { theme: 'light', primary_color: '#24634b', font: 'serif', colors: {} } },
};
const initial = { theme: 'dark', primary_color: '#6366f1', appearance: { font: 'system', base_size: 16, title_scale: 100, colors: {} } };

test('presets apply their typography and reset composition rather than inheriting the last theme', () => {
    const extended = { ...schema, options: {
        hero: { default: 'split', values: { split: 'Split', centered: 'Centered' } },
        grid: { default: 'three', values: { three: 'Three', list: 'List' } },
    } };
    const chosen = presetSettings({ theme: 'light', primary_color: '#24634b', font: 'serif', title_scale: 110, base_size: 18, appearance: { hero: 'centered' }, colors: {} }, extended);
    assert.equal(chosen.appearance.title_scale, 110);
    assert.equal(chosen.appearance.base_size, 18);
    assert.equal(chosen.appearance.hero, 'centered');
    assert.equal(chosen.appearance.grid, 'three');
});
let dom;
let originalFetch;

beforeEach(() => {
    dom = new JSDOM('<!doctype html><html><head><meta name="csrf-token" content="token"></head><body></body></html>', { url: 'https://portfolio.test/admin/portfolio' });
    globalThis.window = dom.window;
    globalThis.document = window.document;
    globalThis.ResizeObserver = class { observe() {} };
    originalFetch = globalThis.fetch;
});
afterEach(() => { globalThis.fetch = originalFetch; dom.window.close(); });

function mount() {
    document.body.innerHTML = `<button data-open-appearance disabled>Open</button><input id="theme" value="dark"><input id="primary_color" value="#6366f1">
        <dialog data-appearance-editor><script type="application/json" data-appearance-config></script><form action="/admin/portfolio/appearance">
        <select data-appearance-preset><option value=""></option><option value="light">Light</option></select><button type="button" data-appearance-reset>Reset</button>
        <button type="button" data-preset-choice="light" aria-pressed="false">Light sample</button><p data-preset-description></p>
        <select data-appearance-theme><option value="dark">Dark</option><option value="light">Light</option></select><input type="color" data-appearance-accent>
        <select data-appearance-field="font"><option value="system">System</option><option value="serif">Serif</option></select>
        <select data-appearance-field="base_size"><option value="16">16</option><option value="18">18</option></select>
        <select data-appearance-field="title_scale"><option value="100">100</option></select>
        ${Object.keys(palette).map((key) => `<input type="color" data-appearance-color="${key}">`).join('')}
        <p data-appearance-contrast hidden></p><div class="appearance-editor-body" data-pane="settings"></div>
        <button type="button" data-appearance-pane="settings"></button><button type="button" data-appearance-pane="preview"></button>
        <button type="button" data-appearance-device="desktop"></button><button type="button" data-appearance-device="mobile"></button>
        <button type="button" data-appearance-home></button><div data-appearance-canvas><iframe data-appearance-frame data-src="/admin/portfolio/preview"></iframe></div>
        <button type="button" data-appearance-close>Cancel</button><button data-appearance-save>Save</button><p data-appearance-status></p></form></dialog>`;
    const dialog = document.querySelector('dialog');
    dialog.querySelector('script').textContent = JSON.stringify({ schema, initial });
    dialog.showModal = () => { dialog.open = true; };
    dialog.close = () => { dialog.open = false; dialog.dispatchEvent(new window.Event('close')); };
    const canvas = dialog.querySelector('[data-appearance-canvas]');
    Object.defineProperties(canvas, { clientWidth: { value: 800 }, clientHeight: { value: 600 } });
    initializeAppearanceEditor(dialog);
    const click = (selector) => document.querySelector(selector).click();
    const change = (selector, value, event = 'change') => {
        const input = document.querySelector(selector); input.value = value; input.dispatchEvent(new window.Event(event));
    };
    click('[data-open-appearance]');
    return { dialog, click, change, frame: dialog.querySelector('iframe') };
}

test('settings reject CSS injection and apply safe typography and colors with matching contrast', () => {
    const safe = normalizeSettings({ theme: 'unknown', primary_color: 'red;display:none', appearance: { font: '__proto__', base_size: 999, colors: { page: 'url(x)', unknown: '#ffffff' } } }, schema);
    assert.deepEqual(safe, initial);
    applySettings(document, { ...initial, primary_color: '#ffffff', appearance: { ...initial.appearance, font: 'serif', base_size: 18, colors: { page: '#eeeeee' } } }, schema);
    assert.equal(document.body.style.getPropertyValue('--page'), '#eeeeee');
    assert.equal(document.body.style.getPropertyValue('--font'), 'Georgia, serif');
    assert.equal(document.body.style.getPropertyValue('--accent-ink'), '#000000');
    assert.equal(document.documentElement.style.fontSize, '18px');
    assert.equal(contrast('#ffffff', '#000000'), 21);
});

test('visual theme samples apply the preset and custom edits clear their selected state', () => {
    const { dialog, click, change } = mount();
    click('[data-preset-choice="light"]');
    assert.equal(dialog.querySelector('[data-appearance-preset]').value, 'light');
    assert.equal(dialog.querySelector('[data-preset-choice]').getAttribute('aria-pressed'), 'true');
    assert.equal(dialog.querySelector('[data-appearance-field="font"]').value, 'serif');
    change('[data-appearance-accent]', '#123456', 'input');
    assert.equal(dialog.querySelector('[data-appearance-preset]').value, '');
    assert.equal(dialog.querySelector('[data-preset-choice]').getAttribute('aria-pressed'), 'false');
    assert.match(dialog.querySelector('[data-preset-description]').textContent, /Personalizado/);
});

test('cancel discards unsaved changes, presets update controls and device sizes preserve real viewport', () => {
    const { dialog, click, change, frame } = mount();
    change('[data-appearance-preset]', 'light');
    assert.equal(document.querySelector('[data-appearance-field="font"]').value, 'serif');
    assert.equal(document.querySelector('[data-appearance-theme]').value, 'light');
    click('[data-appearance-device="mobile"]');
    assert.equal(frame.style.width, '390px');
    click('[data-appearance-pane="preview"]');
    assert.equal(dialog.querySelector('.appearance-editor-body').dataset.pane, 'preview');
    click('[data-appearance-close]');
    assert.equal(dialog.open, false);
    click('[data-open-appearance]');
    assert.equal(document.querySelector('[data-appearance-theme]').value, 'dark');
    assert.equal(document.querySelector('[data-appearance-field="font"]').value, 'system');
});

test('preview messages only respond to the embedded same-origin window and include live edits', () => {
    const { frame, change } = mount();
    const sent = [];
    frame.contentWindow.postMessage = (...args) => sent.push(args);
    window.dispatchEvent(new window.MessageEvent('message', { origin: 'https://evil.test', source: frame.contentWindow, data: { type: 'portfolio:preview-ready' } }));
    window.dispatchEvent(new window.MessageEvent('message', { origin: window.location.origin, source: window, data: { type: 'portfolio:preview-ready' } }));
    assert.equal(sent.length, 0);
    change('[data-appearance-color="page"]', '#222222', 'input');
    assert.equal(sent.at(-1)[0].settings.appearance.colors.page, '#222222');
    assert.equal(sent.at(-1)[1], window.location.origin);
    window.dispatchEvent(new window.MessageEvent('message', { origin: window.location.origin, source: frame.contentWindow, data: { type: 'portfolio:preview-ready' } }));
    assert.equal(sent.length, 2);
});

test('save posts only appearance with CSRF, updates parent hidden inputs and keeps saved changes on reopen', async () => {
    const { dialog, click, change } = mount();
    let request;
    globalThis.fetch = async (url, options) => { request = { url, ...options }; return { ok: true, json: async () => ({ message: 'Saved' }) }; };
    change('[data-appearance-preset]', 'light');
    dialog.querySelector('form').dispatchEvent(new window.Event('submit', { cancelable: true }));
    await new Promise(setImmediate);
    assert.equal(request.method, 'PATCH');
    assert.equal(request.headers['X-CSRF-TOKEN'], 'token');
    assert.equal(JSON.parse(request.body).theme, 'light');
    assert.equal(document.querySelector('#theme').value, 'light');
    assert.equal(document.querySelector('#primary_color').value, '#24634b');
    click('[data-appearance-close]'); click('[data-open-appearance]');
    assert.equal(document.querySelector('[data-appearance-theme]').value, 'light');
});

test('failed saves show an error, retain editable draft and leave saved state untouched', async () => {
    const { dialog, click, change } = mount();
    globalThis.fetch = async () => ({ ok: false, json: async () => ({ errors: { theme: ['Invalid theme'] } }) });
    change('[data-appearance-preset]', 'light');
    dialog.querySelector('form').dispatchEvent(new window.Event('submit', { cancelable: true }));
    await new Promise(setImmediate);
    assert.equal(dialog.querySelector('[data-appearance-status]').textContent, 'Invalid theme');
    assert.equal(dialog.querySelector('[data-appearance-save]').disabled, false);
    assert.equal(document.querySelector('#theme').value, 'dark');
    assert.equal(document.querySelector('[data-appearance-theme]').value, 'light');
    click('[data-appearance-close]'); click('[data-open-appearance]');
    assert.equal(document.querySelector('[data-appearance-theme]').value, 'dark');
});

test('layout normalization whitelists values and live preview applies every selected dataset', () => {
    const extended = { ...schema, options: {
        hero: { default: 'split', values: { split: 'Split', centered: 'Centered' } },
        grid: { default: 'three', values: { three: 'Three', list: 'List' } },
    } };
    const settings = applySettings(document, { ...initial, appearance: { ...initial.appearance, hero: 'centered', grid: 'list' } }, extended);
    assert.equal(settings.appearance.hero, 'centered');
    assert.equal(document.body.dataset.hero, 'centered');
    assert.equal(document.body.dataset.grid, 'list');
    applySettings(document, { ...initial, appearance: { ...initial.appearance, hero: 'url(evil)', grid: '__proto__' } }, extended);
    assert.equal(document.body.dataset.hero, 'split');
    assert.equal(document.body.dataset.grid, 'three');
});
