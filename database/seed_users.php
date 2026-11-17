<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$db = new Database();

$users = [
    [
        'name' => 'John Operator',
        'email' => 'operator@nnaco.com',
        'password' => password_hash('operator123', PASSWORD_DEFAULT),
        'role' => 'Operator',
        'status' => 'Active'
    ],
    [
        'name' => 'Jane Admin',
        'email' => 'admin@nnaco.com',
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'role' => 'Admin',
        'status' => 'Active'
    ],
    [
        'name' => 'Boss Owner',
        'email' => 'owner@nnaco.com',
        'password' => password_hash('owner123', PASSWORD_DEFAULT),
        'role' => 'Owner',
        'status' => 'Active'
    ],
    [
        'name' => 'Demo Customer',
        'email' => 'customer@nnaco.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'role' => 'Customer',
        'status' => 'Active'
    ]
];

foreach ($users as $user) {
    // Check if exists
    $db->query("SELECT id FROM users WHERE email = :email");
    $db->bind('email', $user['email']);
    $db->execute();
    
    if ($db->rowCount() == 0) {
        $db->query("INSERT INTO users (name, email, password, role, status) VALUES (:name, :email, :password, :role, :status)");
        $db->bind('name', $user['name']);
        $db->bind('email', $user['email']);
        $db->bind('password', $user['password']);
        $db->bind('role', $user['role']);
        $db->bind('status', $user['status']);
        $db->execute();
        echo "User {$user['role']} created.\n";
    } else {
        $db->query("UPDATE users SET password = :password, status = :status WHERE email = :email");
        $db->bind('password', $user['password']);
        $db->bind('status', $user['status']);
        $db->bind('email', $user['email']);
        $db->execute();
        echo "User {$user['role']} ({$user['email']}) password updated.\n";
    }
}
echo "Seeding selesai.\n";
