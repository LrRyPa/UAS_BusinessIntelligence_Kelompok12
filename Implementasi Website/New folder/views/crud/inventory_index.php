<?php

$inventory_data = $inventory_data ?? [
    'rows' => [],
    'total' => 0,
    'page' => 1,
    'per_page' => 12
];

$search = $search ?? '';
$status_f = $status_f ?? '';

?>

<div class="kpi-grid kpi-grid-4">
    <div class="mini-card">
        <div class="mini-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 8V21H3V8M1 3h22v5z"/></svg></div>
        <div><div class="mini-value"><?= compact_number((int)$summary['total_stock']) ?></div><div class="mini-label">Total Stock</div></div>
    </div>
    <div class="mini-card danger">
        <div class="mini-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="12"/></svg></div>
        <div><div class="mini-value"><?= e((string)$summary['out_count']) ?></div><div class="mini-label">Out of Stock</div></div>
    </div>
    <div class="mini-card warning">
        <div class="mini-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg></div>
        <div><div class="mini-value"><?= e((string)$summary['low_count']) ?></div><div class="mini-label">Low Stock</div></div>
    </div>
    <div class="mini-card success">
        <div class="mini-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
        <div><div class="mini-value"><?= e((string)$summary['overstock_count']) ?></div><div class="mini-label">Overstock</div></div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-eyebrow">Stock Records</div>
            <h3>Inventory Management (<?= number_format($inventory_data['total']) ?>)</h3>
        </div>
        <button class="btn btn-primary" onclick="openModal('inv-create')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Stock Record
        </button>
    </div>

    <form method="GET" action="<?= base_url() ?>/index.php" class="toolbar-row">
        <input type="hidden" name="r" value="inventory">
        <div class="search-inline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search product or store…">
        </div>
        <select name="stock">
            <option value="" <?= $stock === '' ? 'selected':'' ?>>All stock levels</option>
            <option value="out" <?= $stock === 'out' ? 'selected':'' ?>>Out of stock (0)</option>
            <option value="low" <?= $stock === 'low' ? 'selected':'' ?>>Low (1–10)</option>
            <option value="normal" <?= $stock === 'normal' ? 'selected':'' ?>>Normal (11–50)</option>
            <option value="overstock" <?= $stock === 'overstock' ? 'selected':'' ?>>Overstock (&gt;50)</option>
        </select>
        <button type="submit" class="btn btn-light">Filter</button>
        <a href="<?= url('inventory') ?>" class="btn btn-ghost">Clear</a>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead><tr>
                <th>Product</th><th>Category</th><th>Store</th><th>City</th>
                <th class="num">Stock</th><th>Status</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php foreach ($inventory_data['rows'] as $r):
                $s = (int)$r['stock_on_hand'];
                if ($s === 0) { $statusClass='chip-danger'; $statusLabel='Out of Stock'; }
                elseif ($s <= 10) { $statusClass='chip-warning'; $statusLabel='Low'; }
                elseif ($s <= 50) { $statusClass='chip-info'; $statusLabel='Normal'; }
                else { $statusClass='chip-success'; $statusLabel='Overstock'; }
            ?>
            <tr>
                <td><strong><?= e($r['product_name']) ?></strong></td>
                <td><span class="chip chip-soft"><?= e($r['product_category']) ?></span></td>
                <td><?= e($r['store_name']) ?></td>
                <td><?= e($r['store_city']) ?></td>
                <td class="num"><strong><?= e((string)$r['stock_on_hand']) ?></strong></td>
                <td><span class="chip <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                <td>
                    <button class="btn btn-sm btn-light" onclick='openInvEdit(<?= json_encode($r) ?>)'>Update Stock</button>
                    <button class="btn btn-sm btn-danger-ghost" onclick='confirmInvDelete(<?= (int)$r['inventory_key'] ?>)'>Delete</button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    $pages = max(1, (int)ceil($inventory_data['total'] / $inventory_data['per_page']));
    $current = (int)$inventory_data['page'];
    if ($pages > 1):
    ?>
    <div class="pagination">
        <?php
        $start_p = max(1, $current - 3);
        $end_p   = min($pages, $current + 3);
        for ($i=$start_p; $i<=$end_p; $i++): ?>
            <a href="<?= base_url() ?>/index.php?r=inventory&q=<?= urlencode($search) ?>&stock=<?= urlencode($stock) ?>&page=<?= $i ?>"
               class="page-btn <?= $i === $current ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<div id="modal-inv-create" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header"><h3>Add Stock Record</h3><button class="modal-close" onclick="closeModal('inv-create')">&times;</button></div>
        <form method="POST" action="<?= url('inventory/store') ?>" class="modal-body">
            <input type="hidden" name="_token" value="<?= e(Auth::csrfToken()) ?>">
            <div class="form-grid">
                <div class="form-group full">
                    <label>Product</label>
                    <select name="product_key" required>
                        <option value="">Select product…</option>
                        <?php foreach ($products as $p): ?>
                        <option value="<?= (int)$p['product_key'] ?>"><?= e($p['product_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group full">
                    <label>Store</label>
                    <select name="store_key" required>
                        <option value="">Select store…</option>
                        <?php foreach ($stores as $s): ?>
                        <option value="<?= (int)$s['store_key'] ?>"><?= e($s['store_name']) ?> · <?= e($s['store_city']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group full">
                    <label>Stock on hand</label>
                    <input type="number" name="stock_on_hand" min="0" required value="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('inv-create')">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Record</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-inv-edit" class="modal-overlay">
    <div class="modal-card sm">
        <div class="modal-header"><h3>Update Stock</h3><button class="modal-close" onclick="closeModal('inv-edit')">&times;</button></div>
        <form method="POST" action="<?= url('inventory/update') ?>" class="modal-body">
            <input type="hidden" name="_token" value="<?= e(Auth::csrfToken()) ?>">
            <input type="hidden" name="inventory_key" id="ie_key">
            <div class="form-group">
                <label>Product</label>
                <div class="readonly-box" id="ie_product"></div>
            </div>
            <div class="form-group">
                <label>Store</label>
                <div class="readonly-box" id="ie_store"></div>
            </div>
            <div class="form-group">
                <label>Stock on hand</label>
                <input type="number" name="stock_on_hand" id="ie_stock" min="0" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('inv-edit')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-inv-delete" class="modal-overlay">
    <div class="modal-card sm">
        <div class="modal-header"><h3>Delete Stock Record?</h3><button class="modal-close" onclick="closeModal('inv-delete')">&times;</button></div>
        <form method="POST" action="<?= url('inventory/delete') ?>" class="modal-body">
            <input type="hidden" name="_token" value="<?= e(Auth::csrfToken()) ?>">
            <input type="hidden" name="inventory_key" id="di_key">
            <p>This will remove the stock record entirely. Continue?</p>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('inv-delete')">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
function openInvEdit(r) {
    document.getElementById('ie_key').value = r.inventory_key;
    document.getElementById('ie_product').textContent = r.product_name;
    document.getElementById('ie_store').textContent   = r.store_name + ' · ' + r.store_city;
    document.getElementById('ie_stock').value = r.stock_on_hand;
    openModal('inv-edit');
}
function confirmInvDelete(id) {
    document.getElementById('di_key').value = id;
    openModal('inv-delete');
}
</script>
