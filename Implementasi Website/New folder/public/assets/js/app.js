/* =====================================================
   ToySight - Smart Analytics for Toy Retail Business
   Main JS: sidebar, modals, toasts, shortcuts
   ===================================================== */
(function () {
    'use strict';

    // ============== SIDEBAR =================
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarCollapse = document.getElementById('sidebarCollapse');
    const body = document.body;

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            if (window.innerWidth <= 880) {
                body.classList.toggle('sidebar-open');
            } else {
                body.classList.toggle('sidebar-collapsed');
                try { localStorage.setItem('ts_sidebar_collapsed', body.classList.contains('sidebar-collapsed') ? '1' : '0'); } catch (e) {}
            }
        });
    }
    if (sidebarCollapse) {
        sidebarCollapse.addEventListener('click', function (e) {
            e.preventDefault();
            body.classList.toggle('sidebar-collapsed');
            try { localStorage.setItem('ts_sidebar_collapsed', body.classList.contains('sidebar-collapsed') ? '1' : '0'); } catch (e) {}
        });
    }
    try {
        if (localStorage.getItem('ts_sidebar_collapsed') === '1' && window.innerWidth > 880) {
            body.classList.add('sidebar-collapsed');
        }
    } catch (e) {}

    // Close mobile sidebar when clicking outside
    document.addEventListener('click', function (e) {
        if (window.innerWidth <= 880 && body.classList.contains('sidebar-open')) {
            const sidebar = document.querySelector('.sidebar');
            if (sidebar && !sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                body.classList.remove('sidebar-open');
            }
        }
    });

    // ============== MODAL =================
    window.openModal = function (name) {
        const m = document.getElementById('modal-' + name);
        if (m) {
            m.classList.add('open');
            document.body.style.overflow = 'hidden';
            const firstInput = m.querySelector('input:not([type=hidden]), select, textarea');
            if (firstInput) setTimeout(() => firstInput.focus(), 60);
        }
    };
    window.closeModal = function (name) {
        const m = document.getElementById('modal-' + name);
        if (m) {
            m.classList.remove('open');
            document.body.style.overflow = '';
        }
    };
    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                overlay.classList.remove('open');
                document.body.style.overflow = '';
            }
        });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.open').forEach(function (m) {
                m.classList.remove('open');
            });
            document.body.style.overflow = '';
        }
    });

    // ============== TOAST AUTO-HIDE =================
    setTimeout(function () {
        document.querySelectorAll('.toast').forEach(function (toast) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(20px)';
            setTimeout(() => toast.remove(), 350);
        });
    }, 4500);
    document.querySelectorAll('.toast-close').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const toast = btn.closest('.toast');
            if (toast) {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 200);
            }
        });
    });

    // ============== SHORTCUTS =================
    document.addEventListener('keydown', function (e) {
        // Cmd/Ctrl + K -> focus search
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            const search = document.getElementById('globalSearch');
            if (search) search.focus();
        }
    });

    // ============== GLOBAL SEARCH (basic in-page) =================
    const globalSearch = document.getElementById('globalSearch');
    if (globalSearch) {
        globalSearch.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                const v = globalSearch.value.trim();
                if (!v) return;
                // Try to match common pages
                const path = window.location.search.includes('r=products') ? 'products'
                           : window.location.search.includes('r=sales')    ? 'sales'
                           : window.location.search.includes('r=stores')   ? 'stores'
                           : 'products';
                const base = window.location.pathname;
                window.location.href = base + '?r=' + path + '&q=' + encodeURIComponent(v);
            }
        });
    }

    // ============== AUTO-SUBMIT FORMS WITH .auto-submit =================
    document.querySelectorAll('form.auto-submit').forEach(function (f) {
        f.querySelectorAll('select, input[type=date]').forEach(function (input) {
            input.addEventListener('change', () => f.submit());
        });
    });
})();
