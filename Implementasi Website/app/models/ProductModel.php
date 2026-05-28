<?php
class ProductModel
{
    public function paginate(string $search = '', string $category = '', int $page = 1, int $perPage = 12): array
    {
        $offset  = ($page - 1) * $perPage;
        $clauses = [];
        $params  = [];

        if ($search !== '') {
            $clauses[] = "(p.product_name LIKE ? OR CAST(p.product_id AS TEXT) LIKE ?)";
            $like      = "%$search%";
            $params[]  = $like;
            $params[]  = $like;
        }

        if ($category !== '') {
            $clauses[] = "p.product_category = ?";
            $params[]  = $category;
        }

        $where = empty($clauses) ? '' : 'WHERE ' . implode(' AND ', $clauses);

        try {
            $rows = Database::fetchAll(
                "SELECT p.*,
                        COALESCE((SELECT SUM(stock_on_hand) FROM dw__fact_inventory i WHERE i.product_key = p.product_key), 0) AS total_stock,
                        COALESCE((SELECT SUM(units)          FROM dw__fact_sales s     WHERE s.product_key = p.product_key), 0) AS total_units_sold,
                        COALESCE((SELECT ROUND(SUM(revenue),2) FROM dw__fact_sales s   WHERE s.product_key = p.product_key), 0) AS total_revenue
                 FROM dw__dim_product p
                 $where
                 ORDER BY p.product_key
                 LIMIT $perPage OFFSET $offset",
                $params
            );

            $total = (int)Database::fetchValue("SELECT COUNT(*) FROM dw__dim_product p $where", $params);

            return [
                'rows'     => $rows  ?? [],
                'total'    => $total ?? 0,
                'page'     => $page,
                'per_page' => $perPage,
            ];
        } catch (Throwable $e) {
            error_log('[ProductModel::paginate] ' . $e->getMessage());
            return ['rows' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage];
        }
    }

    public function all(): array
    {
        try {
            return Database::fetchAll("SELECT * FROM dw__dim_product ORDER BY product_name") ?? [];
        } catch (Throwable $e) {
            error_log('[ProductModel::all] ' . $e->getMessage());
            return [];
        }
    }

    public function find(int $key): ?array
    {
        return Database::fetchOne("SELECT * FROM dw__dim_product WHERE product_key = ?", [$key]);
    }

    public function categories(): array
    {
        try {
            return Database::fetchAll("SELECT * FROM dw__dim_category ORDER BY category_name") ?? [];
        } catch (Throwable $e) {
            error_log('[ProductModel::categories] ' . $e->getMessage());
            return [];
        }
    }

    public function create(array $d): int
    {
        $cat = Database::fetchOne("SELECT * FROM dw__dim_category WHERE category_key = ?", [$d['category_key']]);
        if (!$cat) throw new RuntimeException('Invalid category');

        $tier    = $this->priceTier((float)$d['product_price']);
        $nextKey = (int)Database::fetchValue("SELECT COALESCE(MAX(product_key),0)+1 FROM dw__dim_product");
        $nextId  = (int)Database::fetchValue("SELECT COALESCE(MAX(product_id),0)+1 FROM dw__dim_product");

        $stmt = Database::connection()->prepare(
            "INSERT INTO dw__dim_product
                (product_key, product_id, category_key, product_name, product_category, product_cost, product_price, price_tier)
             VALUES (?,?,?,?,?,?,?,?)"
        );
        $stmt->execute([
            $nextKey, $nextId, $d['category_key'], $d['product_name'], $cat['category_name'],
            (float)$d['product_cost'], (float)$d['product_price'], $tier,
        ]);
        return $nextKey;
    }

    public function update(int $key, array $d): bool
    {
        $cat = Database::fetchOne("SELECT * FROM dw__dim_category WHERE category_key = ?", [$d['category_key']]);
        if (!$cat) throw new RuntimeException('Invalid category');

        $tier = $this->priceTier((float)$d['product_price']);
        $stmt = Database::connection()->prepare(
            "UPDATE dw__dim_product SET
                category_key=?, product_name=?, product_category=?, product_cost=?, product_price=?, price_tier=?
             WHERE product_key=?"
        );
        return $stmt->execute([
            $d['category_key'], $d['product_name'], $cat['category_name'],
            (float)$d['product_cost'], (float)$d['product_price'], $tier, $key,
        ]);
    }

