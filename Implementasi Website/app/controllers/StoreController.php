<?php
class StoreController extends Controller
{
    public function analytics(): void
    {
        Auth::require('analytics.store');
        $model = new StoreModel();
        $dash  = new DashboardModel();
        $bounds= $dash->dateBounds();
        $start = (string)input('start_date', $bounds['min_date']);
        $end   = (string)input('end_date',   $bounds['max_date']);

        $this->render('analytics/store', [
            'page'         => 'store',
            'page_title'   => 'Analitik Toko',
            'ranking'      => $model->ranking($start, $end),
            'by_city'      => $model->revenueByCity($start, $end),
            'by_location'  => $model->revenueByLocation($start, $end),
            'kpis'         => $dash->kpis($start, $end),
            'bounds'       => $bounds,
            'start_date'   => $start,
            'end_date'     => $end,
        ]);
    }

    public function index(): void
    {
        Auth::require('crud.stores');
        $model = new StoreModel();
        $search = (string)input('q','');
        $city   = (string)input('city','');
        $page   = max(1, (int)input('page',1));
        $data   = $model->paginate($search, $city, $page, 12);
        $this->render('crud/stores_index', [
            'page'      => 'store_crud',
            'page_title'=> 'Manajemen Toko',
            'store_data'=> $data,
            'search'    => $search,
            'city_f'    => $city,
            'cities'    => $model->cities(),
            'locations' => $model->locations(),
        ]);
    }

    public function store(): void
    {
        Auth::require('crud.stores');
        if (!Auth::checkCsrf((string)input('_token'))) { $this->redirect('stores'); return; }
        try {
            (new StoreModel())->create([
                'store_name'     => trim((string)input('store_name')),
                'store_city'     => trim((string)input('store_city')),
                'store_location' => (string)input('store_location'),
                'store_open_date'=> (string)input('store_open_date'),
            ]);
            flash('success','Toko berhasil dibuat.');
        } catch (Exception $e) {
            flash('error','Gagal membuat toko: '.$e->getMessage());
        }
        $this->redirect('stores');
    }

    public function update(): void
    {
        Auth::require('crud.stores');
        if (!Auth::checkCsrf((string)input('_token'))) { $this->redirect('stores'); return; }
        try {
            (new StoreModel())->update((int)input('store_key'), [
                'store_name'     => trim((string)input('store_name')),
                'store_city'     => trim((string)input('store_city')),
                'store_location' => (string)input('store_location'),
                'store_open_date'=> (string)input('store_open_date'),
            ]);
            flash('success','Toko berhasil diperbarui.');
        } catch (Exception $e) {
            flash('error','Pembaruan gagal: '.$e->getMessage());
        }
        $this->redirect('stores');
    }

    public function delete(): void
    {
        Auth::require('crud.stores');
        if (!Auth::checkCsrf((string)input('_token'))) { $this->redirect('stores'); return; }
        try {
            (new StoreModel())->delete((int)input('store_key'));
            flash('success','Toko berhasil dihapus.');
        } catch (Exception $e) {
            flash('error','Penghapusan gagal: '.$e->getMessage());
        }
        $this->redirect('stores');
    }
}