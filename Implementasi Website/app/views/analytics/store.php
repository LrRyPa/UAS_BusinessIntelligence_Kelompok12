<div class="filter-bar card">
    <form method="GET" action="<?= base_url() ?>/index.php" class="filter-form">
        <input type="hidden" name="r" value="stores/analytics">
        <div class="filter-group">
            <label>Dari</label>
            <input type="date" name="start_date" value="<?= e($start_date) ?>" min="<?= e($bounds['min_date']) ?>" max="<?= e($bounds['max_date']) ?>">
        </div>
        <div class="filter-group">
            <label>Sampai</label>
            <input type="date" name="end_date" value="<?= e($end_date) ?>" min="<?= e($bounds['min_date']) ?>" max="<?= e($bounds['max_date']) ?>">
        </div>
        <button class="btn btn-primary" type="submit">Terapkan</button>
        <a class="btn btn-ghost" href="<?= url('stores/analytics') ?>">Reset</a>
    </form>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header"><div><div class="card-eyebrow">Geografis</div><h3>Pendapatan Berdasarkan Kota</h3></div></div>
        <div id="chartCity" class="chart-area"></div>
    </div>
    <div class="card">
        <div class="card-header"><div><div class="card-eyebrow">Tipe Lokasi</div><h3>Pendapatan Berdasarkan Tipe Lokasi</h3></div></div>
        <div id="chartLocation" class="chart-area"></div>
    </div>
</div>

<div class="card">
    <div class="card-header"><div><div class="card-eyebrow">Performa</div><h3>Peringkat Toko</h3></div></div>
    <div class="table-wrap"><table class="table table-clean">
        <thead><tr>
            <th>#</th><th>Toko</th><th>Kota</th><th>Lokasi</th><th class="num">Usia (thn)</th>
            <th class="num">Pesanan</th><th class="num">Unit</th><th class="num">Pendapatan</th><th class="num">Laba</th>
        </tr></thead>
        <tbody>
        <?php foreach ($ranking as $i => $st): ?>
        <tr>
            <td><span class="rank-mini <?= $i < 3 ? 'rank-top' : '' ?>"><?= $i+1 ?></span></td>
            <td><strong><?= e($st['store_name']) ?></strong></td>
            <td><?= e($st['store_city']) ?></td>
            <td><span class="chip chip-soft"><?= e($st['store_location']) ?></span></td>
            <td class="num"><?= number_format((float)$st['store_age_years'], 1) ?></td>
            <td class="num"><?= compact_number((int)$st['orders']) ?></td>
            <td class="num"><?= compact_number((int)$st['units']) ?></td>
            <td class="num"><strong><?= compact_money((float)$st['revenue']) ?></strong></td>
            <td class="num"><?= compact_money((float)$st['profit']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>

<script>
(function() {
    const byCity     = <?= json_encode($by_city) ?>;
    const byLocation = <?= json_encode($by_location) ?>;

    new ApexCharts(document.getElementById('chartCity'), {
        chart:{ type:'bar', height:480, fontFamily:'Inter', toolbar:{show:false}, animations:{speed:500} },
        series:[{ name:'Pendapatan', data: byCity.map(c => parseFloat(c.revenue)) }],
        xaxis:{ categories: byCity.map(c => c.city), labels:{ formatter: v => '$' + (v >= 1000 ? (v/1000).toFixed(0)+'K':v) } },
        plotOptions:{ bar:{ horizontal:true, borderRadius:5, distributed:true, barHeight:'70%' } },
        colors:['#1E3A5F','#22D3EE','#F59E0B','#10B981','#6366F1','#EC4899','#3B82F6','#F97316','#14B8A6','#8B5CF6','#EF4444','#84CC16','#06B6D4','#A855F7','#F43F5E'],
        dataLabels:{enabled:false}, legend:{show:false},
        tooltip:{ y:{ formatter: v => '$' + Number(v).toLocaleString() } },
        grid:{ borderColor:'#eef2f7' }
    }).render();

    new ApexCharts(document.getElementById('chartLocation'), {
        chart:{ type:'donut', height:340, fontFamily:'Inter', animations:{speed:500} },
        series: byLocation.map(l => parseFloat(l.revenue)),
        labels: byLocation.map(l => l.location),
        colors:['#1E3A5F','#F59E0B','#22D3EE','#10B981'],
        legend:{ position:'bottom' },
        plotOptions:{ pie:{ donut:{ size:'68%' } } },
        stroke:{ colors:['#fff'] },
        tooltip:{ y:{ formatter: v => '$' + Number(v).toLocaleString() } }
    }).render();
})();
</script>