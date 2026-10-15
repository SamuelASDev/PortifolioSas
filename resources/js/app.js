import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

if (document.querySelector('[data-project-organization]')) {
    import('./projects/organization.js').then(({ initializeProjectOrganization }) => {
        initializeProjectOrganization(document.querySelector('[data-project-organization]'));
    });
}

if (document.querySelector('[data-sharing-settings]')) {
    import('./sharing/preview.js').then(({ initializeSharingPreview }) => {
        document.querySelectorAll('[data-sharing-settings]').forEach(initializeSharingPreview);
    });
}

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
