<div class="error-page">
    <div class="error-icon">404</div>
    <h2>Page not found</h2>
    <p class="muted">The page <code><?= e($route ?? '') ?></code> doesn't exist.</p>
    <a class="btn btn-primary" href="<?= url('dashboard') ?>">Back to Dashboard</a>
</div>
