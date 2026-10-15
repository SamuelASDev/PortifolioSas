import { Node } from '@tiptap/core';

export function canonicalVideo(src) {
    const youtube = /^https:\/\/www\.youtube-nocookie\.com\/embed\/([a-zA-Z0-9_-]{11})$/.exec(src ?? '');
    if (youtube) return { src, title: 'Vídeo do YouTube' };
    const vimeo = /^https:\/\/player\.vimeo\.com\/video\/([0-9]{1,12})$/.exec(src ?? '');
    return vimeo ? { src, title: 'Vídeo do Vimeo' } : null;
}

export function isManagedImageSource(src, baseUrl = null) {
    try {
        const url = new URL(src, window.location.href);
        if (baseUrl) {
            const base = new URL(baseUrl, window.location.href);
            const directory = `${base.pathname.replace(/\/+$/, '')}/`;
            return ['http:', 'https:'].includes(url.protocol) && !url.username && !url.password
                && url.origin === base.origin && url.pathname.startsWith(directory)
                && /^[a-zA-Z0-9_-]+\.(?:jpg|jpeg|png|webp)$/.test(url.pathname.slice(directory.length));
        }
        return url.origin === window.location.origin && /\/storage\/portfolios\/\d+\/attachments\/[a-zA-Z0-9_-]+\.(?:jpg|jpeg|png|webp)$/.test(url.pathname);
    } catch { return false; }
}

function imageAttributes(element, baseUrl) {
    const image = element.tagName === 'IMG' ? element : element.querySelector('img[data-media-id]');
    if (!image || !/^[a-f0-9-]{36}$/i.test(image.getAttribute('data-media-id') ?? '')) return false;
    if (!isManagedImageSource(image.getAttribute('src'), baseUrl)) return false;
    return {
        src: image.getAttribute('src'), mediaId: image.getAttribute('data-media-id'),
        alt: image.getAttribute('alt') ?? '', caption: element.querySelector('figcaption')?.textContent ?? '',
    };
}

export const ProjectImage = Node.create({
    name: 'projectImage', group: 'block', atom: true, draggable: true,
    addOptions: () => ({ baseUrl: null }),
    addAttributes: () => ({ src: { default: null }, mediaId: { default: null }, alt: { default: '' }, caption: { default: '' } }),
    parseHTML() {
        return [
            { tag: 'figure', getAttrs: (element) => imageAttributes(element, this.options.baseUrl) },
            { tag: 'img[data-media-id]', getAttrs: (element) => imageAttributes(element, this.options.baseUrl) },
        ];
    },
    renderHTML({ node }) {
        const children = [['img', { src: node.attrs.src, alt: node.attrs.alt, 'data-media-id': node.attrs.mediaId, loading: 'lazy' }]];
        if (node.attrs.caption) children.push(['figcaption', {}, node.attrs.caption]);
        return ['figure', {}, ...children];
    },
});

export const ProjectVideo = Node.create({
    name: 'projectVideo', group: 'block', atom: true, draggable: true,
    addAttributes: () => ({ src: { default: null }, title: { default: 'Vídeo' } }),
    parseHTML: () => [{ tag: 'iframe', getAttrs: (element) => canonicalVideo(element.getAttribute('src')) ?? false }],
    renderHTML: ({ node }) => ['iframe', {
        src: node.attrs.src, title: node.attrs.title, allowfullscreen: '', loading: 'lazy', referrerpolicy: 'strict-origin-when-cross-origin',
    }],
});

export async function postMedia(url, body) {
    const response = await fetch(url, {
        method: 'POST', credentials: 'same-origin',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
        body,
    });
    const result = await response.json().catch(() => ({}));
    if (!response.ok) {
        const message = [401, 419].includes(response.status) ? 'Sua sessão expirou. Recarregue a página antes de enviar.'
            : Object.values(result.errors ?? {}).flat()[0] ?? 'Não foi possível inserir a mídia. Tente novamente.';
        throw new Error(message);
    }
    return result;
}

