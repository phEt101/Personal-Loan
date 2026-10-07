<div id="appToast" class="app-toast" role="status" aria-live="polite" hidden>
    <span id="appToastMessage"></span>
</div>

<script>
    (() => {
        const toast = document.getElementById('appToast');
        const message = document.getElementById('appToastMessage');
        let dismissTimer;

        window.showToast = (text, type = 'success') => {
            if (!toast || !message || !text) return;

            window.clearTimeout(dismissTimer);
            message.textContent = text;
            toast.classList.toggle('is-error', type === 'error');
            toast.hidden = false;
            requestAnimationFrame(() => toast.classList.add('is-visible'));
            dismissTimer = window.setTimeout(() => {
                toast.classList.remove('is-visible');
                window.setTimeout(() => { toast.hidden = true; }, 200);
            }, 4000);
        };

        window.showToast(
            @json($toastMessage ?? session('status')),
            @json($toastType ?? 'success')
        );
    })();
</script>
