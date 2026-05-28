<?php
class ProductController extends Controller
{
    public function analytics(): void
    {
        Auth::require('analytics.product');
        $model = new ProductModel();
        $dash  = new DashboardModel();
        $bounds = $dash->dateBounds();
        $start  = (string)input('start_date', $bounds['min_date']);
        $end    = (string)input('end_date',   $bounds['max_date']);

        $this->render('analytics/product', [
            'page'        => 'product',
            'page_title'  => 'Analitik Produk',
            'top_revenue' => $model->topRevenue(10, $start, $end),
            'cat_perf'    => $model->categoryPerformance($start, $end),
            'tier_perf'   => $model->priceTierPerformance($start, $end),
            'kpis'        => $dash->kpis($start, $end),
            'bounds'      => $bounds,
            'start_date'  => $start,
            'end_date'    => $end,
        ]);
    }

    public function index(): void
    {
        Auth::require('crud.products');
        $model = new ProductModel();
        $search   = (string)input('q', '');
        $category = (string)input('cat', '');
        $page     = max(1, (int)input('page', 1));
        $data     = $model->paginate($search, $category, $page, 12);

        $this->render('crud/products_index', [
            'page'        => 'product_crud',
            'page_title'  => 'Manajemen Produk',
            'product_data'=> $data,
            'search'      => $search,
            'category_f'  => $category,
            'categories'  => $model->categories(),
            'can_edit'    => Auth::canEdit('crud.products'),
        ]);
    }

    public function store(): void
    {
        Auth::require('crud.products');
        if (!Auth::canEdit('crud.products')) { flash('error','Akses hanya-baca.'); $this->redirect('products'); }
        if (!Auth::checkCsrf((string)input('_token'))) { $this->redirect('products'); return; }
        try {
            (new ProductModel())->create([
                'category_key'   => (int)input('category_key'),
                'product_name'   => trim((string)input('product_name')),
                'product_cost'   => (float)input('product_cost'),
                'product_price'  => (float)input('product_price'),
            ]);
            flash('success','Produk berhasil dibuat.');
        } catch (Exception $e) {
            flash('error','Gagal membuat produk: '.$e->getMessage());
        }
        $this->redirect('products');
    }

    public function update(): void
    {
        Auth::require('crud.products');
        if (!Auth::canEdit('crud.products')) { flash('error','Akses hanya-baca.'); $this->redirect('products'); }
        if (!Auth::checkCsrf((string)input('_token'))) { $this->redirect('products'); return; }
        try {
            (new ProductModel())->update((int)input('product_key'), [
                'category_key'   => (int)input('category_key'),
                'product_name'   => trim((string)input('product_name')),
                'product_cost'   => (float)input('product_cost'),
                'product_price'  => (float)input('product_price'),
            ]);
            flash('success','Produk berhasil diperbarui.');
        } catch (Exception $e) {
            flash('error','Pembaruan gagal: '.$e->getMessage());
        }
        $this->redirect('products');
    }

    public function delete(): void
    {
        Auth::require('crud.products');
        if (!Auth::canEdit('crud.products')) { flash('error','Akses hanya-baca.'); $this->redirect('products'); }
        if (!Auth::checkCsrf((string)input('_token'))) { $this->redirect('products'); return; }
        try {
            (new ProductModel())->delete((int)input('product_key'));
            flash('success','Produk berhasil dihapus.');
        } catch (Exception $e) {
            flash('error','Penghapusan gagal: '.$e->getMessage());
        }
        $this->redirect('products');
    }
}