export function initializeMediaControls(root, editor) {
    const imagePanel = root.querySelector('[data-image-panel]');
    const videoPanel = root.querySelector('[data-video-panel]');
    if (!imagePanel || !videoPanel) return;
    const status = root.querySelector('[data-editor-status]');
    const token = root.querySelector('[data-media-token]');
    let busy = false;
    const show = (message) => { status.textContent = message; status.hidden = false; };
    const close = () => {
        imagePanel.hidden = true; videoPanel.hidden = true;
        root.querySelectorAll('[data-editor-command="image"], [data-editor-command="video"]').forEach((button) => button.setAttribute('aria-expanded', 'false'));
    };
    const run = async (task, success) => {
        if (busy) return;
        busy = true;
        root.dataset.mediaBusy = 'true';
        root.setAttribute('aria-busy', 'true');
        editor.setEditable(false);
        root.querySelectorAll('[data-image-insert], [data-video-insert]').forEach((button) => { button.disabled = true; });
        show('Enviando… Aguarde antes de salvar.');
        try {
            await task();
            close();
            editor.setEditable(true);
            show(success);
        } catch (error) {
            editor.setEditable(true);
            show(error.message || 'Não foi possível inserir a mídia.');
        } finally {
            busy = false;
            root.dataset.mediaBusy = 'false';
            root.setAttribute('aria-busy', 'false');
            root.querySelectorAll('[data-image-insert], [data-video-insert]').forEach((button) => { button.disabled = false; });
        }
    };
    root.querySelector('[data-editor-command="image"]').addEventListener('click', () => {
        if (busy) return;
        close(); imagePanel.hidden = false; imagePanel.querySelector('input').focus();
        root.querySelector('[data-editor-command="image"]').setAttribute('aria-expanded', 'true');
    });
    root.querySelector('[data-editor-command="video"]').addEventListener('click', () => {
        if (busy) return;
        close(); videoPanel.hidden = false; videoPanel.querySelector('input').focus();
        root.querySelector('[data-editor-command="video"]').setAttribute('aria-expanded', 'true');
    });
    root.querySelectorAll('[data-media-cancel]').forEach((button) => button.addEventListener('click', () => {
        if (busy) { show('Aguarde a conclusão do envio.'); return; }
        close(); editor.commands.focus();
    }));
    imagePanel.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && event.target.matches('input[type="text"]')) {
            event.preventDefault(); imagePanel.querySelector('[data-image-insert]').click();
        }
    });
    for (const panel of [imagePanel, videoPanel]) {
        panel.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !busy) { event.preventDefault(); close(); editor.commands.focus(); }
        });
    }
    imagePanel.querySelector('[data-image-insert]').addEventListener('click', () => {
        const file = imagePanel.querySelector('[data-image-input]').files[0];
        const alt = imagePanel.querySelector('[data-image-alt]').value.trim();
        const caption = imagePanel.querySelector('[data-image-caption]').value.trim();
        if (!file || file.size > 10485760 || !alt) { show('Selecione uma imagem de até 10 MB e informe uma descrição para ela.'); return; }
        run(async () => {
            const body = new FormData();
            body.append('image', file); body.append('upload_token', token.value);
            if (root.dataset.projectId) body.append('project_id', root.dataset.projectId);
            const result = await postMedia(root.dataset.uploadUrl, body);
            if (!/^[a-f0-9-]{36}$/i.test(result.id ?? '') || !isManagedImageSource(result.url, root.dataset.mediaBaseUrl)) throw new Error('A resposta do upload foi inválida. Tente novamente.');
            editor.chain().focus().insertContent({ type: 'projectImage', attrs: { src: result.url, mediaId: result.id, alt, caption } }).run();
            imagePanel.querySelector('[data-image-input]').value = '';
        }, 'Imagem inserida. Salve o projeto para confirmar.');
    });
    const insertVideo = () => {
        const value = videoPanel.querySelector('[data-video-input]').value.trim();
        if (!value) { show('Informe um link do YouTube ou Vimeo.'); return; }
        run(async () => {
            const body = new FormData(); body.append('url', value);
            const result = await postMedia(root.dataset.videoUrl, body);
            if (!canonicalVideo(result.src)) throw new Error('Endereço de vídeo inválido.');
            editor.chain().focus().insertContent({ type: 'projectVideo', attrs: result }).run();
        }, 'Vídeo inserido. Salve o projeto para confirmar.');
    };
    videoPanel.querySelector('[data-video-insert]').addEventListener('click', insertVideo);
    videoPanel.querySelector('[data-video-input]').addEventListener('keydown', (event) => {
        if (event.key === 'Enter') { event.preventDefault(); insertVideo(); }
    });
    root.closest('form').addEventListener('submit', (event) => {
        if (busy) { event.preventDefault(); show('Aguarde o envio da mídia antes de salvar.'); }
    });
}
