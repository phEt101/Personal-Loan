<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <title>{{ $title ?? __('messages.layout.title') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('icon/S__.ico') }}?v={{ filemtime(public_path('icon/S__.ico')) }}">
    <link rel="stylesheet" href="{{ asset('vendor/choices/choices.min.css') }}?v={{ filemtime(public_path('vendor/choices/choices.min.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/modules.css') }}?v={{ filemtime(public_path('css/modules.css')) }}">
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/toast.css') }}?v={{ filemtime(public_path('css/toast.css')) }}">
</head>
<body
    data-session-expired-message="{{ __('auth::messages.session_expired') }}"
    data-login-url="{{ route('login') }}"
>
    <div class="layout">
        @include('partials.sidebar')

        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <main class="content">
            @include('partials.topbar')

            @yield('content')

            <section class="footer">
                {{ __('messages.layout.footer') }}
            </section>
        </main>
    </div>

    @include('partials.toast')

    <script>
        (() => {
            const nativeFetch = window.fetch.bind(window);
            let sessionRedirecting = false;

            window.fetch = async (...args) => {
                const response = await nativeFetch(...args);
                if (![401, 419].includes(response.status)) return response;

                if (!sessionRedirecting) {
                    sessionRedirecting = true;
                    let payload = {};
                    try {
                        payload = await response.clone().json();
                    } catch (error) {
                        payload = {};
                    }

                    window.alert(payload.message || document.body.dataset.sessionExpiredMessage);
                    const loginUrl = new URL(payload.login_url || document.body.dataset.loginUrl, window.location.origin);
                    loginUrl.searchParams.set('redirect', `${window.location.pathname}${window.location.search}${window.location.hash}`);
                    window.location.assign(loginUrl);
                }

                return new Promise(() => {});
            };
        })();
    </script>

    <!-- Global attachment preview modal -->
    <div id="attachmentPreviewModal" class="modal">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h3 class="modal-title">Preview</h3>
                <button type="button" class="close-btn" id="closeAttachmentPreviewModal" aria-label="Close modal">&times;</button>
            </div>
            <div class="modal-body modal-body--pdf">
                <div class="pdf-content-padding" id="attachmentPreviewContent"></div>
            </div>
            <div class="modal-footer">
                <div class="form-actions modal-form-actions">
                    <button type="button" class="action-btn outline" id="attachmentPreviewCloseFooter">ปิด</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const userToggle = document.getElementById('userToggle');
        const userDropdown = document.getElementById('userDropdown');

        function toggleSidebar() {
            sidebar.classList.toggle('open');
            document.body.classList.toggle('sidebar-is-open', sidebar.classList.contains('open'));
            menuToggle.setAttribute('aria-expanded', sidebar.classList.contains('open') ? 'true' : 'false');
        }

        function closeSidebar() {
            sidebar.classList.remove('open');
            document.body.classList.remove('sidebar-is-open');
            menuToggle.setAttribute('aria-expanded', 'false');
        }

        function toggleUserDropdown(event) {
            event.stopPropagation();
            userDropdown.classList.toggle('open');
        }

        menuToggle.addEventListener('click', toggleSidebar);
        menuToggle.setAttribute('aria-controls', 'sidebar');
        menuToggle.setAttribute('aria-expanded', 'false');
        sidebarOverlay.addEventListener('click', closeSidebar);
        sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeSidebar));
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeSidebar();
        });
        document.addEventListener('submit', (event) => {
            const form = event.target.closest('form[data-confirm]');
            if (form && !window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        }, true);
        userToggle?.addEventListener('click', toggleUserDropdown);
        document.addEventListener('click', (event) => {
            if (!userDropdown.contains(event.target) && !userToggle.contains(event.target)) {
                userDropdown.classList.remove('open');
            }
        });
    </script>

    <script>
        // Global openAttachmentPreview used by multiple modules
        window.openAttachmentPreview = window.openAttachmentPreview || function(ev, url, mime, name, onClose) {
            try { ev && ev.preventDefault(); } catch (e) {}
            const modal = document.getElementById('attachmentPreviewModal');
            const body = document.getElementById('attachmentPreviewContent');
            if (!modal || !body) { window.open(url, '_blank', 'noopener'); return; }

            if (typeof modal._attachmentPreviewCleanup === 'function') {
                modal._attachmentPreviewCleanup();
            }
            modal._attachmentPreviewCleanup = typeof onClose === 'function' ? onClose : null;
            body.replaceChildren();

            const title = document.createElement('h4');
            title.style.margin = '0 0 8px';
            title.textContent = name || '';
            body.appendChild(title);

            if ((mime||'').startsWith('image/') || url.match(/\.(png|jpe?g|gif)(\?|$)/i)) {
                const img = document.createElement('img'); img.src = url; img.style.maxWidth='100%'; img.style.height='auto'; img.alt = name || ''; body.appendChild(img);
            } else if ((mime||'').includes('pdf') || url.toLowerCase().endsWith('.pdf')) {
                const iframe = document.createElement('iframe'); iframe.src = url; iframe.style.width='100%'; iframe.style.height='75vh'; iframe.setAttribute('title', name||'Preview'); body.appendChild(iframe);
            } else {
                const download = document.createElement('a'); download.href = url; download.download = name || 'file'; download.textContent = name || 'ดาวน์โหลดไฟล์'; body.appendChild(download);
            }
            modal.style.display = 'flex'; modal.offsetHeight; modal.classList.add('show');
            const closePreview = () => {
                modal.classList.remove('show');
                modal.style.display = 'none';
                body.replaceChildren();
                if (typeof modal._attachmentPreviewCleanup === 'function') modal._attachmentPreviewCleanup();
                modal._attachmentPreviewCleanup = null;
            };
            document.getElementById('closeAttachmentPreviewModal').onclick = closePreview;
            document.getElementById('attachmentPreviewCloseFooter').onclick = closePreview;
        };
    </script>
</body>
</html>
