<?php
// Simulate NvidiaAIService execution
require_once 'app/config/config.php';
require_once 'app/services/AIServiceInterface.php';
require_once 'app/services/NvidiaAIService.php';

$service = new NvidiaAIService();

$userMessage = "bantu saya rekap stok produk";
$chatHistory = [];
$productCatalog = [
    ['id' => 1, 'name' => 'Kalung Emas', 'price' => 5000000, 'stock' => 10],
    ['id' => 2, 'name' => 'Gelang Perak', 'price' => 1000000, 'stock' => 5]
];

$response = $service->generateResponse($userMessage, $chatHistory, $productCatalog);
echo "Final Response:\n";
print_r($response);
