<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Masuk · <?= e($app['name']) ?></title>
<link rel="icon" type="image/svg+xml" href="<?= asset('img/favicon.svg') ?>">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="login-body">
<div class="login-shell">
    <div class="login-art">
        <div class="login-art-inner">
            <div class="login-brand">
                <svg width="56" height="56" viewBox="0 0 36 36" fill="none">
                    <defs>
                        <linearGradient id="lbg" x1="0" y1="0" x2="36" y2="36" gradientUnits="userSpaceOnUse">
                            <stop offset="0" stop-color="#FFFFFF" stop-opacity=".95"/>
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity=".75"/>
                        </linearGradient>
                    </defs>
                    <rect x="2" y="2" width="32" height="32" rx="9" fill="url(#lbg)"/>
                    <rect x="9"  y="20" width="3" height="7"  rx="1" fill="#22D3EE"/>
                    <rect x="14" y="16" width="3" height="11" rx="1" fill="#F59E0B"/>
                    <rect x="19" y="12" width="3" height="15" rx="1" fill="#1E3A5F"/>
                    <circle cx="26" cy="11" r="3"   fill="#F59E0B"/>
                    <circle cx="26" cy="11" r="1.2" fill="#1E3A5F"/>
                </svg>
                <div>
                    <div class="lb-title">ToySight</div>
                    <div class="lb-sub">Suite Business Intelligence</div>
                </div>
            </div>

            <h1 class="login-headline">
                Analitik Cerdas untuk <span class="hl">Bisnis Retail Mainan</span>
            </h1>
            <p class="login-lede">
                KPI real-time, tren penjualan, performa toko, dan pemantauan inventaris &mdash; dirancang untuk operasional retail mainan modern.
            </p>

            <div class="feature-list">
                <div class="feat">
                    <span class="feat-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 17 9 11 13 15 21 7"/></svg></span>
                    <span>Analitik pendapatan &amp; penjualan langsung</span>
                </div>
                <div class="feat">
                    <span class="feat-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4 8 4 8-4z"/><polyline points="4 12 12 16 20 12"/></svg></span>
                    <span>Pemantauan inventaris &amp; stok</span>
                </div>
                <div class="feat">
                    <span class="feat-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l1-6h16l1 6"/><path d="M3 9v11h18V9"/></svg></span>
                    <span>Peringkat performa toko</span>
                </div>
                <div class="feat">
                    <span class="feat-ico"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
                    <span>Akses aman multi-role</span>
                </div>
            </div>

            <div class="deco-blocks">
                <span class="db b1"></span>
                <span class="db b2"></span>
                <span class="db b3"></span>
                <span class="db b4"></span>
            </div>
        </div>
    </div>

    <div class="login-form-pane">
        <div class="login-card">
            <div class="login-mobile-brand">
                <svg width="42" height="42" viewBox="0 0 36 36" fill="none">
                    <rect x="2" y="2" width="32" height="32" rx="9" fill="#1E3A5F"/>
                    <rect x="9"  y="20" width="3" height="7"  rx="1" fill="#22D3EE"/>
                    <rect x="14" y="16" width="3" height="11" rx="1" fill="#F59E0B"/>
                    <rect x="19" y="12" width="3" height="15" rx="1" fill="#FFFFFF"/>
                    <circle cx="26" cy="11" r="3"   fill="#F59E0B"/>
                    <circle cx="26" cy="11" r="1.2" fill="#1E3A5F"/>
                </svg>
                <div class="lb-title">ToySight</div>
            </div>

            <h2>Selamat datang kembali</h2>
            <p class="muted">Masuk untuk mengakses dashboard analitik Anda.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= url('login') ?>" class="login-form" autocomplete="on">
                <div class="form-group">
                    <label>Username</label>
                    <div class="input-wrap">
                        <span class="input-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
                        <input type="text" name="username" placeholder="Masukkan username Anda" autofocus required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrap">
                        <span class="input-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
                        <input type="password" name="password" id="pwd" placeholder="Masukkan password Anda" required>
                        <button type="button" class="pwd-toggle" onclick="togglePwd()" aria-label="Tampilkan password">
                            <svg id="pwdEye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>

                <button class="btn btn-primary btn-block" type="submit">
                    Masuk
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>
            </form>

            <div class="demo-creds">
                <div class="dc-title">Akun demo <span class="muted">(klik untuk autofill)</span></div>
                <div class="dc-grid">
                    <button type="button" class="dc-btn" onclick="fill('admin','admin123')">
                        <span class="dc-role admin">Admin</span>
                        <span class="dc-user">admin / admin123</span>
                    </button>
                    <button type="button" class="dc-btn" onclick="fill('manager','manager123')">
                        <span class="dc-role manager">Manager</span>
                        <span class="dc-user">manager / manager123</span>
                    </button>
                    <button type="button" class="dc-btn" onclick="fill('sales','sales123')">
                        <span class="dc-role sales">Sales</span>
                        <span class="dc-user">sales / sales123</span>
                    </button>
                    <button type="button" class="dc-btn" onclick="fill('warehouse','warehouse123')">
                        <span class="dc-role warehouse">Gudang</span>
                        <span class="dc-user">warehouse / warehouse123</span>
                    </button>
                </div>
            </div>

            <div class="login-foot muted">
                <?= e($app['name']) ?> &middot; v<?= e($app['version']) ?> &middot; &copy; <?= date('Y') ?>
            </div>
        </div>
    </div>
</div>

<script>
function togglePwd() {
    const i = document.getElementById('pwd');
    i.type = i.type === 'password' ? 'text' : 'password';
}
function fill(u, p) {
    document.querySelector('input[name="username"]').value = u;
    document.querySelector('input[name="password"]').value = p;
}
</script>
</body>
</html>