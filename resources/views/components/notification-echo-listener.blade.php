@auth
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const userId = @json(auth()->id());
            const pollingEnabled = @json((bool) config('lms-notifications.polling_enabled', true));
            const pollingInterval = Math.max(
                10000,
                Number(@json((int) config('lms-notifications.polling_interval_ms', 30000))) || 30000
            );

            if (!window.Livewire || !userId) {
                return;
            }

            const refreshDropdown = (event = {}) => {
                window.Livewire.dispatch('notifikasi-baru', event);
            };

            if (window.Echo) {
                window.Echo.private(`lms.notifications.${userId}`)
                    .listen('.notifikasi-baru', (event) => {
                        refreshDropdown(event);
                    });

                return;
            }

            // Fallback untuk hosting tanpa websocket/Echo.
            // Dropdown tetap refresh berkala, tetapi tidak butuh queue worker/realtime server.
            if (pollingEnabled) {
                window.setInterval(() => refreshDropdown(), pollingInterval);
            }
        });
    </script>
@endauth
