<div class="error-page">
    <div class="error-icon">500</div>
    <h2>Server Error</h2>
    <p class="muted">Something went wrong while processing your request.</p>
    <?php if (!empty($error)): ?>
    <pre class="error-trace"><?= e($error->getMessage()) ?></pre>
    <?php endif; ?>
    <a class="btn btn-primary" href="<?= url('dashboard') ?>">Back to Dashboard</a>
</div>
