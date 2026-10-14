import assert from 'node:assert/strict';
import { afterEach, beforeEach, test } from 'node:test';
import { JSDOM } from 'jsdom';
import { initializeDescriptionEditor, isHttpUrl } from '../../resources/js/editors/project-description.js';

const dom = new JSDOM('<!doctype html><html><body></body></html>', {
    url: 'http://portfolio.test', pretendToBeVisual: true,
});
for (const key of ['window', 'document', 'navigator', 'Node', 'HTMLElement', 'Element', 'DOMParser', 'MutationObserver', 'getComputedStyle']) {
    Object.defineProperty(globalThis, key, {
        value: typeof dom.window[key] === 'function' && key === 'getComputedStyle' ? dom.window[key].bind(dom.window) : dom.window[key],
        configurable: true, writable: true,
    });
}
globalThis.requestAnimationFrame = dom.window.requestAnimationFrame.bind(dom.window);
globalThis.cancelAnimationFrame = dom.window.cancelAnimationFrame.bind(dom.window);
globalThis.innerHeight = 768;
globalThis.innerWidth = 1024;
// JSDOM não calcula geometria visual; os testes verificam documento e eventos.
dom.window.Range.prototype.getClientRects = () => [];
dom.window.Range.prototype.getBoundingClientRect = () => ({ top: 0, left: 0, bottom: 0, right: 0, width: 0, height: 0 });
const editors = [];

beforeEach(() => { document.body.innerHTML = ''; });
afterEach(() => { editors.splice(0).forEach((editor) => editor.destroy()); });

function mount(value, format = 'text') {
    document.body.innerHTML = `<form><div data-description-editor>
        <label id="description-label" for="description">Descrição</label>
        <input data-description-format name="description_format" type="hidden">
        <div data-editor-ui hidden>
            <div data-editor-toolbar>
                <button type="button" data-editor-command="bold">Negrito</button>
                <button type="button" data-editor-command="h2">Título</button>
                <button type="button" data-editor-command="link">Link</button>
                <button type="button" data-editor-command="unlink">Remover link</button>
                <button type="button" data-editor-command="undo">Desfazer</button>
                <button type="button" data-editor-command="redo">Refazer</button>
            </div>
            <div data-link-panel hidden><input data-link-input><p data-link-error hidden></p>
                <button type="button" data-link-apply>Aplicar</button><button type="button" data-link-cancel>Cancelar</button>
            </div>
            <div data-editor-content></div>
        </div>
        <textarea id="description" data-description-source name="description" maxlength="50000"></textarea>
        <p data-editor-status hidden></p>
    </div></form>`;
    const root = document.querySelector('[data-description-editor]');
    const source = root.querySelector('textarea');
    source.value = value;
    source.dataset.initialFormat = format;
    assert.equal(source.hidden, false, 'o campo de texto funciona antes do JavaScript');
    const editor = initializeDescriptionEditor(root);
    editors.push(editor);
    return { root, source, editor, form: document.querySelector('form') };
}

const tick = () => new Promise((resolve) => setTimeout(resolve, 30));

test('legacy text with literal HTML and blank lines is imported as text nodes', () => {
    const { editor, source, root } = mount('Exemplo <strong>literal</strong>\n\nSegunda linha & texto.');
    const blocks = editor.getJSON().content;
    assert.equal(blocks[0].content[0].text, 'Exemplo <strong>literal</strong>');
    assert.equal(blocks[1].content, undefined);
    assert.equal(blocks[2].content[0].text, 'Segunda linha & texto.');
    assert.match(source.value, /&lt;strong&gt;literal&lt;\/strong&gt;/);
    assert.equal(source.hidden, true);
    assert.equal(root.querySelector('[data-editor-ui]').hidden, false);
    assert.equal(root.querySelector('[data-description-format]').value, 'html');
    assert.equal(initializeDescriptionEditor(root), null, 'não duplica o editor');
});

test('rich text loads with marks and updates the submitted form content', () => {
    const { editor, source, form } = mount('<h2>Problema</h2><p>Uma <strong>solução</strong>.</p>', 'html');
    assert.equal(editor.getJSON().content[0].type, 'heading');
    assert.match(source.value, /<strong>solução<\/strong>/);
    editor.commands.setContent('<blockquote><p>Resultado</p></blockquote>');
    const submit = new dom.window.Event('submit', { bubbles: true, cancelable: true });
    form.dispatchEvent(submit);
    assert.equal(submit.defaultPrevented, false);
    assert.match(source.value, /<blockquote><p>Resultado<\/p><\/blockquote>/);
    assert.equal(new dom.window.FormData(form).get('description'), source.value);
});

test('toolbar applies bold and reflects the selection state', async () => {
    const { editor, root, source } = mount('Resultado');
    editor.commands.setTextSelection({ from: 1, to: 10 });
    root.querySelector('[data-editor-command="bold"]').click();
    await tick();
    assert.match(source.value, /<strong>Resultado<\/strong>/);
    assert.equal(root.querySelector('[data-editor-command="bold"]').getAttribute('aria-pressed'), 'true');
});

test('link panel rejects unsafe URLs and applies an absolute HTTPS link', async () => {
    const { editor, root, source } = mount('Referência');
    editor.commands.setTextSelection({ from: 1, to: 11 });
    root.querySelector('[data-editor-command="link"]').click();
    const input = root.querySelector('[data-link-input]');
    input.value = 'javascript:alert(1)';
    root.querySelector('[data-link-apply]').click();
    assert.equal(root.querySelector('[data-link-error]').hidden, false);
    assert.doesNotMatch(source.value, /href=/);
    input.value = 'https://example.com/docs';
    root.querySelector('[data-link-apply]').click();
    await tick();
    assert.match(source.value, /href="https:\/\/example.com\/docs"/);
    assert.equal(root.querySelector('[data-link-panel]').hidden, true);
});

test('empty document submits an empty description', () => {
    const { editor, source } = mount('Texto');
    editor.commands.clearContent();
    assert.equal(source.value, '');
});

test('oversized content blocks submit without dropping the text', async () => {
    const { source, form, root } = mount('Uma descrição que deve permanecer inteira');
    source.maxLength = 10;
    const submit = new dom.window.Event('submit', { bubbles: true, cancelable: true });
    form.dispatchEvent(submit);
    await tick();
    assert.equal(submit.defaultPrevented, true);
    assert.match(source.value, /deve permanecer inteira/);
    assert.equal(root.querySelector('[data-editor-status]').hidden, false);
});

test('URL validation accepts only explicit HTTP(S) addresses', () => {
    for (const value of ['https://example.com', 'http://localhost:8000/path', 'https://example.com/?a=1&b=2']) {
        assert.equal(isHttpUrl(value), true, value);
    }
    for (const value of ['javascript:alert(1)', 'data:text/html,evil', '//example.com', '/path', 'mailto:x@example.com', 'https://', 'https://example.com/\npath']) {
        assert.equal(isHttpUrl(value), false, value);
    }
});
