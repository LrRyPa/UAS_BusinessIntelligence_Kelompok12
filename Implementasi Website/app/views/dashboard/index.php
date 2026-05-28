<?php
$total_rev   = (float)($kpis['total_revenue'] ?? 0);
$total_ord   = (int)($kpis['total_orders'] ?? 0);
$total_units = (int)($kpis['total_units'] ?? 0);
$total_prof  = (float)($kpis['total_profit'] ?? 0);
$rev_growth  = (float)($kpis['rev_growth'] ?? 0);
$ord_growth  = (float)($kpis['order_growth'] ?? 0);
$unit_growth = (float)($kpis['unit_growth'] ?? 0);

$trendData = $monthly ?? $daily ?? [];
?>
<div class="hero-banner">
    <div class="hero-text">
        <div class="hero-eyebrow">
            <span class="dot"></span> ToySight &middot; Suite Analitik Cerdas
        </div>
        <h2>Selamat datang kembali, <?= e(explode(' ', $user['full_name'])[0]) ?></h2>
        <p>Berikut yang sedang terjadi di seluruh jaringan retail mainan Anda dari
            <strong><?= e(date('M d, Y', strtotime($start_date))) ?></strong> sampai
            <strong><?= e(date('M d, Y', strtotime($end_date))) ?></strong>.</p>
    </div>
    <form method="GET" action="<?= base_url() ?>/index.php" class="hero-filter">
        <input type="hidden" name="r" value="dashboard">
        <div class="filter-group">
            <label>Dari</label>
            <input type="date" name="start_date" value="<?= e($start_date) ?>"
                   min="<?= e($bounds['min_date']) ?>" max="<?= e($bounds['max_date']) ?>">
        </div>
        <div class="filter-group">
            <label>Sampai</label>
            <input type="date" name="end_date" value="<?= e($end_date) ?>"
                   min="<?= e($bounds['min_date']) ?>" max="<?= e($bounds['max_date']) ?>">
        </div>
        <button class="btn btn-light" type="submit">Terapkan</button>
    </form>
</div>

<div class="kpi-grid">
    <?php
    $kpiCards = [
        ['Total Pendapatan', compact_money($total_rev),    $rev_growth,  'cyan',   'M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6'],
        ['Total Pesanan',    compact_number($total_ord),   $ord_growth,  'orange', 'M3 3h2l3 12h13l3-9H6M9 21a1 1 0 100-2 1 1 0 000 2zM20 21a1 1 0 100-2 1 1 0 000 2z'],
        ['Unit Terjual',     compact_number($total_units), $unit_growth, 'blue',   'M21 16V8a2 2 0 00-1-1.7l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.7l7 4a2 2 0 002 0l7-4A2 2 0 0021 16zM3.3 7L12 12l8.7-5M12 22V12'],
        ['Laba Kotor',       compact_money($total_prof),   null,         'green',  'M3 17l6-6 4 4 8-8M14 7h7v7'],
    ];
    foreach ($kpiCards as $i => $c):
        [$label, $value, $growth, $color, $icon] = $c;
    ?>
    <div class="kpi-card kpi-<?= e($color) ?>" style="animation-delay: <?= $i*60 ?>ms">
        <div class="kpi-top">
            <span class="kpi-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="<?= $icon ?>"/>
                </svg>
            </span>
            <?php if ($growth !== null): ?>
            <span class="kpi-trend <?= $growth >= 0 ? 'up' : 'down' ?>">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <?php if ($growth >= 0): ?><polyline points="6 15 12 9 18 15"/><?php else: ?><polyline points="6 9 12 15 18 9"/><?php endif; ?>
                </svg>
                <?= ($growth >= 0 ? '+' : '') . $growth ?>%
            </span>
            <?php endif; ?>
        </div>
        <div class="kpi-value"><?= e($value) ?></div>
        <div class="kpi-label"><?= e($label) ?></div>
        <div class="kpi-spark" id="spark-<?= $i ?>"></div>
    </div>
    <?php endforeach; ?>
</div>

