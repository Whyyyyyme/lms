(function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (csrfToken) {
        window.Laravel = window.Laravel || {};
        window.Laravel.csrfToken = csrfToken;
    }

    function dispatchLivewire(eventName, detail = {}) {
        if (!window.Livewire) {
            return;
        }

        if (typeof window.Livewire.dispatch === 'function') {
            window.Livewire.dispatch(eventName, detail);
            return;
        }

        if (typeof window.Livewire.emit === 'function') {
            window.Livewire.emit(eventName, detail);
        }
    }

    document.addEventListener('click', function (event) {
        const panel = document.querySelector('[data-notification-dropdown-panel]');

        if (!panel) {
            return;
        }

        const clickedInsidePanel = panel.contains(event.target);
        const clickedToggle = event.target.closest('[data-notification-dropdown-toggle]');

        if (!clickedInsidePanel && !clickedToggle) {
            dispatchLivewire('notification-dropdown-close');
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            dispatchLivewire('notification-dropdown-close');
        }
    });
})();
