<?php
require_once 'app/config/config.php';

$url = 'https://integrate.api.nvidia.com/v1/chat/completions';
$apiKey = NVIDIA_API_KEY;

$businessData = [
    'test_data' => 'This is a test of business data'
];
$question = 'What is the stock summary?';

$systemInstruction = "Kamu adalah Penasihat Bisnis (Business Advisor AI) untuk Pemilik Toko NNACO SA. " .
    "Tugasmu adalah menganalisis data bisnis yang diberikan dan menjawab pertanyaan dari pemilik toko secara profesional, " .
    "akurat, dan memberikan insight atau saran yang berguna.\n\n" .
    "Gunakan format Markdown agar mudah dibaca.\n\n" .
    "Berikut adalah data bisnis terkini (JSON):\n" . json_encode($businessData);

$messages = [];
$messages[] = [
    'role' => 'system',
    'content' => $systemInstruction
];
$messages[] = [
    'role' => 'user',
    'content' => $question
];

$data = [
    'model' => 'meta/llama-3.2-11b-vision-instruct',
    'messages' => $messages,
    'temperature' => 0.4,
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

// Important for XAMPP to avoid SSL cert issues
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
