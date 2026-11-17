<?php
class MockPaymentGateway implements PaymentGatewayInterface {
    
    public function createPayment($order_id, $amount, $payment_method, $customer_details = []) {
        // Generate a fake transaction ID
        $transaction_id = 'NNACO-MOCK-' . strtoupper(uniqid());
        
        // Return simulated redirect URL which points to our local mock simulation UI
        $payment_url = BASEURL . '/payment/simulate/' . $transaction_id;

        return [
            'transaction_id' => $transaction_id,
            'payment_url' => $payment_url,
            'status' => 'PENDING'
        ];
    }

    public function getPaymentStatus($transaction_id) {
        // In mock, we can just return PENDING or retrieve from some temporary cache.
        // Actually, our payment webhook will update the DB directly in mock mode.
        // This method simulates API polling if needed.
        return ['status' => 'PENDING'];
    }

    public function verifyCallbackSignature($payload) {
        // Mock verification (always true or verify a simple static token)
        // In production (e.g. Midtrans/Xendit), this will check sha512(order_id+status+amount+server_key)
        return true;
    }
}
