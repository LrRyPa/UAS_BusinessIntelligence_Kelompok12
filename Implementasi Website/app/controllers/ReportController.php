<?php
class ReportController extends Controller
{
    private const VALID_TYPES = [
        'sales_summary',
        'product_performance',
        'store_ranking',
        'category_sales',
    ];

    public function index(): void
    {
        Auth::require('reports.view');

        $dash   = new DashboardModel();
        $bounds = $dash->dateBounds();

        $minDate = !empty($bounds['min_date']) ? $bounds['min_date'] : date('Y-01-01');
        $maxDate = !empty($bounds['max_date']) ? $bounds['max_date'] : date('Y-m-d');

        $start = $this->parseDate((string)input('start_date', $minDate), $minDate);
        $end   = $this->parseDate((string)input('end_date',   $maxDate), $maxDate);

        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        $report_type = (string)input('type', 'sales_summary');
        if (!in_array($report_type, self::VALID_TYPES, true)) {
            $report_type = 'sales_summary';
        }

        $sales = new SalesModel();
        $store = new StoreModel();
        $prod  = new ProductModel();

        $data = match ($report_type) {
            'sales_summary'       => $sales->monthlyTrend($start, $end, null, null),
            'product_performance' => $prod->topRevenue(50, $start, $end),
            'store_ranking'       => $store->ranking($start, $end),
            'category_sales'      => $prod->categoryPerformance($start, $end),
            default               => [],
        };

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

        $dash    = new DashboardModel();
        $bounds  = $dash->dateBounds();
        $minDate = $bounds['min_date'] ?? date('Y-01-01');
        $maxDate = $bounds['max_date'] ?? date('Y-m-d');

        $type  = (string)input('type', 'sales_summary');
        $start = $this->parseDate((string)input('start_date'), $minDate);
        $end   = $this->parseDate((string)input('end_date'),   $maxDate);

        if (!in_array($type, self::VALID_TYPES, true)) $type = 'sales_summary';

        $sales    = new SalesModel();
        $prod     = new ProductModel();
        $store    = new StoreModel();
        $rows     = [];
        $headers  = [];
        $filename = $type . '_' . date('Ymd_His') . '.csv';

        switch ($type) {
            case 'sales_summary':
                $headers = ['Period', 'Label', 'Revenue', 'Profit', 'Orders'];
                foreach ($sales->monthlyTrend($start, $end, null, null) as $r) {
                    $rows[] = [$r['period'] ?? '', $r['label'] ?? '', $r['revenue'] ?? 0, $r['profit'] ?? 0, $r['orders'] ?? 0];
                }
                break;

            case 'product_performance':
                $headers = ['Product', 'Category', 'Tier', 'Revenue', 'Units', 'Profit', 'Margin %'];
                foreach ($prod->topRevenue(500, $start, $end) as $r) {
                    $rows[] = [$r['product_name'] ?? '', $r['product_category'] ?? '', $r['price_tier'] ?? '', $r['revenue'] ?? 0, $r['units'] ?? 0, $r['profit'] ?? 0, $r['margin_pct'] ?? 0];
                }
                break;

            case 'store_ranking':
                $headers = ['Store', 'City', 'Location', 'Revenue', 'Orders', 'Units', 'Profit'];
                foreach ($store->ranking($start, $end) as $r) {
                    $rows[] = [$r['store_name'] ?? '', $r['store_city'] ?? '', $r['store_location'] ?? '', $r['revenue'] ?? 0, $r['orders'] ?? 0, $r['units'] ?? 0, $r['profit'] ?? 0];
                }
                break;

            case 'category_sales':
                $headers = ['Category', 'Revenue', 'Units', 'Products', 'Profit'];
                foreach ($prod->categoryPerformance($start, $end) as $r) {
                    $rows[] = [$r['category'] ?? '', $r['revenue'] ?? 0, $r['units'] ?? 0, $r['products'] ?? 0, $r['profit'] ?? 0];
                }
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

        $dash    = new DashboardModel();
        $bounds  = $dash->dateBounds();
        $minDate = $bounds['min_date'] ?? date('Y-01-01');
        $maxDate = $bounds['max_date'] ?? date('Y-m-d');

        $type  = (string)input('type', 'sales_summary');
        $start = $this->parseDate((string)input('start_date'), $minDate);
        $end   = $this->parseDate((string)input('end_date'),   $maxDate);

        if (!in_array($type, self::VALID_TYPES, true)) $type = 'sales_summary';

        $sales = new SalesModel();
        $prod  = new ProductModel();
        $store = new StoreModel();

        $data = match ($type) {
            'sales_summary'       => $sales->monthlyTrend($start, $end, null, null),
            'product_performance' => $prod->topRevenue(100, $start, $end),
            'store_ranking'       => $store->ranking($start, $end),
            'category_sales'      => $prod->categoryPerformance($start, $end),
            default               => [],
        };

        $this->render('reports/print', [
            'page_title'  => 'Print Report',
            'data'        => $data,
            'report_type' => $type,
            'start_date'  => $start,
            'end_date'    => $end,
            'kpis'        => $dash->kpis($start, $end),
        ], 'layouts/print');
    }

    private function parseDate(string $raw, string $default): string
    {
        $raw = trim($raw);
        if ($raw === '') return $default;

        try {
            $dt = DateTime::createFromFormat('Y-m-d', $raw);
            if ($dt && $dt->format('Y-m-d') === $raw) {
                return $raw;
            }

            $ts = strtotime($raw);
            if ($ts !== false && $ts > 0) {
                return date('Y-m-d', $ts);
            }
        } catch (Throwable $e) {
            
        }

        return $default;
    }
}