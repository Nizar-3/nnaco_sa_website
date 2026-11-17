<?php
class ProductController extends Controller {
    public function index() {
        $data['title'] = 'Katalog Produk - nnaco_sa';

        $keyword = $_GET['q'] ?? '';
        $category_id = $_GET['category'] ?? '';
        $min_price = $_GET['min_price'] ?? '';
        $max_price = $_GET['max_price'] ?? '';
        
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 9;
        $offset = ($page - 1) * $limit;

        $productModel = $this->model('ProductModel');
        
        $data['products'] = $productModel->getFilteredProducts($keyword, $category_id, $min_price, $max_price, $limit, $offset);
        $totalProducts = $productModel->getTotalFilteredProducts($keyword, $category_id, $min_price, $max_price);
        $data['total_pages'] = ceil($totalProducts / $limit);
        $data['current_page'] = $page;
        
        $data['filters'] = [
            'q' => htmlspecialchars($keyword),
            'category' => htmlspecialchars($category_id),
            'min_price' => htmlspecialchars($min_price),
            'max_price' => htmlspecialchars($max_price)
        ];

        $data['categories'] = $this->model('CategoryModel')->getAllCategories();

        $this->view('templates/header', $data);
        $this->view('product/index', $data);
        $this->view('templates/footer', $data);
    }

    public function detail($id = null) {
        if (is_null($id)) {
            header('Location: ' . BASEURL . '/product');
            exit;
        }

        $data['product'] = $this->model('ProductModel')->getProductById($id);
        
        if (!$data['product']) {
            Flasher::setFlash('Produk', 'tidak ditemukan', 'danger');
            header('Location: ' . BASEURL . '/product');
            exit;
        }

        $data['title'] = $data['product']['name'] . ' - nnaco_sa';

        $this->view('templates/header', $data);
        $this->view('product/detail', $data);
        $this->view('templates/footer', $data);
    }
}
