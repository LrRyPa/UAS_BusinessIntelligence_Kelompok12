<?php

$sales_data = $sales_data ?? [
    'rows' => [],
    'total' => 0,
    'page' => 1,
    'per_page' => 12
];

$search = $search ?? '';
$store_f = $store_f ?? '';
$product_f = $product_f ?? '';

?>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-eyebrow">Transaksi</div>
            <h3>Data Penjualan (<?= number_format($sales_data['total']) ?>)</h3>
        </div>
        <button class="btn btn-primary" onclick="openModal('sale-create')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Penjualan Baru
        </button>
    </div>

    <form method="GET" action="<?= base_url() ?>/index.php" class="toolbar-row">
        <input type="hidden" name="r" value="sales">
        <div class="search-inline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari berdasarkan produk, toko, atau ID penjualan…">
        </div>
        <button type="submit" class="btn btn-light">Cari</button>
        <a href="<?= url('sales') ?>" class="btn btn-ghost">Reset</a>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead><tr>
                <th>ID Penjualan</th><th>Tanggal</th><th>Produk</th><th>Toko</th>
                <th class="num">Unit</th><th class="num">Harga Satuan</th>
                <th class="num">Pendapatan</th><th class="num">Keuntungan</th>
                <th>Aksi</th>
            </tr></thead>
            <tbody>
            <?php foreach ($sales_data['rows'] as $r): ?>
            <tr>
                <td>#<?= e((string)$r['sale_id']) ?></td>
                <td><?= e(date('M d, Y', strtotime($r['full_date']))) ?></td>
                <td><strong><?= e($r['product_name']) ?></strong>
                    <div class="muted small"><?= e($r['product_category']) ?></div></td>
                <td><?= e($r['store_name']) ?>
                    <div class="muted small"><?= e($r['store_city']) ?></div></td>
                <td class="num"><?= e((string)$r['units']) ?></td>
                <td class="num"><?= money((float)$r['unit_price']) ?></td>
                <td class="num"><strong><?= money((float)$r['revenue']) ?></strong></td>
                <td class="num"><?= money((float)$r['gross_profit']) ?></td>
                <td>
                    <button
                        type="button"
                        class="btn btn-sm btn-light"
                        onclick="openSaleEdit(this)"

                        data-sales-key="<?= (int)$r['sale_id'] ?>"
                        data-product-key="<?= (int)$r['product_key'] ?>"
                        data-store-key="<?= (int)$r['store_key'] ?>"
                        data-date="<?= e($r['full_date']) ?>"
                        data-units="<?= (int)$r['units'] ?>"
                        data-price="<?= e((string)$r['unit_price']) ?>"
                    >
                        Edit
                    </button>
                    <button class="btn btn-sm btn-danger-ghost" onclick='confirmSaleDelete(<?= (int)$r['sale_id'] ?>)'>Hapus</button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    $pages = max(1, (int)ceil($sales_data['total'] / $sales_data['per_page']));
    $current = (int)$sales_data['page'];
    if ($pages > 1):
    ?>
    <div class="pagination">
        <?php
        $start_p = max(1, $current - 3);
        $end_p   = min($pages, $current + 3);
        if ($start_p > 1) echo '<a class="page-btn" href="'.base_url().'/index.php?r=sales&q='.urlencode($search).'&page=1">1</a><span class="page-ellipsis">…</span>';
        for ($i=$start_p; $i<=$end_p; $i++): ?>
            <a href="<?= base_url() ?>/index.php?r=sales&q=<?= urlencode($search) ?>&page=<?= $i ?>"
               class="page-btn <?= $i === $current ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor;
        if ($end_p < $pages) echo '<span class="page-ellipsis">…</span><a class="page-btn" href="'.base_url().'/index.php?r=sales&q='.urlencode($search).'&page='.$pages.'">'.$pages.'</a>';
        ?>
    </div>
    <?php endif; ?>
</div>

