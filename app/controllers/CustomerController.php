<?php
class CustomerController extends Controller {
    public function __construct() {
        Middleware::role('Customer');
    }

    public function index() {
        $data['title'] = 'Dashboard Customer';
        $this->view('templates/header', $data);
        $this->view('dashboard/customer', $data);
        $this->view('templates/footer', $data);
    }

    public function history() {
        $data['title'] = 'Riwayat Pesanan - nnaco_sa';
        $orderModel = $this->model('OrderModel');
        $paymentModel = $this->model('PaymentModel');
        
        $keyword = trim($_GET['q'] ?? '');
        $status = trim($_GET['status'] ?? '');

        // If user typed "#ORD-00012" or "ORD-00012", strip prefixes to search by numeric order id
        $searchKey = $keyword;
        if (preg_match('/(?:#?ORD-)?0*(\d+)/i', $keyword, $matches)) {
            $searchKey = $matches[1];
        }

        $orders = $orderModel->getOrdersByUserId($_SESSION['user_id'], $searchKey, $status);
        
        // Attach payment info to each order
        foreach ($orders as &$order) {
            $payment = $paymentModel->getPaymentByOrderId($order['id']);
            $order['payment'] = $payment;
        }
        $data['orders'] = $orders;
        $data['keyword'] = htmlspecialchars($keyword);
        $data['statusFilter'] = htmlspecialchars($status);

        if ($this->isAjax()) {
            $this->view('order/history', $data);
            return;
        }

        $this->view('templates/header', $data);
        $this->view('order/history', $data);
        $this->view('templates/footer', $data);
    }

    public function orderDetail($id = null) {
        if (is_null($id)) {
            header('Location: ' . BASEURL . '/customer/history');
            exit;
        }

        $orderModel = $this->model('OrderModel');
        $data['order'] = $orderModel->getOrderByIdAndUser($id, $_SESSION['user_id']);

        if (!$data['order']) {
            Flasher::setFlash('Pesanan', 'tidak ditemukan atau Anda tidak memiliki akses', 'danger');
            header('Location: ' . BASEURL . '/customer/history');
            exit;
        }

        $paymentModel = $this->model('PaymentModel');
        $data['payment'] = $paymentModel->getPaymentByOrderId($id);
        
        $data['order_details'] = $orderModel->getOrderDetails($id);
        $data['title'] = 'Detail Pesanan #NNACO-'.str_pad($id, 5, '0', STR_PAD_LEFT);

        $this->view('templates/header', $data);
        $this->view('order/detail', $data);
        $this->view('templates/footer', $data);
    }
}
