<aside class="sidebar" id="sidebar">
    <div>
        <div class="brand">{{ __('messages.layout.title') }}</div>
        <nav class="nav-group">
            <a href="{{ route('home.index') }}" class="nav-item {{ Request::routeIs('home.index') ? 'active' : '' }}">
                <span class="nav-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 10.5L12 4l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10.5z" fill="#10b981"/>
                    </svg>
                </span>
                <span>{{ __('messages.nav.home') }}</span>
            </a>
            <a href="{{ route('consent.index') }}" class="nav-item {{ Request::routeIs('consent.*') ? 'active' : '' }}">
                <span class="nav-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M7 2h7l5 5v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V2z" fill="#10b981"/>
                        <path d="M9 9h6v1H9V9zm0 3h6v1H9v-1z" fill="#ffffff" opacity="0.9"/>
                    </svg>
                </span>
                <span>{{ __('messages.nav.consent') }}</span>
            </a>
            {{--
            <a href="{{ route('consentreview.index') }}" class="nav-item {{ Request::routeIs('consentreview.*') ? 'active' : '' }}">
                <span class="nav-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4 4h16v4H4V4zM4 10h16v10H4V10z" fill="#10b981"/>
                    </svg>
                </span>
                <span>{{ __('messages.nav.consent_review') }}</span>
            </a>
            --}}
        </nav>
    </div>
</aside>
