<?php
class PaymentModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function createPayment($data) {
        $this->db->query("INSERT INTO payments (order_id, payment_method, gateway, transaction_id, payment_url, amount, status) 
                          VALUES (:order_id, :payment_method, :gateway, :transaction_id, :payment_url, :amount, :status)");
        $this->db->bind('order_id', $data['order_id']);
        $this->db->bind('payment_method', $data['payment_method']);
        $this->db->bind('gateway', $data['gateway']);
        $this->db->bind('transaction_id', $data['transaction_id']);
        $this->db->bind('payment_url', $data['payment_url']);
        $this->db->bind('amount', $data['amount']);
        $this->db->bind('status', $data['status']);
        
        $this->db->execute();
        return $this->db->lastInsertId();
    }

    public function getPaymentByTransactionId($transaction_id) {
        $this->db->query("SELECT * FROM payments WHERE transaction_id = :transaction_id");
        $this->db->bind('transaction_id', $transaction_id);
        return $this->db->single();
    }

    public function getPaymentByOrderId($order_id) {
        // Fetch the latest payment attempt for an order
        $this->db->query("SELECT * FROM payments WHERE order_id = :order_id ORDER BY created_at DESC LIMIT 1");
        $this->db->bind('order_id', $order_id);
        return $this->db->single();
    }

    public function updatePaymentStatus($transaction_id, $status) {
        $query = "UPDATE payments SET status = :status";
        if ($status === 'PAID') {
            $query .= ", paid_at = CURRENT_TIMESTAMP";
        } elseif ($status === 'EXPIRED') {
            $query .= ", expired_at = CURRENT_TIMESTAMP";
        }
        $query .= " WHERE transaction_id = :transaction_id";

        $this->db->query($query);
        $this->db->bind('status', $status);
        $this->db->bind('transaction_id', $transaction_id);
        
        $this->db->execute();
        return $this->db->rowCount();
    }
    
    // For Dashboard Stats
    public function getPaymentStatistics() {
        $this->db->query("SELECT payment_method, COUNT(*) as total_count FROM payments WHERE status = 'PAID' GROUP BY payment_method");
        return $this->db->resultSet();
    }

    public function getTotalRevenue() {
        $this->db->query("SELECT SUM(amount) as total FROM payments WHERE status = 'PAID'");
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }

    public function countPaymentsByStatus($status) {
        $this->db->query("SELECT COUNT(*) as total FROM payments WHERE status = :status");
        $this->db->bind('status', $status);
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }
}
