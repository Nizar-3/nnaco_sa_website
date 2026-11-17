<?php
interface PaymentGatewayInterface {
    /**
     * Create a payment transaction in the gateway
     *
     * @param int $order_id
     * @param float $amount
     * @param string $payment_method (e.g. gopay, bca, dll)
     * @param array $customer_details
     * @return array ['transaction_id' => string, 'payment_url' => string, 'status' => string]
     */
    public function createPayment($order_id, $amount, $payment_method, $customer_details = []);

    /**
     * Get payment status from gateway
     *
     * @param string $transaction_id
     * @return array ['status' => 'PENDING|PAID|FAILED|EXPIRED|CANCELLED']
     */
    public function getPaymentStatus($transaction_id);

    /**
     * Verify incoming webhook/callback payload
     *
     * @param array $payload
     * @return bool
     */
    public function verifyCallbackSignature($payload);
}
