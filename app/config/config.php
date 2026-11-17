<?php
// ===================================================
// Muat file .env (jika ada) ke dalam environment PHP
// Token dan secret TIDAK boleh di-hardcode di sini
// ===================================================
(function() {
    $envFile = dirname(__DIR__, 2) . '/.env';
    if (!file_exists($envFile)) return;
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (strpos($line, '=') !== false) {
            [$key, $val] = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val);
            if (!getenv($key)) putenv("$key=$val");
        }
    }
})();

define('BASEURL', 'http://localhost/nnaco_sa/public');

// DB Constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nnaco_sa_db');

// AI Constants
define('AI_PROVIDER', getenv('AI_PROVIDER') ?: 'groq'); // 'gemini', 'groq', or 'mock'
define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: '');
define('AI_AUTH_SECRET', 'nnaco_secure_ai_hmac_secret_2026_x87b');


