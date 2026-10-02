@extends('layouts.app', ['title' => __('settings::messages.edit_title')])

@section('content')
    <section class="dashboard settings-user-page">
        <div class="hero compact-hero">
            <div class="hero-body">
                <h2>{{ __('settings::messages.edit_title') }}</h2>
                <p>{{ __('settings::messages.edit_subtitle') }}</p>
            </div>
        </div>

        <div class="settings-create-back">
            <a href="{{ route('settings.users.index') }}" class="action-btn outline">← {{ __('settings::messages.all_users') }}</a>
        </div>

        <div class="settings-user-card">
            <h3>{{ $user->employee_code }} — {{ $user->full_name }}</h3>

            @if ($errors->any())
                <div class="profile-alert profile-alert-error" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('settings.users.update', $user) }}" class="profile-form">
                @csrf
                @method('PUT')

                <div class="profile-form-grid">
                    <div class="profile-field profile-field-full">
                        <label for="setting_edit_role">{{ __('settings::messages.role') }} <span class="required-asterisk">*</span></label>
                        <select id="setting_edit_role" name="role" required @disabled($user->employee_code === 'EMP0001')>
                            <option value="user" @selected(old('role', $user->role) === 'user')>{{ __('settings::messages.role_user') }}</option>
                            <option value="admin" @selected(old('role', $user->role) === 'admin')>{{ __('settings::messages.role_admin') }}</option>
                        </select>
                        @if ($user->employee_code === 'EMP0001')
                            <input type="hidden" name="role" value="admin">
                            <small>{{ __('settings::messages.primary_admin_role_locked') }}</small>
                        @endif
                        @error('role') <span class="profile-field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="profile-field profile-field-full">
                        <label for="setting_edit_email">{{ __('settings::messages.email') }} <span class="required-asterisk">*</span></label>
                        <input id="setting_edit_email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="191" required autocomplete="email">
                        @error('email') <span class="profile-field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="profile-field">
                        <div class="profile-field-label-row">
                            <label for="setting_edit_password">{{ __('settings::messages.password') }}</label>
                            <small>{{ __('profile::messages.password_hint') }}</small>
                        </div>
                        <input id="setting_edit_password" name="password" type="password" minlength="8" autocomplete="new-password">
                        @error('password') <span class="profile-field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="profile-field">
                        <label for="setting_edit_password_confirmation">{{ __('settings::messages.password_confirmation') }}</label>
                        <input id="setting_edit_password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password">
                    </div>
                </div>

                <div class="profile-form-actions">
                    <button type="submit" class="action-btn">{{ __('settings::messages.save_changes') }}</button>
                </div>
            </form>
        </div>
    </section>
@endsection
