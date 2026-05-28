<?php
class InventoryController extends Controller
{
    public function analytics(): void
    {
        Auth::require('analytics.inventory');
        $model = new InventoryModel();
        $this->render('analytics/inventory', [
            'page'         => 'inventory',
            'page_title'   => 'Analitik Inventaris',
            'summary'      => $model->summary(),
            'low_stock'    => $model->lowStock(10, 15),
            'by_category'  => $model->stockByCategory(),
            'by_store'     => $model->stockByStore(10),
            'distribution' => $model->stockDistribution(),
        ]);
    }

    public function index(): void
    {
        Auth::require('crud.inventory');
        $model  = new InventoryModel();
        $search = (string)input('q','');
        $stock  = (string)input('stock','');
        $page   = max(1,(int)input('page',1));
        $data   = $model->paginate($search, $stock, $page, 15);
        $prods  = (new ProductModel())->all();
        $stores = (new StoreModel())->all();
        $this->render('crud/inventory_index', [
            'page'        => 'inventory_crud',
            'page_title'  => 'Manajemen Inventaris',
            'inventory_data' => $data,
            'search'      => $search,
            'stock'       => $stock,
            'summary'     => $model->summary(),
            'products'    => $prods,
            'stores'      => $stores,
        ]);
    }

    public function store(): void
    {
        Auth::require('crud.inventory');
        if (!Auth::checkCsrf((string)input('_token'))) { $this->redirect('inventory'); return; }
        try {
            (new InventoryModel())->create([
                'product_key'   => (int)input('product_key'),
                'store_key'     => (int)input('store_key'),
                'stock_on_hand' => (int)input('stock_on_hand'),
            ]);
            flash('success','Data inventaris berhasil dibuat.');
        } catch (Exception $e) {
            flash('error','Gagal membuat data: '.$e->getMessage());
        }
        $this->redirect('inventory');
    }

    public function update(): void
    {
        Auth::require('crud.inventory');
        if (!Auth::checkCsrf((string)input('_token'))) { $this->redirect('inventory'); return; }
        try {
            (new InventoryModel())->update((int)input('inventory_key'), [
                'stock_on_hand' => (int)input('stock_on_hand'),
            ]);
            flash('success','Stok berhasil diperbarui.');
        } catch (Exception $e) {
            flash('error','Pembaruan gagal: '.$e->getMessage());
        }
        $this->redirect('inventory');
    }

    public function delete(): void
    {
        Auth::require('crud.inventory');
        if (!Auth::checkCsrf((string)input('_token'))) { $this->redirect('inventory'); return; }
        try {
            (new InventoryModel())->delete((int)input('inventory_key'));
            flash('success','Data inventaris berhasil dihapus.');
        } catch (Exception $e) {
            flash('error','Penghapusan gagal: '.$e->getMessage());
        }
        $this->redirect('inventory');
    }
}