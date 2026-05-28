<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($page_title ?? 'Dashboard') ?> · <?= e($app['name']) ?></title>
<link rel="icon" type="image/svg+xml" href="<?= asset('img/favicon.svg') ?>">
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<link rel="stylesheet" href="<?= asset('css/reports.css') ?>"> 
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
</head>
<body>
<div class="app-layout">
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="main-pane">
        <?php require __DIR__ . '/../partials/topbar.php'; ?>

        <main class="content">
            <?= $content ?>
        </main>

        <footer class="footer">
            <span><?= e($app['name']) ?> · v<?= e($app['version']) ?></span>
            <span><?= e($app['tagline']) ?></span>
        </footer>
    </div>
</div>

<div id="toast-container" class="toast-container">
    <?php if (!empty($flash)): ?>
        <div class="toast toast-<?= e($flash['type']) ?>" data-autohide>
            <span class="toast-icon">
                <?php if ($flash['type'] === 'success'): ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                <?php elseif ($flash['type'] === 'error'): ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 18L18 6M6 6l12 12"/></svg>
                <?php else: ?>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
                <?php endif; ?>
            </span>
            <span class="toast-msg"><?= e($flash['message']) ?></span>
            <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>
</div>

<div id="modal-root"></div>

<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
