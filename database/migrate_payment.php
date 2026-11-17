<?php
$pdo = new PDO('mysql:host=localhost;dbname=nnaco_sa_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    echo "Memulai migrasi...\n";
    
    // 1. Alter orders.status ENUM
    // First, update empty strings to 'Menunggu Pembayaran' wait, we need to alter table first to allow it
    $pdo->exec("ALTER TABLE orders MODIFY COLUMN status ENUM('MENUNGGU PEMBAYARAN', 'DIPROSES', 'DIKEMAS', 'DIKIRIM', 'SELESAI', 'DIBATALKAN') NOT NULL DEFAULT 'MENUNGGU PEMBAYARAN'");
    echo "Tabel orders berhasil diubah.\n";

    // Update empty statuses (which were invalid enums) to MENUNGGU PEMBAYARAN
    $pdo->exec("UPDATE orders SET status = 'MENUNGGU PEMBAYARAN' WHERE status = ''");

    // 2. Drop and recreate payments table entirely to match new requirements
    // (Payments table wasn't actively used yet according to requirements)
    $pdo->exec("DROP TABLE IF EXISTS payments");
    $sql = "
    CREATE TABLE payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        gateway VARCHAR(50) NULL,
        transaction_id VARCHAR(100) NULL,
        payment_url VARCHAR(255) NULL,
        amount DECIMAL(12, 2) NOT NULL,
        status ENUM('PENDING', 'PAID', 'FAILED', 'EXPIRED', 'CANCELLED') NOT NULL DEFAULT 'PENDING',
        paid_at TIMESTAMP NULL,
        expired_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;
    ";
    $pdo->exec($sql);
    echo "Tabel payments berhasil dibuat ulang dengan skema baru.\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
