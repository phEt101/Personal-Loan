<section class="topbar">
    <button class="menu-button" id="menuToggle">☰</button>
    <div class="topbar-actions">
        <div class="user-box">
            <button class="user-toggle" id="userToggle" type="button">
                <div class="user-avatar-compact">{{ strtoupper(substr(auth()->user()->name ?? 'G', 0, 1)) }}</div>
                <span class="user-name-compact">{{ auth()->user()->name ?? 'Guest' }}</span>
            </button>
            <div class="user-dropdown" id="userDropdown">
                <div class="user-dropdown-card">
                    <div class="user-dropdown-header">
                        <div class="user-avatar-large">{{ strtoupper(substr(auth()->user()->name ?? 'G', 0, 1)) }}</div>
                        <div>
                            <div class="user-dropdown-name">{{ auth()->user()->name ?? 'Guest' }}</div>
                            <div class="user-dropdown-email">{{ auth()->user()->email ?? 'guest@example.com' }}</div>
                        </div>
                    </div>
                    <div class="user-dropdown-links">
                        <a href="#" class="user-dropdown-item">My Profile</a>
                        <a href="#" class="user-dropdown-item">Messages</a>
                        <a href="#" class="user-dropdown-item">Activity</a>
                        <a href="#" class="user-dropdown-item">FAQ</a>
                        <a href="#" class="user-dropdown-item user-dropdown-signout">Sign Out</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
