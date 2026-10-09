@extends('layouts.app', ['title' => __('settings::messages.page_title')])

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/settings.css') }}?v={{ filemtime(public_path('css/settings.css')) }}">
@endpush

@section('content')
    <section class="dashboard module-page">
        <div class="hero compact-hero">
            <div class="hero-body">
                <h2>{{ __('settings::messages.page_title') }}</h2>
                <p>{{ __('settings::messages.subtitle') }}</p>
            </div>
        </div>

        <div class="settings-create-back">
            <a href="{{ route('settings.users.index') }}" class="action-btn outline">← {{ __('settings::messages.all_users') }}</a>
        </div>

        <div class="module-card">
            <h3>{{ __('settings::messages.user_information') }}</h3>

            @if (session('status'))
                <output class="module-alert module-alert-success">{{ session('status') }}</output>
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

            <form method="POST" action="{{ route('settings.users.store') }}" class="module-form">
                @csrf

                <div class="module-form-grid">
                    <div class="module-field">
                        <label for="setting_user_type">{{ __('settings::messages.user_type') }} <span class="required-asterisk">*</span></label>
                        <select id="setting_user_type" name="user_type" required>
                            <option value="">{{ __('settings::messages.select_user_type') }}</option>
                            <option value="internal" @selected(old('user_type') === 'internal')>{{ __('settings::messages.internal') }}</option>
                            <option value="external" @selected(old('user_type') === 'external')>{{ __('settings::messages.external') }}</option>
                        </select>
                        @error('user_type') <span class="module-field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="module-field">
                        <label for="setting_role">{{ __('settings::messages.role') }} <span class="required-asterisk">*</span></label>
                        <select id="setting_role" name="role_id" required>
                            <option value="">{{ __('settings::messages.select_role') }}</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected((string) old('role_id', $defaultRoleId) === (string) $role->id)>{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                        @error('role_id') <span class="module-field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="module-field">
                        <label for="setting_first_name">{{ __('settings::messages.first_name') }} <span class="required-asterisk">*</span></label>
                        <input id="setting_first_name" name="first_name" type="text" value="{{ old('first_name') }}" maxlength="100" required autocomplete="given-name">
                        @error('first_name') <span class="module-field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="module-field">
                        <label for="setting_last_name">{{ __('settings::messages.last_name') }} <span class="required-asterisk">*</span></label>
                        <input id="setting_last_name" name="last_name" type="text" value="{{ old('last_name') }}" maxlength="100" required autocomplete="family-name">
                        @error('last_name') <span class="module-field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="module-field module-field-full">
                        <label for="setting_email">{{ __('settings::messages.email') }} <span class="required-asterisk">*</span></label>
                        <input id="setting_email" name="email" type="email" value="{{ old('email') }}" maxlength="191" required autocomplete="email">
                        @error('email') <span class="module-field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="module-field">
                        <div class="module-field-label-row">
                            <label for="setting_password">{{ __('settings::messages.password') }} <span class="required-asterisk">*</span></label>
                            <small>{{ __('settings::messages.password_hint') }}</small>
                        </div>
                        <input id="setting_password" name="password" type="password" minlength="8" required autocomplete="new-password">
                        @error('password') <span class="module-field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="module-field">
                        <label for="setting_password_confirmation">{{ __('settings::messages.password_confirmation') }} <span class="required-asterisk">*</span></label>
                        <input id="setting_password_confirmation" name="password_confirmation" type="password" minlength="8" required autocomplete="new-password">
                    </div>
                    <div class="module-field module-field-full">
                        <label for="setting_note">{{ __('settings::messages.note') }}</label>
                        <textarea id="setting_note" name="note" rows="4">{{ old('note') }}</textarea>
                        @error('note') <span class="module-field-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="module-form-actions">
                    <button type="submit" class="action-btn">{{ __('settings::messages.save') }}</button>
                </div>
            </form>
        </div>
    </section>
@endsection
