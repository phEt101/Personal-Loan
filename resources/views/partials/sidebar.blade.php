<aside class="sidebar" id="sidebar">
    <div>
        <div class="brand">
            <img class="brand-icon" src="{{ asset('icon/S__.ico') }}?v={{ filemtime(public_path('icon/S__.ico')) }}" alt="" aria-hidden="true">
            <span>{{ __('messages.layout.title') }}</span>
        </div>
        <nav class="nav-group">
            <a href="{{ route('customer-history.index') }}" class="nav-item {{ Request::routeIs('customer-history.*') ? 'active' : '' }}">
                <span class="nav-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="9" cy="8" r="3" fill="#10b981"/>
                        <path d="M3.5 19c.7-3 2.5-4.5 5.5-4.5s4.8 1.5 5.5 4.5H3.5z" fill="#10b981"/>
                        <path d="M16 11h5M18.5 8.5V14" stroke="#ffffff" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>{{ __('messages.nav.customer_history') }}</span>
            </a>
            @if (auth()->user()?->isAdmin())
                <details class="nav-section" @if(Request::routeIs('settings.*')) open @endif>
                    <summary class="nav-item {{ Request::routeIs('settings.*') ? 'active' : '' }}">
                        <span class="nav-icon nav-icon-settings" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 15.25A3.25 3.25 0 1 0 12 8.75a3.25 3.25 0 0 0 0 6.5Z" stroke="currentColor" stroke-width="1.8"/>
                                <path d="m19.4 13.5 1.1.86-1.7 2.94-1.3-.52a7.9 7.9 0 0 1-1.5.87l-.2 1.38h-3.4l-.2-1.38a7.9 7.9 0 0 1-1.5-.87l-1.3.52-1.7-2.94 1.1-.86a8.2 8.2 0 0 1 0-1.74l-1.1-.86 1.7-2.94 1.3.52a7.9 7.9 0 0 1 1.5-.87l.2-1.38h3.4l.2 1.38a7.9 7.9 0 0 1 1.5.87l1.3-.52 1.7 2.94-1.1.86a8.2 8.2 0 0 1 0 1.74Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span>{{ __('messages.nav.settings') }}</span>
                        <span class="nav-chevron" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </summary>
                    <div class="nav-submenu">
                        <a href="{{ route('settings.users.index') }}" class="nav-subitem {{ Request::routeIs('settings.users.*') ? 'active' : '' }}">
                            <span class="nav-subitem-dot" aria-hidden="true"></span>
                            <span>{{ __('messages.nav.users') }}</span>
                        </a>
                    </div>
                </details>
            @endif
        </nav>
    </div>
</aside>
