<?php $page_title = $page_title ?? 'Dashboard'; ?>
<header class="topbar">
    <div class="topbar-left">
        <button class="hamburger" id="sidebarToggle" aria-label="Tampilkan menu">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div class="topbar-titles">
            <div class="brand-pill">
                <svg width="16" height="16" viewBox="0 0 36 36" fill="none">
                    <rect x="2" y="2" width="32" height="32" rx="9" fill="#1E3A5F"/>
                    <rect x="9"  y="20" width="3" height="7"  rx="1" fill="#22D3EE"/>
                    <rect x="14" y="16" width="3" height="11" rx="1" fill="#F59E0B"/>
                    <rect x="19" y="12" width="3" height="15" rx="1" fill="#FFFFFF"/>
                    <circle cx="26" cy="11" r="3"   fill="#F59E0B"/>
                </svg>
                <span>ToySight</span>
            </div>
            <h1 class="page-title"><?= e($page_title) ?></h1>
        </div>
    </div>

    <div class="topbar-right">
        <div class="search-box">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" placeholder="Pencarian cepat…" id="globalSearch">
            <kbd>⌘K</kbd>
        </div>
        <button class="icon-btn" title="Notifikasi">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <span class="badge-dot"></span>
        </button>
        <div class="topbar-user">
            <div class="avatar sm"><?= e(strtoupper(substr(Auth::user()['full_name'] ?? 'U', 0, 1))) ?></div>
            <div class="tu-text">
                <div class="tu-name"><?= e(Auth::user()['full_name']) ?></div>
                <div class="tu-role"><?php $rcfg = Auth::roles(); echo e($rcfg['roles'][Auth::role()]['label'] ?? ''); ?></div>
            </div>
        </div>
    </div>
</header>