<?php
class SalesModel
{
    public function dailyTrend(?string $start = null, ?string $end = null, ?int $storeKey = null, ?int $categoryKey = null): array
    {
        [$where, $params] = $this->buildFilter($start, $end, $storeKey, $categoryKey);
        return Database::fetchAll(
            "SELECT d.full_date AS day,
                    ROUND(SUM(s.revenue),2) AS revenue,
                    ROUND(SUM(s.gross_profit),2) AS profit,
                    SUM(s.units) AS units
             FROM dw__fact_sales s
             JOIN dw__dim_date d   ON d.date_key   = s.date_key
             JOIN dw__dim_product p ON p.product_key = s.product_key
             $where
             GROUP BY d.full_date
             ORDER BY d.full_date", $params);
    }

    public function monthlyTrend(?string $start = null, ?string $end = null, ?int $storeKey = null, ?int $categoryKey = null): array
    {
        [$where, $params] = $this->buildFilter($start, $end, $storeKey, $categoryKey);
        return Database::fetchAll(
            "SELECT d.year || '-' || PRINTF('%02d',d.month) AS period,
                    d.month_name || ' ' || d.year AS label,
                    ROUND(SUM(s.revenue),2) AS revenue,
                    ROUND(SUM(s.gross_profit),2) AS profit,
                    COUNT(DISTINCT s.sale_id) AS orders
             FROM dw__fact_sales s
             JOIN dw__dim_date d   ON d.date_key=s.date_key
             JOIN dw__dim_product p ON p.product_key = s.product_key
             $where
             GROUP BY d.year,d.month,d.month_name
             ORDER BY d.year,d.month",$params);
    }

    public function bestSelling(int $limit = 10, ?string $start = null, ?string $end = null, ?int $storeKey = null, ?int $categoryKey = null): array
    {
        [$where, $params] = $this->buildFilter($start, $end, $storeKey, $categoryKey);
        return Database::fetchAll(
            "SELECT p.product_name, p.product_category, p.price_tier,
                    ROUND(SUM(s.revenue),2) AS revenue,
                    SUM(s.units) AS units,
                    ROUND(SUM(s.gross_profit),2) AS profit
             FROM dw__fact_sales s
             JOIN dw__dim_date d ON d.date_key = s.date_key
             JOIN dw__dim_product p ON p.product_key = s.product_key
             $where
             GROUP BY p.product_key,p.product_name,p.product_category,p.price_tier
             ORDER BY revenue DESC
             LIMIT $limit",$params);
    }

    public function worstSelling(int $limit = 10, ?string $start = null, ?string $end = null, ?int $storeKey = null, ?int $categoryKey = null): array
    {
        [$where, $params] = $this->buildFilter($start, $end, $storeKey, $categoryKey);
        return Database::fetchAll(
            "SELECT p.product_name, p.product_category, p.price_tier,
                    ROUND(SUM(s.revenue),2) AS revenue,
                    SUM(s.units) AS units
             FROM dw__fact_sales s
             JOIN dw__dim_date d ON d.date_key = s.date_key
             JOIN dw__dim_product p ON p.product_key = s.product_key
             $where
             GROUP BY p.product_key,p.product_name,p.product_category,p.price_tier
             ORDER BY revenue ASC
             LIMIT $limit",$params);
    }

    public function salesByStore(?string $start = null, ?string $end = null, ?int $categoryKey = null): array
    {
        [$where, $params] = $this->buildFilter($start, $end, null, $categoryKey);
        return Database::fetchAll(
            "SELECT st.store_name, st.store_city, st.store_location,
                    ROUND(SUM(s.revenue),2) AS revenue,
                    COUNT(DISTINCT s.sale_id) AS orders
             FROM dw__fact_sales s
             JOIN dw__dim_date d ON d.date_key=s.date_key
             JOIN dw__dim_product p ON p.product_key=s.product_key
             JOIN dw__dim_store st ON st.store_key=s.store_key
             $where
             GROUP BY st.store_key, st.store_name, st.store_city, st.store_location
             ORDER BY revenue DESC",$params);
    }

    public function salesByCategory(?string $start = null, ?string $end = null, ?int $storeKey = null): array
    {
        [$where, $params] = $this->buildFilter($start, $end, $storeKey, null);
        return Database::fetchAll(
            "SELECT p.product_category AS category,
                    ROUND(SUM(s.revenue),2) AS revenue,
                    SUM(s.units) AS units,
                    COUNT(DISTINCT s.sale_id) AS orders
             FROM dw__fact_sales s
             JOIN dw__dim_date d ON d.date_key=s.date_key
             JOIN dw__dim_product p ON p.product_key=s.product_key
             $where
             GROUP BY p.product_category
             ORDER BY revenue DESC",$params);
    }

    public function salesByDayOfWeek(?string $start = null, ?string $end = null): array
    {
        [$where, $params] = $this->buildFilter($start, $end, null, null);
        return Database::fetchAll(
            "SELECT d.day_of_week, d.day_name,
                    ROUND(SUM(s.revenue),2) AS revenue,
                    SUM(s.units) AS units
             FROM dw__fact_sales s
             JOIN dw__dim_date d ON d.date_key=s.date_key
             JOIN dw__dim_product p ON p.product_key=s.product_key
             $where
             GROUP BY d.day_of_week, d.day_name
             ORDER BY d.day_of_week",$params);
    }

    private function buildFilter(?string $start, ?string $end, ?int $storeKey, ?int $categoryKey): array
    {
        $clauses = [];
        $params  = [];
        if ($start && $end) {
            $clauses[] = "d.full_date BETWEEN ? AND ?";
            $params[] = $start; $params[] = $end;
        }
        if ($storeKey) {
            $clauses[] = "s.store_key = ?";
            $params[]  = $storeKey;
        }
        if ($categoryKey) {
            $clauses[] = "p.category_key = ?";
            $params[]  = $categoryKey;
        }
        $where = empty($clauses) ? '' : 'WHERE ' . implode(' AND ', $clauses);
        return [$where, $params];
    }

    public function paginate(string $search = '', int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $where  = '';
        $params = [];
        if ($search !== '') {
            $where = "WHERE p.product_name LIKE ? OR st.store_name LIKE ? OR CAST(s.sale_id AS TEXT) LIKE ?";
            $like = "%$search%";
            $params = [$like, $like, $like];
        }
        $rows = Database::fetchAll(
            "SELECT s.sales_key, s.sale_id, s.product_key, s.store_key, d.full_date,
                    p.product_name, p.product_category, p.product_price,
                    st.store_name, st.store_city,
                    s.units, s.unit_price, s.revenue, s.gross_profit
             FROM dw__fact_sales s
             JOIN dw__dim_date d    ON d.date_key   = s.date_key
             JOIN dw__dim_product p ON p.product_key = s.product_key
             JOIN dw__dim_store st  ON st.store_key  = s.store_key
             $where
             ORDER BY s.sale_id DESC
             LIMIT $perPage OFFSET $offset", $params);
        $total = (int)Database::fetchValue(
            "SELECT COUNT(*) FROM dw__fact_sales s
             JOIN dw__dim_product p ON p.product_key = s.product_key
             JOIN dw__dim_store st ON st.store_key = s.store_key
             $where", $params);
        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    public function find(int $salesKey): ?array
    {
        return Database::fetchOne(
            "SELECT s.*, d.full_date, p.product_name, p.product_price, p.product_cost,
                    st.store_name, st.store_city
             FROM dw__fact_sales s
             JOIN dw__dim_date d    ON d.date_key   = s.date_key
             JOIN dw__dim_product p ON p.product_key = s.product_key
             JOIN dw__dim_store st  ON st.store_key  = s.store_key
             WHERE s.sales_key = ?", [$salesKey]);
    }

    public function create(array $d): int
    {
        $prod  = Database::fetchOne("SELECT * FROM dw__dim_product WHERE product_key=?", [$d['product_key']]);
        $store = Database::fetchOne("SELECT store_key FROM dw__dim_store WHERE store_key=?", [$d['store_key']]);
        $date  = Database::fetchOne("SELECT date_key FROM dw__dim_date WHERE full_date=?", [$d['full_date']]);
        if (!$prod || !$store || !$date) {
            throw new RuntimeException('Invalid product, store, or date.');
        }
        $units    = (int)$d['units'];
        $price    = (float)($d['unit_price'] ?? $prod['product_price']);
        $cost     = (float)$prod['product_cost'];
        $revenue  = $units * $price;
        $cogs     = $units * $cost;
        $profit   = $revenue - $cogs;
        $margin   = $revenue > 0 ? round($profit / $revenue * 100, 2) : 0;

        $nextSalesKey = (int)Database::fetchValue(
            "SELECT COALESCE(MAX(sales_key),0)+1 FROM dw__fact_sales"
        );
        $nextSaleId = (int)Database::fetchValue("SELECT COALESCE(MAX(sale_id),0)+1 FROM dw__fact_sales");

        $stmt = Database::connection()->prepare(
            "INSERT INTO dw__fact_sales
            (
                sales_key,
                sale_id,
                date_key,
                product_key,
                store_key,
                units,
                unit_price,
                unit_cost,
                revenue,
                cogs,
                gross_profit,
                margin_pct
            )
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)"
        );

        $stmt->execute([
            $nextSalesKey,
            $nextSaleId,
            $date['date_key'],
            $prod['product_key'],
            $store['store_key'],
            $units,
            $price,
            $cost,
            $revenue,
            $cogs,
            $profit,
            $margin,
        ]);

        return $nextSalesKey;
    }

    public function update(int $salesKey, array $d): bool
    {
        $row = Database::fetchOne(
            "SELECT * FROM dw__fact_sales WHERE sale_id=?",
            [$salesKey]
        );
        if (!$row) return false;
        $prod = Database::fetchOne("SELECT * FROM dw__dim_product WHERE product_key=?", [$d['product_key'] ?? $row['product_key']]);
        $units = (int)($d['units'] ?? $row['units']);
        $price = (float)($d['unit_price'] ?? $row['unit_price']);
        $cost  = (float)$prod['product_cost'];
        $revenue = $units * $price;
        $cogs    = $units * $cost;
        $profit  = $revenue - $cogs;
        $margin  = $revenue > 0 ? round($profit / $revenue * 100, 2) : 0;

        $fullDate = $d['full_date'] ?? null;

        if (!$fullDate) {

            $dateRow = Database::fetchOne(
                "SELECT full_date
                FROM dw__dim_date
                WHERE date_key=?",
                [$row['date_key']]
            );

            $fullDate = $dateRow['full_date'] ?? null;
        }

        $date = Database::fetchOne(
            "SELECT date_key
            FROM dw__dim_date
            WHERE full_date=?",
            [$fullDate]
        );

        $stmt = Database::connection()->prepare(
            "UPDATE dw__fact_sales SET
                date_key=?, product_key=?, store_key=?, units=?, unit_price=?, unit_cost=?, revenue=?, cogs=?, gross_profit=?, margin_pct=?
             WHERE sale_id=?"
        );
        return $stmt->execute([
            $date['date_key'], $prod['product_key'], $d['store_key'] ?? $row['store_key'],
            $units, $price, $cost, $revenue, $cogs, $profit, $margin, $salesKey,
        ]);
    }

    public function delete(int $salesKey): bool
    {
        $stmt = Database::connection()->prepare("DELETE FROM dw__fact_sales WHERE sale_id=?");
        return $stmt->execute([$salesKey]);
    }
}
