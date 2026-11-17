<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

$db = new Database();
$db->query("SELECT id, phone, status, expires_at, NOW() as server_now, (expires_at > NOW()) as is_valid FROM otp_requests ORDER BY id DESC LIMIT 5");
$rows = $db->resultSet();
echo "=== OTP Records (last 5) ===\n";
print_r($rows);

// Test timezone
$db->query("SELECT @@global.time_zone as global_tz, @@session.time_zone as session_tz, NOW() as mysql_now");
$tz = $db->single();
echo "\n=== MySQL Timezone ===\n";
print_r($tz);

echo "\n=== PHP time() ===\n";
echo "PHP time()    = " . time() . "\n";
echo "PHP date now  = " . date('Y-m-d H:i:s') . "\n";
echo "OTP expires   = " . date('Y-m-d H:i:s', time() + 300) . "\n";
