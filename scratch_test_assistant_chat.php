<?php
session_start();
require_once __DIR__ . '/app/config/config.php';
require_once __DIR__ . '/app/core/CSRF.php';

$token = CSRF::generateToken();
echo "Generated CSRF Token in session: " . $token . "\n";

// Test 1: POST to /assistant/chat with X-CSRF-TOKEN header and csrf_token in JSON body
$cookie = session_name() . '=' . session_id();

$ch = curl_init('http://localhost/nnaco_sa/public/assistant/chat');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-CSRF-TOKEN: ' . $token,
        'Cookie: ' . $cookie
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'message' => 'Halo, apakah kalung mutiara ada?',
        'csrf_token' => $token
    ]),
    CURLOPT_TIMEOUT => 15
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
