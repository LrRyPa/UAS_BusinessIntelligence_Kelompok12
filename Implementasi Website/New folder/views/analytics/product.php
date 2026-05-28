<div class="filter-bar card">
    <form method="GET" action="<?= base_url() ?>/index.php" class="filter-form">
        <input type="hidden" name="r" value="products/analytics">
        <div class="filter-group">
            <label>Dari</label>
            <input type="date" name="start_date" value="<?= e($start_date) ?>" min="<?= e($bounds['min_date']) ?>" max="<?= e($bounds['max_date']) ?>">
        </div>
        <div class="filter-group">
            <label>Sampai</label>
            <input type="date" name="end_date" value="<?= e($end_date) ?>" min="<?= e($bounds['min_date']) ?>" max="<?= e($bounds['max_date']) ?>">
        </div>
        <button class="btn btn-primary" type="submit">Terapkan</button>
        <a class="btn btn-ghost" href="<?= url('products/analytics') ?>">Reset</a>
    </form>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><div><div class="card-eyebrow">Kategori</div><h3>Performa Kategori</h3></div></div>
        <div id="chartCatPerf" class="chart-area"></div>
    </div>
    <div class="card">
        <div class="card-header"><div><div class="card-eyebrow">Tingkat Harga</div><h3>Distribusi Pendapatan per Tier</h3></div></div>
        <div id="chartTier" class="chart-area"></div>
    </div>
</div>

<div class="card">
    <div class="card-header"><div><div class="card-eyebrow">Produk Terlaris</div><h3>10 Produk Teratas Berdasarkan Pendapatan</h3></div></div>
    <div id="chartTopRev" class="chart-area"></div>
</div>

<div class="card">
    <div class="card-header"><div><div class="card-eyebrow">Performa Detail</div><h3>Pendapatan & Margin Produk</h3></div></div>
    <div class="table-wrap"><table class="table table-clean">
        <thead><tr>
            <th>#</th><th>Produk</th><th>Kategori</th><th>Tier</th>
            <th class="num">Unit</th><th class="num">Pendapatan</th>
            <th class="num">Laba</th><th class="num">Margin %</th>
        </tr></thead>
        <tbody>
        <?php foreach ($top_revenue as $i => $p): ?>
        <tr>
            <td><span class="rank-mini"><?= $i+1 ?></span></td>
            <td><strong><?= e($p['product_name']) ?></strong></td>
            <td><span class="chip chip-soft"><?= e($p['product_category']) ?></span></td>
            <td><span class="chip chip-tier-<?= e(strtolower(str_replace([' ','-'], '', $p['price_tier']))) ?>"><?= e($p['price_tier']) ?></span></td>
            <td class="num"><?= compact_number((int)$p['units']) ?></td>
            <td class="num"><strong><?= compact_money((float)$p['revenue']) ?></strong></td>
            <td class="num"><?= compact_money((float)$p['profit']) ?></td>
            <td class="num">
                <span class="chip chip-margin"><?= number_format((float)$p['margin_pct'], 1) ?>%</span>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>

<script>
(function() {
    const catPerf = <?= json_encode($cat_perf) ?>;
    const tierPerf = <?= json_encode($tier_perf) ?>;
    const topRev  = <?= json_encode($top_revenue) ?>;

    new ApexCharts(document.getElementById('chartCatPerf'), {
        chart:{ type:'bar', height:340, fontFamily:'Inter', toolbar:{show:false}, animations:{speed:500} },
        series:[
            { name:'Pendapatan', data: catPerf.map(c => parseFloat(c.revenue)) },
            { name:'Laba',  data: catPerf.map(c => parseFloat(c.profit))  }
        ],
        xaxis:{ categories: catPerf.map(c => c.category) },
        yaxis:{ labels:{ formatter: v => '$' + (v >= 1000 ? (v/1000).toFixed(0)+'K':v) } },
        plotOptions:{ bar:{ borderRadius:6, columnWidth:'55%' } },
        colors:['#1E3A5F','#22D3EE'],
        dataLabels:{enabled:false},
        legend:{ position:'top', horizontalAlign:'right' },
        grid:{ borderColor:'#eef2f7' },
        tooltip:{ y:{ formatter: v => '$' + Number(v).toLocaleString() } }
    }).render();

    new ApexCharts(document.getElementById('chartTier'), {
        chart:{ type:'donut', height:340, fontFamily:'Inter', animations:{speed:500} },
        series: tierPerf.map(t => parseFloat(t.revenue)),
        labels: tierPerf.map(t => t.price_tier),
        colors:['#F59E0B','#1E3A5F','#22D3EE'],
        legend:{ position:'bottom' },
        plotOptions:{ pie:{ donut:{ size:'68%' } } },
        dataLabels:{ formatter: v => Number(v).toFixed(1)+'%' },
        stroke:{ colors:['#fff'] },
        tooltip:{ y:{ formatter: v => '$' + Number(v).toLocaleString() } }
    }).render();

    new ApexCharts(document.getElementById('chartTopRev'), {
        chart:{ type:'bar', height:380, fontFamily:'Inter', toolbar:{show:false}, animations:{speed:500} },
        series:[{ name:'Pendapatan', data: topRev.map(p => parseFloat(p.revenue)) }],
        xaxis:{ categories: topRev.map(p => p.product_name), labels:{ formatter: v => '$' + (v >= 1000 ? (v/1000).toFixed(0)+'K':v) } },
        plotOptions:{ bar:{ horizontal:true, borderRadius:6, distributed:true, barHeight:'65%' } },
        colors:['#1E3A5F','#22D3EE','#F59E0B','#10B981','#6366F1','#EC4899','#3B82F6','#F97316','#14B8A6','#8B5CF6'],
        dataLabels:{enabled:false}, legend:{show:false},
        tooltip:{ y:{ formatter: v => '$' + Number(v).toLocaleString() } },
        grid:{ borderColor:'#eef2f7' }
    }).render();
})();
</script>