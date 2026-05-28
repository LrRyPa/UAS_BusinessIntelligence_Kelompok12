<?php
class SalesController extends Controller
{
    public function analytics(): void
    {
        Auth::require('analytics.sales');

        $sales  = new SalesModel();
        $dash   = new DashboardModel();
        $store  = new StoreModel();
        $prod   = new ProductModel();
        $bounds = $dash->dateBounds();
        $start  = (string)input('start_date', $bounds['min_date']);
        $end    = (string)input('end_date',   $bounds['max_date']);
        $storeKey  = input('store_key') ? (int)input('store_key') : null;
        $catKey    = input('category_key') ? (int)input('category_key') : null;

        $this->render('analytics/sales', [
            'page'         => 'sales',
            'page_title'   => 'Analitik Penjualan',
            'monthly'      => $sales->monthlyTrend($start, $end, $storeKey, $catKey),
            'daily'        => array_slice($sales->dailyTrend($start, $end, $storeKey, $catKey), -90),
            'best'         => $sales->bestSelling(10, $start, $end, $storeKey, $catKey),
            'worst'        => $sales->worstSelling(10, $start, $end, $storeKey, $catKey),
            'by_store'     => $sales->salesByStore($start, $end, $catKey),
            'by_category'  => $sales->salesByCategory($start, $end, $storeKey),
            'by_dow'       => $sales->salesByDayOfWeek($start, $end),
            'kpis'         => $dash->kpis($start, $end),
            'stores'       => $store->all(),
            'categories'   => $prod->categories(),
            'bounds'       => $bounds,
            'start_date'   => $start,
            'end_date'     => $end,
            'f_store'      => $storeKey,
            'f_category'   => $catKey,
        ]);
    }

    public function index(): void
    {
        Auth::require('crud.sales');

        $model  = new SalesModel();
        $search = (string)input('q', '');
        $page   = max(1, (int)input('page', 1));
        $data   = $model->paginate($search, $page, 20);
        $prodModel = new ProductModel();
        $storeModel= new StoreModel();
        $dash      = new DashboardModel();

        $this->render('crud/sales_index', [
            'page'      => 'sales_crud',
            'page_title'=> 'Manajemen Penjualan',
            'sales_data'=> $data,
            'search'    => $search,
            'products'  => $prodModel->all(),
            'stores'    => $storeModel->all(),
            'bounds'    => $dash->dateBounds(),
        ]);
    }

    public function store(): void
    {
        Auth::require('crud.sales');
        if (!Auth::checkCsrf((string)input('_token'))) { $this->json(['ok'=>false,'msg'=>'Token tidak valid'], 419); return; }
        try {
            $model = new SalesModel();
            $model->create([
                'product_key' => (int)input('product_key'),
                'store_key'   => (int)input('store_key'),
                'full_date'   => (string)input('full_date'),
                'units'       => (int)input('units'),
                'unit_price'  => input('unit_price') !== null ? (float)input('unit_price') : null,
            ]);
            flash('success', 'Penjualan berhasil dicatat.');
        } catch (Exception $e) {
            flash('error', 'Gagal mencatat penjualan: ' . $e->getMessage());
        }
        $this->redirect('sales');
    }

    public function update(): void
    {
        Auth::require('crud.sales');
        if (!Auth::checkCsrf((string)input('_token'))) { $this->json(['ok'=>false,'msg'=>'Token tidak valid'], 419); return; }
        try {
            $model = new SalesModel();
            $model->update((int)input('sales_key'), [
                'product_key' => (int)input('product_key'),
                'store_key'   => (int)input('store_key'),
                'full_date'   => (string)input('full_date'),
                'units'       => (int)input('units'),
                'unit_price'  => (float)input('unit_price'),
            ]);
            flash('success', 'Penjualan berhasil diperbarui.');
        } catch (Exception $e) {
            flash('error', 'Pembaruan gagal: ' . $e->getMessage());
        }
        $this->redirect('sales');
    }

    public function delete(): void
    {
        Auth::require('crud.sales');
        if (!Auth::checkCsrf((string)input('_token'))) { $this->redirect('sales'); return; }
        try {
            (new SalesModel())->delete((int)input('sales_key'));
            flash('success', 'Penjualan berhasil dihapus.');
        } catch (Exception $e) {
            flash('error', 'Penghapusan gagal: ' . $e->getMessage());
        }
        $this->redirect('sales');
    }
}