export function initializeSharingPreview(root) {
    const defaults = JSON.parse(root.querySelector('[data-sharing-defaults]').textContent);
    const title = root.querySelector('[data-share-title]');
    const description = root.querySelector('[data-share-description]');
    const file = root.querySelector('[data-share-image]');
    const remove = root.querySelector('[data-share-remove]');
    const image = root.querySelector('[data-share-preview-image]');
    let objectUrl = null;
    const update = () => {
        root.querySelector('[data-share-preview-title]').textContent = title.value.trim() || defaults.fallback_title;
        root.querySelector('[data-share-preview-description]').textContent = description.value.trim() || defaults.fallback_description;
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = null;
        const selected = file.files?.[0];
        if (selected && ['image/jpeg', 'image/png', 'image/webp'].includes(selected.type) && selected.size <= 10240 * 1024 && !remove?.checked) {
            objectUrl = URL.createObjectURL(selected);
        }
        const source = objectUrl || (remove?.checked ? defaults.fallback_image : defaults.image);
        image.hidden = !source;
        if (source) image.src = source;
        else image.removeAttribute('src');
    };
    title.addEventListener('input', update);
    description.addEventListener('input', update);
    file.addEventListener('change', update);
    remove?.addEventListener('change', update);
    window.addEventListener('pagehide', () => { if (objectUrl) URL.revokeObjectURL(objectUrl); });
    update();
}
