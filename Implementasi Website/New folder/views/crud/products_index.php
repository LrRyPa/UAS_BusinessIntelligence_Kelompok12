<?php
$can_edit   = $can_edit   ?? false;
$product_data = $product_data ?? [
    'rows' => [],
    'total' => 0,
    'page' => 1,
    'per_page' => 12
];
$product_data['rows']    = is_array($product_data['rows'] ?? null)    ? $product_data['rows']    : [];
$product_data['total']   = isset($product_data['total'])              ? (int)$product_data['total']   : 0;
$product_data['page']    = isset($product_data['page'])               ? (int)$product_data['page']    : 1;
$product_data['per_page']= isset($product_data['per_page'])           ? (int)$product_data['per_page']: 12;
$search     = $search     ?? '';
$category_f = $category_f ?? '';
$categories = $categories ?? [];
?>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-eyebrow">Inventory Master product_data</div>
            <h3>Products (<?= number_format($product_data['total']) ?>)</h3>
        </div>
        <?php if ($can_edit): ?>
        <button class="btn btn-primary" onclick="openModal('product-create')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Product
        </button>
        <?php endif; ?>
    </div>

    <form method="GET" action="<?= base_url() ?>/index.php" class="toolbar-row">
        <input type="hidden" name="r" value="products">
        <div class="search-inline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search by name or product ID…">
        </div>
        <select name="cat">
            <option value="">All categories</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?= e($c['category_name']) ?>" <?= ($category_f === $c['category_name']) ? 'selected':'' ?>>
                <?= e($c['category_name']) ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-light">Filter</button>
        <a href="<?= url('products') ?>" class="btn btn-ghost">Clear</a>
    </form>

    <div class="product-grid">
        <?php foreach ($product_data['rows'] as $p): ?>
        <div class="product-card">
            <div class="pc-top">
                <div class="pc-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.3 7 12 12 20.7 7"/><line x1="12" y1="22" x2="12" y2="12"/></svg>
                </div>
                <span class="chip chip-tier-<?= e(strtolower(str_replace([' ','-'], '', $p['price_tier']))) ?>"><?= e($p['price_tier']) ?></span>
            </div>
            <div class="pc-name"><?= e($p['product_name']) ?></div>
            <div class="pc-cat"><?= e($p['product_category']) ?></div>
            <div class="pc-prices">
                <div><span class="muted small">Cost</span><strong><?= money((float)$p['product_cost']) ?></strong></div>
                <div><span class="muted small">Price</span><strong><?= money((float)$p['product_price']) ?></strong></div>
            </div>
            <div class="pc-stats">
                <div><span class="muted small">Stock</span><strong><?= compact_number((int)$p['total_stock']) ?></strong></div>
                <div><span class="muted small">Sold</span><strong><?= compact_number((int)$p['total_units_sold']) ?></strong></div>
                <div><span class="muted small">Revenue</span><strong><?= compact_money((float)$p['total_revenue']) ?></strong></div>
            </div>
            <?php if ($can_edit): ?>
            <div class="pc-actions">
                <button class="btn btn-sm btn-light" onclick='openEdit(<?= json_encode($p) ?>)'>Edit</button>
                <button class="btn btn-sm btn-danger-ghost" onclick='confirmDelete(<?= (int)$p['product_key'] ?>, <?= json_encode($p['product_name']) ?>)'>Delete</button>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <?php
    $pages = max(1, (int)ceil($product_data['total'] / $product_data['per_page']));
    $current = (int)$product_data['page'];
    if ($pages > 1):
    ?>
    <div class="pagination">
        <?php for ($i=max(1,$current-3); $i<=min($pages,$current+3); $i++): ?>
        <a href="<?= base_url() ?>/index.php?r=products&q=<?= urlencode($search) ?>&cat=<?= urlencode($category_f) ?>&page=<?= $i ?>"
           class="page-btn <?= $i === $current ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php if ($can_edit): ?>
<div id="modal-product-create" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <h3>New Product</h3>
            <button class="modal-close" onclick="closeModal('product-create')">&times;</button>
        </div>
        <form method="POST" action="<?= url('products/store') ?>" class="modal-body">
            <input type="hidden" name="_token" value="<?= e(Auth::csrfToken()) ?>">
            <div class="form-grid">
                <div class="form-group full">
                    <label>Product name</label>
                    <input type="text" name="product_name" required>
                </div>
                <div class="form-group full">
                    <label>Category</label>
                    <select name="category_key" required>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['category_key'] ?>"><?= e($c['category_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cost</label>
                    <input type="number" step="0.01" name="product_cost" required min="0">
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <input type="number" step="0.01" name="product_price" required min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('product-create')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-product-edit" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Edit Product</h3>
            <button class="modal-close" onclick="closeModal('product-edit')">&times;</button>
        </div>
        <form method="POST" action="<?= url('products/update') ?>" class="modal-body">
            <input type="hidden" name="_token" value="<?= e(Auth::csrfToken()) ?>">
            <input type="hidden" name="product_key" id="edit_key">
            <div class="form-grid">
                <div class="form-group full">
                    <label>Product name</label>
                    <input type="text" name="product_name" id="edit_name" required>
                </div>
                <div class="form-group full">
                    <label>Category</label>
                    <select name="category_key" id="edit_cat" required>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= (int)$c['category_key'] ?>"><?= e($c['category_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cost</label>
                    <input type="number" step="0.01" name="product_cost" id="edit_cost" required min="0">
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <input type="number" step="0.01" name="product_price" id="edit_price" required min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('product-edit')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-product-delete" class="modal-overlay">
    <div class="modal-card sm">
        <div class="modal-header"><h3>Delete Product?</h3><button class="modal-close" onclick="closeModal('product-delete')">&times;</button></div>
        <form method="POST" action="<?= url('products/delete') ?>" class="modal-body">
            <input type="hidden" name="_token" value="<?= e(Auth::csrfToken()) ?>">
            <input type="hidden" name="product_key" id="del_key">
            <p>You are about to delete <strong id="del_name"></strong>. This action cannot be undone.</p>
            <p class="muted small">Products with sales records cannot be deleted.</p>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('product-delete')">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(p) {
    document.getElementById('edit_key').value   = p.product_key;
    document.getElementById('edit_name').value  = p.product_name;
    document.getElementById('edit_cat').value   = p.category_key;
    document.getElementById('edit_cost').value  = p.product_cost;
    document.getElementById('edit_price').value = p.product_price;
    openModal('product-edit');
}
function confirmDelete(id, name) {
    document.getElementById('del_key').value = id;
    document.getElementById('del_name').textContent = name;
    openModal('product-delete');
}
</script>
<?php endif; ?>
