<div class="error-page">
    <div class="error-icon">403</div>
    <h2>Access Denied</h2>
    <p class="muted">You don't have permission to access this resource <span class="small">(<?= e($perm ?? '') ?>)</span>.</p>
    <a class="btn btn-primary" href="<?= url('dashboard') ?>">Back to Dashboard</a>
</div>
