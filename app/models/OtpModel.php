<?php
/**
 * OtpModel - Semua operasi database terkait OTP WhatsApp
 *
 * KEAMANAN:
 * - OTP disimpan dalam bentuk hash (password_hash), tidak plaintext
 * - Verifikasi atomik: update status 'used' bersamaan dengan pembacaan
 * - Parameter keamanan (masa berlaku, batas percobaan, cooldown) dibaca dari SettingService
 */

require_once __DIR__ . '/../services/SettingService.php';

class OtpModel {
    private Database $db;

    // Konstanta default keamanan OTP (Fallback)
    const OTP_EXPIRY_MINUTES   = 5;   // Masa berlaku OTP
    const OTP_MAX_ATTEMPTS     = 5;   // Maksimal percobaan verifikasi
    const OTP_COOLDOWN_SECONDS = 60;  // Cooldown minta OTP baru
    const RATE_LIMIT_PER_HOUR  = 5;   // Maks request OTP per jam per IP

    public function __construct() {
        $this->db = new Database();
    }

    public function getExpiryMinutes(): int {
        return (int) SettingService::get('otp.expiry_minutes', self::OTP_EXPIRY_MINUTES);
    }

    public function getMaxAttempts(): int {
        return (int) SettingService::get('otp.max_attempts', self::OTP_MAX_ATTEMPTS);
    }

    public function getCooldownSeconds(): int {
        return (int) SettingService::get('otp.resend_cooldown_seconds', self::OTP_COOLDOWN_SECONDS);
    }

    public function getRateLimitPhone(): int {
        return (int) SettingService::get('otp.rate_limit_phone_per_hour', self::RATE_LIMIT_PER_HOUR);
    }

    public function getRateLimitIp(): int {
        return (int) SettingService::get('otp.rate_limit_ip_per_hour', self::RATE_LIMIT_PER_HOUR);
    }

    /**
     * Buat OTP baru untuk nomor tertentu.
     * Membatalkan (cancel) semua OTP pending sebelumnya untuk nomor ini.
     *
     * @param string $phone     Nomor yang dinormalisasi
     * @param string $otpHash   Hash bcrypt dari OTP plaintext
     * @param string $ipAddress IP address pengirim
     * @return int ID record OTP yang baru dibuat
     */
    public function createOtp(string $phone, string $otpHash, string $ipAddress): int {
        // Batalkan semua OTP pending sebelumnya untuk nomor ini
        $this->cancelPendingOtps($phone);

        $expiry = $this->getExpiryMinutes();

        // expires_at menggunakan MySQL DATE_ADD agar sesuai timezone server DB
        $this->db->query("
            INSERT INTO otp_requests (phone, otp_hash, ip_address, status, expires_at)
            VALUES (:phone, :otp_hash, :ip_address, 'pending', DATE_ADD(NOW(), INTERVAL :expiry MINUTE))
        ");
        $this->db->bind('phone',      $phone);
        $this->db->bind('otp_hash',   $otpHash);
        $this->db->bind('ip_address', $ipAddress);
        $this->db->bind('expiry',     $expiry, PDO::PARAM_INT);
        $this->db->execute();

        return (int) $this->db->lastInsertId();
    }

    /**
     * Batalkan semua OTP pending untuk nomor tertentu
     */
    private function cancelPendingOtps(string $phone): void {
        $this->db->query("
            UPDATE otp_requests
            SET status = 'cancelled'
            WHERE phone = :phone AND status = 'pending'
        ");
        $this->db->bind('phone', $phone);
        $this->db->execute();
    }

    /**
     * Ambil OTP pending aktif terbaru untuk nomor tertentu
     *
     * @return array|false
     */
    public function getActiveOtp(string $phone): array|false {
        $this->db->query("
            SELECT * FROM otp_requests
            WHERE phone = :phone
              AND status = 'pending'
              AND expires_at > NOW()
            ORDER BY id DESC
            LIMIT 1
        ");
        $this->db->bind('phone', $phone);
        return $this->db->single();
    }

    /**
     * Tambah jumlah percobaan verifikasi yang gagal
     */
    public function incrementAttempts(int $otpId): void {
        $this->db->query("
            UPDATE otp_requests
            SET attempts = attempts + 1
            WHERE id = :id
        ");
        $this->db->bind('id', $otpId);
        $this->db->execute();
    }

    /**
     * Tandai OTP sebagai sudah digunakan (atomik)
     * Juga simpan user_id yang berhasil login
     */
    public function markAsUsed(int $otpId, int $userId): bool {
        $this->db->query("
            UPDATE otp_requests
            SET status   = 'used',
                used_at  = NOW(),
                user_id  = :user_id
            WHERE id     = :id
              AND status = 'pending'
        ");
        $this->db->bind('user_id', $userId);
        $this->db->bind('id',      $otpId);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    /**
     * Tandai OTP kadaluarsa jika batas percobaan terlampaui
     */
    public function markAsExpired(int $otpId): void {
        $this->db->query("
            UPDATE otp_requests
            SET status = 'expired'
            WHERE id = :id AND status = 'pending'
        ");
        $this->db->bind('id', $otpId);
        $this->db->execute();
    }

    /**
     * Cek apakah ada cooldown aktif (menggunakan setting dinamis)
     *
     * @return int Sisa waktu cooldown dalam detik, atau 0 jika tidak ada
     */
    public function getCooldownRemaining(string $phone): int {
        $this->db->query("
            SELECT TIMESTAMPDIFF(SECOND, created_at, NOW()) as elapsed_seconds
            FROM otp_requests
            WHERE phone = :phone
            ORDER BY id DESC
            LIMIT 1
        ");
        $this->db->bind('phone', $phone);
        $row = $this->db->single();

        if (!$row) return 0;

        $elapsed   = (int)$row['elapsed_seconds'];
        $cooldownLimit = $this->getCooldownSeconds();
        $remaining = $cooldownLimit - $elapsed;

        return max(0, $remaining);
    }

    /**
     * Cek rate limiting berdasarkan IP dalam 1 jam terakhir
     *
     * @return bool true jika sudah melebihi batas
     */
    public function isIpRateLimited(string $ipAddress): bool {
        $this->db->query("
            SELECT COUNT(*) as total FROM otp_requests
            WHERE ip_address = :ip
              AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $this->db->bind('ip', $ipAddress);
        $result = $this->db->single();
        return ($result['total'] ?? 0) >= $this->getRateLimitIp();
    }

    /**
     * Cek rate limiting berdasarkan nomor telepon dalam 1 jam terakhir
     *
     * @return bool true jika sudah melebihi batas
     */
    public function isPhoneRateLimited(string $phone): bool {
        $this->db->query("
            SELECT COUNT(*) as total FROM otp_requests
            WHERE phone = :phone
              AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $this->db->bind('phone', $phone);
        $result = $this->db->single();
        return ($result['total'] ?? 0) >= $this->getRateLimitPhone();
    }

    /**
     * Hapus OTP kadaluarsa (cleanup)
     */
    public function cleanupExpired(): int {
        $this->db->query("
            UPDATE otp_requests
            SET status = 'expired'
            WHERE status = 'pending' AND expires_at < NOW()
        ");
        $this->db->execute();
        return $this->db->rowCount();
    }
}
