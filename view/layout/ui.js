document.addEventListener('DOMContentLoaded', function () {
    // Sidebar toggle: removed automatic hamburger insertion per UX request
    // If you want a toggle later, add a static button in the template and
    // listen for `.sidebar-toggle` clicks to toggle `.collapsed` on the sidebar.
    const topbar = document.querySelector('.topbar');
    const sidebar = document.querySelector('.sidebar');

    // Delegate confirmation for links with data-confirm
    document.body.addEventListener('click', function (e) {
        const el = e.target.closest('a[data-confirm]');
        if (!el) return;
        const msg = el.getAttribute('data-confirm') || '¿Estás seguro?';
        if (!confirm(msg)) {
            e.preventDefault();
        }
    });

    // Client-side validation for forms marked data-validate="true"
    document.querySelectorAll('form[data-validate="true"]').forEach(form => {
        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                form.reportValidity();
                form.dispatchEvent(new CustomEvent('form:invalid', { detail: {} }));
                return;
            }
            // if valid and not AJAX, allow normal submit
        });
    });

    // AJAX forms: forms with data-ajax="true" — validate first, then submit via fetch
    document.querySelectorAll('form[data-ajax="true"]').forEach(form => {
        form.addEventListener('submit', async function (e) {
            // run HTML5 validation first
            if (!form.checkValidity()) {
                e.preventDefault();
                form.reportValidity();
                form.dispatchEvent(new CustomEvent('ajax:invalid', { detail: {} }));
                return;
            }

            e.preventDefault();
            const url = form.action || window.location.href;
            const method = (form.method || 'GET').toUpperCase();
            const formData = new FormData(form);

            const options = { method };
            if (method !== 'GET') options.body = formData;

            try {
                const res = await fetch(url, options);
                const contentType = res.headers.get('content-type') || '';
                let data = null;
                if (contentType.includes('application/json')) data = await res.json();
                else data = await res.text();

                // dispatch custom event with response
                form.dispatchEvent(new CustomEvent('ajax:success', { detail: { status: res.status, data } }));
            } catch (err) {
                form.dispatchEvent(new CustomEvent('ajax:error', { detail: { error: err } }));
            }
        });
    });

    // Simple helper: attach confirm to delete buttons without data-confirm
    document.querySelectorAll('a.btn-danger, a.btn-primary-danger').forEach(a => {
        if (!a.hasAttribute('data-confirm')) {
            a.setAttribute('data-confirm', 'Confirmar acción');
        }
    });
});

// Expose small API for other scripts
window.AppUI = {
    toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) sidebar.classList.toggle('collapsed');
    }
};
