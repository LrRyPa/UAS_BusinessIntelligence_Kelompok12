<?php
$type_labels = [
    'sales_summary' => 'Laporan Ringkasan Penjualan',
    'product_performance' => 'Laporan Performa Produk',
    'store_ranking' => 'Laporan Peringkat Toko',
    'category_sales' => 'Laporan Penjualan Kategori',
];
$title = $type_labels[$report_type] ?? 'Laporan';
?>
<div class="print-page">
    <header class="print-head">
        <div class="print-brand">
            <svg width="42" height="42" viewBox="0 0 36 36" fill="none">
                <rect x="2" y="2" width="32" height="32" rx="9" fill="#1E3A5F"/>
                <rect x="9"  y="20" width="3" height="7"  rx="1" fill="#22D3EE"/>
                <rect x="14" y="16" width="3" height="11" rx="1" fill="#F59E0B"/>
                <rect x="19" y="12" width="3" height="15" rx="1" fill="#FFFFFF"/>
                <circle cx="26" cy="11" r="3" fill="#F59E0B"/>
                <circle cx="26" cy="11" r="1.2" fill="#1E3A5F"/>
            </svg>
            <div>
                <div class="pb-title">ToySight</div>
                <div class="pb-sub">Analitik Cerdas untuk Bisnis Retail Mainan</div>
            </div>
        </div>
        <div class="print-meta">
            <div><strong>Laporan:</strong> <?= e($title) ?></div>
            <div><strong>Periode:</strong> <?= e(date('M d, Y', strtotime($start_date))) ?> &mdash; <?= e(date('M d, Y', strtotime($end_date))) ?></div>
            <div><strong>Dibuat:</strong> <?= date('M d, Y H:i') ?></div>
        </div>
    </header>

    <section class="print-kpis">
        <div class="pk"><span>Pendapatan</span><strong><?= money((float)$kpis['total_revenue']) ?></strong></div>
        <div class="pk"><span>Pesanan</span><strong><?= number_format((int)$kpis['total_orders']) ?></strong></div>
        <div class="pk"><span>Unit Terjual</span><strong><?= number_format((int)$kpis['total_units']) ?></strong></div>
        <div class="pk"><span>Laba Kotor</span><strong><?= money((float)$kpis['total_profit']) ?></strong></div>
    </section>

    <h2 class="print-section-title"><?= e($title) ?></h2>

    <table class="print-table">
    <?php if ($report_type === 'sales_summary'): ?>
        <thead><tr><th>#</th><th>Periode</th><th class="num">Pendapatan</th><th class="num">Laba</th><th class="num">Pesanan</th></tr></thead>
        <tbody>
        <?php foreach ($data as $i => $r): ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><?= e($r['label']) ?></td>
            <td class="num"><?= money((float)$r['revenue']) ?></td>
            <td class="num"><?= money((float)$r['profit']) ?></td>
            <td class="num"><?= number_format((int)$r['orders']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    <?php elseif ($report_type === 'product_performance'): ?>
        <thead><tr><th>#</th><th>Produk</th><th>Kategori</th><th>Tier</th><th class="num">Unit</th><th class="num">Pendapatan</th><th class="num">Margin %</th></tr></thead>
        <tbody>
        <?php foreach ($data as $i => $r): ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><?= e($r['product_name']) ?></td>
            <td><?= e($r['product_category']) ?></td>
            <td><?= e($r['price_tier']) ?></td>
            <td class="num"><?= number_format((int)$r['units']) ?></td>
            <td class="num"><?= money((float)$r['revenue']) ?></td>
            <td class="num"><?= number_format((float)$r['margin_pct'], 1) ?>%</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    <?php elseif ($report_type === 'store_ranking'): ?>
        <thead><tr><th>#</th><th>Toko</th><th>Kota</th><th>Lokasi</th><th class="num">Pesanan</th><th class="num">Pendapatan</th></tr></thead>
        <tbody>
        <?php foreach ($data as $i => $r): ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><?= e($r['store_name']) ?></td>
            <td><?= e($r['store_city']) ?></td>
            <td><?= e($r['store_location']) ?></td>
            <td class="num"><?= number_format((int)$r['orders']) ?></td>
            <td class="num"><?= money((float)$r['revenue']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    <?php elseif ($report_type === 'category_sales'): ?>
        <thead><tr><th>#</th><th>Kategori</th><th class="num">Produk</th><th class="num">Unit</th><th class="num">Pendapatan</th><th class="num">Laba</th></tr></thead>
        <tbody>
        <?php foreach ($data as $i => $r): ?>
        <tr>
            <td><?= $i+1 ?></td>
            <td><?= e($r['category']) ?></td>
            <td class="num"><?= (int)$r['products'] ?></td>
            <td class="num"><?= number_format((int)$r['units']) ?></td>
            <td class="num"><?= money((float)$r['revenue']) ?></td>
            <td class="num"><?= money((float)$r['profit']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    <?php endif; ?>
    </table>

    <footer class="print-foot">
        Dibuat oleh ToySight Business Intelligence &middot; <?= date('Y') ?>
    </footer>
</div>