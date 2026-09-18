

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

document.querySelectorAll('[data-catalog-filter-form]').forEach((form) => {
    const searchInput = form.querySelector('[data-catalog-search-input]');
    if (!searchInput) return;

    let timer = null;
    let composing = false;
    const submitSearch = () => {
        clearTimeout(timer);
        timer = setTimeout(() => form.requestSubmit(), 500);
    };

    searchInput.addEventListener('compositionstart', () => {
        composing = true;
    });
    searchInput.addEventListener('compositionend', () => {
        composing = false;
        submitSearch();
    });
    searchInput.addEventListener('input', () => {
        if (!composing) submitSearch();
    });
});

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
            bState.input.placeholder = project ? 'Buscar un proyecto compatible…' : 'Primero selecciona el Proyecto A';
            bState.input.setAttribute('aria-disabled', String(!project));
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
        if (picker === pickers.b && !pickerState.get(pickers.a).selected) {
            state.results.hidden = false;
            state.results.textContent = 'Selecciona primero el Proyecto A para buscar proyectos de la misma modalidad.';

            return;
        }

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

document.querySelectorAll('[data-project-career]').forEach((careerSelect) => {
    const form = careerSelect.closest('form');
    const academicPickers = form?.querySelectorAll('[data-academic-picker]') || [];

    academicPickers.forEach((picker) => {
        const input = picker.querySelector('[data-academic-search]');
        const value = picker.querySelector('[data-academic-value]');
        const results = picker.querySelector('[data-academic-results]');
        const clear = picker.querySelector('[data-academic-clear]');
        let selected = picker.dataset.selectedId ? { id: picker.dataset.selectedId, label: picker.dataset.selectedLabel } : null;
        let timer = null;
        let controller = null;

        const updateAvailability = () => {
            input.setAttribute('aria-disabled', String(!careerSelect.value));
            input.placeholder = careerSelect.value
                ? (picker.dataset.optional ? 'Escribe nombre, código o especialidad' : 'Escribe nombre, código o correo')
                : 'Primero selecciona una carrera';
        };

        const setSelected = (item) => {
            selected = item;
            value.value = item?.id || '';
            input.value = item?.label || '';
            clear.hidden = !item;
            results.hidden = true;
            picker.classList.remove('picker-error');
        };

        const renderResults = (items) => {
            results.replaceChildren();
            if (items.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'project-result-empty';
                empty.textContent = 'No se encontraron registros para esta carrera.';
                results.append(empty);
            }

            items.forEach((item) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'project-result';
                button.setAttribute('role', 'option');
                const label = document.createElement('strong');
                label.textContent = item.label;
                const meta = document.createElement('span');
                meta.textContent = item.meta || 'Registro académico';
                button.append(label, meta);
                button.addEventListener('click', () => setSelected(item));
                results.append(button);
            });
            results.hidden = false;
        };

        const search = async () => {
            if (!careerSelect.value) {
                results.hidden = false;
                results.textContent = 'Selecciona primero una carrera para mostrar registros compatibles.';

                return;
            }
            controller?.abort();
            controller = new AbortController();
            const params = new URLSearchParams({
                q: input.value.trim(),
                carrera_id: careerSelect.value,
            });
            results.hidden = false;
            results.textContent = 'Buscando…';

            try {
                const response = await fetch(`${picker.dataset.searchUrl}?${params}`, {
                    headers: { Accept: 'application/json' },
                    signal: controller.signal,
                });
                if (!response.ok) throw new Error('No fue posible completar la búsqueda.');
                renderResults((await response.json()).data || []);
            } catch (error) {
                if (error.name !== 'AbortError') {
                    results.textContent = 'No se pudo completar la búsqueda.';
                    results.hidden = false;
                }
            }
        };

        input.addEventListener('focus', search);
        input.addEventListener('input', () => {
            if (selected) {
                const query = input.value;
                setSelected(null);
                input.value = query;
            }
            clearTimeout(timer);
            timer = setTimeout(search, 250);
        });
        clear.addEventListener('click', () => {
            setSelected(null);
            input.focus();
        });
        document.addEventListener('click', (event) => {
            if (!picker.contains(event.target)) results.hidden = true;
        });

        careerSelect.addEventListener('change', () => {
            setSelected(null);
            updateAvailability();
        });
        setSelected(selected);
        updateAvailability();
    });

    form?.addEventListener('submit', (event) => {
        const requiredPickers = Array.from(academicPickers).filter((picker) => picker.dataset.optional !== 'true');
        const missingSelection = requiredPickers.some((picker) => {
            const missing = !picker.querySelector('[data-academic-value]').value;
            picker.classList.toggle('picker-error', missing);
            return missing;
        });
        if (missingSelection) event.preventDefault();
    });
});

document.querySelectorAll('[data-external-search-form]').forEach((form) => {
    const picker = form.querySelector('[data-external-project-picker]');
    const input = picker?.querySelector('[data-external-project-search]');
    const value = picker?.querySelector('[data-external-project-value]');
    const results = picker?.querySelector('[data-external-project-results]');
    const clear = picker?.querySelector('[data-external-project-clear]');
    const searchUrl = form.dataset.projectSearchUrl;
    if (!picker || !input || !value || !results || !clear || !searchUrl) return;

    let selected = picker.dataset.selectedId ? {
        id: picker.dataset.selectedId,
        titulo: picker.dataset.selectedTitle,
        estudiante: picker.dataset.selectedStudent,
        carrera: picker.dataset.selectedCareer,
    } : null;
    let timer = null;
    let controller = null;

    const setSelected = (project) => {
        selected = project;
        value.value = project?.id || '';
        input.value = project ? `${project.titulo} — ${project.estudiante}` : '';
        clear.hidden = !project;
        results.hidden = true;
        picker.classList.remove('picker-error');
    };

    const renderResults = (projects) => {
        results.replaceChildren();
        if (projects.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'project-result-empty';
            empty.textContent = 'No se encontraron proyectos con texto disponible.';
            results.append(empty);
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
            button.addEventListener('click', () => setSelected(project));
            results.append(button);
        });
        results.hidden = false;
    };

    const search = async () => {
        controller?.abort();
        controller = new AbortController();
        const params = new URLSearchParams({ q: input.value.trim() });
        results.hidden = false;
        results.textContent = 'Buscando proyectos…';
        try {
            const response = await fetch(`${searchUrl}?${params}`, {
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            });
            if (!response.ok) throw new Error('No fue posible buscar proyectos.');
            renderResults((await response.json()).data || []);
        } catch (error) {
            if (error.name !== 'AbortError') {
                results.textContent = 'No se pudo completar la búsqueda.';
                results.hidden = false;
            }
        }
    };

    input.addEventListener('focus', search);
    input.addEventListener('input', () => {
        if (selected) {
            const query = input.value;
            setSelected(null);
            input.value = query;
        }
        clearTimeout(timer);
        timer = setTimeout(search, 250);
    });
    clear.addEventListener('click', () => {
        setSelected(null);
        input.focus();
    });
    document.addEventListener('click', (event) => {
        if (!picker.contains(event.target)) results.hidden = true;
    });
    form.addEventListener('submit', (event) => {
        const missing = !value.value;
        picker.classList.toggle('picker-error', missing);
        if (missing) event.preventDefault();
    });

    setSelected(selected);
});
