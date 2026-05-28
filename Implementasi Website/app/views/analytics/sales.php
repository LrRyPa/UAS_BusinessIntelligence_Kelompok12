<div class="filter-bar card">
    <form method="GET" action="<?= base_url() ?>/index.php" class="filter-form">
        <input type="hidden" name="r" value="sales/analytics">
        <div class="filter-group">
            <label>Dari</label>
            <input type="date" name="start_date" value="<?= e($start_date) ?>" min="<?= e($bounds['min_date']) ?>" max="<?= e($bounds['max_date']) ?>">
        </div>
        <div class="filter-group">
            <label>Sampai</label>
            <input type="date" name="end_date" value="<?= e($end_date) ?>" min="<?= e($bounds['min_date']) ?>" max="<?= e($bounds['max_date']) ?>">
        </div>
        <div class="filter-group">
            <label>Toko</label>
            <select name="store_key">
                <option value="">Semua toko</option>
                <?php foreach ($stores as $s): ?>
                <option value="<?= e((string)$s['store_key']) ?>" <?= ($f_store == $s['store_key']) ? 'selected' : '' ?>>
                    <?= e($s['store_name']) ?> &middot; <?= e($s['store_city']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label>Kategori</label>
            <select name="category_key">
                <option value="">Semua kategori</option>
                <?php foreach ($categories as $c): ?>
                <option value="<?= e((string)$c['category_key']) ?>" <?= ($f_category == $c['category_key']) ? 'selected' : '' ?>>
                    <?= e($c['category_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn btn-primary" type="submit">Terapkan Filter</button>
        <a class="btn btn-ghost" href="<?= url('sales/analytics') ?>">Reset</a>
    </form>
</div>

<div class="kpi-grid">
    <div class="kpi-card kpi-cyan">
        <div class="kpi-top"><span class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></span></div>
        <div class="kpi-value"><?= compact_money((float)$kpis['total_revenue']) ?></div>
        <div class="kpi-label">Pendapatan</div>
    </div>
    <div class="kpi-card kpi-orange">
        <div class="kpi-top"><span class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h2l3 12h13l3-9H6"/></svg></span></div>
        <div class="kpi-value"><?= compact_number((int)$kpis['total_orders']) ?></div>
        <div class="kpi-label">Pesanan</div>
    </div>
    <div class="kpi-card kpi-blue">
        <div class="kpi-top"><span class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8L12 3 3 8v8l9 5 9-5z"/></svg></span></div>
        <div class="kpi-value"><?= compact_number((int)$kpis['total_units']) ?></div>
        <div class="kpi-label">Unit Terjual</div>
    </div>
    <div class="kpi-card kpi-green">
        <div class="kpi-top"><span class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 17l6-6 4 4 8-8"/></svg></span></div>
        <div class="kpi-value"><?= compact_money((float)$kpis['total_profit']) ?></div>
        <div class="kpi-label">Laba Kotor</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <h3>Performa Penjualan dari Waktu ke Waktu</h3>
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
    <div id="chartDaily" class="chart-area"></div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><div><div class="card-eyebrow">Kanal</div><h3>Penjualan Berdasarkan Toko (Top 15)</h3></div></div>
        <div id="chartByStore" class="chart-area"></div>
    </div>
    <div class="card">
        <div class="card-header"><div><div class="card-eyebrow">Komposisi</div><h3>Penjualan Berdasarkan Kategori</h3></div></div>
        <div id="chartByCategory" class="chart-area"></div>
    </div>
</div>

<div class="card">
    <div class="card-header"><div><div class="card-eyebrow">Perilaku</div><h3>Penjualan Berdasarkan Hari</h3></div></div>
    <div id="chartDow" class="chart-area"></div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><div><div class="card-eyebrow">Produk Terlaris</div><h3>10 Produk Teratas</h3></div></div>
        <div class="table-wrap"><table class="table table-clean">
            <thead><tr><th>#</th><th>Produk</th><th>Tier</th><th class="num">Unit</th><th class="num">Pendapatan</th></tr></thead>
            <tbody>
            <?php foreach ($best as $i => $p): ?>
            <tr>
                <td><span class="rank-mini"><?= $i+1 ?></span></td>
                <td><strong><?= e($p['product_name']) ?></strong><div class="muted small"><?= e($p['product_category']) ?></div></td>
                <td><span class="chip chip-soft"><?= e($p['price_tier']) ?></span></td>
                <td class="num"><?= compact_number((int)$p['units']) ?></td>
                <td class="num"><strong><?= compact_money((float)$p['revenue']) ?></strong></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    </div>
    <div class="card">
        <div class="card-header"><div><div class="card-eyebrow">Performa Terendah</div><h3>10 Produk Terburuk</h3></div></div>
        <div class="table-wrap"><table class="table table-clean">
            <thead><tr><th>#</th><th>Produk</th><th>Tier</th><th class="num">Unit</th><th class="num">Pendapatan</th></tr></thead>
            <tbody>
            <?php foreach ($worst as $i => $p): ?>
            <tr>
                <td><span class="rank-mini muted"><?= $i+1 ?></span></td>
                <td><strong><?= e($p['product_name']) ?></strong><div class="muted small"><?= e($p['product_category']) ?></div></td>
                <td><span class="chip chip-soft"><?= e($p['price_tier']) ?></span></td>
                <td class="num"><?= compact_number((int)$p['units']) ?></td>
                <td class="num"><?= compact_money((float)$p['revenue']) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    </div>
</div>

<script>
(function() {
    var daily   = <?= json_encode($daily        ?? []) ?>;
    var byStore = <?= json_encode($by_store     ?? []) ?>;
    var byCat   = <?= json_encode($by_category  ?? []) ?>;
    var byDow   = <?= json_encode($by_dow       ?? []) ?>;

    var grid = { borderColor: '#eef2f7', strokeDashArray: 4 };

    function fmtK(v) {
        return '$' + (Math.abs(v) >= 1000 ? (v / 1000).toFixed(0) + 'K' : Number(v).toFixed(0));
    }

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

    if (daily.length && document.getElementById('chartDaily')) {
        new ApexCharts(document.getElementById('chartDaily'), {
            chart: {
                type: 'area', height: 340, fontFamily: 'Inter',
                toolbar    : zoomToolbar,
                zoom       : { enabled: true },
                animations : { speed: 500 }
            },
            series: [
                { name: 'Pendapatan', data: daily.map(function(d){ return parseFloat(d.revenue) || 0; }) },
                { name: 'Laba',       data: daily.map(function(d){ return parseFloat(d.profit)  || 0; }) }
            ],
            xaxis: {
                categories : daily.map(function(d){ return d.day || d.period || ''; }),
                type       : 'datetime',
                labels     : { style: { colors: '#64748b', fontSize: '11px' } },
                axisBorder : { show: false }, axisTicks: { show: false }
            },
            yaxis  : { labels: { formatter: fmtK, style: { colors: '#64748b', fontSize: '11px' } } },
            colors : ['#1E3A5F', '#22D3EE'],
            stroke : { curve: 'smooth', width: [2, 2] },
            fill   : { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.02 } },
            dataLabels: { enabled: false },
            legend : { show: false },
            grid   : grid,
            tooltip: { x: { format: 'dd MMM yyyy' }, y: { formatter: function(v){ return '$' + Number(v).toLocaleString(); } } }
        }).render();
    }

    if (byStore.length && document.getElementById('chartByStore')) {
        var storeData = byStore.slice(0, 15);
        new ApexCharts(document.getElementById('chartByStore'), {
            chart: {
                type: 'bar', height: 480, fontFamily: 'Inter',
                toolbar    : zoomToolbar,
                zoom       : { enabled: true },
                animations : { speed: 500 }
            },
            series     : [{ name: 'Pendapatan', data: storeData.map(function(s){ return parseFloat(s.revenue) || 0; }) }],
            xaxis      : {
                categories : storeData.map(function(s){ return s.store_name || ''; }),
                labels     : { formatter: fmtK, style: { colors: '#64748b', fontSize: '11px' } },
                axisBorder : { show: false }, axisTicks: { show: false }
            },
            yaxis      : { labels: { style: { colors: '#374151', fontSize: '11px' } } },
            plotOptions: { bar: { horizontal: true, borderRadius: 6, distributed: true, barHeight: '70%' } },
            colors     : ['#1E3A5F','#22D3EE','#F59E0B','#10B981','#6366F1','#EC4899',
                          '#3B82F6','#F97316','#14B8A6','#8B5CF6','#EF4444','#84CC16',
                          '#06B6D4','#A855F7','#F43F5E'],
            dataLabels : { enabled: false },
            legend     : { show: false },
            grid       : grid,
            tooltip    : { y: { formatter: function(v){ return '$' + Number(v).toLocaleString(); } } }
        }).render();
    }

    if (byCat.length && document.getElementById('chartByCategory')) {
        new ApexCharts(document.getElementById('chartByCategory'), {
            chart: {
                type: 'bar', height: 380, fontFamily: 'Inter',
                toolbar    : zoomToolbar,
                zoom       : { enabled: true },
                animations : { speed: 500 }
            },
            series     : [{ name: 'Pendapatan', data: byCat.map(function(c){ return parseFloat(c.revenue) || 0; }) }],
            xaxis      : {
                categories : byCat.map(function(c){ return c.category || ''; }),
                labels     : { style: { colors: '#64748b', fontSize: '11px' } },
                axisBorder : { show: false }, axisTicks: { show: false }
            },
            yaxis      : { labels: { formatter: fmtK, style: { colors: '#64748b', fontSize: '11px' } } },
            plotOptions: { bar: { borderRadius: 8, columnWidth: '50%', distributed: true } },
            colors     : ['#1E3A5F','#F59E0B','#22D3EE','#10B981','#6366F1'],
            dataLabels : { enabled: false },
            legend     : { show: false },
            grid       : grid,
            tooltip    : { y: { formatter: function(v){ return '$' + Number(v).toLocaleString(); } } }
        }).render();
    }

    if (byDow.length && document.getElementById('chartDow')) {
        new ApexCharts(document.getElementById('chartDow'), {
            chart: {
                type: 'bar', height: 300, fontFamily: 'Inter',
                toolbar    : zoomToolbar,
                zoom       : { enabled: true },
                animations : { speed: 500 }
            },
            series     : [{ name: 'Pendapatan', data: byDow.map(function(d){ return parseFloat(d.revenue) || 0; }) }],
            xaxis      : {
                categories : byDow.map(function(d){ return d.day_name || ''; }),
                labels     : { style: { colors: '#64748b', fontSize: '11px' } },
                axisBorder : { show: false }, axisTicks: { show: false }
            },
            yaxis      : { labels: { formatter: fmtK, style: { colors: '#64748b', fontSize: '11px' } } },
            plotOptions: { bar: { borderRadius: 8, columnWidth: '45%' } },
            colors     : ['#22D3EE'],
            fill       : { type: 'gradient', gradient: { shade: 'light', type: 'vertical', opacityFrom: 0.95, opacityTo: 0.55 } },
            dataLabels : { enabled: false },
            legend     : { show: false },
            grid       : grid,
            tooltip    : { y: { formatter: function(v){ return '$' + Number(v).toLocaleString(); } } }
        }).render();
    }

})();
</script>