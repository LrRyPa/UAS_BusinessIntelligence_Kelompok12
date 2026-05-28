<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title><?= e($page_title ?? 'Report') ?> · <?= e($app['name']) ?></title>
<link rel="stylesheet" href="<?= asset('css/print.css') ?>">
</head>
<body>
<div class="print-actions no-print">
    <button class="btn-cancel" onclick="window.close()">Close</button>
    <button onclick="window.print()">Print / Save as PDF</button>
</div>
<?= $content ?>
<script>setTimeout(()=>window.print(), 600);</script>
</body>
</html>
