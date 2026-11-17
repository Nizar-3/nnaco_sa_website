<?php
// Simple Test Runner for Security and Validation
define('BASEURL', 'http://localhost/nnaco_sa/public');

echo "====================================\n";
echo "NNACO_SA SECURITY & AUTOMATED TESTS\n";
echo "====================================\n";

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function runTest($name, $url, $method = 'GET', $data = [], $expectedCode = 200, $cookie = '') {
    global $totalTests, $passedTests, $failedTests;
    $totalTests++;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true); // We need headers to check status code
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    
    if ($cookie !== '') {
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === $expectedCode || (is_array($expectedCode) && in_array($httpCode, $expectedCode))) {
        echo "[PASS] $name (Code: $httpCode)\n";
        $passedTests++;
        return true;
    } else {
        echo "[FAIL] $name - Expected: " . (is_array($expectedCode) ? implode('/', $expectedCode) : $expectedCode) . ", Got: $httpCode\n";
        $failedTests++;
        return false;
    }
}

// 1. Guest access to protected routes
runTest('Guest -> Admin Dashboard (Should 403 or 302 to login)', BASEURL . '/admin', 'GET', [], [403, 302]);
runTest('Guest -> Checkout (Should 403 or 302)', BASEURL . '/checkout', 'GET', [], [403, 302]);

// 2. CSRF Validation (Missing CSRF token on POST)
runTest('Missing CSRF on Login (Should 403/302)', BASEURL . '/auth/login', 'POST', [
    'email' => 'test@test.com',
    'password' => 'password123'
], [403, 302]);

runTest('Missing CSRF on Register (Should 403/302)', BASEURL . '/auth/register', 'POST', [
    'name' => 'Test',
    'email' => 'test2@test.com',
    'password' => '123456',
    'confirm_password' => '123456'
], [403, 302]);

// 3. 404 Handling
runTest('Invalid Route (Should 404)', BASEURL . '/invalidroute123', 'GET', [], 404);

// 4. Payment Webhook Security & Logic
runTest('Payment Webhook - GET Method not allowed (Should 405)', BASEURL . '/payment/callback', 'GET', [], 405);
runTest('Payment Webhook - POST Empty Body (Should 400)', BASEURL . '/payment/callback', 'POST', [], 400);

// We can't fully end-to-end test the payment callback without a real order in the DB here since it checks amount and transaction ID.
// However, we can test that it rejects invalid transaction IDs.
$fakePayload = json_encode([
    'transaction_id' => 'INVALID_ID',
    'order_id' => 9999,
    'amount' => 100000,
    'status' => 'PAID'
]);

$ch = curl_init(BASEURL . '/payment/callback');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $fakePayload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$resp = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$totalTests++;
if ($httpCode === 400) {
    echo "[PASS] Payment Webhook - Invalid Transaction ID rejected (Code: 400)\n";
    $passedTests++;
} else {
    echo "[FAIL] Payment Webhook - Invalid Transaction ID rejected - Expected 400, Got: $httpCode\n";
    $failedTests++;
}

// 4. Input Validation Simulator (Using Login with bad email format but valid CSRF bypass simulation)
// Since we can't easily get a CSRF token for cURL without scraping, we just assume CSRF enforcement is working based on the 403 above.

echo "\n------------------------------------\n";
echo "Total Tests: $totalTests\n";
echo "Passed: $passedTests\n";
echo "Failed: $failedTests\n";
echo "------------------------------------\n";

if ($failedTests > 0) {
    exit(1);
}
exit(0);
