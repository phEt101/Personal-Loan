<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>รายงานใบยินยอม</title>
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
                    <div>
                        <h1>รายงาน: ใบยินยอม</h1>
                        <p class="muted">สรุปภาพรวมการเซ็นใบยินยอมของลูกค้า</p>
                    </div>
                    <div class="hero-actions">
                        <a href="#" class="btn btn-ghost">Export CSV</a>
                    </div>
                </div>

                <section class="summary-cards compact-summary">
                    <div class="summary-card">
                        <div class="summary-label">ลูกค้าทั้งหมด</div>
                        <div class="summary-value">{{ $total ?? 0 }}</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">ผ่านใบยินยอม</div>
                        <div class="summary-value">{{ $signed ?? 0 }}</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-label">ยังไม่ผ่าน</div>
                        <div class="summary-value">{{ $failed ?? (($total ?? 0) - ($signed ?? 0)) }}</div>
                    </div>
                </section>

                <div class="card consent-table">
                    <h3>รายการลูกค้าที่เซ็นใบยินยอม</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>ชื่อ</th>
                                <th>อีเมล / โทรศัพท์</th>
                                <th>วันที่เซ็น</th>
                                <th>สถานะ</th>
                                <th>การกระทำ</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($customers as $c)
                            <tr>
                                <td>{{ $c->id }}</td>
                                <td>{{ $c->name }}</td>
                                <td>{{ $c->email }} {{ $c->phone ? ' / '.$c->phone : '' }}</td>
                                <td>{{ isset($c->consent_signed_at) ? \Illuminate\Support\Str::limit($c->consent_signed_at, 19) : '-' }}</td>
                                <td>
                                    @if(isset($c->consent_signed) && $c->consent_signed)
                                        <span class="badge badge-signed">เซ็นแล้ว</span>
                                    @else
                                        <span class="badge" style="background:#fef3c7;color:#92400e">ยังไม่เซ็น</span>
                                    @endif
                                </td>
                                <td><a class="action-link" href="#">ดู</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="muted">ไม่มีข้อมูล</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script>
        document.addEventListener('click', function(e){
            var dropdown = document.getElementById('userDropdown');
            if (!dropdown) return;
            if (!dropdown.classList.contains('open')) return;
            var toggle = document.getElementById('userToggle');
            if (toggle && (toggle.contains(e.target) || dropdown.contains(e.target))) return;
            dropdown.classList.remove('open');
        });
    </script>
</body>
</html>