<div class="kpi-grid kpi-grid-3">
    <?php
    $small = [
        ['Total Produk',    compact_number((int)$kpis['total_products']),  'M16 11V7a4 4 0 0 0-8 0v4M5 11h14l-1 10H6z'],
        ['Toko',            compact_number((int)$kpis['total_stores']),    'M3 9l1-6h16l1 6M3 9v11h18V9'],
        ['Stok Inventaris', compact_number((int)$kpis['total_inventory']), 'M21 8V21H3V8M1 3h22v5z'],
    ];
    foreach ($small as $s): ?>
    <div class="mini-card">
        <div class="mini-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="<?= $s[2] ?>"/>
            </svg>
        </div>
        <div>
            <div class="mini-value"><?= e($s[1]) ?></div>
            <div class="mini-label"><?= e($s[0]) ?></div>
        </div>
    </div>
    <?php endforeach; ?>
    <div class="mini-card">
        <div class="mini-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
            </svg>
        </div>
        <div>
            <div class="mini-value"><?= number_format((float)($kpis['avg_margin'] ?? 0), 1) ?>%</div>
            <div class="mini-label">Margin Rata-rata</div>
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-eyebrow">Performa Pendapatan</div>
                <h3>Tren Pendapatan &amp; Laba Bulanan</h3>
            </div>
            <div style="display:flex;align-items:center;gap:14px;font-size:12px;color:#64748b;flex-shrink:0;margin-left:auto">
                <span style="display:flex;align-items:center;gap:6px">
                    <span style="display:inline-block;width:24px;height:3px;background:#1E3A5F;border-radius:2px"></span>
                    Pendapatan
                </span>
                <span style="display:flex;align-items:center;gap:6px">
                    <span style="display:inline-block;width:24px;height:3px;background:#22D3EE;border-radius:2px"></span>
                    Laba
                </span>
            </div>
        </div>
        <div id="chartTrend" class="chart-area"></div>
    </div>
    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-eyebrow">Komposisi Kategori</div>
                <h3>Pendapatan Berdasarkan Kategori</h3>
            </div>
        </div>
        <div id="chartCategory" class="chart-area"></div>
    </div>
</div>

