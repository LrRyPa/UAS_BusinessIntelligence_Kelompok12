<?php

class DashboardModel
{
    public function kpis(?string $startDate = null, ?string $endDate = null): array
    {
        $where  = '';
        $params = [];

        if ($startDate && $endDate) {
            $where  = "WHERE DATE(d.full_date) BETWEEN DATE(?) AND DATE(?)";
            $params = [$startDate, $endDate];
        }

        $row = Database::fetchOne(
            "SELECT
                ROUND(COALESCE(SUM(s.revenue), 0), 2)      AS total_revenue,
                COUNT(DISTINCT s.sale_id)                  AS total_orders,
                COALESCE(SUM(s.units), 0)                  AS total_units,
                ROUND(COALESCE(SUM(s.gross_profit), 0), 2) AS total_profit
             FROM dw__fact_sales s
             JOIN dw__dim_date d ON d.date_key = s.date_key
             $where",
            $params
        );

        $row = $row ?? [
            'total_revenue' => 0,
            'total_orders'  => 0,
            'total_units'   => 0,
            'total_profit'  => 0,
        ];

        $row['total_products']  = (int)Database::fetchValue("SELECT COUNT(*) FROM dw__dim_product");
        $row['total_stores']    = (int)Database::fetchValue("SELECT COUNT(*) FROM dw__dim_store");
        $row['total_inventory'] = (int)Database::fetchValue("SELECT COALESCE(SUM(stock_on_hand),0) FROM dw__fact_inventory");

        $row['avg_margin'] = (float)(Database::fetchValue(
            "SELECT ROUND(AVG(s.margin_pct), 2)
             FROM dw__fact_sales s
             JOIN dw__dim_date d ON d.date_key = s.date_key
             $where",
            $params
        ) ?? 0);

        if ($startDate && $endDate) {
            $start = new DateTime($startDate);
            $end   = new DateTime($endDate);
            $diff  = $start->diff($end)->days + 1;

            $prevEnd   = (clone $start)->modify('-1 day')->format('Y-m-d');
            $prevStart = (clone $start)->modify('-' . $diff . ' day')->format('Y-m-d');

            $prev = Database::fetchOne(
                "SELECT
                    ROUND(COALESCE(SUM(s.revenue), 0), 2) AS rev,
                    COUNT(DISTINCT s.sale_id)              AS orders,
                    COALESCE(SUM(s.units), 0)              AS units
                 FROM dw__fact_sales s
                 JOIN dw__dim_date d ON d.date_key = s.date_key
                 WHERE DATE(d.full_date) BETWEEN DATE(?) AND DATE(?)",
                [$prevStart, $prevEnd]
            );

            $prev = $prev ?? ['rev' => 0, 'orders' => 0, 'units' => 0];

            $row['rev_growth']   = $this->growth((float)$row['total_revenue'], (float)$prev['rev']);
            $row['order_growth'] = $this->growth((float)$row['total_orders'],  (float)$prev['orders']);
            $row['unit_growth']  = $this->growth((float)$row['total_units'],   (float)$prev['units']);
        } else {
            $row['rev_growth']   = 0;
            $row['order_growth'] = 0;
            $row['unit_growth']  = 0;
        }

        return $row;
    }

    private function growth(float $curr, float $prev): float
    {
        if ($prev <= 0) return $curr > 0 ? 100.0 : 0.0;
        return round((($curr - $prev) / $prev) * 100, 1);
    }

    public function monthlyTrend(?string $startDate = null, ?string $endDate = null): array
    {
        $where  = '';
        $params = [];

        if ($startDate && $endDate) {
            $where  = "WHERE DATE(d.full_date) BETWEEN DATE(?) AND DATE(?)";
            $params = [$startDate, $endDate];
        }

        return Database::fetchAll(
            "SELECT
                d.year || '-' || PRINTF('%02d', d.month)  AS period,
                d.month_name || ' ' || d.year             AS label,
                ROUND(SUM(s.revenue), 2)                  AS revenue,
                ROUND(SUM(s.gross_profit), 2)             AS profit,
                COUNT(DISTINCT s.sale_id)                 AS orders
             FROM dw__fact_sales s
             JOIN dw__dim_date d ON d.date_key = s.date_key
             $where
             GROUP BY d.year, d.month, d.month_name
             ORDER BY d.year, d.month",
            $params
        ) ?? [];
    }
    
