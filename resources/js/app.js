import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

if (document.querySelector('[data-appearance-editor]')) {
    import('./appearance/editor.js').then(({ initializeAppearanceEditor }) => {
        initializeAppearanceEditor(document.querySelector('[data-appearance-editor]'));
    });
}

if (document.querySelector('[data-description-editor]')) {
    import('./editors/project-description.js')
        .then(({ initializeDescriptionEditors }) => initializeDescriptionEditors())
        .catch(() => {
            const status = document.querySelector('[data-editor-status]');
            status.textContent = 'O editor visual não carregou. Você pode editar a descrição no campo de texto.';
            status.hidden = false;
        });
}
