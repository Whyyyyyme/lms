@auth
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const userId = @json(auth()->id());

            if (!window.Echo || !window.Livewire || !userId) {
                return;
            }

            window.Echo.private(`lms.notifications.${userId}`)
                .listen('.notifikasi-baru', (event) => {
                    window.Livewire.dispatch('notifikasi-baru', event);
                });
        });
    </script>
@endauth
