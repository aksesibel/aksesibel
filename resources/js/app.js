document.addEventListener('submit', (event) => {
    const message = event.target.dataset.confirm;
    if (message && !window.confirm(message)) {
        event.preventDefault();
    }
});

const workspace = document.querySelector('[data-workspace]');

if (workspace) {
    const sidebar = document.getElementById('workspace-navigation');
    const content = document.getElementById('workspace-content');
    const toggle = workspace.querySelector('[data-menu-toggle]');
    const backdrop = workspace.querySelector('.sidebar-backdrop');
    const desktop = window.matchMedia('(min-width: 1024px)');

    const setMenuOpen = (open, restoreFocus = true) => {
        sidebar.toggleAttribute('data-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Tutup navigasi' : 'Buka navigasi');
        backdrop.hidden = !open;
        content.inert = open;
        workspace.style.overflow = open ? 'hidden' : '';

        if (open) {
            sidebar.querySelector('[data-menu-close]').focus();
        } else if (restoreFocus) {
            toggle.focus();
        }
    };

    toggle.addEventListener('click', () => setMenuOpen(!sidebar.hasAttribute('data-open')));
    workspace.querySelectorAll('[data-menu-close]').forEach((button) => {
        button.addEventListener('click', () => setMenuOpen(false));
    });
    document.addEventListener('keydown', (event) => {
        if (!sidebar.hasAttribute('data-open')) {
            return;
        }

        if (event.key === 'Escape') {
            setMenuOpen(false);
        }

        if (event.key === 'Tab') {
            const links = [...sidebar.querySelectorAll('a[href], button')];
            const first = links[0];
            const last = links.at(-1);

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });
    desktop.addEventListener('change', () => setMenuOpen(false, false));
}

document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('.alert-dismissible');

    alerts.forEach((alert) => {
        let hideTimeout;

        const hideAlert = () => {
            if (alert.classList.contains('is-hiding')) return;

            clearTimeout(hideTimeout);
            alert.classList.add('is-hiding');

            setTimeout(() => {
                alert.remove();
            }, 300);
        };

        // Hilang otomatis setelah 4 detik
        hideTimeout = setTimeout(hideAlert, 4000);

        // Tutup ketika tombol X diklik
        const closeButton = alert.querySelector('.alert-close');

        closeButton?.addEventListener('click', hideAlert);
    });
});