    public function dailyTrend(?string $startDate = null, ?string $endDate = null): array
    {
        $where  = '';
        $params = [];

        if ($startDate && $endDate) {
            $where  = "WHERE DATE(d.full_date) BETWEEN DATE(?) AND DATE(?)";
            $params = [$startDate, $endDate];
        }

        return Database::fetchAll(
            "SELECT
                d.full_date                        AS period,
                STRFTIME('%d %m', d.full_date)     AS label,
                ROUND(SUM(s.revenue), 2)           AS revenue,
                ROUND(SUM(s.gross_profit), 2)      AS profit,
                COUNT(DISTINCT s.sale_id)          AS orders
             FROM dw__fact_sales s
             JOIN dw__dim_date d ON d.date_key = s.date_key
             $where
             GROUP BY d.full_date
             ORDER BY d.full_date",
            $params
        );
    }

    public function revenueByCategory(?string $startDate = null, ?string $endDate = null): array
    {
        $where  = '';
        $params = [];

        if ($startDate && $endDate) {
            $where  = "WHERE DATE(d.full_date) BETWEEN DATE(?) AND DATE(?)";
            $params = [$startDate, $endDate];
        }

        return Database::fetchAll(
            "SELECT
                p.product_category AS category,
                ROUND(SUM(s.revenue), 2)  AS revenue,
                COUNT(DISTINCT s.sale_id) AS orders
             FROM dw__fact_sales s
             JOIN dw__dim_date d   ON d.date_key   = s.date_key
             JOIN dw__dim_product p ON p.product_key = s.product_key
             $where
             GROUP BY p.product_category
             ORDER BY revenue DESC",
            $params
        );
    }

    public function topProducts(int $limit = 5, ?string $startDate = null, ?string $endDate = null): array
    {
        $where  = '';
        $params = [];

        if ($startDate && $endDate) {
            $where  = "WHERE DATE(d.full_date) BETWEEN DATE(?) AND DATE(?)";
            $params = [$startDate, $endDate];
        }

        return Database::fetchAll(
            "SELECT
                p.product_name,
                p.product_category,
                p.product_price,
                ROUND(SUM(s.revenue), 2) AS revenue,
                SUM(s.units)             AS units
             FROM dw__fact_sales s
             JOIN dw__dim_date d   ON d.date_key   = s.date_key
             JOIN dw__dim_product p ON p.product_key = s.product_key
             $where
             GROUP BY p.product_key, p.product_name, p.product_category, p.product_price
             ORDER BY revenue DESC
             LIMIT $limit",
            $params
        );
    }

    public function topStores(int $limit = 5, ?string $startDate = null, ?string $endDate = null): array
    {
        $where  = '';
        $params = [];

        if ($startDate && $endDate) {
            $where  = "WHERE DATE(d.full_date) BETWEEN DATE(?) AND DATE(?)";
            $params = [$startDate, $endDate];
        }

        return Database::fetchAll(
            "SELECT
                st.store_name, st.store_city, st.store_location,
                ROUND(SUM(s.revenue), 2)  AS revenue,
                COUNT(DISTINCT s.sale_id) AS orders
             FROM dw__fact_sales s
             JOIN dw__dim_store st ON st.store_key = s.store_key
             JOIN dw__dim_date d   ON d.date_key   = s.date_key
             $where
             GROUP BY st.store_key, st.store_name, st.store_city, st.store_location
             ORDER BY revenue DESC
             LIMIT $limit",
            $params
        );
    }

    public function recentSales(int $limit = 8): array
    {
        return Database::fetchAll(
            "SELECT
                s.sale_id, d.full_date,
                p.product_name, p.product_category,
                st.store_name,
                s.units, s.unit_price, s.revenue
             FROM dw__fact_sales s
             JOIN dw__dim_date d   ON d.date_key   = s.date_key
             JOIN dw__dim_product p ON p.product_key = s.product_key
             JOIN dw__dim_store st  ON st.store_key  = s.store_key
             ORDER BY s.sale_id DESC
             LIMIT $limit"
        );
    }

    public function dateBounds(): array
    {
        $row = Database::fetchOne(
            "SELECT
                MIN(d.full_date) AS min_date,
                MAX(d.full_date) AS max_date
             FROM dw__fact_sales s
             JOIN dw__dim_date d ON d.date_key = s.date_key"
        );
        return $row ?: ['min_date' => null, 'max_date' => null];
    }
}