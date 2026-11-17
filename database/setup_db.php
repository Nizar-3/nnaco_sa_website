<?php
require_once __DIR__ . '/../app/config/config.php';

echo "Memulai proses setup database...\n";

// 1. Koneksi ke MySQL server tanpa memilih database
$dsn = 'mysql:host=' . DB_HOST . ';charset=utf8mb4';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    echo "Koneksi ke server MySQL berhasil.\n";

    // 2. Baca file schema.sql
    $sqlFile = __DIR__ . '/schema.sql';
    if (!file_exists($sqlFile)) {
        die("File schema.sql tidak ditemukan di " . $sqlFile . "\n");
    }

    $sql = file_get_contents($sqlFile);

    // 3. Eksekusi query (Termasuk CREATE DATABASE dan USE)
    $pdo->exec($sql);
    echo "Skema database berhasil dieksekusi!\n";
    echo "Database '" . DB_NAME . "' dan seluruh tabel telah dibuat.\n";

} catch (PDOException $e) {
    die("Error eksekusi setup database: " . $e->getMessage() . "\n");
}
