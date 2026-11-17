<?php
require_once 'app/config/config.php';

$url = 'https://integrate.api.nvidia.com/v1/chat/completions';
$apiKey = NVIDIA_API_KEY;

$data = [
    'model' => 'meta/llama-3.2-11b-vision-instruct',
    'messages' => [
        ['role' => 'user', 'content' => 'Test message']
    ],
    'temperature' => 0.5,
    'max_tokens' => 1024,
    'stream' => false
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
// Add SSL verify false just in case it's a local XAMPP cert issue
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "cURL Error: $error\n";
echo "Response: $response\n";
