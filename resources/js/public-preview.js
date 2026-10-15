import { applySettings } from './appearance/settings';

const schema = JSON.parse(document.querySelector('[data-preview-schema]').textContent);
window.addEventListener('message', (event) => {
    if (event.origin !== window.location.origin || event.source !== window.parent || event.data?.type !== 'portfolio:appearance') return;
    applySettings(document, event.data.settings, schema);
});
if (window.parent !== window) window.parent.postMessage({ type: 'portfolio:preview-ready' }, window.location.origin);
