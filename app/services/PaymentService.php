<?php
class PaymentService {
    private $gateway;
    private $paymentModel;
    private $orderModel;

    public function __construct(PaymentGatewayInterface $gateway) {
        $this->gateway = $gateway;
        require_once '../app/models/PaymentModel.php';
        require_once '../app/models/OrderModel.php';
        $this->paymentModel = new PaymentModel();
        $this->orderModel = new OrderModel();
    }

    public function processCheckoutPayment($order_id, $amount, $payment_method, $customer_details = []) {
        // 1. Call Gateway API
        $gatewayResponse = $this->gateway->createPayment($order_id, $amount, $payment_method, $customer_details);

        // 2. Save Payment Record
        $paymentData = [
            'order_id' => $order_id,
            'payment_method' => $payment_method,
            'gateway' => get_class($this->gateway),
            'transaction_id' => $gatewayResponse['transaction_id'],
            'payment_url' => $gatewayResponse['payment_url'],
            'amount' => $amount,
            'status' => 'PENDING'
        ];

        $payment_id = $this->paymentModel->createPayment($paymentData);

        return $gatewayResponse; // Returns transaction_id and payment_url to redirect user
    }

    public function handleCallback($payload) {
        // 1. Verify signature
        if (!$this->gateway->verifyCallbackSignature($payload)) {
            error_log("PaymentService: Invalid callback signature.");
            return false;
        }

        $transaction_id = $payload['transaction_id'];
        $new_status = $payload['status']; // 'PAID', 'FAILED', 'EXPIRED'

        // 2. Find payment
        $payment = $this->paymentModel->getPaymentByTransactionId($transaction_id);
        if (!$payment) {
            error_log("PaymentService: Transaction ID not found: " . $transaction_id);
            return false;
        }

        // 3. Check idempotency (Prevent double processing if already PAID)
        if ($payment['status'] === 'PAID') {
            return true; // Already processed successfully
        }

        // 4. Update Payment Status
        $this->paymentModel->updatePaymentStatus($transaction_id, $new_status);

        // 5. Update Order Status if PAID
        if ($new_status === 'PAID') {
            // Check if amount matches
            if ((float)$payment['amount'] !== (float)$payload['amount']) {
                error_log("PaymentService: Amount mismatch for transaction: " . $transaction_id);
                return false;
            }
            $this->orderModel->updateOrderStatus($payment['order_id'], 'DIPROSES');

            // Memicu Notifikasi WhatsApp Pembayaran Berhasil & Pesanan Diproses
            try {
                $waService = new OrderNotificationService();
                $waService->notify('payment_confirmed', (int)$payment['order_id'], [
                    'payment_method' => $payment['payment_method'] ?? 'Online Gateway'
                ]);
                $waService->notify('order_processing', (int)$payment['order_id']);
            } catch (\Throwable $e) {
                error_log('[PaymentService] Gagal memicu notifikasi WA callback: ' . $e->getMessage());
            }
        }

        return true;
    }
}
