<?php
$users_data = $users_data ?? [];
$users_data['rows']  = is_array($users_data['rows'] ?? null) ? $users_data['rows'] : [];
$users_data['total'] = isset($users_data['total'])           ? (int)$users_data['total'] : 0;
 
$search    = $search    ?? '';
$page_num  = $page_num  ?? 1;
$per_page  = $per_page  ?? 15;
$roles_cfg = $roles_cfg ?? [];
?>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-eyebrow">Kontrol Akses</div>
            <h3>Pengguna &amp; Role (<?= number_format($users_data['total']) ?>)</h3>
        </div>
        <button class="btn btn-primary" onclick="openModal('user-create')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Pengguna Baru
        </button>
    </div>

    <form method="GET" action="<?= base_url() ?>/index.php" class="toolbar-row">
        <input type="hidden" name="r" value="users">
        <div class="search-inline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari username, nama, atau email…">
        </div>
        <button type="submit" class="btn btn-light">Cari</button>
        <a href="<?= url('users') ?>" class="btn btn-ghost">Bersihkan</a>
    </form>

    <div class="table-wrap">
        <table class="table">
            <thead><tr>
                <th>Pengguna</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Dibuat</th><th>Aksi</th>
            </tr></thead>
            <tbody>
            <?php foreach ($users_data['rows'] as $u): ?>
            <tr>
                <td>
                    <div class="user-cell">
                        <div class="avatar sm"><?= e(strtoupper(substr($u['full_name'],0,1))) ?></div>
                        <strong><?= e($u['full_name']) ?></strong>
                    </div>
                </td>
                <td><?= e($u['username']) ?></td>
                <td><?= e($u['email'] ?? '—') ?></td>
                <td><span class="chip chip-role-<?= e($u['role']) ?>"><?= e($roles_cfg[$u['role']]['label'] ?? $u['role']) ?></span></td>
                <td>
                    <?php if ((int)$u['is_active'] === 1): ?>
                    <span class="chip chip-success">Aktif</span>
                    <?php else: ?>
                    <span class="chip chip-soft">Nonaktif</span>
                    <?php endif; ?>
                </td>
                <td><?= e(date('M d, Y', strtotime($u['created_at']))) ?></td>
                <td>
                    <button class="btn btn-sm btn-light" onclick='openUserEdit(<?= json_encode($u) ?>)'>Edit</button>
                    <?php if ((int)$u['id'] !== (int)$user['id']): ?>
                    <button class="btn btn-sm btn-danger-ghost" onclick='confirmUserDelete(<?= (int)$u['id'] ?>, <?= json_encode($u['full_name']) ?>)'>Hapus</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    $pages = max(1, (int)ceil($users_data['total'] / $per_page));
    if ($pages > 1):
    ?>
    <div class="pagination">
        <?php for ($i=max(1,$page_num-3); $i<=min($pages,$page_num+3); $i++): ?>
        <a href="<?= base_url() ?>/index.php?r=users&q=<?= urlencode($search) ?>&page=<?= $i ?>"
           class="page-btn <?= $i === $page_num ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<div id="modal-user-create" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header"><h3>Pengguna Baru</h3><button class="modal-close" onclick="closeModal('user-create')">&times;</button></div>
        <form method="POST" action="<?= url('users/store') ?>" class="modal-body">
            <input type="hidden" name="_token" value="<?= e(Auth::csrfToken()) ?>">
            <div class="form-grid">
                <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
                <div class="form-group"><label>Password</label><input type="password" name="password" required minlength="6"></div>
                <div class="form-group full"><label>Nama Lengkap</label><input type="text" name="full_name" required></div>
                <div class="form-group full"><label>Email</label><input type="email" name="email"></div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" required>
                        <?php foreach ($roles_cfg as $key => $cfg): ?>
                        <option value="<?= e($key) ?>"><?= e($cfg['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="is_active"><option value="1">Aktif</option><option value="0">Nonaktif</option></select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('user-create')">Batal</button>
                <button type="submit" class="btn btn-primary">Buat Pengguna</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-user-edit" class="modal-overlay">
    <div class="modal-card">
        <div class="modal-header"><h3>Edit Pengguna</h3><button class="modal-close" onclick="closeModal('user-edit')">&times;</button></div>
        <form method="POST" action="<?= url('users/update') ?>" class="modal-body">
            <input type="hidden" name="_token" value="<?= e(Auth::csrfToken()) ?>">
            <input type="hidden" name="id" id="ue_id">
            <div class="form-grid">
                <div class="form-group full"><label>Nama Lengkap</label><input type="text" name="full_name" id="ue_name" required></div>
                <div class="form-group full"><label>Email</label><input type="email" name="email" id="ue_email"></div>
                <div class="form-group">
                    <label>Role</label>
                    <select name="role" id="ue_role" required>
                        <?php foreach ($roles_cfg as $key => $cfg): ?>
                        <option value="<?= e($key) ?>"><?= e($cfg['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="is_active" id="ue_active"><option value="1">Aktif</option><option value="0">Nonaktif</option></select>
                </div>
                <div class="form-group full">
                    <label>Password Baru <span class="muted small">(kosongkan untuk mempertahankan password saat ini)</span></label>
                    <input type="password" name="password" minlength="6">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('user-edit')">Batal</button>
                <button type="submit" class="btn btn-primary">Perbarui</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-user-delete" class="modal-overlay">
    <div class="modal-card sm">
        <div class="modal-header"><h3>Hapus Pengguna?</h3><button class="modal-close" onclick="closeModal('user-delete')">&times;</button></div>
        <form method="POST" action="<?= url('users/delete') ?>" class="modal-body">
            <input type="hidden" name="_token" value="<?= e(Auth::csrfToken()) ?>">
            <input type="hidden" name="id" id="du_id">
            <p>Anda akan menghapus <strong id="du_name"></strong>. Tindakan ini tidak dapat dibatalkan.</p>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal('user-delete')">Batal</button>
                <button type="submit" class="btn btn-danger">Hapus</button>
            </div>
        </form>
    </div>
</div>

<script>
function openUserEdit(u) {
    document.getElementById('ue_id').value     = u.id;
    document.getElementById('ue_name').value   = u.full_name;
    document.getElementById('ue_email').value  = u.email || '';
    document.getElementById('ue_role').value   = u.role;
    document.getElementById('ue_active').value = u.is_active;
    openModal('user-edit');
}
function confirmUserDelete(id, name) {
    document.getElementById('du_id').value = id;
    document.getElementById('du_name').textContent = name;
    openModal('user-delete');
}
</script>