

import Alpine from 'alpinejs';
import 'bootstrap';

window.Alpine = Alpine;

Alpine.start();

const sidebar = document.querySelector('#app-sidebar');
const sidebarToggle = sidebar?.querySelector('[data-sidebar-toggle]');

if (sidebar && sidebarToggle) {
    const preferenceKey = 'sello-itsa-sidebar-collapsed';
    const desktop = window.matchMedia('(min-width: 992px)');

    const savedPreference = () => {
        try {
            return window.localStorage.getItem(preferenceKey) === 'true';
        } catch {
            return false;
        }
    };

    const applySidebarState = (collapsed) => {
        const shouldCollapse = desktop.matches && collapsed;
        sidebar.classList.toggle('is-collapsed', shouldCollapse);
        sidebarToggle.setAttribute('aria-expanded', String(!shouldCollapse));
        sidebarToggle.setAttribute('aria-label', shouldCollapse ? 'Expandir panel lateral' : 'Contraer panel lateral');
        sidebarToggle.title = shouldCollapse ? 'Expandir panel' : 'Contraer panel';
        sidebarToggle.querySelector('i')?.classList.toggle('bi-chevron-right', shouldCollapse);
        sidebarToggle.querySelector('i')?.classList.toggle('bi-chevron-left', !shouldCollapse);

        sidebar.querySelectorAll('.nav-link').forEach((link) => {
            const label = link.querySelector('.sidebar-link-text')?.textContent.trim();
            if (label) link.title = shouldCollapse ? label : '';
        });
    };

    applySidebarState(savedPreference());
    sidebarToggle.addEventListener('click', () => {
        const collapsed = !sidebar.classList.contains('is-collapsed');
        applySidebarState(collapsed);
        try {
            window.localStorage.setItem(preferenceKey, String(collapsed));
        } catch {
            // El panel sigue funcionando aunque el navegador bloquee el almacenamiento local.
        }
    });
    desktop.addEventListener('change', () => applySidebarState(savedPreference()));
}

document.querySelectorAll('[data-comparison-form]').forEach((form) => {
    const pickers = {
        a: form.querySelector('[data-project-picker="a"]'),
        b: form.querySelector('[data-project-picker="b"]'),
    };
    const hint = form.querySelector('[data-comparison-hint]');
    const searchUrl = form.dataset.searchUrl;

    if (!pickers.a || !pickers.b || !searchUrl) return;

    const pickerState = new Map();

    const projectFromDataset = (picker) => picker.dataset.selectedId ? {
        id: picker.dataset.selectedId,
        titulo: picker.dataset.selectedTitle,
        modalidad: picker.dataset.selectedModalidad,
        modalidad_nombre: picker.dataset.selectedModalidadName,
        estudiante: picker.dataset.selectedStudent,
        carrera: picker.dataset.selectedCareer,
        anio: picker.dataset.selectedYear,
    } : null;

    const updateHint = () => {
        const modality = pickerState.get(pickers.a)?.selected?.modalidad;
        if (hint) {
            const icon = hint.querySelector('i');
            const message = modality
                ? ` Proyecto B mostrará únicamente: ${pickerState.get(pickers.a).selected.modalidad_nombre}.`
                : ' Selecciona Proyecto A para habilitar la búsqueda de proyectos compatibles.';
            hint.replaceChildren(icon || document.createElement('i'), document.createTextNode(message));
        }
    };

    const setSelected = (picker, project, notify = true) => {
        const state = pickerState.get(picker);
        state.selected = project;
        state.value.value = project?.id || '';
        state.input.value = project ? `${project.titulo} — ${project.estudiante}` : '';
        state.clear.hidden = !project;
        state.results.hidden = true;
        picker.classList.remove('picker-error');

        if (picker === pickers.a) {
            state.input.placeholder = 'Escribe título, estudiante o carrera…';
            const bState = pickerState.get(pickers.b);
            bState.input.disabled = !project;
            bState.input.placeholder = project ? 'Buscar un proyecto compatible…' : 'Primero selecciona el Proyecto A';
            if (notify && bState.selected && (!project || bState.selected.modalidad !== project.modalidad || String(bState.selected.id) === String(project.id))) {
                setSelected(pickers.b, null, false);
            }
            updateHint();
        }
    };

    const renderResults = (picker, projects) => {
        const state = pickerState.get(picker);
        state.results.replaceChildren();

        if (projects.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'project-result-empty';
            empty.textContent = 'No se encontraron proyectos compatibles.';
            state.results.append(empty);
        }

        projects.forEach((project) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'project-result';
            button.setAttribute('role', 'option');

            const title = document.createElement('strong');
            title.textContent = project.titulo;
            const meta = document.createElement('span');
            meta.textContent = `${project.estudiante} · ${project.carrera} · ${project.anio}`;
            const mode = document.createElement('small');
            mode.textContent = project.modalidad_nombre;
            button.append(title, meta, mode);
            button.addEventListener('click', () => setSelected(picker, project));
            state.results.append(button);
        });

        state.results.hidden = false;
    };

    const search = async (picker) => {
        const state = pickerState.get(picker);
        if (picker === pickers.b && !pickerState.get(pickers.a).selected) return;

        state.controller?.abort();
        state.controller = new AbortController();
        const params = new URLSearchParams({ q: state.input.value.trim() });
        if (picker === pickers.b) {
            const selectedA = pickerState.get(pickers.a).selected;
            params.set('modalidad', selectedA.modalidad);
            params.set('excluir', selectedA.id);
        }

        state.results.hidden = false;
        state.results.textContent = 'Buscando proyectos…';
        try {
            const response = await fetch(`${searchUrl}?${params}`, {
                headers: { Accept: 'application/json' },
                signal: state.controller.signal,
            });
            if (!response.ok) throw new Error('No fue posible buscar proyectos.');
            renderResults(picker, (await response.json()).data || []);
        } catch (error) {
            if (error.name !== 'AbortError') {
                state.results.textContent = 'No se pudo completar la búsqueda.';
                state.results.hidden = false;
            }
        }
    };

    Object.values(pickers).forEach((picker) => {
        const state = {
            input: picker.querySelector('[data-project-search]'),
            value: picker.querySelector('[data-project-value]'),
            results: picker.querySelector('[data-project-results]'),
            clear: picker.querySelector('[data-project-clear]'),
            selected: null,
            timer: null,
            controller: null,
        };
        pickerState.set(picker, state);
        state.input.addEventListener('focus', () => search(picker));
        state.input.addEventListener('input', () => {
            if (state.selected) {
                const query = state.input.value;
                setSelected(picker, null);
                state.input.value = query;
            }
            clearTimeout(state.timer);
            state.timer = setTimeout(() => search(picker), 250);
        });
        state.clear.addEventListener('click', () => {
            setSelected(picker, null);
            state.input.focus();
        });
    });

    setSelected(pickers.a, projectFromDataset(pickers.a), false);
    setSelected(pickers.b, projectFromDataset(pickers.b), false);
    updateHint();

    document.addEventListener('click', (event) => {
        Object.values(pickers).forEach((picker) => {
            if (!picker.contains(event.target)) pickerState.get(picker).results.hidden = true;
        });
    });

    form.addEventListener('submit', (event) => {
        let valid = true;
        Object.values(pickers).forEach((picker) => {
            const missing = !pickerState.get(picker).value.value;
            picker.classList.toggle('picker-error', missing);
            valid = valid && !missing;
        });
        if (!valid) event.preventDefault();
    });
});
