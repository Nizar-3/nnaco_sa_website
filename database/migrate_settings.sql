-- Migration: Tabel system_settings dan system_audit_logs
-- Untuk konfigurasi AI Model, OTP WhatsApp Fonnte, dan Audit Log

USE `nnaco_sa_db`;

-- 1. Tabel system_settings
CREATE TABLE IF NOT EXISTS `system_settings` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key`   VARCHAR(100) NOT NULL UNIQUE COMMENT 'Key unik konfigurasi, contoh: ai.provider, otp.enabled',
    `setting_value` TEXT NULL COMMENT 'Nilai konfigurasi (dienkripsi jika is_secret = 1)',
    `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general' COMMENT 'Grup: ai, otp, security, general',
    `is_secret`     TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 jika nilai berisi token/key rahasia',
    `description`   VARCHAR(255) NULL COMMENT 'Deskripsi fungsi pengaturan',
    `updated_by`    INT NULL COMMENT 'ID user yang terakhir mengubah',
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_settings_group` (`setting_group`)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Tabel system_audit_logs
CREATE TABLE IF NOT EXISTS `system_audit_logs` (
    `id`            INT AUTO_INCREMENT PRIMARY KEY,
    `user_id`       INT NULL COMMENT 'ID pengguna yang melakukan aksi',
    `user_name`     VARCHAR(100) NULL COMMENT 'Nama pengguna saat aksi dilakukan',
    `user_role`     VARCHAR(50) NULL COMMENT 'Role pengguna (Admin, Owner)',
    `action`        VARCHAR(50) NOT NULL COMMENT 'Jenis aksi, misal: UPDATE_AI_CONFIG, TEST_FONNTE',
    `category`      VARCHAR(50) NOT NULL COMMENT 'Kategori: ai, otp, security',
    `field_name`    VARCHAR(100) NULL COMMENT 'Nama field yang diubah',
    `old_value`     TEXT NULL COMMENT 'Nilai lama (TIDAK BOLEH menyimpan secret)',
    `new_value`     TEXT NULL COMMENT 'Nilai baru (TIDAK BOLEH menyimpan secret)',
    `status`        ENUM('SUCCESS', 'FAILED') NOT NULL DEFAULT 'SUCCESS',
    `details`       TEXT NULL COMMENT 'Keterangan ringkas atau pesan error aman',
    `ip_address`    VARCHAR(45) NULL COMMENT 'IP address pemohon',
    `user_agent`    VARCHAR(255) NULL COMMENT 'User Agent browser',
    `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_category` (`category`),
    INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
