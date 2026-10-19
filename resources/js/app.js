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
const sidebarClose = document.querySelector('[data-sidebar-close]');
const accountMenuToggle = document.querySelector('[data-account-menu-toggle]');
const accountMenu = document.querySelector('[data-account-menu]');
const accountMenuChevron = document.querySelector('[data-account-menu-chevron]');
const publicMenu = document.querySelector('[data-public-menu]');
const publicMenuSummary = document.querySelector('[data-public-menu-summary]');

document.querySelectorAll('[data-public-image]').forEach((image) => {
    const fallback = image.parentElement?.querySelector('[data-public-image-fallback]');
    const showFallback = () => {
        image.hidden = true;
        fallback?.removeAttribute('hidden');
    };

    image.addEventListener('error', showFallback, { once: true });

    if (image.complete && image.naturalWidth === 0) {
        showFallback();
    }
});

if (publicMenu && publicMenuSummary) {
    const closePublicMenu = (restoreFocus = false) => {
        publicMenu.open = false;
        publicMenuSummary.setAttribute('aria-label', 'Buka menu navigasi');
        publicMenuSummary.querySelector('.sr-only').textContent = 'Buka menu navigasi';

        if (restoreFocus) {
            publicMenuSummary.focus();
        }
    };

    publicMenu.addEventListener('toggle', () => {
        const label = publicMenu.open ? 'Tutup menu navigasi' : 'Buka menu navigasi';
        publicMenuSummary.setAttribute('aria-label', label);
        publicMenuSummary.querySelector('.sr-only').textContent = label;
    });

    document.addEventListener('click', (event) => {
        if (publicMenu.open && !publicMenu.contains(event.target)) {
            closePublicMenu();
        }
    });

    window.matchMedia('(min-width: 1024px)').addEventListener('change', (event) => {
        if (event.matches) {
            closePublicMenu();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && publicMenu.open) {
            closePublicMenu(true);
        }
    });
}

const closeAccountMenu = (restoreFocus = false) => {
    if (!accountMenuToggle || !accountMenu) {
        return;
    }

    accountMenu.hidden = true;
    accountMenuToggle.setAttribute('aria-expanded', 'false');
    accountMenuChevron?.classList.remove('rotate-180');

    if (restoreFocus) {
        accountMenuToggle.focus();
    }
};

if (accountMenuToggle && accountMenu) {
    accountMenuToggle.addEventListener('click', () => {
        const shouldOpen = accountMenuToggle.getAttribute('aria-expanded') !== 'true';

        if (!shouldOpen) {
            closeAccountMenu();
            return;
        }

        accountMenu.hidden = false;
        accountMenuToggle.setAttribute('aria-expanded', 'true');
        accountMenuChevron?.classList.add('rotate-180');
    });

    document.addEventListener('click', (event) => {
        if (accountMenuToggle.getAttribute('aria-expanded') === 'true'
            && !accountMenuToggle.contains(event.target)
            && !accountMenu.contains(event.target)) {
            closeAccountMenu();
        }
    });
}

if (sidebar && sidebarToggle && sidebarOverlay) {
    const desktopSidebarQuery = window.matchMedia('(min-width: 1024px)');

    const closeSidebar = (restoreFocus = false) => {
        closeAccountMenu();
        sidebar.classList.add('-translate-x-full');
        sidebarOverlay.classList.add('hidden');
        sidebarToggle.setAttribute('aria-expanded', 'false');
        sidebarToggle.setAttribute('aria-label', 'Buka navigasi');
        document.body.classList.remove('overflow-hidden');

        if (!desktopSidebarQuery.matches) {
            sidebar.inert = true;
            sidebar.setAttribute('aria-hidden', 'true');
        }

        if (restoreFocus) {
            sidebarToggle.focus();
        }
    };

    const openSidebar = () => {
        sidebar.inert = false;
        sidebar.removeAttribute('aria-hidden');
        sidebar.classList.remove('-translate-x-full');
        sidebarOverlay.classList.remove('hidden');
        sidebarToggle.setAttribute('aria-expanded', 'true');
        sidebarToggle.setAttribute('aria-label', 'Tutup navigasi');
        document.body.classList.add('overflow-hidden');
        sidebar.querySelector('a, button')?.focus();
    };

    const syncSidebar = (isDesktop) => {
        if (isDesktop) {
            sidebar.inert = false;
            sidebar.removeAttribute('aria-hidden');
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
            sidebarToggle.setAttribute('aria-expanded', 'false');
            sidebarToggle.setAttribute('aria-label', 'Buka navigasi');
            document.body.classList.remove('overflow-hidden');

            return;
        }

        closeSidebar();
    };

    sidebarToggle.addEventListener('click', () => {
        if (sidebarToggle.getAttribute('aria-expanded') === 'true') {
            closeSidebar(true);
        } else {
            openSidebar();
        }
    });

    sidebarOverlay.addEventListener('click', () => closeSidebar(true));
    sidebarClose?.addEventListener('click', () => closeSidebar(true));
    desktopSidebarQuery.addEventListener('change', (event) => syncSidebar(event.matches));
    syncSidebar(desktopSidebarQuery.matches);
}

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    if (accountMenuToggle?.getAttribute('aria-expanded') === 'true') {
        closeAccountMenu(true);
        return;
    }

    if (sidebarToggle?.getAttribute('aria-expanded') === 'true') {
        sidebarClose?.click();
    }
});

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
