<?php
class OperatorProductController extends Controller {
    public function __construct() {
        Middleware::role('Operator');
    }

    public function index() {
        $data['title'] = 'Manajemen Stok Produk - Operator';
        
        $keyword = trim($_GET['q'] ?? '');
        $category_id = trim($_GET['category_id'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $productModel = $this->model('ProductModel');
        $data['categories'] = $this->model('CategoryModel')->getAllCategories();
        $data['products'] = $productModel->getAllProductsAdmin($keyword, $category_id, $status, $limit, $offset);
        $totalProducts = $productModel->getTotalProductsAdmin($keyword, $category_id, $status);
        
        $data['total_pages'] = ceil($totalProducts / $limit);
        $data['current_page'] = $page;
        $data['total_products'] = $totalProducts;
        $data['keyword'] = htmlspecialchars($keyword);
        $data['selected_category'] = $category_id;
        $data['selected_status'] = $status;

        if ($this->isAjax()) {
            $this->view('operator/product/index', $data);
            return;
        }

        $this->view('templates/header', $data);
        $this->view('operator/product/index', $data);
        $this->view('templates/footer', $data);
    }

    public function edit($id = null) {
        if (is_null($id)) { header('Location: ' . BASEURL . '/operatorproduct'); exit; }

        $data['title'] = 'Edit Stok Produk - Operator';
        $data['product'] = $this->model('ProductModel')->getProductByIdAdmin($id);

        if (!$data['product']) {
            Flasher::setFlash('Produk', 'tidak ditemukan', 'danger');
            header('Location: ' . BASEURL . '/operatorproduct');
            exit;
        }

        $this->view('templates/header', $data);
        $this->view('operator/product/edit', $data);
        $this->view('templates/footer', $data);
    }

    public function updateStock($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !is_null($id)) {
            Middleware::verifyCsrf();
            
            $newStock = (int)$_POST['stock'];
            $status = $_POST['status'];

            if ($newStock < 0) {
                Flasher::setFlash('Gagal', 'Stok tidak boleh negatif', 'danger');
                header('Location: ' . BASEURL . '/operatorproduct/edit/' . $id);
                exit;
            }

            if ($this->model('ProductModel')->updateProductStockStatus($id, $newStock, $status) >= 0) {
                Flasher::setFlash('Stok Produk', 'berhasil diperbarui', 'success');
            } else {
                Flasher::setFlash('Stok Produk', 'gagal diperbarui', 'danger');
            }
            
            $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (BASEURL . '/operatorproduct');
            header('Location: ' . $redirect);
            exit;
        }
    }

    public function delete($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !is_null($id)) {
            Middleware::verifyCsrf();
            if ($this->model('ProductModel')->deleteProduct($id) > 0) {
                Flasher::setFlash('Produk', 'berhasil dihapus', 'success');
            } else {
                Flasher::setFlash('Produk', 'gagal dihapus', 'danger');
            }
        }
        $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (BASEURL . '/operatorproduct');
        header('Location: ' . $redirect);
        exit;
    }
}
