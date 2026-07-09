<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('auth::messages.heading') }} | {{ __('messages.layout.title') }}</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body class="auth-page">
    <div class="auth-orb auth-orb-left"></div>
    <div class="auth-orb auth-orb-right"></div>

    <main class="auth-shell">
        <section class="auth-marketing">
            <div class="auth-brand">{{ __('messages.layout.title') }}</div>
            <p class="auth-kicker">{{ __('auth::messages.kicker') }}</p>
            <h1>{{ __('auth::messages.heading') }}</h1>


        </section>

        <section class="auth-panel">
            <div class="auth-panel-header">
                <div class="auth-panel-heading">
                    <p class="auth-panel-label">{{ __('auth::messages.panel_label') }}</p>
                    <h2>{{ __('auth::messages.panel_title') }}</h2>
                </div>

                <div class="auth-language">
                    <select aria-label="{{ __('auth::messages.language_label') }}" onchange="if (this.value) window.location.href = this.value;">
                        <option value="{{ route('locale.switch', 'th') }}" @selected(app()->getLocale() === 'th')>{{ __('messages.language.th') }}</option>
                        <option value="{{ route('locale.switch', 'en') }}" @selected(app()->getLocale() === 'en')>{{ __('messages.language.en') }}</option>
                    </select>
                </div>
            </div>

            @if (session('status'))
                <div class="auth-alert auth-alert-success">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="auth-alert auth-alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" class="auth-form">
                @csrf

                <div class="auth-field">
                    <label for="email">{{ __('auth::messages.email_label') }}</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="{{ __('auth::messages.email_placeholder') }}"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="auth-field">
                    <label for="password">{{ __('auth::messages.password_label') }}</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="{{ __('auth::messages.password_placeholder') }}"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <label class="auth-remember">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                    <span>{{ __('auth::messages.remember_me') }}</span>
                </label>

                <button type="submit" class="auth-submit">{{ __('auth::messages.sign_in') }}</button>
            </form>
        </section>
    </main>
</body>
</html>
