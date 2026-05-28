<?php
class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::require('dashboard.view');

        $model  = new DashboardModel();
        $bounds = $model->dateBounds();
        $start  = (string)input('start_date', $bounds['min_date']);
        $end    = (string)input('end_date', $bounds['max_date']);

        $kpis     = $model->kpis($start, $end);
        $daily  = $model->dailyTrend($start, $end);
        $byCat    = $model->revenueByCategory($start, $end);
        $topProd  = $model->topProducts(5, $start, $end);
        $topStore = $model->topStores(5, $start, $end);
        $recent   = $model->recentSales(8);

        $this->render('dashboard/index', [
            'page'      => 'dashboard',
            'page_title'=> 'Dashboard Overview',
            'kpis'      => $kpis,
            'daily'     => $daily,
            'by_cat'    => $byCat,
            'top_prod'  => $topProd,
            'top_store' => $topStore,
            'recent'    => $recent,
            'bounds'    => $bounds,
            'start_date'=> $start,
            'end_date'  => $end,
        ]);
    }
}
