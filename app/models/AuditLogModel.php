<?php
/**
 * AuditLogModel - Pencatatan riwayat perubahan sistem & aktivitas sensitif
 *
 * KEAMANAN:
 * - Tidak pernah mencatat plaintext API Key, Token Fonnte, OTP, kata sandi, atau secret
 * - Otomatis menyensor field bernuansa rahasia
 */
class AuditLogModel {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Catat log audit secara aman
     */
    public function log(
        string $action,
        string $category,
        ?string $fieldName = null,
        ?string $oldValue = null,
        ?string $newValue = null,
        string $status = 'SUCCESS',
        ?string $details = null
    ): bool {
        // Ambil data user dari session jika tersedia
        $userId = $_SESSION['user_id'] ?? null;
        $userName = $_SESSION['user_name'] ?? 'System/Guest';
        $userRole = $_SESSION['user_role'] ?? 'System';

        // Deteksi IP & User Agent
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipParts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ipParts[0]);
        }
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 250);

        // SENSOR OTOMATIS: Jika field name mengandung kata sensitif
        $isSensitive = false;
        if ($fieldName !== null) {
            $lowerField = strtolower($fieldName);
            if (str_contains($lowerField, 'key') 
                || str_contains($lowerField, 'token') 
                || str_contains($lowerField, 'secret') 
                || str_contains($lowerField, 'password')
                || str_contains($lowerField, 'otp')) {
                $isSensitive = true;
            }
        }

        if ($isSensitive) {
            $oldValue = !empty($oldValue) ? '[NILAI RAHASIA LAMA]' : null;
            $newValue = !empty($newValue) ? '[NILAI RAHASIA DIPERBARUI]' : null;
        }

        // Pastikan details tidak mengandung secret/token
        if ($details !== null) {
            $details = preg_replace('/(gsk_[a-zA-Z0-9_-]+)/', '[GROQ_KEY_MASKED]', $details);
            $details = preg_replace('/(AIza[a-zA-Z0-9_-]+)/', '[API_KEY_MASKED]', $details);
        }

        try {
            $this->db->query("
                INSERT INTO system_audit_logs 
                (user_id, user_name, user_role, action, category, field_name, old_value, new_value, status, details, ip_address, user_agent)
                VALUES 
                (:user_id, :user_name, :user_role, :action, :category, :field_name, :old_val, :new_val, :status, :details, :ip, :ua)
            ");
            $this->db->bind('user_id',    $userId);
            $this->db->bind('user_name',  $userName);
            $this->db->bind('user_role',  $userRole);
            $this->db->bind('action',     $action);
            $this->db->bind('category',   $category);
            $this->db->bind('field_name', $fieldName);
            $this->db->bind('old_val',    $oldValue);
            $this->db->bind('new_val',    $newValue);
            $this->db->bind('status',     $status);
            $this->db->bind('details',    $details);
            $this->db->bind('ip',         $ip);
            $this->db->bind('ua',         $userAgent);
            $this->db->execute();
            return true;
        } catch (Exception $e) {
            error_log('[AuditLogModel] Gagal menulis audit log: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ambil riwayat audit log terbaru
     */
    public function getRecentLogs(int $limit = 50, ?string $category = null): array {
        try {
            if ($category) {
                $this->db->query("
                    SELECT * FROM system_audit_logs 
                    WHERE category = :category 
                    ORDER BY id DESC 
                    LIMIT :limit
                ");
                $this->db->bind('category', $category);
                $this->db->bind('limit', $limit, PDO::PARAM_INT);
            } else {
                $this->db->query("
                    SELECT * FROM system_audit_logs 
                    ORDER BY id DESC 
                    LIMIT :limit
                ");
                $this->db->bind('limit', $limit, PDO::PARAM_INT);
            }
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log('[AuditLogModel] Gagal membaca audit log: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Hitung total log
     */
    public function getTotalLogs(?string $category = null): int {
        try {
            if ($category) {
                $this->db->query("SELECT COUNT(*) as total FROM system_audit_logs WHERE category = :category");
                $this->db->bind('category', $category);
            } else {
                $this->db->query("SELECT COUNT(*) as total FROM system_audit_logs");
            }
            $row = $this->db->single();
            return (int) ($row['total'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }
}
