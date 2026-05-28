<?php
$role = Auth::role();
$roles_cfg = Auth::roles();
$menu = Auth::menu();
$page = $page ?? '';
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-mark">
            <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="brandGrad" x1="0" y1="0" x2="36" y2="36" gradientUnits="userSpaceOnUse">
                        <stop offset="0" stop-color="#1E3A5F"/>
                        <stop offset="1" stop-color="#2C5B8F"/>
                    </linearGradient>
                </defs>
                <rect x="2" y="2" width="32" height="32" rx="9" fill="url(#brandGrad)"/>
                <rect x="9"  y="20" width="3" height="7" rx="1" fill="#22D3EE"/>
                <rect x="14" y="16" width="3" height="11" rx="1" fill="#F59E0B"/>
                <rect x="19" y="12" width="3" height="15" rx="1" fill="#FFFFFF"/>
                <circle cx="26" cy="11" r="3" fill="#F59E0B"/>
                <circle cx="26" cy="11" r="1.2" fill="#1E3A5F"/>
            </svg>
        </div>
        <div class="brand-text">
            <div class="brand-title">ToySight</div>
            <div class="brand-sub">Analitik Mainan Cerdas</div>
        </div>
        <button class="sidebar-collapse" id="sidebarCollapse" aria-label="Tutup sidebar">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="11 17 6 12 11 7"/><polyline points="18 17 13 12 18 7"/></svg>
        </button>
    </div>

    <div class="sidebar-section-title">Utama</div>

    <a href="<?= url('dashboard') ?>" class="nav-item <?= active('dashboard', $page) ?>">
        <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg></span>
        <span class="nav-label">Dashboard</span>
    </a>

    <?php if (Auth::can('analytics.sales')): ?>
    <div class="sidebar-section-title">Analitik</div>

    <?php if (in_array('sales', $menu)): ?>
    <a href="<?= url('sales/analytics') ?>" class="nav-item <?= active('sales', $page) ?>">
        <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 17 9 11 13 15 21 7"/><polyline points="14 7 21 7 21 14"/></svg></span>
        <span class="nav-label">Analitik Penjualan</span>
    </a>
    <?php endif; ?>

    <?php if (Auth::can('analytics.product') && in_array('product', $menu)): ?>
    <a href="<?= url('products/analytics') ?>" class="nav-item <?= active('product', $page) ?>">
        <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></span>
        <span class="nav-label">Analitik Produk</span>
    </a>
    <?php endif; ?>

    <?php if (Auth::can('analytics.inventory') && (in_array('inventory', $menu) || in_array('stock_monitor', $menu))): ?>
    <a href="<?= url('inventory/analytics') ?>" class="nav-item <?= active('inventory', $page) ?>">
        <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4 8 4 8-4z"/><polyline points="4 12 12 16 20 12"/><polyline points="4 17 12 21 20 17"/></svg></span>
        <span class="nav-label">Analitik Inventaris</span>
    </a>
    <?php endif; ?>

    <?php if (Auth::can('analytics.store') && in_array('store', $menu)): ?>
    <a href="<?= url('stores/analytics') ?>" class="nav-item <?= active('store', $page) ?>">
        <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l1-6h16l1 6"/><path d="M3 9v11a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V9"/><path d="M3 9c0 1.7 1.3 3 3 3s3-1.3 3-3M9 9c0 1.7 1.3 3 3 3s3-1.3 3-3M15 9c0 1.7 1.3 3 3 3s3-1.3 3-3"/></svg></span>
        <span class="nav-label">Performa Toko</span>
    </a>
    <?php endif; ?>
    <?php endif; ?>

    <?php if (Auth::can('crud.products') || Auth::can('crud.inventory') || Auth::can('crud.sales') || Auth::can('crud.stores')): ?>
    <div class="sidebar-section-title">Kelola</div>

    <?php if (Auth::can('crud.sales') && (in_array('sales_crud', $menu) || in_array('orders', $menu))): ?>
    <a href="<?= url('sales') ?>" class="nav-item <?= active('sales_crud', $page) ?>">
        <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg></span>
        <span class="nav-label">Penjualan / Pesanan</span>
    </a>
    <?php endif; ?>

    <?php if (Auth::can('crud.products') || in_array('products_view', $menu)): ?>
    <a href="<?= url('products') ?>" class="nav-item <?= ($page === 'product') ? '' : active('product_crud', $page) ?>">
        <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 11V7a4 4 0 0 0-8 0v4"/><rect x="5" y="11" width="14" height="10" rx="2"/></svg></span>
        <span class="nav-label">Produk</span>
    </a>
    <?php endif; ?>

    <?php if (Auth::can('crud.inventory') && (in_array('inventory_crud', $menu) || in_array('stock_monitor', $menu))): ?>
    <a href="<?= url('inventory') ?>" class="nav-item <?= active('inventory_crud', $page) ?>">
        <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8V21H3V8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg></span>
        <span class="nav-label">Inventaris</span>
    </a>
    <?php endif; ?>

    <?php if (Auth::can('crud.stores')): ?>
    <a href="<?= url('stores') ?>" class="nav-item <?= active('store_crud', $page) ?>">
        <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l1-6h16l1 6"/><path d="M3 9v11h18V9"/></svg></span>
        <span class="nav-label">Toko</span>
    </a>
    <?php endif; ?>
    <?php endif; ?>

    <?php if (Auth::can('reports.view')): ?>
    <div class="sidebar-section-title">Laporan</div>
    <a href="<?= url('reports') ?>" class="nav-item <?= active('reports', $page) ?>">
        <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></span>
        <span class="nav-label">Laporan</span>
    </a>
    <?php endif; ?>

    <?php if (Auth::can('crud.users')): ?>
    <div class="sidebar-section-title">Sistem</div>
    <a href="<?= url('users') ?>" class="nav-item <?= active('users', $page) ?>">
        <span class="nav-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
        <span class="nav-label">Pengguna & Role</span>
    </a>
    <?php endif; ?>

    <div class="sidebar-spacer"></div>
    <div class="sidebar-user">
        <div class="avatar"><?= e(strtoupper(substr(Auth::user()['full_name'] ?? 'U', 0, 1))) ?></div>
        <div class="user-info">
            <div class="user-name"><?= e(Auth::user()['full_name']) ?></div>
            <div class="user-role"><?= e($roles_cfg['roles'][$role]['label'] ?? ucfirst($role)) ?></div>
        </div>
        <a class="logout-btn" href="<?= url('logout') ?>" title="Keluar">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
    </div>
</aside>