<div class="grid-3">
    <div class="card span-2">
        <div class="card-header">
            <div>
                <div class="card-eyebrow">Produk Terlaris</div>
                <h3>Produk dengan Performa Terbaik</h3>
            </div>
            <a href="<?= url('products/analytics') ?>" class="link-more">Lihat semua →</a>
        </div>
        <div class="table-wrap">
            <table class="table table-clean">
                <thead><tr>
                    <th>Produk</th><th>Kategori</th><th class="num">Unit</th><th class="num">Pendapatan</th>
                </tr></thead>
                <tbody>
                <?php foreach ($top_prod as $p): ?>
                <tr>
                    <td>
                        <strong><?= e($p['product_name']) ?></strong>
                        <div class="muted small">@ <?= e(money((float)$p['product_price'])) ?></div>
                    </td>
                    <td><span class="chip chip-soft"><?= e($p['product_category']) ?></span></td>
                    <td class="num"><?= compact_number((int)$p['units']) ?></td>
                    <td class="num"><strong><?= compact_money((float)$p['revenue']) ?></strong></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-eyebrow">Peringkat</div>
                <h3>Toko Teratas</h3>
            </div>
        </div>
        <ol class="store-list">
        <?php foreach ($top_store as $i => $st): ?>
            <li>
                <span class="rank rank-<?= $i+1 ?>"><?= $i+1 ?></span>
                <div class="sl-info">
                    <div class="sl-name"><?= e($st['store_name']) ?></div>
                    <div class="sl-sub"><?= e($st['store_city']) ?> &middot; <?= e($st['store_location']) ?></div>
                </div>
                <div class="sl-val"><?= compact_money((float)$st['revenue']) ?></div>
            </li>
        <?php endforeach; ?>
        </ol>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-eyebrow">Aktivitas Terbaru</div>
            <h3>Transaksi Penjualan Terbaru</h3>
        </div>
        <?php if (Auth::can('crud.sales')): ?>
        <a href="<?= url('sales') ?>" class="link-more">Semua transaksi →</a>
        <?php endif; ?>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr>
                <th>ID Penjualan</th><th>Tanggal</th><th>Produk</th><th>Toko</th>
                <th class="num">Unit</th><th class="num">Harga Satuan</th><th class="num">Pendapatan</th>
            </tr></thead>
            <tbody>
            <?php foreach ($recent as $r): ?>
            <tr>
                <td>#<?= e((string)$r['sale_id']) ?></td>
                <td><?= e(date('M d, Y', strtotime($r['full_date']))) ?></td>
                <td>
                    <strong><?= e($r['product_name']) ?></strong>
                    <div class="muted small"><?= e($r['product_category']) ?></div>
                </td>
                <td><?= e($r['store_name']) ?></td>
                <td class="num"><?= e((string)$r['units']) ?></td>
                <td class="num"><?= money((float)$r['unit_price']) ?></td>
                <td class="num"><strong><?= money((float)$r['revenue']) ?></strong></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    var trendData = <?= json_encode(array_values($trendData)) ?>;
    var byCat     = <?= json_encode($by_cat ?? []) ?>;

    var labels  = trendData.map(function(d){ return d.label   || d.day || ''; });
    var revArr  = trendData.map(function(d){ return parseFloat(d.revenue) || 0; });
    var profArr = trendData.map(function(d){ return parseFloat(d.profit)  || 0; });
    var ordArr  = trendData.map(function(d){ return parseInt(d.orders, 10) || 0; });

    var grid = { borderColor: '#eef2f7', strokeDashArray: 4 };

    var zoomToolbar = {
        show        : true,
        autoSelected: 'zoom',
        tools: {
            download : false,
            selection: true,
            zoom     : true,
            zoomin   : true,
            zoomout  : true,
            pan      : true,
            reset    : true,
        }
    };

    if (trendData.length && document.getElementById('chartTrend')) {
        new ApexCharts(document.getElementById('chartTrend'), {
            chart: {
                type       : 'area',
                height     : 340,
                fontFamily : 'Inter, system-ui',
                toolbar    : zoomToolbar,
                zoom       : { enabled: true },
                animations : { easing: 'easeinout', speed: 600 }
            },
            series: [
                { name: 'Pendapatan', data: revArr  },
                { name: 'Laba',       data: profArr }
            ],
            xaxis: {
                categories : labels,
                labels     : {
                    rotate   : -35,
                    style    : { colors: '#64748b', fontSize: '11px' },
                    maxHeight: 60,
                },
                axisBorder : { show: false },
                axisTicks  : { show: false },
            },
            yaxis: {
                labels: {
                    formatter: function(v){ return '$' + (v >= 1000 ? (v/1000).toFixed(0) + 'K' : v.toFixed(0)); },
                    style    : { colors: '#64748b' }
                }
            },
            colors     : ['#1E3A5F', '#22D3EE'],
            stroke     : { curve: 'smooth', width: [3, 2.5] },
            fill       : { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.02, stops: [0, 90, 100] } },
            dataLabels : { enabled: false },
            legend     : { show: false },
            grid       : grid,
            tooltip    : { y: { formatter: function(v){ return '$' + Number(v).toLocaleString(); } } },
        }).render();
    }

    if (byCat.length && document.getElementById('chartCategory')) {
        new ApexCharts(document.getElementById('chartCategory'), {
            chart      : { type: 'donut', height: 340, fontFamily: 'Inter, system-ui', toolbar: { show: false }, animations: { speed: 600 } },
            series     : byCat.map(function(c){ return parseFloat(c.revenue) || 0; }),
            labels     : byCat.map(function(c){ return c.category || ''; }),
            colors     : ['#1E3A5F', '#F59E0B', '#22D3EE', '#10B981', '#6366F1'],
            legend     : { position: 'bottom' },
            plotOptions: {
                pie: { donut: { size: '70%', labels: {
                    show : true,
                    total: {
                        show     : true,
                        label    : 'Total',
                        formatter: function(w){
                            var sum = w.globals.seriesTotals.reduce(function(a,b){ return a+b; }, 0);
                            return '$' + (sum / 1000000).toFixed(2) + 'M';
                        }
                    }
                }}}
            },
            dataLabels : { formatter: function(val){ return Number(val).toFixed(1) + '%'; } },
            stroke     : { colors: ['#fff'] },
            tooltip    : { y: { formatter: function(v){ return '$' + Number(v).toLocaleString(); } } }
        }).render();
    }

    function sparkBase(color, data) {
        return {
            chart  : { type: 'area', height: 60, sparkline: { enabled: true }, animations: { speed: 500 } },
            stroke : { curve: 'smooth', width: 2 },
            fill   : { type: 'gradient', gradient: { opacityFrom: 0.5, opacityTo: 0 } },
            colors : [color],
            series : [{ data: data }],
            tooltip: { enabled: false }
        };
    }

    if (revArr.length) {
        new ApexCharts(document.getElementById('spark-0'), sparkBase('#22D3EE', revArr)).render();
        new ApexCharts(document.getElementById('spark-1'), sparkBase('#F59E0B', ordArr)).render();
        new ApexCharts(document.getElementById('spark-2'), sparkBase('#3B82F6', revArr.map(function(v){ return v / 1.5; }))).render();
        new ApexCharts(document.getElementById('spark-3'), sparkBase('#10B981', profArr)).render();
    }
})();
</script>
