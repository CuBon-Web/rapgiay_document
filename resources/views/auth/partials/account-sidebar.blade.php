<aside class="account-sidebar">
    <div class="account-sidebar__user">
        <div class="account-sidebar__avatar">{{ mb_strtoupper(mb_substr($profile->name ?? 'K', 0, 1)) }}</div>
        <div class="account-sidebar__meta">
            <p class="account-sidebar__label">Tài khoản</p>
            <p class="account-sidebar__name">{{ $profile->name ?? 'Khách' }}</p>
        </div>
    </div>
    <nav class="account-sidebar__nav">
        <a href="{{ route('accoungOrder') }}" class="account-sidebar__link {{ ($active ?? '') === 'orders' ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            Đơn hàng của bạn
        </a>
        <a href="{{ route('account.password') }}" class="account-sidebar__link {{ ($active ?? '') === 'password' ? 'is-active' : '' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Đổi mật khẩu
        </a>
        <a href="{{ route('home') }}" class="account-sidebar__link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Tiếp tục mua hàng
        </a>
        <a href="{{ route('logout') }}" class="account-sidebar__link account-sidebar__link--muted">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Đăng xuất
        </a>
    </nav>
</aside>
