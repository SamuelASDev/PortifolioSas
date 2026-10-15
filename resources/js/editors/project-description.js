import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import { initializeMediaControls, ProjectImage, ProjectVideo } from './project-media.js';

export function plainTextDocument(text) {
    return {
        type: 'doc',
        content: text.split(/\r\n|\r|\n/).map((line) => ({
            type: 'paragraph',
            content: line ? [{ type: 'text', text: line }] : [],
        })),
    };
}

export function isHttpUrl(value) {
    if (!/^https?:\/\//i.test(value) || /[\u0000-\u0020\u007f]/u.test(value)) return false;
    try {
        const url = new URL(value);
        return ['http:', 'https:'].includes(url.protocol) && Boolean(url.hostname);
    } catch {
        return false;
    }
}

export function initializeDescriptionEditor(root) {
    const source = root.querySelector('[data-description-source]');
    const format = root.querySelector('[data-description-format]');
    const ui = root.querySelector('[data-editor-ui]');
    const toolbar = root.querySelector('[data-editor-toolbar]');
    const status = root.querySelector('[data-editor-status]');
    const buttons = [...toolbar.querySelectorAll('[data-editor-command]')];
    const panel = root.querySelector('[data-link-panel]');
    const input = panel.querySelector('[data-link-input]');
    const linkError = panel.querySelector('[data-link-error]');
    const form = root.closest('form');
    if (root.dataset.editorReady === 'true') return null;

    const synchronize = (editor) => {
        source.value = editor.isEmpty ? '' : editor.getHTML();
        format.value = 'html';
        status.hidden = true;
    };

    const updateToolbar = (editor) => {
        const active = {
            paragraph: editor.isActive('paragraph'),
            h2: editor.isActive('heading', { level: 2 }),
            h3: editor.isActive('heading', { level: 3 }),
            bold: editor.isActive('bold'), italic: editor.isActive('italic'),
            bulletList: editor.isActive('bulletList'), orderedList: editor.isActive('orderedList'),
            blockquote: editor.isActive('blockquote'), code: editor.isActive('code'),
            codeBlock: editor.isActive('codeBlock'), link: editor.isActive('link'),
        };
        for (const button of buttons) {
            const command = button.dataset.editorCommand;
            if (command in active) button.setAttribute('aria-pressed', String(active[command]));
            button.disabled = (command === 'undo' && !editor.can().undo())
                || (command === 'redo' && !editor.can().redo())
                || (command === 'unlink' && !editor.isActive('link'));
        }
    };

    const editor = new Editor({
        element: root.querySelector('[data-editor-content]'),
        content: source.dataset.initialFormat === 'html' ? source.value : plainTextDocument(source.value),
        extensions: [ProjectImage.configure({ baseUrl: root.dataset.mediaBaseUrl ?? null }), ProjectVideo, StarterKit.configure({
            heading: { levels: [2, 3] },
            strike: false, underline: false, horizontalRule: false,
            link: {
                openOnClick: false, autolink: false, defaultProtocol: 'https',
                isAllowedUri: (url, context) => context.defaultValidate(url) && isHttpUrl(url),
                HTMLAttributes: { target: null, rel: null },
            },
        })],
        editorProps: {
            attributes: {
                id: 'description-visual', class: 'project-prose description-editor-content',
                role: 'textbox', 'aria-multiline': 'true',
                'aria-invalid': source.getAttribute('aria-invalid') ?? 'false',
                'aria-labelledby': 'description-label', 'aria-describedby': 'description-help description-error',
            },
        },
        onUpdate: ({ editor }) => synchronize(editor),
        onTransaction: ({ editor }) => updateToolbar(editor),
    });

    const closePanel = () => {
        panel.hidden = true;
        root.querySelector('[data-editor-command="link"]').setAttribute('aria-expanded', 'false');
        linkError.hidden = true;
        input.removeAttribute('aria-invalid');
    };
    const applyLink = () => {
        const href = input.value.trim();
        if (!isHttpUrl(href)) {
            linkError.hidden = false;
            input.setAttribute('aria-invalid', 'true');
            input.focus();
            return;
        }
        editor.chain().focus().extendMarkRange('link').setLink({ href }).run();
        closePanel();
    };

    for (const button of buttons) {
        button.addEventListener('mousedown', (event) => event.preventDefault());
        button.addEventListener('click', () => {
            if (root.dataset.mediaBusy === 'true') return;
            const command = button.dataset.editorCommand;
            if (command === 'image' || command === 'video') { closePanel(); return; }
            if (command === 'link') {
                panel.hidden = false;
                button.setAttribute('aria-expanded', 'true');
                input.value = editor.getAttributes('link').href ?? '';
                input.focus();
                return;
            }
            closePanel();
            const chain = editor.chain().focus();
            const commands = {
                paragraph: () => chain.setParagraph().run(),
                h2: () => chain.toggleHeading({ level: 2 }).run(),
                h3: () => chain.toggleHeading({ level: 3 }).run(),
                bold: () => chain.toggleBold().run(), italic: () => chain.toggleItalic().run(),
                bulletList: () => chain.toggleBulletList().run(), orderedList: () => chain.toggleOrderedList().run(),
                blockquote: () => chain.toggleBlockquote().run(), code: () => chain.toggleCode().run(),
                codeBlock: () => chain.toggleCodeBlock().run(), unlink: () => chain.extendMarkRange('link').unsetLink().run(),
                undo: () => chain.undo().run(), redo: () => chain.redo().run(),
                clear: () => chain.unsetAllMarks().clearNodes().run(),
            };
            commands[command]?.();
        });
    }
    toolbar.addEventListener('keydown', (event) => {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
        const available = buttons.filter((button) => !button.disabled);
        const index = available.indexOf(document.activeElement);
        if (index < 0) return;
        event.preventDefault();
        const next = event.key === 'Home' ? 0 : event.key === 'End' ? available.length - 1
            : (index + (event.key === 'ArrowRight' ? 1 : -1) + available.length) % available.length;
        available[next].focus();
    });
    panel.querySelector('[data-link-apply]').addEventListener('click', applyLink);
    panel.querySelector('[data-link-cancel]').addEventListener('click', () => {
        closePanel();
        editor.commands.focus();
    });
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') { event.preventDefault(); applyLink(); }
        if (event.key === 'Escape') { event.preventDefault(); closePanel(); editor.commands.focus(); }
    });
    form.addEventListener('submit', (event) => {
        synchronize(editor);
        if (Array.from(source.value).length > source.maxLength) {
            event.preventDefault();
            status.textContent = 'A descrição ficou longa demais. Reduza o conteúdo antes de salvar.';
            status.hidden = false;
            editor.commands.focus();
        }
    });

    synchronize(editor);
    updateToolbar(editor);
    source.hidden = true;
    ui.hidden = false;
    const label = root.querySelector('#description-label');
    label.htmlFor = 'description-visual';
    label.addEventListener('click', (event) => { event.preventDefault(); editor.commands.focus(); });
    root.dataset.editorReady = 'true';
    initializeMediaControls(root, editor);

    return editor;
}

export function initializeDescriptionEditors() {
    return [...document.querySelectorAll('[data-description-editor]')].map(initializeDescriptionEditor);
}
