<?php
class CheckoutController extends Controller {
    public function __construct() {
        // Hanya Customer yang bisa checkout
        Middleware::role('Customer');
    }

    public function index() {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            Flasher::setFlash('Keranjang', 'masih kosong', 'warning');
            header('Location: ' . BASEURL . '/product');
            exit;
        }

        $data['title'] = 'Ringkasan Checkout - nnaco_sa';
        $data['cart_items'] = [];
        $data['total'] = 0;
        
        $productModel = $this->model('ProductModel');
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $product = $productModel->getProductById($product_id);
            if ($product) {
                // Pastikan stok cukup (cek ulang di awal)
                if ($quantity > $product['stock']) {
                    Flasher::setFlash('Mohon maaf,', 'Stok ' . $product['name'] . ' tidak mencukupi untuk pesanan Anda. Sisa: ' . $product['stock'], 'danger');
                    header('Location: ' . BASEURL . '/cart');
                    exit;
                }
                $subtotal = $product['price'] * $quantity;
                $data['total'] += $subtotal;
                $data['cart_items'][] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal
                ];
            } else {
                // Jika produk sudah di-hide atau dihapus
                unset($_SESSION['cart'][$product_id]);
            }
        }

        $data['payment_methods'] = [
            'Pembayaran Tunai' => [
                'cash' => 'Cash (Bayar di Tempat / Store)'
            ]
        ];

        $this->view('templates/header', $data);
        $this->view('checkout/index', $data);
        $this->view('templates/footer', $data);
    }

    public function process() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['cart'])) {
            header('Location: ' . BASEURL . '/cart');
            exit;
        }

        Middleware::verifyCsrf();

        $productModel = $this->model('ProductModel');
        $cart_items = [];
        $total = 0;

        // Ambil data terbaru dari server
        foreach ($_SESSION['cart'] as $product_id => $quantity) {
            $product = $productModel->getProductById($product_id);
            if ($product) {
                $subtotal = $product['price'] * $quantity;
                $total += $subtotal;
                $cart_items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $subtotal
                ];
            }
        }

        if (empty($cart_items)) {
            Flasher::setFlash('Checkout gagal', 'Produk tidak valid', 'danger');
            header('Location: ' . BASEURL . '/cart');
            exit;
        }

        $payment_method = $_POST['payment_method'] ?? '';
        if (empty($payment_method)) {
            Flasher::setFlash('Checkout gagal', 'Pilih metode pembayaran terlebih dahulu', 'danger');
            header('Location: ' . BASEURL . '/checkout');
            exit;
        }

        $orderModel = $this->model('OrderModel');
        $order_id = $orderModel->placeOrder($_SESSION['user_id'], $cart_items, $total);

        if ($order_id) {
            // Sukses
            unset($_SESSION['cart']);

            // Memicu Notifikasi WhatsApp Pesanan (Non-blocking & Idempotent)
            try {
                $waService = new OrderNotificationService();
                $waService->notify('order_created', (int)$order_id, ['payment_method' => $payment_method]);
                $waService->notify('payment_pending', (int)$order_id, ['payment_method' => $payment_method]);
            } catch (\Throwable $e) {
                error_log('[CheckoutController] Gagal memicu notifikasi WA pesanan: ' . $e->getMessage());
            }
            
            Flasher::setFlash('Pesanan Anda', 'berhasil dibuat. Silakan selesaikan pembayaran.', 'success');
            header('Location: ' . BASEURL . '/customer/history');
            exit;
            
        } else {
            // Gagal (kemungkinan race condition stok)
            Flasher::setFlash('Checkout gagal', 'terjadi kesalahan saat membuat pesanan', 'danger');
            header('Location: ' . BASEURL . '/cart');
            exit;
        }
    }
}
