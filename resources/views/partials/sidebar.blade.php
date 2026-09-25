<aside class="sidebar" id="sidebar">
    <div>
        <div class="brand">{{ __('messages.layout.title') }}</div>
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
        </nav>
    </div>
</aside>
