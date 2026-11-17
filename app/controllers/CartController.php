<?php
class CartController extends Controller {
    public function index() {
        $data['title'] = 'Keranjang Belanja - nnaco_sa';
        
        $data['cart_items'] = [];
        $data['total'] = 0;
        
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            $productModel = $this->model('ProductModel');
            foreach ($_SESSION['cart'] as $product_id => $quantity) {
                $product = $productModel->getProductById($product_id);
                if ($product) {
                    $subtotal = $product['price'] * $quantity;
                    $data['total'] += $subtotal;
                    $data['cart_items'][] = [
                        'product' => $product,
                        'quantity' => $quantity,
                        'subtotal' => $subtotal
                    ];
                }
            }
        }

        $this->view('templates/header', $data);
        $this->view('cart/index', $data);
        $this->view('templates/footer', $data);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Middleware::verifyCsrf();
            
            $product_id = $_POST['product_id'];
            $quantity = (int)($_POST['quantity'] ?? 0);

            if ($quantity <= 0) {
                Flasher::setFlash('Kuantitas', 'tidak valid', 'danger');
                header('Location: ' . BASEURL . '/product/detail/' . $product_id);
                exit;
            }

            $product = $this->model('ProductModel')->getProductById($product_id);
            if (!$product) {
                Flasher::setFlash('Produk', 'tidak ditemukan', 'danger');
                header('Location: ' . BASEURL . '/product');
                exit;
            }

            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }

            if (isset($_SESSION['cart'][$product_id])) {
                $new_qty = $_SESSION['cart'][$product_id] + $quantity;
            } else {
                $new_qty = $quantity;
            }

            if ($new_qty > $product['stock']) {
                Flasher::setFlash('Stok tidak mencukupi.', 'Sisa stok: ' . $product['stock'], 'danger');
                header('Location: ' . BASEURL . '/product/detail/' . $product_id);
                exit;
            }

            $_SESSION['cart'][$product_id] = $new_qty;
            Flasher::setFlash('Produk', 'berhasil ditambahkan ke keranjang', 'success');
            header('Location: ' . BASEURL . '/cart');
            exit;
        }
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Middleware::verifyCsrf();
            
            $product_id = $_POST['product_id'];
            $quantity = (int)($_POST['quantity'] ?? 0);

            if ($quantity <= 0) {
                $this->remove($product_id);
                exit; // remove already redirects
            }

            $product = $this->model('ProductModel')->getProductById($product_id);
            if ($product && $quantity <= $product['stock']) {
                $_SESSION['cart'][$product_id] = $quantity;
                Flasher::setFlash('Keranjang', 'diperbarui', 'success');
            } else {
                Flasher::setFlash('Gagal update.', 'Stok tidak mencukupi', 'danger');
            }
            header('Location: ' . BASEURL . '/cart');
            exit;
        }
    }

    public function remove($id = null) {
        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
            Flasher::setFlash('Produk', 'dihapus dari keranjang', 'success');
        }
        header('Location: ' . BASEURL . '/cart');
        exit;
    }
}
