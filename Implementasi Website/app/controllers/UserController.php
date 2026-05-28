<?php
class UserController extends Controller
{
    public function index(): void
    {
        Auth::require('crud.users');
        $model  = new UserModel();
        $search = (string)input('q','');
        $page   = max(1,(int)input('page',1));
        $perPage= 15;
        $offset = ($page - 1) * $perPage;
        $data   = $model->all($search, $perPage, $offset);

        $this->render('users/index', [
            'page'      => 'users',
            'page_title'=> 'Manajemen Pengguna',
            'users_data'=> $data,
            'search'    => $search,
            'page_num'  => $page,
            'per_page'  => $perPage,
            'roles_cfg' => Auth::roles()['roles'],
        ]);
    }

    public function store(): void
    {
        Auth::require('crud.users');
        if (!Auth::checkCsrf((string)input('_token'))) { $this->redirect('users'); return; }
        try {
            (new UserModel())->create([
                'username'  => trim((string)input('username')),
                'password'  => (string)input('password'),
                'full_name' => trim((string)input('full_name')),
                'email'     => (string)input('email'),
                'role'      => (string)input('role'),
                'is_active' => (int)input('is_active', 1),
            ]);
            flash('success','Pengguna berhasil dibuat.');
        } catch (Exception $e) {
            flash('error','Gagal membuat pengguna: '.$e->getMessage());
        }
        $this->redirect('users');
    }

    public function update(): void
    {
        Auth::require('crud.users');
        if (!Auth::checkCsrf((string)input('_token'))) { $this->redirect('users'); return; }
        try {
            (new UserModel())->update((int)input('id'), [
                'full_name' => trim((string)input('full_name')),
                'email'     => (string)input('email'),
                'role'      => (string)input('role'),
                'is_active' => (int)input('is_active', 1),
                'password'  => (string)input('password',''),
            ]);
            flash('success','Pengguna berhasil diperbarui.');
        } catch (Exception $e) {
            flash('error','Pembaruan gagal: '.$e->getMessage());
        }
        $this->redirect('users');
    }

    public function delete(): void
    {
        Auth::require('crud.users');
        if (!Auth::checkCsrf((string)input('_token'))) { $this->redirect('users'); return; }
        $id = (int)input('id');
        if ($id === (int)Auth::user()['id']) {
            flash('error','Anda tidak dapat menghapus akun sendiri.');
            $this->redirect('users');
            return;
        }
        try {
            (new UserModel())->delete($id);
            flash('success','Pengguna berhasil dihapus.');
        } catch (Exception $e) {
            flash('error','Penghapusan gagal: '.$e->getMessage());
        }
        $this->redirect('users');
    }
}