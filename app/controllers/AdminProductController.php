<?php
class AdminProductController extends Controller {
    public function __construct() {
        Middleware::adminOrOwner();
    }

    public function index() {
        $data['title'] = 'Manajemen Produk - Admin';
        
        $keyword = $_GET['q'] ?? '';
        $category = $_GET['category'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $productModel = $this->model('ProductModel');
        $data['products'] = $productModel->getAllProductsAdmin($keyword, $category, $status, $limit, $offset);
        $totalProducts = $productModel->getTotalProductsAdmin($keyword, $category, $status);
        
        $data['categories'] = $this->model('CategoryModel')->getAllCategories();
        $data['total_pages'] = ceil($totalProducts / $limit);
        $data['current_page'] = $page;
        $data['keyword'] = htmlspecialchars($keyword);
        $data['categoryFilter'] = htmlspecialchars($category);
        $data['statusFilter'] = htmlspecialchars($status);
        $data['total_products'] = $totalProducts;

        if ($this->isAjax()) {
            $this->view('admin/product/index', $data);
            return;
        }

        $this->view('templates/header', $data);
        $this->view('admin/product/index', $data);
        $this->view('templates/footer', $data);
    }

    public function create() {
        $data['title'] = 'Tambah Produk - Admin';
        $data['categories'] = $this->model('CategoryModel')->getAllCategories();

        $this->view('templates/header', $data);
        $this->view('admin/product/create', $data);
        $this->view('templates/footer', $data);
    }

    private function uploadImage() {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] === 4) { return null; }

        $fileName = $_FILES['image']['name'];
        $fileSize = $_FILES['image']['size'];
        $tmpName = $_FILES['image']['tmp_name'];

        $validExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $validMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        
        $fileExt = explode('.', $fileName);
        $fileExt = strtolower(end($fileExt));

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmpName);
        finfo_close($finfo);

        if (!in_array($fileExt, $validExtensions) || !in_array($mime, $validMimeTypes)) {
            Flasher::setFlash('Gagal', 'Ekstensi file tidak diizinkan', 'danger');
            return false;
        }

        if ($fileSize > 2000000) {
            Flasher::setFlash('Gagal', 'Ukuran gambar maksimal 2MB', 'danger');
            return false;
        }

        $newFileName = uniqid() . '.' . $fileExt;
        $destDir = __DIR__ . '/../../public/img/products/';
        
        if (!is_dir($destDir)) { mkdir($destDir, 0777, true); }
        move_uploaded_file($tmpName, $destDir . $newFileName);
        
        return $newFileName;
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Middleware::verifyCsrf();
            
            $name = trim($_POST['name'] ?? '');
            $category_id = (int)($_POST['category_id'] ?? 0);
            $price = (float)($_POST['price'] ?? -1);
            $stock = (int)($_POST['stock'] ?? -1);

            if (empty($name) || $category_id <= 0 || $price < 0 || $stock < 0) {
                Flasher::setFlash('Gagal', 'Input tidak valid. Harap periksa semua field.', 'danger');
                header('Location: ' . BASEURL . '/adminproduct/create');
                exit;
            }

            $imgName = $this->uploadImage();
            if ($imgName === false) {
                header('Location: ' . BASEURL . '/adminproduct/create');
                exit;
            }

            $_POST['image'] = $imgName;

            if ($this->model('ProductModel')->insertProduct($_POST) > 0) {
                Flasher::setFlash('Produk', 'berhasil ditambahkan', 'success');
            } else {
                Flasher::setFlash('Produk', 'gagal ditambahkan', 'danger');
            }
            header('Location: ' . BASEURL . '/adminproduct');
            exit;
        }
    }

    public function edit($id = null) {
        if (is_null($id)) { header('Location: ' . BASEURL . '/adminproduct'); exit; }

        $data['title'] = 'Edit Produk - Admin';
        $data['product'] = $this->model('ProductModel')->getProductByIdAdmin($id);
        $data['categories'] = $this->model('CategoryModel')->getAllCategories();

        $this->view('templates/header', $data);
        $this->view('admin/product/edit', $data);
        $this->view('templates/footer', $data);
    }

    public function update($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !is_null($id)) {
            Middleware::verifyCsrf();
            
            $name = trim($_POST['name'] ?? '');
            $category_id = (int)($_POST['category_id'] ?? 0);
            $price = (float)($_POST['price'] ?? -1);
            $stock = (int)($_POST['stock'] ?? -1);

            if (empty($name) || $category_id <= 0 || $price < 0 || $stock < 0) {
                Flasher::setFlash('Gagal', 'Input tidak valid. Harap periksa semua field.', 'danger');
                header('Location: ' . BASEURL . '/adminproduct/edit/' . $id);
                exit;
            }

            if (isset($_FILES['image']) && $_FILES['image']['error'] !== 4) {
                $imgName = $this->uploadImage();
                if ($imgName === false) {
                    header('Location: ' . BASEURL . '/adminproduct/edit/' . $id);
                    exit;
                }
                $_POST['image'] = $imgName;
            }

            if ($this->model('ProductModel')->updateProduct($id, $_POST) >= 0) {
                Flasher::setFlash('Produk', 'berhasil diupdate', 'success');
            } else {
                Flasher::setFlash('Produk', 'gagal diupdate', 'danger');
            }
            $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (BASEURL . '/adminproduct');
            header('Location: ' . $redirect);
            exit;
        }
    }

    public function delete($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !is_null($id)) {
            Middleware::verifyCsrf();
            // Optional: Hapus file gambar dari server
            $prod = $this->model('ProductModel')->getProductByIdAdmin($id);
            if ($prod && $prod['image']) {
                $imgPath = __DIR__ . '/../../public/img/products/' . $prod['image'];
                if (file_exists($imgPath)) { unlink($imgPath); }
            }

            if ($this->model('ProductModel')->deleteProduct($id) > 0) {
                Flasher::setFlash('Produk', 'berhasil dihapus', 'success');
            } else {
                Flasher::setFlash('Produk', 'gagal dihapus', 'danger');
            }
        }
        $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (BASEURL . '/adminproduct');
        header('Location: ' . $redirect);
        exit;
    }
}
