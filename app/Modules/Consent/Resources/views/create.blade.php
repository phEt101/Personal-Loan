@extends('layouts.app', ['title' => 'สร้างใบยินยอม'])

@section('content')
    <section class="dashboard">
        <div class="hero compact-hero">
            <h2>สร้างใบยินยอม</h2>
            <p>กรอกข้อมูลลูกค้าเพื่อสร้างใบยินยอมใหม่</p>
        </div>

        <div class="card form-card">
            <form method="POST" action="{{ route('consent.store') }}" class="consent-form">
                @csrf

                <div class="form-group">
                    <label for="name">ชื่อลูกค้า</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        placeholder="ระบุชื่อลูกค้า"
                        required
                    >
                    @error('name')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-actions">
                    <a href="{{ route('consent.index') }}" class="action-btn outline">ยกเลิก</a>
                    <button type="submit" class="action-btn">สร้างใบยินยอม</button>
                </div>
            </form>
        </div>
    </section>
@endsection
