@extends('layouts.app', ['title' => __('profile::messages.page_title')])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}?v={{ filemtime(public_path('css/profile.css')) }}">
@endpush

@section('content')
    <section class="dashboard profile-page">
        <div class="hero compact-hero">
            <div class="hero-body">
                <h2>{{ __('profile::messages.page_title') }}</h2>
                <p>{{ __('profile::messages.subtitle') }}</p>
            </div>
        </div>

        <div class="profile-card">
            <div class="profile-card-heading">
                <div class="profile-page-avatar">{{ mb_strtoupper(mb_substr($user->full_name, 0, 1)) }}</div>
                <div>
                    <h3>{{ $user->full_name }}</h3>
                    <p>{{ $user->email }}</p>
                </div>
            </div>

            @if (session('status'))
                <div class="module-alert module-alert-success" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="module-alert module-alert-error" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" class="module-form">
                @csrf
                @method('PUT')

                <h4>{{ __('profile::messages.account_information') }}</h4>

                <div class="module-form-grid">
                    <div class="module-field">
                        <label for="profile_first_name">{{ __('profile::messages.first_name') }} <span class="required-asterisk">*</span></label>
                        <input id="profile_first_name" name="first_name" type="text" value="{{ old('first_name', $user->first_name) }}" maxlength="100" required autocomplete="given-name">
                        @error('first_name') <span class="module-field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="module-field">
                        <label for="profile_last_name">{{ __('profile::messages.last_name') }} <span class="required-asterisk">*</span></label>
                        <input id="profile_last_name" name="last_name" type="text" value="{{ old('last_name', $user->last_name) }}" maxlength="100" required autocomplete="family-name">
                        @error('last_name') <span class="module-field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="module-field module-field-full">
                        <label for="profile_email">{{ __('profile::messages.email') }} <span class="required-asterisk">*</span></label>
                        <input id="profile_email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="191" required autocomplete="email">
                        @error('email') <span class="module-field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="module-field">
                        <div class="module-field-label-row">
                            <label for="profile_password">{{ __('profile::messages.password') }}</label>
                            <small>{{ __('profile::messages.password_hint') }}</small>
                        </div>
                        <input id="profile_password" name="password" type="password" minlength="8" autocomplete="new-password">
                        @error('password') <span class="module-field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="module-field">
                        <label for="profile_password_confirmation">{{ __('profile::messages.password_confirmation') }}</label>
                        <input id="profile_password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password">
                    </div>
                    <div class="module-field module-field-full">
                        <label for="profile_note">{{ __('profile::messages.note') }}</label>
                        <textarea id="profile_note" name="note" rows="4">{{ old('note', $user->note) }}</textarea>
                        @error('note') <span class="module-field-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="module-form-actions">
                    <button type="submit" class="action-btn">{{ __('profile::messages.save') }}</button>
                </div>
            </form>
        </div>
    </section>
@endsection
