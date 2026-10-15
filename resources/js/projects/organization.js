export function initializeProjectOrganization(form) {
    const list = form.querySelector('[data-project-list]');
    const status = form.querySelector('[data-organization-status]');
    const save = form.querySelector('[data-organization-save]');
    let revision = form.dataset.revision;
    let dirty = false;
    let saving = false;
    let dragging = null;
    const rows = () => [...list.querySelectorAll('[data-project-row]')];
    const markChanged = () => { dirty = true; status.textContent = 'Alterações pendentes. Salve para aplicar.'; };
    const refresh = () => rows().forEach((row, index, all) => {
        row.querySelector('[data-project-position]').textContent = index + 1;
        row.querySelector('[data-project-up]').disabled = saving || index === 0;
        row.querySelector('[data-project-down]').disabled = saving || index === all.length - 1;
    });
    const move = (row, direction, focus = true) => {
        if (saving) return;
        const neighbor = direction < 0 ? row.previousElementSibling : row.nextElementSibling;
        if (!neighbor) return;
        list.insertBefore(row, direction < 0 ? neighbor : neighbor.nextElementSibling);
        markChanged(); refresh();
        if (focus) row.querySelector('[data-project-handle]').focus({ preventScroll: true });
    };
    rows().forEach((row) => {
        const handle = row.querySelector('[data-project-handle]');
        row.querySelector('[data-project-up]').addEventListener('click', () => move(row, -1));
        row.querySelector('[data-project-down]').addEventListener('click', () => move(row, 1));
        row.querySelector('[data-project-featured]').addEventListener('change', markChanged);
        handle.addEventListener('keydown', (event) => {
            if (event.key !== 'ArrowUp' && event.key !== 'ArrowDown') return;
            event.preventDefault(); move(row, event.key === 'ArrowUp' ? -1 : 1);
        });
        handle.addEventListener('pointerdown', (event) => {
            if (saving || event.button !== 0) return;
            event.preventDefault();
            dragging = { row, pointerId: event.pointerId };
            row.classList.add('is-dragging'); handle.focus({ preventScroll: true });
        });
    });
    document.addEventListener('pointermove', (event) => {
        if (!dragging || dragging.pointerId !== event.pointerId || saving) return;
        event.preventDefault();
        const bounds = list.getBoundingClientRect();
        if (event.clientY < bounds.top + 48) list.scrollTop -= 24;
        if (event.clientY > bounds.bottom - 48) list.scrollTop += 24;
        const target = document.elementFromPoint(event.clientX, event.clientY)?.closest('[data-project-row]');
        if (!target || !list.contains(target) || target === dragging.row) return;
        const rect = target.getBoundingClientRect();
        list.insertBefore(dragging.row, event.clientY < rect.top + rect.height / 2 ? target : target.nextElementSibling);
        markChanged(); refresh();
    }, { passive: false });
    const endDrag = (event) => {
        if (!dragging || dragging.pointerId !== event.pointerId) return;
        dragging.row.classList.remove('is-dragging'); dragging = null;
    };
    document.addEventListener('pointerup', endDrag);
    document.addEventListener('pointercancel', endDrag);
    window.addEventListener('beforeunload', (event) => {
        if (!dirty) return;
        event.preventDefault(); event.returnValue = '';
    });
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (saving) return;
        saving = true;
        const projects = rows().map((row) => ({ id: Number(row.dataset.projectId), is_featured: row.querySelector('[data-project-featured]').checked }));
        form.querySelectorAll('button, input').forEach((control) => { control.disabled = true; });
        status.textContent = 'Salvando…';
        try {
            const response = await fetch(form.action, {
                method: 'PATCH', credentials: 'same-origin', signal: AbortSignal.timeout(15000),
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ revision, projects }),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(Object.values(result.errors ?? {}).flat().join(' ') || result.message || 'Não foi possível salvar. Confira sua sessão e tente novamente.');
            revision = result.revision; dirty = false; status.textContent = result.message;
        } catch (error) {
            status.textContent = error instanceof SyntaxError ? 'Não foi possível salvar. Confira sua sessão e conexão.' : error.message;
        } finally {
            saving = false;
            form.querySelectorAll('button, input').forEach((control) => { control.disabled = false; });
            refresh();
        }
    });
    save.disabled = false; refresh();
}
