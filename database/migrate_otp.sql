-- Migration: Tambah tabel otp_requests untuk sistem OTP WhatsApp
-- Jalankan: php database/migrate_otp.php
-- Atau jalankan query ini langsung di phpMyAdmin

USE `nnaco_sa_db`;

-- Tabel otp_requests untuk menyimpan OTP WhatsApp
CREATE TABLE IF NOT EXISTS `otp_requests` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `phone`         VARCHAR(20) NOT NULL COMMENT 'Nomor WhatsApp yang dinormalisasi (format: 628xxx)',
    `otp_hash`      VARCHAR(255) NOT NULL COMMENT 'Hash bcrypt dari OTP plaintext',
    `user_id`       INT NULL COMMENT 'NULL jika nomor belum terdaftar, diisi saat verifikasi berhasil',
    `ip_address`    VARCHAR(45) NOT NULL COMMENT 'IP pengirim request',
    `attempts`      TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Jumlah percobaan verifikasi',
    `status`        ENUM('pending','used','expired','cancelled') NOT NULL DEFAULT 'pending',
    `expires_at`    TIMESTAMP NOT NULL COMMENT 'Waktu kadaluarsa OTP (5 menit dari pembuatan)',
    `used_at`       TIMESTAMP NULL DEFAULT NULL COMMENT 'Waktu OTP berhasil digunakan',
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_otp_phone_status` (`phone`, `status`),
    INDEX `idx_otp_expires` (`expires_at`),
    INDEX `idx_otp_ip` (`ip_address`, `created_at`)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tambah kolom phone ke tabel users jika belum ada
-- (opsional: untuk menghubungkan user ke nomor WhatsApp)
ALTER TABLE `users`
    ADD COLUMN IF NOT EXISTS `phone` VARCHAR(20) NULL DEFAULT NULL
        COMMENT 'Nomor WhatsApp (format: 628xxx)'
        AFTER `email`,
    ADD UNIQUE KEY IF NOT EXISTS `idx_users_phone` (`phone`);
