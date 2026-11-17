<?php
require_once 'app/config/config.php';

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $users = [
        ['name' => 'Demo Admin', 'email' => 'admin@nnaco.com', 'role' => 'Admin'],
        ['name' => 'Demo Operator', 'email' => 'operator@nnaco.com', 'role' => 'Operator'],
        ['name' => 'Demo Owner', 'email' => 'owner@nnaco.com', 'role' => 'Owner'],
        ['name' => 'Demo Customer', 'email' => 'customer@nnaco.com', 'role' => 'Customer']
    ];
    
    $password = password_hash('password123', PASSWORD_DEFAULT);
    
    foreach($users as $user) {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$user['email']]);
        if (!$stmt->fetch()) {
            $stmt = $db->prepare("INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, 'Active')");
            $stmt->execute([$user['name'], $user['email'], $password, $user['role']]);
            echo "Akun {$user['role']} berhasil dibuat.\n";
        } else {
            // Update password just in case
            $stmt = $db->prepare("UPDATE users SET password = ?, status = 'Active', role = ? WHERE email = ?");
            $stmt->execute([$password, $user['role'], $user['email']]);
            echo "Akun {$user['role']} sudah ada dan berhasil di-reset password-nya.\n";
        }
    }
    
    echo "\nSelesai membuat akun demo.\n";
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
