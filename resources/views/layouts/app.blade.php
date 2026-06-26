<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Personal Loan' }}</title>
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
</head>
<body>
    <div class="layout">
        @include('partials.sidebar')

        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <main class="content">
            @include('partials.topbar')

            @yield('content')

            <section class="footer">
                © 2026 Personal Loan
            </section>
        </main>
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
</body>
</html>
