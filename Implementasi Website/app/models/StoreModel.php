<?php
class StoreModel
{
    public function all(): array
    {
        try {
            return Database::fetchAll("SELECT * FROM dw__dim_store ORDER BY store_name") ?? [];
        } catch (Throwable $e) {
            error_log('[StoreModel::all] ' . $e->getMessage());
            return [];
        }
    }

    public function paginate(string $search = '', string $city = '', int $page = 1, int $perPage = 12): array
    {
        $offset  = ($page - 1) * $perPage;
        $clauses = [];
        $params  = [];

        if ($search !== '') {
            $clauses[] = "(store_name LIKE ? OR store_city LIKE ?)";
            $like      = "%$search%";
            $params[]  = $like;
            $params[]  = $like;
        }
        if ($city !== '') {
            $clauses[] = "store_city = ?";
            $params[]  = $city;
        }

        $where = empty($clauses) ? '' : 'WHERE ' . implode(' AND ', $clauses);

        try {
            $rows = Database::fetchAll(
                "SELECT s.*,
                        (SELECT COALESCE(ROUND(SUM(revenue),2),0) FROM dw__fact_sales fs WHERE fs.store_key=s.store_key) AS total_revenue,
                        (SELECT COUNT(DISTINCT sale_id)            FROM dw__fact_sales fs WHERE fs.store_key=s.store_key) AS total_orders
                 FROM dw__dim_store s
                 $where
                 ORDER BY s.store_key
                 LIMIT $perPage OFFSET $offset",
                $params
            );

            $total = (int)Database::fetchValue("SELECT COUNT(*) FROM dw__dim_store $where", $params);

            return [
                'rows'     => $rows     ?? [],
                'total'    => $total    ?? 0,
                'page'     => $page,
                'per_page' => $perPage,
            ];
        } catch (Throwable $e) {
            error_log('[StoreModel::paginate] ' . $e->getMessage());
            return ['rows' => [], 'total' => 0, 'page' => $page, 'per_page' => $perPage];
        }
    }

    public function find(int $key): ?array
    {
        try {
            return Database::fetchOne("SELECT * FROM dw__dim_store WHERE store_key = ?", [$key]);
        } catch (Throwable $e) {
            error_log('[StoreModel::find] ' . $e->getMessage());
            return null;
        }
    }

    public function cities(): array
    {
        try {
            $rows = Database::fetchAll("SELECT DISTINCT store_city FROM dw__dim_store ORDER BY store_city");
            return array_column($rows ?? [], 'store_city');
        } catch (Throwable $e) {
            error_log('[StoreModel::cities] ' . $e->getMessage());
            return [];
        }
    }

    public function locations(): array
    {
        try {
            $rows = Database::fetchAll("SELECT DISTINCT store_location FROM dw__dim_store ORDER BY store_location");
            return array_column($rows ?? [], 'store_location');
        } catch (Throwable $e) {
            error_log('[StoreModel::locations] ' . $e->getMessage());
            return [];
        }
    }

    public function ranking(?string $start = null, ?string $end = null): array
    {
        $where  = '';
        $params = [];

        if ($start && $end) {
           
            $where  = "WHERE DATE(d.full_date) BETWEEN DATE(?) AND DATE(?)";
            $params = [$start, $end];
        }

        try {
            return Database::fetchAll(
                "SELECT st.store_name, st.store_city, st.store_location, st.store_age_years,
                        ROUND(SUM(s.revenue), 2)  AS revenue,
                        COUNT(DISTINCT s.sale_id) AS orders,
                        SUM(s.units)              AS units,
                        ROUND(SUM(s.gross_profit),2) AS profit
                 FROM dw__fact_sales s
                 JOIN dw__dim_store st ON st.store_key = s.store_key
                 JOIN dw__dim_date d   ON d.date_key  = s.date_key
                 $where
                 GROUP BY st.store_key, st.store_name, st.store_city, st.store_location, st.store_age_years
                 ORDER BY revenue DESC",
                $params
            ) ?? [];
        } catch (Throwable $e) {
            error_log('[StoreModel::ranking] ' . $e->getMessage());
            return [];
        }
    }

