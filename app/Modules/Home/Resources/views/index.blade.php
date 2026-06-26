<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>หน้าหลัก Home Module</title>
    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
</head>
<body>
    <div class="layout">
        @include('partials.sidebar')

        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <main class="content">
            @include('partials.topbar')

            <section class="dashboard">
                <div class="hero compact-hero">
                    <h2>รายงานใบยินยอม</h2>
                    <p>สรุปสถานะการเซ็นใบยินยอมของลูกค้า</p>
                </div>

                @php
                    $customers = [
                        ['id' => 'CUST-001', 'name' => 'สมชาย ใจดี', 'signed_at' => '2026-06-20', 'status' => 'Signed'],
                        ['id' => 'CUST-002', 'name' => 'สมหญิง ปิติ', 'signed_at' => '2026-06-21', 'status' => 'Signed'],
                        ['id' => 'CUST-003', 'name' => 'จักริน ทองดี', 'signed_at' => '2026-06-22', 'status' => 'Signed'],
                    ];
                    $total = count($customers);
                    $signed = collect($customers)->where('status', 'Signed')->count();
                    $failed = $total - $signed;
                @endphp

                <section class="summary-cards compact-summary">
                    <div class="summary-card">
                        <div class="summary-label">ลูกค้าทั้งหมด</div>
                        <div class="summary-value">{{ $total }}</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">ผ่านใบยินยอม</div>
                        <div class="summary-value">{{ $signed }}</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">ยังไม่ผ่าน</div>
                        <div class="summary-value">{{ $failed }}</div>
                    </div>
                </section>

                <div class="card">
                    <div class="consent-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>รหัส</th>
                                    <th>ชื่อ</th>
                                    <th>วันที่เซ็น</th>
                                    <th>สถานะ</th>
                                    <th>การกระทำ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customers as $c)
                                    <tr>
                                        <td>{{ $c['id'] }}</td>
                                        <td>{{ $c['name'] }}</td>
                                        <td>{{ $c['signed_at'] }}</td>
                                        <td><span class="badge badge-signed">{{ $c['status'] }}</span></td>
                                        <td><a href="#" class="action-link">ดูเอกสาร</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>


            <section class="footer">
                © 2026 Personal Loan - Home Module
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

        function closeUserDropdown() {
            userDropdown.classList.remove('open');
        }

        menuToggle.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);
        userToggle?.addEventListener('click', toggleUserDropdown);
        document.addEventListener('click', (event) => {
            if (!userDropdown.contains(event.target) && !userToggle.contains(event.target)) {
                closeUserDropdown();
            }
        });
    </script>
</body>
</html>
