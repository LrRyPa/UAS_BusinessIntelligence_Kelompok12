<?php
class InventoryModel
{
    public function paginate(string $search = '', string $stockFilter = '', int $page = 1, int $perPage = 15): array
    {
        $offset  = ($page - 1) * $perPage;
        $clauses = [];
        $params  = [];
        if ($search !== '') {
            $clauses[] = "(p.product_name LIKE ? OR st.store_name LIKE ?)";
            $like = "%$search%";
            $params[] = $like; $params[] = $like;
        }
        switch ($stockFilter) {
            case 'out':       $clauses[] = "i.stock_on_hand = 0"; break;
            case 'low':       $clauses[] = "i.stock_on_hand > 0 AND i.stock_on_hand <= 10"; break;
            case 'normal':    $clauses[] = "i.stock_on_hand > 10 AND i.stock_on_hand <= 50"; break;
            case 'overstock': $clauses[] = "i.stock_on_hand > 50"; break;
        }
        $where = empty($clauses) ? '' : 'WHERE ' . implode(' AND ', $clauses);

        $rows = Database::fetchAll(
            "SELECT i.inventory_key, i.stock_on_hand,
                    p.product_key, p.product_name, p.product_category, p.product_price,
                    st.store_key, st.store_name, st.store_city, st.store_location
             FROM dw__fact_inventory i
             JOIN dw__dim_product p ON p.product_key = i.product_key
             JOIN dw__dim_store st ON st.store_key = i.store_key
             $where
             ORDER BY i.stock_on_hand ASC, p.product_name
             LIMIT $perPage OFFSET $offset", $params);
        $total = (int)Database::fetchValue(
            "SELECT COUNT(*) FROM dw__fact_inventory i
             JOIN dw__dim_product p ON p.product_key = i.product_key
             JOIN dw__dim_store st ON st.store_key = i.store_key
             $where", $params);
        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    public function find(int $key): ?array
    {
        return Database::fetchOne(
            "SELECT i.*, p.product_name, st.store_name
             FROM dw__fact_inventory i
             JOIN dw__dim_product p ON p.product_key=i.product_key
             JOIN dw__dim_store st ON st.store_key=i.store_key
             WHERE i.inventory_key = ?", [$key]);
    }

    public function update(int $key, array $d): bool
    {
        $stmt = Database::connection()->prepare(
            "UPDATE dw__fact_inventory SET stock_on_hand = ?
             WHERE inventory_key = ?"
        );
        return $stmt->execute([(int)$d['stock_on_hand'], $key]);
    }

    public function create(array $d): int
    {
        $nextKey = (int)Database::fetchValue("SELECT COALESCE(MAX(inventory_key),0)+1 FROM dw__fact_inventory");
        $stmt = Database::connection()->prepare(
            "INSERT INTO dw__fact_inventory (inventory_key, product_key, store_key, stock_on_hand)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $nextKey, (int)$d['product_key'], (int)$d['store_key'], (int)$d['stock_on_hand'],
        ]);
        return $nextKey;
    }

    public function delete(int $key): bool
    {
        $stmt = Database::connection()->prepare("DELETE FROM dw__fact_inventory WHERE inventory_key = ?");
        return $stmt->execute([$key]);
    }

    public function summary(): array
    {
        return Database::fetchOne(
            "SELECT
                COALESCE(SUM(stock_on_hand),0)                                  AS total_stock,
                SUM(CASE WHEN stock_on_hand = 0 THEN 1 ELSE 0 END)              AS out_count,
                SUM(CASE WHEN stock_on_hand BETWEEN 1 AND 10 THEN 1 ELSE 0 END) AS low_count,
                SUM(CASE WHEN stock_on_hand > 50 THEN 1 ELSE 0 END)             AS overstock_count,
                COUNT(*)                                                         AS total_records
             FROM dw__fact_inventory");
    }

    public function lowStock(int $threshold = 10, int $limit = 20): array
    {
        return Database::fetchAll(
            "SELECT i.inventory_key, i.stock_on_hand,
                    p.product_name, p.product_category,
                    st.store_name, st.store_city
             FROM dw__fact_inventory i
             JOIN dw__dim_product p ON p.product_key = i.product_key
             JOIN dw__dim_store st ON st.store_key = i.store_key
             WHERE i.stock_on_hand <= ?
             ORDER BY i.stock_on_hand ASC, p.product_name
             LIMIT $limit", [$threshold]);
    }

    public function stockByCategory(): array
    {
        return Database::fetchAll(
            "SELECT p.product_category AS category,
                    COALESCE(SUM(i.stock_on_hand),0) AS stock,
                    COUNT(DISTINCT i.product_key) AS products
             FROM dw__fact_inventory i
             JOIN dw__dim_product p ON p.product_key=i.product_key
             GROUP BY p.product_category
             ORDER BY stock DESC");
    }

    public function stockByStore(int $limit = 10): array
    {
        return Database::fetchAll(
            "SELECT st.store_name, st.store_city,
                    COALESCE(SUM(i.stock_on_hand),0) AS stock
             FROM dw__fact_inventory i
             JOIN dw__dim_store st ON st.store_key=i.store_key
             GROUP BY st.store_key, st.store_name, st.store_city
             ORDER BY stock DESC
             LIMIT $limit");
    }

    public function stockDistribution(): array
    {
        $rows = Database::fetchAll(
            "SELECT
                SUM(CASE WHEN stock_on_hand = 0                   THEN 1 ELSE 0 END) AS out_stock,
                SUM(CASE WHEN stock_on_hand BETWEEN 1 AND 10      THEN 1 ELSE 0 END) AS low_stock,
                SUM(CASE WHEN stock_on_hand BETWEEN 11 AND 50     THEN 1 ELSE 0 END) AS normal,
                SUM(CASE WHEN stock_on_hand > 50                  THEN 1 ELSE 0 END) AS overstock
             FROM dw__fact_inventory");
        return $rows[0] ?? [];
    }
}
