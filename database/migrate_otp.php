<?php
/**
 * Migration Runner: Tabel OTP WhatsApp
 * Jalankan: php database/migrate_otp.php
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

// Load .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            [$key, $val] = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($val));
        }
    }
}

$db = new Database();

echo "=== Migrasi OTP WhatsApp ===\n\n";

// 1. Buat tabel otp_requests
try {
    $db->query("
        CREATE TABLE IF NOT EXISTS `otp_requests` (
            `id`            INT AUTO_INCREMENT PRIMARY KEY,
            `phone`         VARCHAR(20) NOT NULL COMMENT 'Nomor WhatsApp yang dinormalisasi (format: 628xxx)',
            `otp_hash`      VARCHAR(255) NOT NULL COMMENT 'Hash bcrypt dari OTP plaintext',
            `user_id`       INT NULL,
            `ip_address`    VARCHAR(45) NOT NULL COMMENT 'IP pengirim request',
            `attempts`      TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Jumlah percobaan verifikasi',
            `status`        ENUM('pending','used','expired','cancelled') NOT NULL DEFAULT 'pending',
            `expires_at`    TIMESTAMP NOT NULL COMMENT 'Waktu kadaluarsa OTP',
            `used_at`       TIMESTAMP NULL DEFAULT NULL,
            `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_otp_phone_status` (`phone`, `status`),
            INDEX `idx_otp_expires` (`expires_at`),
            INDEX `idx_otp_ip` (`ip_address`, `created_at`)
        ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ");
    $db->execute();
    echo "[OK] Tabel otp_requests berhasil dibuat (atau sudah ada).\n";
} catch (Exception $e) {
    echo "[ERROR] Gagal membuat tabel otp_requests: " . $e->getMessage() . "\n";
}

// 2. Tambah kolom phone ke tabel users
try {
    // Cek apakah kolom phone sudah ada
    $db->query("SHOW COLUMNS FROM users LIKE 'phone'");
    $col = $db->single();
    if (!$col) {
        $db->query("ALTER TABLE `users` ADD COLUMN `phone` VARCHAR(20) NULL DEFAULT NULL COMMENT 'Nomor WhatsApp (format: 628xxx)' AFTER `email`");
        $db->execute();
        echo "[OK] Kolom 'phone' berhasil ditambahkan ke tabel users.\n";
    } else {
        echo "[SKIP] Kolom 'phone' sudah ada di tabel users.\n";
    }
} catch (Exception $e) {
    echo "[ERROR] Gagal menambah kolom phone: " . $e->getMessage() . "\n";
}

// 3. Tambah unique index pada phone
try {
    $db->query("SHOW INDEX FROM users WHERE Key_name = 'idx_users_phone'");
    $idx = $db->single();
    if (!$idx) {
        $db->query("ALTER TABLE `users` ADD UNIQUE KEY `idx_users_phone` (`phone`)");
        $db->execute();
        echo "[OK] Unique index idx_users_phone berhasil ditambahkan.\n";
    } else {
        echo "[SKIP] Unique index idx_users_phone sudah ada.\n";
    }
} catch (Exception $e) {
    echo "[WARNING] Index phone: " . $e->getMessage() . "\n";
}

echo "\n=== Migrasi selesai! ===\n";
echo "Tabel otp_requests siap digunakan.\n";
echo "Kolom phone tersedia di tabel users.\n\n";
echo "Selanjutnya:\n";
echo "  1. Edit file .env, isi FONNTE_TOKEN dengan token Fonnte Anda\n";
echo "  2. Pastikan nomor WhatsApp user sudah terdaftar di kolom phone\n";
