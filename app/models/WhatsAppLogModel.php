<?php
/**
 * WhatsAppLogModel
 * Mengelola riwayat dan status pengiriman notifikasi WhatsApp pesanan
 */
class WhatsAppLogModel {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Simpan log pengiriman baru
     */
    public function createLog(array $data): int|false {
        $query = "INSERT INTO whatsapp_notification_logs 
                  (order_id, customer_id, event_type, recipient_phone, message_content, status, attempt_count, provider_message_id, error_code, error_message, idempotency_key, sent_at)
                  VALUES 
                  (:order_id, :customer_id, :event_type, :recipient_phone, :message_content, :status, :attempt_count, :provider_message_id, :error_code, :error_message, :idempotency_key, :sent_at)";

        $this->db->query($query);
        $this->db->bind('order_id', $data['order_id'] ?? null);
        $this->db->bind('customer_id', $data['customer_id'] ?? null);
        $this->db->bind('event_type', $data['event_type']);
        $this->db->bind('recipient_phone', $data['recipient_phone']);
        $this->db->bind('message_content', $data['message_content']);
        $this->db->bind('status', $data['status'] ?? 'PENDING');
        $this->db->bind('attempt_count', $data['attempt_count'] ?? 1);
        $this->db->bind('provider_message_id', $data['provider_message_id'] ?? null);
        $this->db->bind('error_code', $data['error_code'] ?? null);
        $this->db->bind('error_message', $data['error_message'] ?? null);
        $this->db->bind('idempotency_key', $data['idempotency_key']);
        $this->db->bind('sent_at', ($data['status'] === 'SENT') ? date('Y-m-d H:i:s') : null);

        $this->db->execute();
        $insertId = (int)$this->db->lastInsertId();
        return $insertId > 0 ? $insertId : false;
    }

    /**
     * Cari log berdasarkan idempotency key
     */
    public function findByIdempotencyKey(string $key): ?array {
        $this->db->query("SELECT * FROM whatsapp_notification_logs WHERE idempotency_key = :k LIMIT 1");
        $this->db->bind('k', $key);
        $res = $this->db->single();
        return $res ?: null;
    }

    /**
     * Ambil log berdasarkan ID
     */
    public function getById(int $id): ?array {
        $this->db->query("SELECT l.*, u.name as customer_name, u.email as customer_email 
                          FROM whatsapp_notification_logs l
                          LEFT JOIN users u ON l.customer_id = u.id
                          WHERE l.id = :id LIMIT 1");
        $this->db->bind('id', $id);
        $res = $this->db->single();
        return $res ?: null;
    }

    /**
     * Perbarui status pengiriman log (misalnya setelah retry atau callback)
     */
    public function updateStatus(
        int $id,
        string $status,
        ?string $providerId = null,
        ?string $errorCode = null,
        ?string $errorMessage = null,
        bool $incrementAttempt = false
    ): bool {
        $query = "UPDATE whatsapp_notification_logs SET 
                  status = :status,
                  provider_message_id = COALESCE(:provider_id, provider_message_id),
                  error_code = :error_code,
                  error_message = :error_message";

        if ($status === 'SENT') {
            $query .= ", sent_at = CURRENT_TIMESTAMP";
        }
        if ($incrementAttempt) {
            $query .= ", attempt_count = attempt_count + 1";
        }

        $query .= " WHERE id = :id";

        $this->db->query($query);
        $this->db->bind('status', $status);
        $this->db->bind('provider_id', $providerId);
        $this->db->bind('error_code', $errorCode);
        $this->db->bind('error_message', $errorMessage);
        $this->db->bind('id', $id);

        $this->db->execute();
        return true;
    }

    /**
     * Ambil riwayat log notifikasi untuk tabel UI Admin
     */
    public function getRecentLogs(int $limit = 50, ?string $status = null, ?string $search = null): array {
        $query = "SELECT l.*, u.name as customer_name 
                  FROM whatsapp_notification_logs l
                  LEFT JOIN users u ON l.customer_id = u.id
                  WHERE 1=1";

        if (!empty($status) && in_array($status, ['PENDING', 'SENT', 'FAILED'], true)) {
            $query .= " AND l.status = :status";
        }

        if (!empty($search)) {
            $query .= " AND (l.recipient_phone LIKE :search OR l.order_id LIKE :search OR u.name LIKE :search)";
        }

        $query .= " ORDER BY l.id DESC LIMIT :limit";

        $this->db->query($query);

        if (!empty($status) && in_array($status, ['PENDING', 'SENT', 'FAILED'], true)) {
            $this->db->bind('status', $status);
        }

        if (!empty($search)) {
            $this->db->bind('search', "%$search%");
        }

        $this->db->bind('limit', $limit, PDO::PARAM_INT);

        return $this->db->resultSet() ?: [];
    }

    /**
     * Hitung total log notifikasi
     */
    public function getTotalLogs(?string $status = null, ?string $search = null): int {
        $query = "SELECT COUNT(l.id) as total 
                  FROM whatsapp_notification_logs l
                  LEFT JOIN users u ON l.customer_id = u.id
                  WHERE 1=1";

        if (!empty($status) && in_array($status, ['PENDING', 'SENT', 'FAILED'], true)) {
            $query .= " AND l.status = :status";
        }

        if (!empty($search)) {
            $query .= " AND (l.recipient_phone LIKE :search OR l.order_id LIKE :search OR u.name LIKE :search)";
        }

        $this->db->query($query);

        if (!empty($status) && in_array($status, ['PENDING', 'SENT', 'FAILED'], true)) {
            $this->db->bind('status', $status);
        }

        if (!empty($search)) {
            $this->db->bind('search', "%$search%");
        }

        $res = $this->db->single();
        return (int)($res['total'] ?? 0);
    }

    /**
     * Ambil log notifikasi berdasarkan ID pesanan
     */
    public function getByOrderId(int $orderId): array {
        $this->db->query("SELECT * FROM whatsapp_notification_logs WHERE order_id = :oid ORDER BY id ASC");
        $this->db->bind('oid', $orderId);
        return $this->db->resultSet() ?: [];
    }
}
