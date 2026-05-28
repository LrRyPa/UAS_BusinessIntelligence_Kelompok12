<?php
$types = [
    'sales_summary' => [
        'label'       => 'Ringkasan Penjualan',
        'icon'        => 'M3 17l6-6 4 4 8-8',
        'chart_title' => 'Tren Pendapatan & Laba Bulanan',
        'chart_sub'   => 'Revenue vs Profit per periode',
    ],
    'product_performance' => [
        'label'       => 'Performa Produk',
        'icon'        => 'M21 16V8L12 3 3 8v8l9 5 9-5z',
        'chart_title' => 'Top 10 Produk — Pendapatan',
        'chart_sub'   => 'Revenue & Profit per produk',
    ],
    'store_ranking' => [
        'label'       => 'Peringkat Toko',
        'icon'        => 'M3 9l1-6h16l1 6M3 9v11h18V9',
        'chart_title' => 'Pendapatan per Toko',
        'chart_sub'   => 'Revenue & Profit tiap toko',
    ],
    'category_sales' => [
        'label'       => 'Penjualan Kategori',
        'icon'        => 'M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2',
        'chart_title' => 'Distribusi Penjualan Kategori',
        'chart_sub'   => 'Revenue & unit per kategori produk',
    ],
];

$current   = $types[$report_type] ?? $types['sales_summary'];
$dataCount = count($data ?? []);

$rankClass = static function (int $i): string {
    return match (true) { $i === 0 => 'rk-1', $i === 1 => 'rk-2', $i === 2 => 'rk-3', default => '' };
};
$marginClass = static function (float $pct): string {
    if ($pct >= 30) return 'mg-high';
    if ($pct >= 15) return 'mg-mid';
    return 'mg-low';
};
?>

<?php /* ════════ HIDDEN MASTER FORM ════════════════════════
         All nav items and date pickers write into this form,
         then trigger submit(). action matches other GET forms
         in the codebase: base_url()/index.php?r=reports
         ═══════════════════════════════════════════════════ */ ?>
<form id="rpForm" method="GET" action="<?= base_url() ?>/index.php" style="display:none">
    <input type="hidden" name="r"          value="reports">
    <input type="hidden" id="rpType"       name="type"       value="<?= e($report_type) ?>">
    <input type="hidden" id="rpStartDate"  name="start_date" value="<?= e($start_date) ?>">
    <input type="hidden" id="rpEndDate"    name="end_date"   value="<?= e($end_date) ?>">
</form>

<?php /* ════════ KPI STRIP ════════════════════════════════
         Uses existing .kpi-grid / .kpi-card from app.css
         ═══════════════════════════════════════════════════ */ ?>
<div class="kpi-grid" style="margin-bottom:20px">

    <div class="kpi-card kpi-cyan">
        <div class="kpi-top">
            <span class="kpi-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </span>
        </div>
        <div class="kpi-value"><?= compact_money((float)($kpis['total_revenue'] ?? 0)) ?></div>
        <div class="kpi-label">Total Pendapatan</div>
    </div>

    <div class="kpi-card kpi-orange">
        <div class="kpi-top">
            <span class="kpi-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
            </span>
        </div>
        <div class="kpi-value"><?= compact_number((int)($kpis['total_orders'] ?? 0)) ?></div>
        <div class="kpi-label">Total Pesanan</div>
    </div>

    <div class="kpi-card kpi-blue">
        <div class="kpi-top">
            <span class="kpi-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8L12 3 3 8v8l9 5 9-5z"/>
                </svg>
            </span>
        </div>
        <div class="kpi-value"><?= compact_number((int)($kpis['total_units'] ?? 0)) ?></div>
        <div class="kpi-label">Unit Terjual</div>
    </div>

    <div class="kpi-card kpi-green">
        <div class="kpi-top">
            <span class="kpi-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                    <polyline points="17 6 23 6 23 12"/>
                </svg>
            </span>
        </div>
        <div class="kpi-value"><?= compact_money((float)($kpis['total_profit'] ?? 0)) ?></div>
        <div class="kpi-label">Laba Kotor</div>
    </div>

</div><!-- /kpi-grid -->


<?php /* ════════ TWO-COLUMN LAYOUT ════════════════════════
         .rp-layout  → defined in reports.css
         lives entirely inside .content from main layout
         ═══════════════════════════════════════════════════ */ ?>
