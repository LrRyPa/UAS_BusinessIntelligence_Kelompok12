<?php
class ReportController extends Controller
{
    public function index(): void
    {
        Auth::require('reports.view');
        $dash   = new DashboardModel();
        $bounds = $dash->dateBounds();
        $start  = (string)input('start_date', $bounds['min_date']);
        $end    = (string)input('end_date',   $bounds['max_date']);

        $sales  = new SalesModel();
        $store  = new StoreModel();
        $prod   = new ProductModel();
        $report_type = (string)input('type', 'sales_summary');

        $data = [];
        switch ($report_type) {
            case 'sales_summary':
                $data = $sales->monthlyTrend($start, $end);
                break;
            case 'product_performance':
                $data = $prod->topRevenue(50, $start, $end);
                break;
            case 'store_ranking':
                $data = $store->ranking($start, $end);
                break;
            case 'category_sales':
                $data = $prod->categoryPerformance($start, $end);
                break;
        }

        $this->render('reports/index', [
            'page'        => 'reports',
            'page_title'  => 'Laporan',
            'kpis'        => $dash->kpis($start, $end),
            'data'        => $data,
            'report_type' => $report_type,
            'bounds'      => $bounds,
            'start_date'  => $start,
            'end_date'    => $end,
        ]);
    }

    public function exportCsv(): void
    {
        Auth::require('reports.export');
        $type   = (string)input('type', 'sales_summary');
        $start  = (string)input('start_date');
        $end    = (string)input('end_date');

        $sales = new SalesModel();
        $prod  = new ProductModel();
        $store = new StoreModel();
        $rows  = [];
        $headers = [];
        $filename = $type . '_' . date('Ymd_His') . '.csv';

        switch ($type) {
            case 'sales_summary':
                $headers = ['Periode','Label','Pendapatan','Laba','Pesanan'];
                $data = $sales->monthlyTrend($start, $end);
                foreach ($data as $r) $rows[] = [$r['period'],$r['label'],$r['revenue'],$r['profit'],$r['orders']];
                break;
            case 'product_performance':
                $headers = ['Produk','Kategori','Tier','Pendapatan','Unit','Laba','Margin %'];
                $data = $prod->topRevenue(500, $start, $end);
                foreach ($data as $r) $rows[] = [$r['product_name'],$r['product_category'],$r['price_tier'],$r['revenue'],$r['units'],$r['profit'],$r['margin_pct']];
                break;
            case 'store_ranking':
                $headers = ['Toko','Kota','Lokasi','Pendapatan','Pesanan','Unit','Laba'];
                $data = $store->ranking($start, $end);
                foreach ($data as $r) $rows[] = [$r['store_name'],$r['store_city'],$r['store_location'],$r['revenue'],$r['orders'],$r['units'],$r['profit']];
                break;
            case 'category_sales':
                $headers = ['Kategori','Pendapatan','Unit','Produk','Laba'];
                $data = $prod->categoryPerformance($start, $end);
                foreach ($data as $r) $rows[] = [$r['category'],$r['revenue'],$r['units'],$r['products'],$r['profit']];
                break;
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');

        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $headers);
        foreach ($rows as $r) fputcsv($out, $r);
        fclose($out);
        exit;
    }

    public function exportPrint(): void
    {
        Auth::require('reports.export');
        $type  = (string)input('type', 'sales_summary');
        $start = (string)input('start_date');
        $end   = (string)input('end_date');

        $sales = new SalesModel();
        $prod  = new ProductModel();
        $store = new StoreModel();
        $dash  = new DashboardModel();
        $data  = [];
        switch ($type) {
            case 'sales_summary':         $data = $sales->monthlyTrend($start, $end);    break;
            case 'product_performance':   $data = $prod->topRevenue(100, $start, $end);  break;
            case 'store_ranking':         $data = $store->ranking($start, $end);         break;
            case 'category_sales':        $data = $prod->categoryPerformance($start, $end); break;
        }
        $this->render('reports/print', [
            'page_title'  => 'Cetak Laporan',
            'data'        => $data,
            'report_type' => $type,
            'start_date'  => $start,
            'end_date'    => $end,
            'kpis'        => $dash->kpis($start, $end),
        ], 'layouts/print');
    }
}