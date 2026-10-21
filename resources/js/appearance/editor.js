import { contrast, normalizeSettings, presetSettings, resolvedColors } from './settings.js';

export function initializeAppearanceEditor(dialog) {
    const { schema, initial } = JSON.parse(dialog.querySelector('[data-appearance-config]').textContent);
    const form = dialog.querySelector('form');
    const frame = dialog.querySelector('[data-appearance-frame]');
    const canvas = dialog.querySelector('[data-appearance-canvas]');
    const status = dialog.querySelector('[data-appearance-status]');
    const save = dialog.querySelector('[data-appearance-save]');
    const preset = dialog.querySelector('[data-appearance-preset]');
    let saved = normalizeSettings(initial, schema);
    let draft = structuredClone(saved);
    let device = 'desktop';
    let saving = false;
    let opener;
    const origin = window.location.origin;

    const notify = () => {
        dialog.querySelectorAll('[data-preset-choice]').forEach((button) => {
            button.setAttribute('aria-pressed', String(button.dataset.presetChoice === preset.value));
        });
        const description = dialog.querySelector('[data-preset-description]');
        if (description) description.textContent = schema.presets[preset.value]?.description ?? 'Personalizado. Combine fonte, composição e cores para deixar com a sua identidade.';
        if (dialog.open) frame.contentWindow?.postMessage({ type: 'portfolio:appearance', settings: draft }, origin);
        const colors = resolvedColors(draft, schema);
        const pairs = [['ink', 'page'], ['muted', 'page'], ['title', 'page'], ['ink', 'card'], ['muted', 'card'], ['title', 'card'], ['ink', 'header'], ['ink', 'footer'], ['ink', 'surface']];
        dialog.querySelector('[data-appearance-contrast]').hidden = pairs.every(([a, b]) => contrast(colors[a], colors[b]) >= 4.5);
    };
    const sync = () => {
        dialog.querySelector('[data-appearance-theme]').value = draft.theme;
        dialog.querySelector('[data-appearance-accent]').value = draft.primary_color;
        dialog.querySelectorAll('[data-appearance-field]').forEach((input) => { input.value = draft.appearance[input.dataset.appearanceField]; });
        const colors = resolvedColors(draft, schema);
        dialog.querySelectorAll('[data-appearance-color]').forEach((input) => { input.value = colors[input.dataset.appearanceColor]; });
        notify();
    };
    const resize = () => {
        const width = device === 'mobile' ? 390 : 1100;
        const scale = Math.min(1, canvas.clientWidth / width);
        if (!scale) return;
        frame.style.width = `${width}px`;
        frame.style.height = `${canvas.clientHeight / scale}px`;
        frame.style.transform = `translateX(-50%) scale(${scale})`;
    };
    const close = () => { if (!saving) dialog.close(); };
    document.querySelectorAll('[data-open-appearance]').forEach((button) => {
        button.disabled = false;
        button.addEventListener('click', () => {
            opener = button;
            draft = structuredClone(saved);
            preset.value = '';
            status.textContent = '';
            dialog.showModal();
            sync();
            if (!frame.getAttribute('src')) frame.src = frame.dataset.src;
            resize();
        });
    });
    dialog.querySelectorAll('[data-appearance-close]').forEach((button) => button.addEventListener('click', close));
    dialog.addEventListener('cancel', (event) => { event.preventDefault(); close(); });
    dialog.addEventListener('close', () => { opener?.focus(); });
    const applyPreset = () => {
        const selected = schema.presets[preset.value];
        if (!selected) return;
        draft = presetSettings(selected, schema);
        sync();
    };
    preset.addEventListener('change', applyPreset);
    dialog.querySelectorAll('[data-preset-choice]').forEach((button) => button.addEventListener('click', () => {
        preset.value = button.dataset.presetChoice;
        applyPreset();
    }));
    dialog.querySelector('[data-appearance-reset]').addEventListener('click', () => {
        if (!preset.value) preset.value = draft.theme;
        applyPreset();
    });
    dialog.querySelector('[data-appearance-theme]').addEventListener('change', (event) => {
        draft.theme = event.target.value; draft.appearance.colors = {}; preset.value = ''; sync();
    });
    dialog.querySelector('[data-appearance-accent]').addEventListener('input', (event) => { draft.primary_color = event.target.value; preset.value = ''; notify(); });
    dialog.querySelectorAll('[data-appearance-color]').forEach((input) => input.addEventListener('input', () => {
        draft.appearance.colors[input.dataset.appearanceColor] = input.value; preset.value = ''; notify();
    }));
    dialog.querySelectorAll('[data-appearance-field]').forEach((input) => input.addEventListener('change', () => {
        draft.appearance[input.dataset.appearanceField] = input.value;
        preset.value = '';
        draft = normalizeSettings(draft, schema); notify();
    }));
    dialog.querySelectorAll('[data-appearance-device]').forEach((button) => button.addEventListener('click', () => {
        device = button.dataset.appearanceDevice;
        dialog.querySelectorAll('[data-appearance-device]').forEach((item) => item.setAttribute('aria-pressed', String(item === button)));
        resize();
    }));
    dialog.querySelectorAll('[data-appearance-pane]').forEach((button) => button.addEventListener('click', () => {
        dialog.querySelector('.appearance-editor-body').dataset.pane = button.dataset.appearancePane;
        dialog.querySelectorAll('[data-appearance-pane]').forEach((item) => item.setAttribute('aria-pressed', String(item === button)));
        resize();
    }));
    dialog.querySelector('[data-appearance-home]').addEventListener('click', () => { frame.src = frame.dataset.src; });
    window.addEventListener('message', (event) => {
        if (event.origin === origin && event.source === frame.contentWindow && event.data?.type === 'portfolio:preview-ready') notify();
    });
    frame.addEventListener('load', () => { resize(); notify(); });
    new ResizeObserver(resize).observe(canvas);
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (saving) return;
        saving = true; save.disabled = true; status.textContent = 'Salvando…';
        form.querySelectorAll('input, select, button').forEach((control) => { control.disabled = true; });
        const snapshot = structuredClone(draft);
        try {
            const response = await fetch(form.action, {
                method: 'PATCH', credentials: 'same-origin',
                signal: AbortSignal.timeout(15000),
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(snapshot),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(Object.values(result.errors ?? {}).flat().join(' ') || 'Não foi possível salvar. Reabra sua sessão e tente novamente.');
            saved = snapshot;
            document.querySelector('#theme').value = saved.theme;
            document.querySelector('#primary_color').value = saved.primary_color;
            status.textContent = result.message;
        } catch (error) {
            status.textContent = error instanceof SyntaxError ? 'Não foi possível salvar. Confira sua sessão e conexão.' : error.message;
        } finally {
            saving = false; save.disabled = false;
            form.querySelectorAll('input, select, button').forEach((control) => { control.disabled = false; });
        }
    });
}