    public function revenueByCity(?string $start = null, ?string $end = null): array
    {
        $where  = '';
        $params = [];

        if ($start && $end) {
            $where  = "WHERE DATE(d.full_date) BETWEEN DATE(?) AND DATE(?)";
            $params = [$start, $end];
        }

        try {
            return Database::fetchAll(
                "SELECT st.store_city AS city,
                        ROUND(SUM(s.revenue), 2)    AS revenue,
                        COUNT(DISTINCT st.store_key) AS stores
                 FROM dw__fact_sales s
                 JOIN dw__dim_store st ON st.store_key = s.store_key
                 JOIN dw__dim_date d   ON d.date_key  = s.date_key
                 $where
                 GROUP BY st.store_city
                 ORDER BY revenue DESC",
                $params
            ) ?? [];
        } catch (Throwable $e) {
            error_log('[StoreModel::revenueByCity] ' . $e->getMessage());
            return [];
        }
    }

    public function revenueByLocation(?string $start = null, ?string $end = null): array
    {
        $where  = '';
        $params = [];

        if ($start && $end) {
            $where  = "WHERE DATE(d.full_date) BETWEEN DATE(?) AND DATE(?)";
            $params = [$start, $end];
        }

        try {
            return Database::fetchAll(
                "SELECT st.store_location AS location,
                        ROUND(SUM(s.revenue), 2)    AS revenue,
                        COUNT(DISTINCT st.store_key) AS stores
                 FROM dw__fact_sales s
                 JOIN dw__dim_store st ON st.store_key = s.store_key
                 JOIN dw__dim_date d   ON d.date_key  = s.date_key
                 $where
                 GROUP BY st.store_location
                 ORDER BY revenue DESC",
                $params
            ) ?? [];
        } catch (Throwable $e) {
            error_log('[StoreModel::revenueByLocation] ' . $e->getMessage());
            return [];
        }
    }

    public function create(array $d): int
    {
        $age     = $this->ageYears($d['store_open_date']);
        $nextKey = (int)Database::fetchValue("SELECT COALESCE(MAX(store_key),0)+1 FROM dw__dim_store");
        $nextId  = (int)Database::fetchValue("SELECT COALESCE(MAX(store_id),0)+1 FROM dw__dim_store");

        $stmt = Database::connection()->prepare(
            "INSERT INTO dw__dim_store
                (store_key, store_id, store_name, store_city, store_location, store_open_date, store_age_years)
             VALUES (?,?,?,?,?,?,?)"
        );
        $stmt->execute([
            $nextKey, $nextId, $d['store_name'], $d['store_city'],
            $d['store_location'], $d['store_open_date'], $age,
        ]);
        return $nextKey;
    }

    public function update(int $key, array $d): bool
    {
        $age  = $this->ageYears($d['store_open_date']);
        $stmt = Database::connection()->prepare(
            "UPDATE dw__dim_store SET
                store_name=?, store_city=?, store_location=?, store_open_date=?, store_age_years=?
             WHERE store_key=?"
        );
        return $stmt->execute([
            $d['store_name'], $d['store_city'], $d['store_location'], $d['store_open_date'], $age, $key,
        ]);
    }

    public function delete(int $key): bool
    {
        $hasSales = (int)Database::fetchValue("SELECT COUNT(*) FROM dw__fact_sales WHERE store_key = ?", [$key]);
        if ($hasSales > 0) {
            throw new RuntimeException('Cannot delete store with existing sales.');
        }
        Database::execute("DELETE FROM dw__fact_inventory WHERE store_key = ?", [$key]);
        $stmt = Database::connection()->prepare("DELETE FROM dw__dim_store WHERE store_key = ?");
        return $stmt->execute([$key]);
    }

    private function ageYears(string $openDate): float
    {
        try {
            $d    = new DateTime($openDate);
            $now  = new DateTime();
            $days = $now->diff($d)->days;
            return round($days / 365.25, 1);
        } catch (Exception $e) {
            return 0.0;
        }
    }
}