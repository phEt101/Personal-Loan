@extends('layouts.app', ['title' => 'รายงานใบยินยอม'])

@section('content')
    <section class="dashboard">
        <div class="hero compact-hero hero-with-actions">
            <div>
                <h2>รายงานใบยินยอม</h2>
                <p>สรุปสถานะการเซ็นใบยินยอมของลูกค้า</p>
            </div>
            <div class="hero-actions">
                <a href="{{ route('consent.create') }}" class="action-btn">+ สร้างใบยินยอม</a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

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
                        @forelse($customers as $customer)
                            <tr>
                                <td>{{ $customer->code ?? $customer->id }}</td>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->signed_at ?? '-' }}</td>
                                <td>
                                    @if($customer->signed)
                                        <span class="badge badge-signed">Signed</span>
                                    @else
                                        <span class="badge badge-pending">ยังไม่เซ็น</span>
                                    @endif
                                </td>
                                <td><a href="#" class="action-link">ดูเอกสาร</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-cell">ไม่มีข้อมูล</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection
