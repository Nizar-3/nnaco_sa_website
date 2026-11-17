<?php
class PaymentController extends Controller {

    public function __construct() {
        // We do not enforce role here globally because callback is accessed by external webhook
    }

    // Displays the Mock Payment Simulator UI
    public function simulate($transaction_id = null) {
        if (!$transaction_id) {
            header('Location: ' . BASEURL);
            exit;
        }

        $paymentModel = $this->model('PaymentModel');
        $payment = $paymentModel->getPaymentByTransactionId($transaction_id);

        if (!$payment) {
            Flasher::setFlash('Error', 'Transaksi tidak ditemukan', 'danger');
            header('Location: ' . BASEURL);
            exit;
        }

        $orderModel = $this->model('OrderModel');
        $order = $orderModel->getOrderByIdAdmin($payment['order_id']);

        $data['title'] = 'nnaco_sa Payment Simulation';
        $data['payment'] = $payment;
        $data['order'] = $order;

        $this->view('templates/header', $data);
        $this->view('payment/simulation', $data);
        $this->view('templates/footer', $data);
    }

    // Handles the actual POST callback from the gateway
    public function callback() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['transaction_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Bad Request']);
            exit;
        }

        $gateway = new MockPaymentGateway();
        $service = new PaymentService($gateway);

        $success = $service->handleCallback($input);

        if ($success) {
            http_response_code(200);
            echo json_encode(['status' => 'OK']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Callback verification failed']);
        }
        exit;
    }

    // Retry a failed or expired payment for an existing order
    public function retry($order_id = null) {
        Middleware::role('Customer');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$order_id) {
            header('Location: ' . BASEURL . '/customer/history');
            exit;
        }
        Middleware::verifyCsrf();

        $payment_method = $_POST['payment_method'] ?? '';
        if (empty($payment_method)) {
            Flasher::setFlash('Gagal', 'Pilih metode pembayaran', 'danger');
            header('Location: ' . BASEURL . '/order/detail/' . $order_id);
            exit;
        }

        $orderModel = $this->model('OrderModel');
        $order = $orderModel->getOrderByIdAndUser($order_id, $_SESSION['user_id']);

        if (!$order) {
            Flasher::setFlash('Gagal', 'Order tidak ditemukan', 'danger');
            header('Location: ' . BASEURL . '/customer/history');
            exit;
        }

        if ($order['status'] !== 'MENUNGGU PEMBAYARAN') {
            Flasher::setFlash('Gagal', 'Order ini sudah diproses atau tidak dapat dibayar lagi', 'danger');
            header('Location: ' . BASEURL . '/order/detail/' . $order_id);
            exit;
        }

        // Process a new payment attempt
        $gateway = new MockPaymentGateway();
        $service = new PaymentService($gateway);

        $customer_details = ['email' => $_SESSION['user_email'] ?? 'customer@example.com'];
        $paymentResponse = $service->processCheckoutPayment($order['id'], $order['total'], $payment_method, $customer_details);

        if (isset($paymentResponse['payment_url'])) {
            header('Location: ' . $paymentResponse['payment_url']);
            exit;
        } else {
            Flasher::setFlash('Gagal', 'Gagal memuat gateway pembayaran', 'danger');
            header('Location: ' . BASEURL . '/order/detail/' . $order_id);
            exit;
        }
    }
}
