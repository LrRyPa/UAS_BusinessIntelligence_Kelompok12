<?php
$store_data = $store_data ?? [
    'rows' => [],
    'total' => 0,
    'page' => 1,
    'per_page' => 12
];

$store_data['rows']    = is_array($store_data['rows'] ?? null)   ? $store_data['rows']    : [];
$store_data['total']   = isset($store_data['total'])             ? (int)$store_data['total'] : 0;
$store_data['page']    = isset($store_data['page'])              ? (int)$store_data['page']  : 1;
$store_data['per_page']= isset($store_data['per_page'])          ? (int)$store_data['per_page'] : 12;
?>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-eyebrow">Retail Network</div>
            <h3>Stores (<?= number_format($store_data['total']) ?>)</h3>
        </div>
        <button class="btn btn-primary" onclick="openModal('store-create')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Store
        </button>
    </div>

    <form method="GET" action="<?= base_url() ?>/index.php" class="toolbar-row">
        <input type="hidden" name="r" value="stores">
        <div class="search-inline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search store name or city…">
        </div>
        <select name="city">
            <option value="">All cities</option>
            <?php foreach ($cities as $c): ?>
            <option value="<?= e($c) ?>" <?= ($city_f === $c) ? 'selected':'' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-light">Filter</button>
        <a href="<?= url('stores') ?>" class="btn btn-ghost">Clear</a>
    </form>

    <div class="store-grid">
        <?php foreach ($store_data['rows'] as $s): ?>
        <div class="store-card">
            <div class="sc-head">
                <div class="sc-pin">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l1-6h16l1 6"/><path d="M3 9v11h18V9"/></svg>
                </div>
                <span class="chip chip-soft"><?= e($s['store_location']) ?></span>
            </div>
            <div class="sc-name"><?= e($s['store_name']) ?></div>
            <div class="sc-city">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7-7-7-12a7 7 0 0114 0c0 5-7 12-7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>
                <?= e($s['store_city']) ?>
            </div>
            <div class="sc-meta">
                <div><span class="muted small">Opened</span><strong><?= e(date('M Y', strtotime($s['store_open_date']))) ?></strong></div>
                <div><span class="muted small">Age</span><strong><?= number_format((float)$s['store_age_years'],1) ?> yrs</strong></div>
            </div>
            <div class="sc-stats">
                <div><span class="muted small">Revenue</span><strong><?= compact_money((float)$s['total_revenue']) ?></strong></div>
                <div><span class="muted small">Orders</span><strong><?= compact_number((int)$s['total_orders']) ?></strong></div>
            </div>
            <div class="sc-actions">
                <button class="btn btn-sm btn-light" onclick='openStoreEdit(<?= json_encode($s) ?>)'>Edit</button>
                <button class="btn btn-sm btn-danger-ghost" onclick='confirmStoreDelete(<?= (int)$s['store_key'] ?>, <?= json_encode($s['store_name']) ?>)'>Delete</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php
    $pages = max(1, (int)ceil($store_data['total'] / $store_data['per_page']));
    $current = (int)$store_data['page'];
    if ($pages > 1):
    ?>
    <div class="pagination">
        <?php for ($i=max(1,$current-3); $i<=min($pages,$current+3); $i++): ?>
        <a href="<?= base_url() ?>/index.php?r=stores&q=<?= urlencode($search) ?>&city=<?= urlencode($city_f) ?>&page=<?= $i ?>"
           class="page-btn <?= $i === $current ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<div id="modal-store-create" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header"><h3>New Store</h3><button class="modal-close" onclick="closeModal('store-create')">&times;</button></div>
        <form method="POST" action="<?= url('stores/store') ?>" class="modal-body">
            <input type="hidden" name="_token" value="<?= e(Auth::csrfToken()) ?>">
            <div class="form-grid">
                <div class="form-group full">
                    <label>Store name</label>
                    <input type="text" name="store_name" required>
                </div>
                <div class="form-group">
                    <label>City</label>
                    <input type="text" name="store_city" required list="cities-list">
                    <datalist id="cities-list">
                        <?php foreach ($cities as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?>
                    </datalist>
                </div>
                <div class="form-group">
                    <label>Location type</label>
                    <select name="store_location" required>
                        <?php foreach ($locations as $l): ?>
                        <option value="<?= e($l) ?>"><?= e($l) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group full">
                    <label>Open date</label>
                    <input type="date" name="store_open_date" required value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('store-create')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Store</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-store-edit" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header"><h3>Edit Store</h3><button class="modal-close" onclick="closeModal('store-edit')">&times;</button></div>
        <form method="POST" action="<?= url('stores/update') ?>" class="modal-body">
            <input type="hidden" name="_token" value="<?= e(Auth::csrfToken()) ?>">
            <input type="hidden" name="store_key" id="se_key">
            <div class="form-grid">
                <div class="form-group full"><label>Store name</label><input type="text" name="store_name" id="se_name" required></div>
                <div class="form-group"><label>City</label><input type="text" name="store_city" id="se_city" required list="cities-list"></div>
                <div class="form-group">
                    <label>Location type</label>
                    <select name="store_location" id="se_loc" required>
                        <?php foreach ($locations as $l): ?><option value="<?= e($l) ?>"><?= e($l) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group full"><label>Open date</label><input type="date" name="store_open_date" id="se_date" required></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('store-edit')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-store-delete" class="modal-overlay">
    <div class="modal-card sm">
        <div class="modal-header"><h3>Delete Store?</h3><button class="modal-close" onclick="closeModal('store-delete')">&times;</button></div>
        <form method="POST" action="<?= url('stores/delete') ?>" class="modal-body">
            <input type="hidden" name="_token" value="<?= e(Auth::csrfToken()) ?>">
            <input type="hidden" name="store_key" id="ds_skey">
            <p>You are about to delete <strong id="ds_sname"></strong>. This action cannot be undone.</p>
            <p class="muted small">Stores with sales records cannot be deleted.</p>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('store-delete')">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
function openStoreEdit(s) {
    document.getElementById('se_key').value  = s.store_key;
    document.getElementById('se_name').value = s.store_name;
    document.getElementById('se_city').value = s.store_city;
    document.getElementById('se_loc').value  = s.store_location;
    document.getElementById('se_date').value = s.store_open_date;
    openModal('store-edit');
}
function confirmStoreDelete(id, name) {
    document.getElementById('ds_skey').value = id;
    document.getElementById('ds_sname').textContent = name;
    openModal('store-delete');
}
</script>