<div class="rp-layout">

    <!-- ════ LEFT SIDEBAR: type nav + date filter ═══════ -->
    <aside class="rp-sidebar">

        <div class="rp-sidebar-head">Tipe Laporan</div>

        <nav class="rp-nav">
            <?php foreach ($types as $key => $meta): ?>
            <?php /* Each item is an <a> tag that builds the full URL directly.
                     This is the most reliable approach: no JS needed to switch type,
                     start_date / end_date are preserved via URL params. */ ?>
            <a class="rp-nav-item <?= $report_type === $key ? 'is-active' : '' ?>"
               href="<?= base_url() ?>/index.php?r=reports&type=<?= urlencode($key) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>">
                <span class="rp-nav-icon">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2">
                        <path d="<?= $meta['icon'] ?>"/>
                    </svg>
                </span>
                <span class="rp-nav-title"><?= e($meta['label']) ?></span>
            </a>
            <?php endforeach; ?>
        </nav>

        <div class="rp-sidebar-filter">
            <div class="rp-filter-row">
                <label class="rp-filter-label">Dari</label>
                <input type="date"
                       class="rp-filter-input"
                       id="uiStartDate"
                       value="<?= e($start_date) ?>"
                       min="<?= e($bounds['min_date'] ?? '') ?>"
                       max="<?= e($bounds['max_date'] ?? '') ?>"
                       onchange="document.getElementById('rpStartDate').value = this.value">
            </div>
            <div class="rp-filter-row">
                <label class="rp-filter-label">Sampai</label>
                <input type="date"
                       class="rp-filter-input"
                       id="uiEndDate"
                       value="<?= e($end_date) ?>"
                       min="<?= e($bounds['min_date'] ?? '') ?>"
                       max="<?= e($bounds['max_date'] ?? '') ?>"
                       onchange="document.getElementById('rpEndDate').value = this.value">
            </div>
            <button type="button"
                    class="btn btn-primary btn-sm btn-block"
                    onclick="document.getElementById('rpForm').submit()">
                Buat Laporan
            </button>
        </div>

    </aside><!-- /rp-sidebar -->


    <!-- ════ RIGHT: CHART + TABLE ═══════════════════════ -->
    <div class="rp-main">

        <!-- Chart card -->
        <div class="rp-chart-card">
            <div class="rp-chart-head">
                <div>
                    <p class="rp-chart-title"><?= e($current['chart_title']) ?></p>
                    <p class="rp-chart-sub"><?= e($current['chart_sub']) ?></p>
                </div>
                <div class="rp-chart-legend" id="rpLegend"></div>
            </div>
            <div id="rp-chart"></div>
        </div><!-- /chart card -->

        <!-- Result card -->
        <div class="rp-result-card">

            <div class="rp-result-head">
                <div>
                    <div class="rp-result-eyebrow">
                        <?= e(date('d M Y', strtotime($start_date))) ?>
                        &mdash;
                        <?= e(date('d M Y', strtotime($end_date))) ?>
                    </div>
                    <h3 class="rp-result-title"><?= e($current['label']) ?></h3>
                </div>

                <?php if (Auth::can('reports.export')): ?>
                <div class="rp-actions">
                    <a class="btn btn-light btn-sm"
                       href="<?= url('reports/export') ?>&type=<?= urlencode($report_type) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Export CSV
                    </a>
                    <a class="btn btn-primary btn-sm"
                       href="<?= url('reports/print') ?>&type=<?= urlencode($report_type) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>"
                       target="_blank">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 6 2 18 2 18 9"/>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                            <rect x="6" y="14" width="12" height="8"/>
                        </svg>
                        Cetak / PDF
                    </a>
                </div>
                <?php endif; ?>
            </div><!-- /rp-result-head -->

            <!-- ════ DATA TABLE ═══════════════════════════════ -->
            <?php if ($dataCount === 0): ?>

            <div class="rp-empty">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                Tidak ada data untuk rentang tanggal ini.
            </div>

            <?php else: ?>
            <div class="rp-table-wrap">
                <table class="table table-clean">

                    <?php /* ── SALES SUMMARY ──────────────────────────────────────
                               Model: SalesModel::monthlyTrend()
                               Keys : label | revenue | profit | orders
                               ─────────────────────────────────────────────────── */ ?>
                    <?php if ($report_type === 'sales_summary'): ?>
                    <thead>
                        <tr>
                            <th style="width:46px;text-align:center">#</th>
                            <th>Periode</th>
                            <th class="num">Pendapatan</th>
                            <th class="num">Laba</th>
                            <th class="num">Pesanan</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $i => $r): ?>
                        <tr>
                            <td style="text-align:center">
                                <span class="rank-badge <?= $rankClass((int)$i) ?>"><?= (int)$i + 1 ?></span>
                            </td>
                            <td><strong><?= e((string)($r['label'] ?? '—')) ?></strong></td>
                            <td class="num td-revenue"><?= money((float)($r['revenue'] ?? 0)) ?></td>
                            <td class="num td-profit"><?= money((float)($r['profit']  ?? 0)) ?></td>
                            <td class="num"><?= compact_number((int)($r['orders'] ?? 0)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>

                    <?php /* ── PRODUCT PERFORMANCE ─────────────────────────────────
                               Model: ProductModel::topRevenue(50, $start, $end)
                               Keys : product_name | product_category | price_tier
                                      units | revenue | profit | margin_pct
                               ─────────────────────────────────────────────────── */ ?>
                    <?php elseif ($report_type === 'product_performance'): ?>
                    <thead>
                        <tr>
                            <th style="width:46px;text-align:center">#</th>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Tier</th>
                            <th class="num">Unit</th>
                            <th class="num">Pendapatan</th>
                            <th class="num">Laba</th>
                            <th class="num">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $i => $r):
                        $mgPct = (float)($r['margin_pct'] ?? 0);
                    ?>
                        <tr>
                            <td style="text-align:center">
                                <span class="rank-badge <?= $rankClass((int)$i) ?>"><?= (int)$i + 1 ?></span>
                            </td>
                            <td><strong><?= e((string)($r['product_name']     ?? '—')) ?></strong></td>
                            <td><?= e((string)($r['product_category'] ?? '—')) ?></td>
                            <td><span class="chip chip-soft"><?= e((string)($r['price_tier'] ?? '—')) ?></span></td>
                            <td class="num"><?= compact_number((int)($r['units'] ?? 0)) ?></td>
                            <td class="num td-revenue"><strong><?= money((float)($r['revenue'] ?? 0)) ?></strong></td>
                            <td class="num td-profit"><?= money((float)($r['profit'] ?? 0)) ?></td>
                            <td class="num">
                                <span class="margin-pill <?= $marginClass($mgPct) ?>">
                                    <?= number_format($mgPct, 1) ?>%
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>

                    <?php /* ── STORE RANKING ─────────────────────────────────────
                               Model: StoreModel::ranking($start, $end)
                               Keys : store_name | store_city | store_location
                                      orders | units | revenue | profit
                               ─────────────────────────────────────────────────── */ ?>
                    <?php elseif ($report_type === 'store_ranking'): ?>
                    <thead>
                        <tr>
                            <th style="width:46px;text-align:center">#</th>
                            <th>Toko</th>
                            <th>Kota</th>
                            <th>Lokasi</th>
                            <th class="num">Pesanan</th>
                            <th class="num">Unit</th>
                            <th class="num">Pendapatan</th>
                            <th class="num">Laba</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $i => $r): ?>
                        <tr>
                            <td style="text-align:center">
                                <span class="rank-badge <?= $rankClass((int)$i) ?>"><?= (int)$i + 1 ?></span>
                            </td>
                            <td><strong><?= e((string)($r['store_name']     ?? '—')) ?></strong></td>
                            <td><?= e((string)($r['store_city']     ?? '—')) ?></td>
                            <td><span class="chip chip-soft"><?= e((string)($r['store_location'] ?? '—')) ?></span></td>
                            <td class="num"><?= compact_number((int)($r['orders'] ?? 0)) ?></td>
                            <td class="num"><?= compact_number((int)($r['units']  ?? 0)) ?></td>
                            <td class="num td-revenue"><strong><?= money((float)($r['revenue'] ?? 0)) ?></strong></td>
                            <td class="num td-profit"><?= money((float)($r['profit'] ?? 0)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>

                    <?php /* ── CATEGORY SALES ───────────────────────────────────
                               Model: ProductModel::categoryPerformance($start, $end)
                               Keys : category | products | units | revenue | profit
                               ─────────────────────────────────────────────────── */ ?>
                    <?php elseif ($report_type === 'category_sales'): ?>
                    <thead>
                        <tr>
                            <th style="width:46px;text-align:center">#</th>
                            <th>Kategori</th>
                            <th class="num">Produk</th>
                            <th class="num">Unit</th>
                            <th class="num">Pendapatan</th>
                            <th class="num">Laba</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $i => $r): ?>
                        <tr>
                            <td style="text-align:center">
                                <span class="rank-badge <?= $rankClass((int)$i) ?>"><?= (int)$i + 1 ?></span>
                            </td>
                            <td><strong><?= e((string)($r['category'] ?? '—')) ?></strong></td>
                            <td class="num"><?= (int)($r['products'] ?? 0) ?></td>
                            <td class="num"><?= compact_number((int)($r['units'] ?? 0)) ?></td>
                            <td class="num td-revenue"><strong><?= money((float)($r['revenue'] ?? 0)) ?></strong></td>
                            <td class="num td-profit"><?= money((float)($r['profit'] ?? 0)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>

                    <?php endif; ?>

                </table>
            </div><!-- /rp-table-wrap -->

            <div class="rp-result-foot">
                <span><?= $dataCount ?> baris ditampilkan</span>
                <span>
                    <?= e($current['label']) ?>
                    &middot;
                    <?= e(date('d M Y', strtotime($start_date))) ?>
                    –
                    <?= e(date('d M Y', strtotime($end_date))) ?>
                </span>
            </div>

            <?php endif; /* end empty check */ ?>

        </div><!-- /rp-result-card -->

    </div><!-- /rp-main -->

</div><!-- /rp-layout -->


<?php /* ════════ APEXCHARTS BOOTSTRAP ════════════════════════
         KEY FIX: rpSelectType() is declared at WINDOW level,
         OUTSIDE DOMContentLoaded, so onclick="" in HTML can
         reach it. The chart init is the only part deferred
         to DOMContentLoaded (DOM must exist before render).

         PHP → JS data flow:
           $data   → json_encode → const DATA   (server rows)
           $report_type → json_encode → const REPORT_TYPE
         ═══════════════════════════════════════════════════ */ ?>
<script>
/* ── Global helper: called by sidebar <a> links (no JS needed
      for navigation, but kept for any programmatic use).       */
function rpSelectType(type) {
    document.getElementById('rpType').value = type;
    document.getElementById('rpForm').submit();
}

/* ── Chart initialisation: runs after DOM is ready ─────────── */
document.addEventListener('DOMContentLoaded', function () {

    /* PHP data injected as JSON ─────────────────────────────── */
    var REPORT_TYPE = <?= json_encode($report_type, JSON_THROW_ON_ERROR) ?>;
    var DATA        = <?= json_encode(array_values($data ?? []), JSON_THROW_ON_ERROR) ?>;

    /* Bail out early if nothing to chart */
    var chartEl = document.getElementById('rp-chart');
    if (!chartEl || !window.ApexCharts || DATA.length === 0) return;

    /* Colour palette matching app.css vars */
    var C = {
        blue   : '#1D4ED8',
        green  : '#10B981',
        accent : '#22D3EE',
        muted  : '#94A3B8',
        border : '#E5EAF1',
        text   : '#1F2937',
    };

    /* Axis label formatters */
    function fmtMoney(v) {
        var a = Math.abs(v);
        if (a >= 1e6) return '$' + (v / 1e6).toFixed(1) + 'M';
        if (a >= 1e3) return '$' + (v / 1e3).toFixed(0) + 'K';
        return '$' + v;
    }
    function fmtNum(v) {
        var a = Math.abs(v);
        if (a >= 1e6) return (v / 1e6).toFixed(1) + 'M';
        if (a >= 1e3) return (v / 1e3).toFixed(0) + 'K';
        return String(v);
    }

    /* Custom legend */
    function renderLegend(items) {
        var el = document.getElementById('rpLegend');
        if (!el) return;
        el.innerHTML = items.map(function (item) {
            return '<span class="rp-legend-dot"><i style="background:' + item[1] + '"></i>' + item[0] + '</span>';
        }).join('');
    }

    /* Shared base config */
    var shared = {
        chart: {
            fontFamily : 'Inter, sans-serif',
            toolbar    : { show: false },
            zoom       : { enabled: false },
            animations : { enabled: true, easing: 'easeinout', speed: 500 },
            background : 'transparent',
        },
        dataLabels : { enabled: false },
        legend     : { show: false },
        grid: {
            borderColor     : C.border,
            strokeDashArray : 4,
            xaxis           : { lines: { show: false } },
        },
        tooltip: {
            theme  : 'light',
            style  : { fontSize: '12px', fontFamily: 'Inter, sans-serif' },
            y      : { formatter: fmtMoney },
        },
    };

    var opts = null;

    /* ── 1. SALES SUMMARY → area chart: Revenue & Profit ─── */
    if (REPORT_TYPE === 'sales_summary') {
        var labels  = DATA.map(function(d){ return d.label   || ''; });
        var revenue = DATA.map(function(d){ return parseFloat(d.revenue) || 0; });
        var profit  = DATA.map(function(d){ return parseFloat(d.profit)  || 0; });

        renderLegend([['Pendapatan', C.blue], ['Laba', C.green]]);

        opts = Object.assign({}, shared, {
            chart  : Object.assign({}, shared.chart, { type: 'area', height: 230 }),
            series : [{ name: 'Pendapatan', data: revenue }, { name: 'Laba', data: profit }],
            colors : [C.blue, C.green],
            stroke : { curve: 'smooth', width: 2 },
            fill   : {
                type     : 'gradient',
                gradient : { shadeIntensity: 1, opacityFrom: 0.2, opacityTo: 0.02, stops: [0, 100] },
            },
            xaxis  : {
                categories : labels,
                labels     : { style: { fontSize: '11px', colors: C.muted } },
                axisBorder : { show: false },
                axisTicks  : { show: false },
            },
            yaxis  : { labels: { style: { fontSize: '11px', colors: C.muted }, formatter: fmtMoney } },
        });

    /* ── 2. PRODUCT PERFORMANCE → horizontal bar: Top 10 ── */
    } else if (REPORT_TYPE === 'product_performance') {
        var top10   = DATA.slice(0, 10);
        var labels  = top10.map(function(d){ return d.product_name || ''; });
        var revenue = top10.map(function(d){ return parseFloat(d.revenue) || 0; });
        var profit  = top10.map(function(d){ return parseFloat(d.profit)  || 0; });

        renderLegend([['Pendapatan', C.blue], ['Laba', C.green]]);

        opts = Object.assign({}, shared, {
            chart       : Object.assign({}, shared.chart, { type: 'bar', height: Math.max(230, top10.length * 28) }),
            plotOptions : { bar: { horizontal: true, barHeight: '55%', borderRadius: 4 } },
            series      : [{ name: 'Pendapatan', data: revenue }, { name: 'Laba', data: profit }],
            colors      : [C.blue, C.green],
            xaxis       : {
                categories : labels,
                labels     : { style: { fontSize: '11px', colors: C.muted }, formatter: fmtMoney },
                axisBorder : { show: false },
                axisTicks  : { show: false },
            },
            yaxis : { labels: { style: { fontSize: '11px', colors: C.text } } },
        });

    /* ── 3. STORE RANKING → grouped column bar ─────────── */
    } else if (REPORT_TYPE === 'store_ranking') {
        var labels  = DATA.map(function(d){ return d.store_name || ''; });
        var revenue = DATA.map(function(d){ return parseFloat(d.revenue) || 0; });
        var profit  = DATA.map(function(d){ return parseFloat(d.profit)  || 0; });

        renderLegend([['Pendapatan', C.blue], ['Laba', C.green]]);

        opts = Object.assign({}, shared, {
            chart       : Object.assign({}, shared.chart, { type: 'bar', height: 230 }),
            plotOptions : { bar: { columnWidth: '55%', borderRadius: 4 } },
            series      : [{ name: 'Pendapatan', data: revenue }, { name: 'Laba', data: profit }],
            colors      : [C.blue, C.green],
            xaxis       : {
                categories : labels,
                labels     : { style: { fontSize: '10px', colors: C.muted }, rotate: -35 },
                axisBorder : { show: false },
                axisTicks  : { show: false },
            },
            yaxis : { labels: { style: { fontSize: '11px', colors: C.muted }, formatter: fmtMoney } },
        });

    } else if (REPORT_TYPE === 'category_sales') {
        var labels  = DATA.map(function(d){ return d.category || ''; });
        var revenue = DATA.map(function(d){ return parseFloat(d.revenue) || 0; });
        var units   = DATA.map(function(d){ return parseInt(d.units, 10) || 0; });

        renderLegend([['Pendapatan', C.blue], ['Unit', C.accent]]);

        opts = Object.assign({}, shared, {
            chart       : Object.assign({}, shared.chart, { type: 'bar', height: 230 }),
            plotOptions : { bar: { columnWidth: '50%', borderRadius: 4 } },
            series      : [{ name: 'Pendapatan', data: revenue }, { name: 'Unit', data: units }],
            colors      : [C.blue, C.accent],
            xaxis       : {
                categories : labels,
                labels     : { style: { fontSize: '11px', colors: C.muted } },
                axisBorder : { show: false },
                axisTicks  : { show: false },
            },
            yaxis : [
                { seriesName: 'Pendapatan', labels: { style: { fontSize: '11px', colors: C.muted }, formatter: fmtMoney } },
                { seriesName: 'Unit', opposite: true, labels: { style: { fontSize: '11px', colors: C.muted }, formatter: fmtNum } },
            ],
        });
    }

    if (opts) {
        new ApexCharts(chartEl, opts).render();
    }

}); 
</script>