<div class="kpi-grid">
    <div class="kpi-card kpi-blue">
        <div class="kpi-top"><span class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8V21H3V8M1 3h22v5z"/></svg></span></div>
        <div class="kpi-value"><?= compact_number((int)$summary['total_stock']) ?></div>
        <div class="kpi-label">Total Stok</div>
    </div>
    <div class="kpi-card kpi-red">
        <div class="kpi-top"><span class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></span></div>
        <div class="kpi-value"><?= compact_number((int)$summary['out_count']) ?></div>
        <div class="kpi-label">Stok Habis</div>
    </div>
    <div class="kpi-card kpi-orange">
        <div class="kpi-top"><span class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg></span></div>
        <div class="kpi-value"><?= compact_number((int)$summary['low_count']) ?></div>
        <div class="kpi-label">Stok Rendah (≤10)</div>
    </div>
    <div class="kpi-card kpi-green">
        <div class="kpi-top"><span class="kpi-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></span></div>
        <div class="kpi-value"><?= compact_number((int)$summary['overstock_count']) ?></div>
        <div class="kpi-label">Stok Berlebih (&gt;50)</div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><div><div class="card-eyebrow">Kesehatan Inventaris</div><h3>Distribusi Stok</h3></div></div>
        <div id="chartDist" class="chart-area"></div>
    </div>
    <div class="card">
        <div class="card-header"><div><div class="card-eyebrow">Penyimpanan</div><h3>Stok Berdasarkan Kategori</h3></div></div>
        <div id="chartByCat" class="chart-area"></div>
    </div>
</div>

<div class="card">
    <div class="card-header"><div><div class="card-eyebrow">Inventaris Teratas</div><h3>Stok Berdasarkan Toko (Top 10)</h3></div></div>
    <div id="chartByStore" class="chart-area"></div>
</div>

<div class="card">
    <div class="card-header">
        <div><div class="card-eyebrow">Perlu Tindakan</div><h3>Peringatan Stok Rendah</h3></div>
        <?php if (Auth::can('crud.inventory')): ?>
        <a href="<?= url('inventory') ?>" class="link-more">Kelola inventaris →</a>
        <?php endif; ?>
    </div>
    <div class="table-wrap"><table class="table">
        <thead><tr>
            <th>Produk</th><th>Kategori</th><th>Toko</th><th>Kota</th><th class="num">Stok</th><th>Status</th>
        </tr></thead>
        <tbody>
        <?php foreach ($low_stock as $r): ?>
        <tr>
            <td><strong><?= e($r['product_name']) ?></strong></td>
            <td><?= e($r['product_category']) ?></td>
            <td><?= e($r['store_name']) ?></td>
            <td><?= e($r['store_city']) ?></td>
            <td class="num"><strong><?= e((string)$r['stock_on_hand']) ?></strong></td>
            <td>
                <?php if ((int)$r['stock_on_hand'] === 0): ?>
                <span class="chip chip-danger">Stok Habis</span>
                <?php else: ?>
                <span class="chip chip-warning">Rendah</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>

<script>
(function() {
    const dist    = <?= json_encode($distribution) ?>;
    const byCat   = <?= json_encode($by_category) ?>;
    const byStore = <?= json_encode($by_store) ?>;

    new ApexCharts(document.getElementById('chartDist'), {
        chart:{ type:'donut', height:340, fontFamily:'Inter', animations:{speed:500} },
        series:[
            parseInt(dist.out_stock||0),
            parseInt(dist.low_stock||0),
            parseInt(dist.normal||0),
            parseInt(dist.overstock||0)
        ],
        labels:['Stok Habis','Rendah (1-10)','Normal (11-50)','Stok Berlebih (>50)'],
        colors:['#EF4444','#F59E0B','#22D3EE','#10B981'],
        legend:{ position:'bottom' },
        plotOptions:{ pie:{ donut:{ size:'70%', labels:{ show:true, total:{ show:true, label:'Data' } } } } },
        stroke:{ colors:['#fff'] }
    }).render();

    new ApexCharts(document.getElementById('chartByCat'), {
        chart:{ type:'bar', height:340, fontFamily:'Inter', toolbar:{show:false}, animations:{speed:500} },
        series:[{ name:'Stok', data: byCat.map(c => parseInt(c.stock)) }],
        xaxis:{ categories: byCat.map(c => c.category) },
        plotOptions:{ bar:{ borderRadius:8, columnWidth:'50%', distributed:true } },
        colors:['#1E3A5F','#F59E0B','#22D3EE','#10B981','#6366F1'],
        dataLabels:{enabled:false}, legend:{show:false},
        grid:{ borderColor:'#eef2f7' }
    }).render();

    new ApexCharts(document.getElementById('chartByStore'), {
        chart:{ type:'bar', height:380, fontFamily:'Inter', toolbar:{show:false}, animations:{speed:500} },
        series:[{ name:'Stok', data: byStore.map(s => parseInt(s.stock)) }],
        xaxis:{ categories: byStore.map(s => s.store_name) },
        plotOptions:{ bar:{ horizontal:true, borderRadius:6, distributed:true, barHeight:'65%' } },
        colors:['#1E3A5F','#22D3EE','#F59E0B','#10B981','#6366F1','#EC4899','#3B82F6','#F97316','#14B8A6','#8B5CF6'],
        dataLabels:{enabled:false}, legend:{show:false},
        grid:{ borderColor:'#eef2f7' }
    }).render();
})();
</script>