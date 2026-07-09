<section class="topbar">
    <button class="menu-button" id="menuToggle">☰</button>
    <div class="topbar-actions">
        <div class="locale-switcher">
            <span class="locale-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#059669" stroke-width="2"/>
                    <path d="M2 12H22" stroke="#059669" stroke-width="2"/>
                    <path d="M12 2C14.5013 4.73857 15.8995 8.23859 15.8995 12C15.8995 15.7614 14.5013 19.2614 12 22C9.49872 19.2614 8.1005 15.7614 8.1005 12C8.1005 8.23859 9.49872 4.73857 12 2Z" stroke="#059669" stroke-width="2"/>
                </svg>
            </span>
            <select class="locale-select" aria-label="Language switcher" onchange="if (this.value) window.location.href = this.value;">
                <option value="{{ route('locale.switch', 'th') }}" @selected(app()->getLocale() === 'th')>{{ __('messages.language.th') }}</option>
                <option value="{{ route('locale.switch', 'en') }}" @selected(app()->getLocale() === 'en')>{{ __('messages.language.en') }}</option>
            </select>
        </div>
        <div class="user-box">
            <button class="user-toggle" id="userToggle" type="button">
                <div class="user-avatar-compact">{{ strtoupper(substr(auth()->user()->name ?? __('messages.layout.guest_name'), 0, 1)) }}</div>
                <span class="user-name-compact">{{ auth()->user()->name ?? __('messages.layout.guest_name') }}</span>
            </button>
            <div class="user-dropdown" id="userDropdown">
                <div class="user-dropdown-card">
                    <div class="user-dropdown-header">
                        <div class="user-avatar-large">{{ strtoupper(substr(auth()->user()->name ?? __('messages.layout.guest_name'), 0, 1)) }}</div>
                        <div>
                            <div class="user-dropdown-name">{{ auth()->user()->name ?? __('messages.layout.guest_name') }}</div>
                            <div class="user-dropdown-email">{{ auth()->user()->email ?? __('messages.layout.guest_email') }}</div>
                        </div>
                    </div>
                    <div class="user-dropdown-links">
                        <a href="#" class="user-dropdown-item">{{ __('messages.layout.my_profile') }}</a>
                        <form method="POST" action="{{ route('logout') }}" class="user-dropdown-form">
                            @csrf
                            <button type="submit" class="user-dropdown-item user-dropdown-signout">{{ __('messages.layout.sign_out') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