<div id="modal-sale-create" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header"><h3>Tambah Penjualan Baru</h3><button class="modal-close" onclick="closeModal('sale-create')">&times;</button></div>
        <form method="POST" action="<?= url('sales/store') ?>" class="modal-body">
            <input type="hidden" name="_token" value="<?= e(Auth::csrfToken()) ?>">
            <div class="form-grid">
                <div class="form-group full">
                    <label>Produk</label>
                    <select name="product_key" required>
                        <option value="">Pilih produk…</option>
                        <?php foreach ($products as $p): ?>
                        <option value="<?= (int)$p['product_key'] ?>"
                        $sales_data-price="<?= e((string)$p['product_price']) ?>">
                            <?= e($p['product_name']) ?> · <?= money((float)$p['product_price']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group full">
                    <label>Toko</label>
                    <select name="store_key" required>
                        <option value="">Pilih toko…</option>
                        <?php foreach ($stores as $s): ?>
                        <option value="<?= (int)$s['store_key'] ?>"><?= e($s['store_name']) ?> · <?= e($s['store_city']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="full_date" required min="<?= e($bounds['min_date']) ?>" value="<?= e($bounds['max_date']) ?>">
                </div>
                <div class="form-group">
                    <label>Jumlah Unit</label>
                    <input type="number" name="units" min="1" value="1" required>
                </div>
                <div class="form-group full">
                    <label>Harga Satuan <span class="muted small">(kosongkan untuk memakai harga katalog)</span></label>
                    <input type="number" step="0.01" name="unit_price">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('sale-create')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Penjualan</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-sale-edit" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header"><h3>Edit Penjualan</h3><button class="modal-close" onclick="closeModal('sale-edit')">&times;</button></div>
        <form method="POST" action="<?= url('sales/update') ?>" class="modal-body">
            <input type="hidden" name="_token" value="<?= e(Auth::csrfToken()) ?>">
            <input type="hidden" name="sales_key" id="es_key">
            <div class="form-grid">
                <div class="form-group full">
                    <label>Produk</label>
                    <select name="product_key" id="es_product" required>
                        <?php foreach ($products as $p): ?>
                        <option value="<?= (int)$p['product_key'] ?>"><?= e($p['product_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group full">
                    <label>Toko</label>
                    <select name="store_key" id="es_store" required>
                        <?php foreach ($stores as $s): ?>
                        <option value="<?= (int)$s['store_key'] ?>"><?= e($s['store_name']) ?> · <?= e($s['store_city']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal</label>
                    <input type="date" name="full_date" id="es_date" required>
                </div>
                <div class="form-group">
                    <label>Jumlah Unit</label>
                    <input type="number" name="units" id="es_units" required min="1">
                </div>
                <div class="form-group full">
                    <label>Harga Satuan</label>
                    <input type="number" step="0.01" name="unit_price" id="es_price" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('sale-edit')">Batal</button>
                <button type="submit" class="btn btn-primary">Perbarui</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-sale-delete" class="modal-overlay">
    <div class="modal-card sm">
        <div class="modal-header"><h3>Hapus Penjualan?</h3><button class="modal-close" onclick="closeModal('sale-delete')">&times;</button></div>
        <form method="POST" action="<?= url('sales/delete') ?>" class="modal-body">
            <input type="hidden" name="_token" value="<?= e(Auth::csrfToken()) ?>">
            <input type="hidden" name="sales_key" id="ds_key">
            <p>Data penjualan ini akan dihapus secara permanen. Lanjutkan?</p>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('sale-delete')">Batal</button>
                <button type="submit" class="btn btn-danger">Hapus</button>
            </div>
        </form>
    </div>
</div>

<script>
function openSaleEdit(btn) {

    document.getElementById('es_key').value =
        btn.dataset.salesKey;

    document.getElementById('es_product').value =
        btn.dataset.productKey;

    document.getElementById('es_store').value =
        btn.dataset.storeKey;

    document.getElementById('es_date').value =
        btn.dataset.date;

    document.getElementById('es_units').value =
        btn.dataset.units;

    document.getElementById('es_price').value =
        btn.dataset.price;

    openModal('sale-edit');
}

function confirmSaleDelete(id) {
    document.getElementById('ds_key').value = id;
    openModal('sale-delete');
}
</script>