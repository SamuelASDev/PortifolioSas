export function initializeShareActions(root) {
    if (root.dataset.shareReady === 'true') return;
    const metadata = JSON.parse(root.querySelector('[data-share-payload]').textContent);
    const copy = root.querySelector('[data-copy-link]');
    const share = root.querySelector('[data-share-link]');
    const buttons = [copy, share].filter(Boolean);
    const manual = root.querySelector('[data-share-manual]');
    const input = root.querySelector('[data-share-url]');
    const status = root.querySelector('[data-share-status]');
    let busy = false;

    const copyLink = async () => {
        try {
            if (!window.navigator.clipboard?.writeText) throw new Error('Clipboard unavailable');
            await window.navigator.clipboard.writeText(metadata.url);
            manual.hidden = true;
            status.textContent = 'Link copiado! Cole onde quiser compartilhar.';
        } catch {
            manual.hidden = false;
            input.focus(); input.select();
            status.textContent = 'Copie o link selecionado para compartilhar.';
        }
    };
    const run = async (action) => {
        if (busy) return;
        busy = true; buttons.forEach((button) => { button.disabled = true; });
        status.textContent = '';
        try { await action(); }
        finally { busy = false; buttons.forEach((button) => { button.disabled = false; }); }
    };
    copy?.addEventListener('click', () => run(copyLink));
    share.addEventListener('click', () => run(async () => {
        const payload = { title: metadata.title, text: metadata.description, url: metadata.url };
        try {
            if (!window.navigator.share || (window.navigator.canShare && !window.navigator.canShare(payload))) {
                await copyLink(); return;
            }
            await window.navigator.share(payload);
            status.textContent = 'Link entregue ao menu de compartilhamento.';
        } catch (error) {
            if (error?.name === 'AbortError') status.textContent = 'Compartilhamento cancelado.';
            else await copyLink();
        }
    }));
    input.addEventListener('focus', () => input.select());
    manual.hidden = true; buttons.forEach((button) => { button.disabled = false; });
    root.dataset.shareReady = 'true';
}
