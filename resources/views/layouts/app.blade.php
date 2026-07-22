<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <title>{{ $title ?? __('messages.layout.title') }}</title>
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    <link rel="stylesheet" href="{{ asset('css/consent.css') }}">
</head>
<body>
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
        }

        function toggleUserDropdown(event) {
            event.stopPropagation();
            userDropdown.classList.toggle('open');
        }

        menuToggle.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);
        userToggle?.addEventListener('click', toggleUserDropdown);
        document.addEventListener('click', (event) => {
            if (!userDropdown.contains(event.target) && !userToggle.contains(event.target)) {
                userDropdown.classList.remove('open');
            }
        });
    </script>

    <script>
        // Global openAttachmentPreview used by multiple modules
        window.openAttachmentPreview = window.openAttachmentPreview || function(ev, url, mime, name) {
            try { ev && ev.preventDefault(); } catch (e) {}
            const modal = document.getElementById('attachmentPreviewModal');
            const body = document.getElementById('attachmentPreviewContent');
            if (!modal || !body) { window.open(url, '_blank', 'noopener'); return; }
            body.innerHTML = '';
            const titleHtml = `<h4 style="margin-top:0;margin-bottom:8px">${(name||'')}</h4>`;
            if ((mime||'').startsWith('image/') || url.match(/\.(png|jpe?g|gif)(\?|$)/i)) {
                const img = document.createElement('img'); img.src = url; img.style.maxWidth='100%'; img.style.height='auto'; body.innerHTML = titleHtml; body.appendChild(img);
            } else if ((mime||'').includes('pdf') || url.toLowerCase().endsWith('.pdf')) {
                const iframe = document.createElement('iframe'); iframe.src = url; iframe.style.width='100%'; iframe.style.height='75vh'; iframe.setAttribute('title', name||'Preview'); body.innerHTML = titleHtml; body.appendChild(iframe);
            } else {
                body.innerHTML = titleHtml + `<a href="${url}" download="${name||'file'}">ดาวน์โหลดไฟล์</a>`;
            }
            modal.style.display = 'flex'; modal.offsetHeight; modal.classList.add('show');
            document.getElementById('closeAttachmentPreviewModal')?.addEventListener('click', () => { modal.classList.remove('show'); modal.style.display='none'; body.innerHTML=''; });
            document.getElementById('attachmentPreviewCloseFooter')?.addEventListener('click', () => { modal.classList.remove('show'); modal.style.display='none'; body.innerHTML=''; });
        };
    </script>
</body>
</html>
