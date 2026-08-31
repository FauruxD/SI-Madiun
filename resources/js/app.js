const sidebar = document.querySelector('[data-sidebar]');
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
