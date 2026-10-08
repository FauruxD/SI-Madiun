const sidebar = document.querySelector('[data-sidebar]');
if (document.querySelector('[data-map-root]')) {
    import('./map.js').then(({ initializeMaps }) => initializeMaps()).catch(() => {
        document.querySelectorAll('[data-map-status]').forEach((status) => {
            status.textContent = 'Asset peta belum tersedia. Muat ulang halaman; daftar informasi tetap dapat dibaca.';
        });
    });
}
const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
const sidebarOverlay = document.querySelector('[data-sidebar-overlay]');

if (sidebar && sidebarToggle && sidebarOverlay) {
    const closeSidebar = () => {
        sidebar.classList.add('-translate-x-full');
        sidebarOverlay.classList.add('hidden');
        sidebarToggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('overflow-hidden');
    };

    const openSidebar = () => {
        sidebar.classList.remove('-translate-x-full');
        sidebarOverlay.classList.remove('hidden');
        sidebarToggle.setAttribute('aria-expanded', 'true');
        document.body.classList.add('overflow-hidden');
        sidebar.querySelector('a')?.focus();
    };

    sidebarToggle.addEventListener('click', () => {
        if (sidebarToggle.getAttribute('aria-expanded') === 'true') {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    sidebarOverlay.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebarToggle.getAttribute('aria-expanded') === 'true') {
            closeSidebar();
            sidebarToggle.focus();
        }
    });

    window.matchMedia('(min-width: 1024px)').addEventListener('change', (event) => {
        if (event.matches) {
            sidebarOverlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
            sidebarToggle.setAttribute('aria-expanded', 'false');
        }
    });
}

const roleSelect = document.querySelector('[data-role-select]');
const desaAssignments = document.querySelector('[data-desa-assignments]');

if (roleSelect && desaAssignments) {
    const updateDesaAssignments = () => {
        desaAssignments.hidden = roleSelect.value !== 'ADMIN_DESA';
    };

    roleSelect.addEventListener('change', updateDesaAssignments);
    updateDesaAssignments();
}

const villagePicker = document.querySelector('[data-village-picker]');

if (villagePicker) {
    const searchInput = villagePicker.querySelector('[data-village-search]');
    const list = villagePicker.querySelector('[data-village-list]');
    const emptyState = villagePicker.querySelector('[data-village-empty]');
    const status = villagePicker.querySelector('[data-village-status]');
    const primaryVillage = villagePicker.querySelector('[data-primary-village]');
    let searchTimer;
    let activeRequest;

    const selectedIds = () => new Set(
        [...list.querySelectorAll('input[name="desa_ids[]"]:checked')].map((input) => input.value),
    );

    const createVillageOption = (village, selected) => {
        const label = document.createElement('label');
        label.className = 'selection-card';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'desa_ids[]';
        checkbox.value = String(village.id);
        checkbox.checked = selected.has(checkbox.value);
        checkbox.className = 'mt-0.5 size-4 shrink-0 text-emerald-800 focus:ring-emerald-700';

        const content = document.createElement('span');
        const name = document.createElement('strong');
        name.className = 'block text-stone-950';
        name.textContent = village.nama;
        const metadata = document.createElement('span');
        metadata.className = 'mt-1 block text-xs text-stone-500';
        metadata.textContent = `${village.kecamatan} · ${village.kode}`;

        content.append(name, metadata);
        label.append(checkbox, content);

        return label;
    };

    const syncPrimaryVillageOptions = () => {
        if (!primaryVillage) {
            return;
        }

        const preferredValue = primaryVillage.value || primaryVillage.dataset.value || '';
        const options = [...list.querySelectorAll('input[name="desa_ids[]"]:checked')].map((input) => {
            const option = document.createElement('option');
            option.value = input.value;
            option.textContent = input.closest('.selection-card')?.querySelector('strong')?.textContent || `Desa ${input.value}`;

            return option;
        });

        if (options.length === 0) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'Pilih desa terlebih dahulu';
            options.push(option);
        }

        primaryVillage.replaceChildren(...options);
        primaryVillage.value = options.some((option) => option.value === preferredValue) ? preferredValue : options[0].value;
        primaryVillage.dataset.value = primaryVillage.value;
    };

    const searchVillages = async () => {
        const selected = selectedIds();
        const url = new URL(villagePicker.dataset.optionsUrl, window.location.origin);
        url.searchParams.set('search', searchInput.value.trim());
        selected.forEach((id) => url.searchParams.append('selected[]', id));

        activeRequest?.abort();
        activeRequest = new AbortController();
        status.textContent = 'Mencari desa…';

        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                signal: activeRequest.signal,
            });

            if (!response.ok) {
                throw new Error('Village search failed');
            }

            const payload = await response.json();
            const villages = Array.isArray(payload.data) ? payload.data : [];
            list.replaceChildren(...villages.map((village) => createVillageOption(village, selected)));
            syncPrimaryVillageOptions();
            emptyState.hidden = villages.length > 0;
            status.textContent = `${villages.length} desa ditampilkan.`;
        } catch (error) {
            if (error.name !== 'AbortError') {
                status.textContent = 'Pencarian gagal. Pilihan sebelumnya tetap tersedia.';
            }
        }
    };

    searchInput.addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(searchVillages, 250);
    });

    list.addEventListener('change', syncPrimaryVillageOptions);
    primaryVillage?.addEventListener('change', () => {
        primaryVillage.dataset.value = primaryVillage.value;
    });
    syncPrimaryVillageOptions();
}
