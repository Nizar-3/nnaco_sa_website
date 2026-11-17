<?php
class AdminCategoryController extends Controller {
    public function __construct() {
        Middleware::adminOrOwner();
    }

    public function index() {
        $data['title'] = 'Manajemen Kategori - Admin';
        $keyword = $_GET['q'] ?? '';
        $data['keyword'] = htmlspecialchars($keyword);
        $data['categories'] = $this->model('CategoryModel')->searchCategories($keyword);

        if ($this->isAjax()) {
            $this->view('admin/category/index', $data);
            return;
        }

        $this->view('templates/header', $data);
        $this->view('admin/category/index', $data);
        $this->view('templates/footer', $data);
    }

    public function create() {
        $data['title'] = 'Tambah Kategori - Admin';
        $this->view('templates/header', $data);
        $this->view('admin/category/create', $data);
        $this->view('templates/footer', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Middleware::verifyCsrf();
            if (empty(trim($_POST['name']))) {
                Flasher::setFlash('Nama kategori', 'tidak boleh kosong', 'danger');
                header('Location: ' . BASEURL . '/admincategory/create');
                exit;
            }

            if ($this->model('CategoryModel')->insertCategory($_POST) > 0) {
                Flasher::setFlash('Kategori', 'berhasil ditambahkan', 'success');
            } else {
                Flasher::setFlash('Kategori', 'gagal ditambahkan', 'danger');
            }
            header('Location: ' . BASEURL . '/admincategory');
            exit;
        }
    }

    public function edit($id = null) {
        if (is_null($id)) {
            header('Location: ' . BASEURL . '/admincategory');
            exit;
        }

        $data['title'] = 'Edit Kategori - Admin';
        $data['category'] = $this->model('CategoryModel')->getCategoryById($id);

        $this->view('templates/header', $data);
        $this->view('admin/category/edit', $data);
        $this->view('templates/footer', $data);
    }

    public function update($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !is_null($id)) {
            Middleware::verifyCsrf();
            if (empty(trim($_POST['name']))) {
                Flasher::setFlash('Nama kategori', 'tidak boleh kosong', 'danger');
                header('Location: ' . BASEURL . '/admincategory/edit/' . $id);
                exit;
            }

            if ($this->model('CategoryModel')->updateCategory($id, $_POST) >= 0) {
                Flasher::setFlash('Kategori', 'berhasil diupdate', 'success');
            } else {
                Flasher::setFlash('Kategori', 'gagal diupdate', 'danger');
            }
            header('Location: ' . BASEURL . '/admincategory');
            exit;
        }
    }

    public function delete($id = null) {
        if (!is_null($id)) {
            if ($this->model('CategoryModel')->deleteCategory($id) > 0) {
                Flasher::setFlash('Kategori', 'berhasil dihapus', 'success');
            } else {
                Flasher::setFlash('Kategori', 'gagal dihapus', 'danger');
            }
        }
        header('Location: ' . BASEURL . '/admincategory');
        exit;
    }
}