    public function delete(int $key): bool
    {
        $hasSales = (int)Database::fetchValue("SELECT COUNT(*) FROM dw__fact_sales WHERE product_key = ?", [$key]);
        if ($hasSales > 0) {
            throw new RuntimeException('Cannot delete product with existing sales.');
        }
        Database::execute("DELETE FROM dw__fact_inventory WHERE product_key = ?", [$key]);
        $stmt = Database::connection()->prepare("DELETE FROM dw__dim_product WHERE product_key = ?");
        return $stmt->execute([$key]);
    }

    private function priceTier(float $price): string
    {
        if ($price >= 20) return 'Premium';
        if ($price >= 10) return 'Mid-Range';
        return 'Budget';
    }

    public function topRevenue(int $limit = 10, ?string $start = null, ?string $end = null): array
    {
        $where  = '';
        $params = [];

        if ($start && $end) {
            
            $where  = "WHERE DATE(d.full_date) BETWEEN DATE(?) AND DATE(?)";
            $params = [$start, $end];
        }

        try {
            return Database::fetchAll(
                "SELECT p.product_name, p.product_category, p.price_tier,
                        ROUND(SUM(s.revenue), 2)     AS revenue,
                        SUM(s.units)                 AS units,
                        ROUND(SUM(s.gross_profit),2) AS profit,
                        ROUND(AVG(s.margin_pct), 2)  AS margin_pct
                 FROM dw__fact_sales s
                 JOIN dw__dim_product p ON p.product_key = s.product_key
                 JOIN dw__dim_date d   ON d.date_key    = s.date_key
                 $where
                 GROUP BY p.product_key, p.product_name, p.product_category, p.price_tier
                 ORDER BY revenue DESC
                 LIMIT $limit",
                $params
            ) ?? [];
        } catch (Throwable $e) {
            error_log('[ProductModel::topRevenue] ' . $e->getMessage());
            return [];
        }
    }

    public function categoryPerformance(?string $start = null, ?string $end = null): array
    {
        $where  = '';
        $params = [];

        if ($start && $end) {
            
            $where  = "WHERE DATE(d.full_date) BETWEEN DATE(?) AND DATE(?)";
            $params = [$start, $end];
        }

        try {
            return Database::fetchAll(
                "SELECT p.product_category              AS category,
                        ROUND(SUM(s.revenue), 2)        AS revenue,
                        SUM(s.units)                    AS units,
                        COUNT(DISTINCT s.product_key)   AS products,
                        ROUND(SUM(s.gross_profit), 2)   AS profit
                 FROM dw__fact_sales s
                 JOIN dw__dim_product p ON p.product_key = s.product_key
                 JOIN dw__dim_date d   ON d.date_key    = s.date_key
                 $where
                 GROUP BY p.product_category
                 ORDER BY revenue DESC",
                $params
            ) ?? [];
        } catch (Throwable $e) {
            error_log('[ProductModel::categoryPerformance] ' . $e->getMessage());
            return [];
        }
    }

    public function priceTierPerformance(?string $start = null, ?string $end = null): array
    {
        $where  = '';
        $params = [];

        if ($start && $end) {
            $where  = "WHERE DATE(d.full_date) BETWEEN DATE(?) AND DATE(?)";
            $params = [$start, $end];
        }

        try {
            return Database::fetchAll(
                "SELECT p.price_tier,
                        ROUND(SUM(s.revenue), 2) AS revenue,
                        SUM(s.units)             AS units
                 FROM dw__fact_sales s
                 JOIN dw__dim_product p ON p.product_key = s.product_key
                 JOIN dw__dim_date d   ON d.date_key    = s.date_key
                 $where
                 GROUP BY p.price_tier
                 ORDER BY revenue DESC",
                $params
            ) ?? [];
        } catch (Throwable $e) {
            error_log('[ProductModel::priceTierPerformance] ' . $e->getMessage());
            return [];
        }
    }
}