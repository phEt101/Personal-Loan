<div id="appToast" class="app-toast" role="status" aria-live="polite" hidden>
    <span id="appToastMessage"></span>
    <button
        id="appToastClose"
        class="app-toast-close"
        type="button"
        aria-label="{{ __('messages.toast.close') }}"
    >&times;</button>
</div>

<script>
    (() => {
        const toast = document.getElementById('appToast');
        const message = document.getElementById('appToastMessage');
        const closeButton = document.getElementById('appToastClose');
        let dismissTimer;
        let hideTimer;

        const dismissToast = () => {
            window.clearTimeout(dismissTimer);
            window.clearTimeout(hideTimer);
            toast?.classList.remove('is-visible');
            hideTimer = window.setTimeout(() => {
                if (toast) toast.hidden = true;
            }, 200);
        };

        window.showToast = (text, type = 'success') => {
            if (!toast || !message || !text) return;

            window.clearTimeout(dismissTimer);
            window.clearTimeout(hideTimer);
            message.textContent = text;
            toast.classList.toggle('is-error', type === 'error');
            toast.hidden = false;
            requestAnimationFrame(() => toast.classList.add('is-visible'));
            dismissTimer = window.setTimeout(dismissToast, 4000);
        };

        closeButton?.addEventListener('click', dismissToast);

        window.showToast(
            @json($toastMessage ?? session('status')),
            @json($toastType ?? 'success')
        );
    })();
</script>
