<?php
/**
 * GeminiAIService.php
 * Layanan integrasi Google Gemini AI REST API dengan Full Database Access & Role-Based Permissions
 *
 * FITUR:
 * - Mengambil konfigurasi dinamis secara real-time dari SettingService
 * - Full Database Context & Schema Awareness via AIDatabaseService
 * - Pemeriksaan Otorisasi Hak Akses Role (Customer, Operator, Admin, Owner)
 * - Menjawab langsung apa yang ditanyakan ("jawab apa yang ditanya")
 * - Graceful fallback otomatis jika API Google Gemini mengalami limit atau kendala jaringan
 */

require_once __DIR__ . '/AIServiceInterface.php';
require_once __DIR__ . '/SettingService.php';
require_once __DIR__ . '/SecretService.php';
require_once __DIR__ . '/AIDatabaseService.php';
require_once __DIR__ . '/AIRolePermissionService.php';

class GeminiAIService implements AIServiceInterface {
    
    public function generateResponse($userMessage, $chatHistory, $productCatalog, array $userContext = []) {
        if (!SettingService::isAiEnabled()) {
            return [
                'reply' => 'Layanan asisten AI toko sedang dinonaktifkan sementara oleh administrator.',
                'recommended_product_ids' => []
            ];
        }

        // 1. Ekstrak Identitas Pengguna & Hak Akses
        $role = $userContext['role'] ?? ($_SESSION['user_role'] ?? 'Customer');
        $userId = $userContext['id'] ?? ($_SESSION['user_id'] ?? null);
        $userName = $userContext['name'] ?? ($_SESSION['user_name'] ?? 'Tamu');
        $userEmail = $userContext['email'] ?? ($_SESSION['user_email'] ?? '');
        $uCtx = ['id' => $userId, 'role' => $role, 'name' => $userName, 'email' => $userEmail];

        $aiDbService = new AIDatabaseService();

        // 2. Cek Fast-Path Database & Intent Khusus
        $dbIntent = $aiDbService->detectIntent($userMessage, $role, $userId);
        if ($dbIntent !== null) {
            if (isset($dbIntent['type']) && $dbIntent['type'] === 'direct_reply') {
                return ['reply' => $dbIntent['reply'], 'recommended_product_ids' => []];
            }
            if (!empty($dbIntent['toolName'])) {
                $toolResult = $aiDbService->executeTool($dbIntent['toolName'], $dbIntent['args'] ?? [], $uCtx);
                if (!$toolResult['success']) {
                    return [
                        'reply' => $toolResult['error'] ?? 'Maaf, Anda tidak memiliki izin untuk mengakses data tersebut.',
                        'recommended_product_ids' => []
                    ];
                }
                $directReply = $aiDbService->formatDirectDataResponse($dbIntent['toolName'], $toolResult, $userMessage);
                return ['reply' => $directReply, 'recommended_product_ids' => []];
            }
        }

        // 3. Ambil API Key Google Gemini
        $apiKey = SettingService::getSecret('ai.api_key');
        if (empty($apiKey) && defined('GEMINI_API_KEY')) {
            $apiKey = GEMINI_API_KEY;
        }

        if (empty($apiKey)) {
            // Fallback ke resolusi database lokal jika API key belum diisi
            return [
                'reply' => 'Konfigurasi Google Gemini API Key belum diisi. Silakan hubungi Administrator untuk memasukkan API Key di Pengaturan Sistem.',
                'recommended_product_ids' => []
            ];
        }

        if (str_starts_with($apiKey, 'gsk_')) {
            return [
                'reply' => "Konfigurasi AI saat ini belum cocok:\n\nProvider AI diatur ke **Google Gemini AI**, tetapi API Key yang tersimpan adalah kunci **Groq Cloud (gsk_...)**.\n\nSilakan buka menu **Pengaturan Sistem (Settings)** lalu:\n1. Masukkan API Key Google Gemini dari **Google AI Studio (aistudio.google.com)**, ATAU\n2. Kembalikan pilihan Provider ke **Groq Cloud AI**.",
                'recommended_product_ids' => []
            ];
        }

        $model = SettingService::get('ai.model', 'gemini-3.5-flash-lite');
        $model = str_replace('models/', '', $model);
        if ($model === 'gemini-1.5-flash' || empty($model)) {
            $model = 'gemini-3.5-flash-lite';
        }
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . urlencode($model) . ':generateContent?key=' . urlencode($apiKey);

        // 4. Siapkan Konteks Database Aktual & Batasan Role
        $dbContext = $aiDbService->getDatabaseContextForAI($role, $userId, $userMessage);

        $systemInstruction = "Kamu adalah nnaco AI Assistant, asisten cerdas untuk website e-commerce toko aksesoris perhiasan nnaco_sa. " .
            "PRINSIP UTAMA:\n" .
            "1. JAWAB APA YANG DITANYA: Berikan jawaban secara langsung, lugas, akurat, dan to-the-point dalam Bahasa Indonesia yang ramah dan profesional.\n" .
            "2. FULL DATABASE AWARENESS: Gunakan fakta data aktual dari KONTEKS DATABASE NNACO_SA di bawah ini. Jangan mengarang data atau berhalusinasi.\n" .
            "3. ROLE-BASED ACCESS CONTROL (RBAC): Pengguna yang sedang berbicara memiliki Role: '{$role}' (ID: #{$userId}, Nama: {$userName}). " .
            "- Jika Customer meminta data rahasia seperti omzet total toko, data pembeli lain, atau log sistem, TOLAK SECARA SOPAN sesuai hak aksesnya.\n" .
            "- Jika Operator meminta omzet keuntungan total toko, ingatkan dengan sopan bahwa data tersebut wewenang Admin/Owner.\n" .
            "- Jika Admin atau Owner bertanya, jawab lengkap dengan data dari basis data.\n\n" .
            "FORMAT OUTPUT:\n" .
            "Kembalikan respon dalam format JSON valid berikut:\n" .
            "{ \"reply\": \"Jawaban Anda di sini (gunakan markdown seperti bold dan tabel jika membantu)\", \"recommended_product_ids\": [1, 2] }\n" .
            "Jika tidak ada produk yang direkomendasikan, biarkan array recommended_product_ids kosong.\n\n" .
            $dbContext;

        // 5. Susun Riwayat Chat
        $contents = [];
        if (is_array($chatHistory)) {
            $maxHistory = (int) SettingService::get('ai.max_history_messages', 10);
            $recentHistory = array_slice($chatHistory, -$maxHistory);
            foreach ($recentHistory as $msg) {
                $mRole = (($msg['role'] ?? 'user') === 'assistant') ? 'model' : 'user';
                $content = trim($msg['content'] ?? '');
                $content = str_replace('[PROD_CARDS]', '', $content);
                if (!empty($content)) {
                    $contents[] = [
                        'role' => $mRole,
                        'parts' => [['text' => $content]]
                    ];
                }
            }
        }
        
        // Pesan user saat ini
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $userMessage]]
        ];

        $temperature = (float) SettingService::get('ai.temperature', 0.2);
        $data = [
            'systemInstruction' => [
                'parts' => [['text' => $systemInstruction]]
            ],
            'contents' => $contents,
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature'      => $temperature
            ]
        ];

        $timeout = (int) SettingService::get('ai.timeout_seconds', 25);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $responseData = json_decode($response, true);
            if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                $text = trim($responseData['candidates'][0]['content']['parts'][0]['text']);
                // Bersihkan kode markdown ```json ... ``` jika model menyertakannya
                if (str_starts_with($text, '```json')) {
                    $text = preg_replace('/^```json\s*/', '', $text);
                    $text = preg_replace('/\s*```$/', '', $text);
                } elseif (str_starts_with($text, '```')) {
                    $text = preg_replace('/^```\s*/', '', $text);
                    $text = preg_replace('/\s*```$/', '', $text);
                }
                
                $jsonResponse = json_decode($text, true);
                if (is_array($jsonResponse) && isset($jsonResponse['reply'])) {
                    return [
                        'reply' => $jsonResponse['reply'],
                        'recommended_product_ids' => $jsonResponse['recommended_product_ids'] ?? []
                    ];
                }
                // Jika teks bukan JSON murni tapi teks biasa
                if (!empty($text)) {
                    return [
                        'reply' => $text,
                        'recommended_product_ids' => []
                    ];
                }
            }
        }

        // Tangani pesan spesifik jika error dari Google Gemini API
        if ($response) {
            $errData = json_decode($response, true);
            $errReason = $errData['error']['details'][0]['reason'] ?? '';
            $errMsg = $errData['error']['message'] ?? '';

            if ($errReason === 'API_KEY_INVALID' || str_contains($errMsg, 'API key not valid')) {
                return [
                    'reply' => "Koneksi ke **Google Gemini AI** gagal: API Key yang digunakan tidak valid.\n\nSilakan periksa kembali API Key Google Gemini Anda di menu **Pengaturan Sistem (Settings)**.",
                    'recommended_product_ids' => []
                ];
            }
            if ($httpCode === 429 || $errReason === 'RESOURCE_EXHAUSTED') {
                return [
                    'reply' => 'Batas kuota (Rate Limit) Google Gemini tercapai sementara. Silakan coba kembali beberapa saat lagi.',
                    'recommended_product_ids' => []
                ];
            }
        }

        // Fallback cerdas: Jangan gagal atau macet, berikan respon dari query katalog lokal
        require_once __DIR__ . '/MockAIService.php';
        $mock = new MockAIService();
        return $mock->generateResponse($userMessage, $chatHistory, $productCatalog, $uCtx);
    }

    /**
     * Uji koneksi Google Gemini API secara aman
     */
    public function testConnection(array $override = []): array {
        $startTime = microtime(true);

        $apiKey = !empty($override['api_key']) && !SecretService::isMaskedPlaceholder($override['api_key'])
            ? trim($override['api_key'])
            : SettingService::getSecret('ai.api_key');

        if (empty($apiKey) && defined('GEMINI_API_KEY')) {
            $apiKey = GEMINI_API_KEY;
        }

        $model = !empty($override['model'])
            ? trim($override['model'])
            : SettingService::get('ai.model', 'gemini-3.5-flash-lite');
        $model = str_replace('models/', '', $model);
        if ($model === 'gemini-1.5-flash' || empty($model)) {
            $model = 'gemini-3.5-flash-lite';
        }

        if (empty($apiKey)) {
            return [
                'success'    => false,
                'provider'   => 'Google Gemini AI',
                'model'      => $model,
                'latency_ms' => 0,
                'tested_at'  => date('Y-m-d H:i:s'),
                'message'    => 'API Key Google Gemini belum diisi. Masukkan API Key dari Google AI Studio (aistudio.google.com).'
            ];
        }

        if (str_starts_with($apiKey, 'gsk_')) {
            return [
                'success'    => false,
                'provider'   => 'Google Gemini AI',
                'model'      => $model,
                'latency_ms' => 0,
                'tested_at'  => date('Y-m-d H:i:s'),
                'message'    => 'API Key yang diuji adalah kunci Groq Cloud (gsk_...). Provider Google Gemini memerlukan API Key resmi dari Google AI Studio (aistudio.google.com).'
            ];
        }

        $timeout = (int)($override['timeout'] ?? SettingService::get('ai.timeout_seconds', 30));
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . urlencode($model) . ':generateContent?key=' . urlencode($apiKey);

        $payload = [
            'contents' => [
                ['parts' => [['text' => 'Halo! Jawab hanya dengan: OK']]]
            ],
            'generationConfig' => [
                'maxOutputTokens' => 10,
                'temperature'     => 0.1
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        $latencyMs = round((microtime(true) - $startTime) * 1000);

        if ($curlErr) {
            return [
                'success'    => false,
                'provider'   => 'Google Gemini AI',
                'model'      => $model,
                'latency_ms' => $latencyMs,
                'tested_at'  => date('Y-m-d H:i:s'),
                'message'    => 'Gagal menghubungi server Gemini: ' . $curlErr
            ];
        }

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success'    => true,
                'provider'   => 'Google Gemini AI',
                'model'      => $model,
                'latency_ms' => $latencyMs,
                'tested_at'  => date('Y-m-d H:i:s'),
                'message'    => "Koneksi Google Gemini berhasil! Model '$model' merespon dalam {$latencyMs}ms."
            ];
        }

        $errMsg = "Server Google Gemini merespon status HTTP $httpCode.";
        $parsedBody = json_decode($response ?? '', true);
        $geminiErrMsg = $parsedBody['error']['message'] ?? '';
        $geminiReason = $parsedBody['error']['details'][0]['reason'] ?? '';

        if ($geminiReason === 'API_KEY_INVALID' || str_contains($geminiErrMsg, 'API key not valid')) {
            $errMsg = "Autentikasi gagal: API Key Google Gemini tidak valid. Pastikan Anda menyalin kunci resmi dari Google AI Studio (aistudio.google.com).";
        } elseif ($httpCode === 400) {
            $errMsg = "Permintaan tidak valid atau nama model '$model' tidak didukung Gemini. " . ($geminiErrMsg ? "(Detail: $geminiErrMsg)" : '');
        } elseif ($httpCode === 403 || $httpCode === 401) {
            $errMsg = "Autentikasi gagal: Google Gemini API Key tidak memiliki izin atau dinonaktifkan.";
        } elseif ($httpCode === 404) {
            $errMsg = "Model '$model' tidak ditemukan di akun Google Gemini API Anda.";
        } elseif ($httpCode === 429) {
            $errMsg = "Batas kuota (Rate Limit) Google Gemini tercapai. Silakan coba sesaat lagi.";
        }

        return [
            'success'    => false,
            'provider'   => 'Google Gemini AI',
            'model'      => $model,
            'latency_ms' => $latencyMs,
            'tested_at'  => date('Y-m-d H:i:s'),
            'message'    => $errMsg
        ];
    }

    /**
     * Ambil daftar model dari Google Gemini API
     */
    public function fetchAvailableModels(?string $apiKey = null): array {
        if (empty($apiKey) || SecretService::isMaskedPlaceholder($apiKey)) {
            $apiKey = SettingService::getSecret('ai.api_key');
        }
        if (empty($apiKey) && defined('GEMINI_API_KEY')) {
            $apiKey = GEMINI_API_KEY;
        }

        $fallbackModels = [
            'gemini-3.5-flash-lite',
            'gemini-3.8-flash',
            'gemini-3.5-flash',
            'gemini-flash-latest',
            'gemini-2.5-pro'
        ];

        if (empty($apiKey)) {
            return [
                'success' => false,
                'models'  => $fallbackModels,
                'message' => 'API Key belum ada. Menggunakan daftar preset Google Gemini.'
            ];
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/models?key=' . urlencode($apiKey);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if (!empty($data['models']) && is_array($data['models'])) {
                $models = [];
                foreach ($data['models'] as $m) {
                    $name = $m['name'] ?? '';
                    $name = str_replace('models/', '', $name);
                    $methods = $m['supportedGenerationMethods'] ?? [];
                    if (in_array('generateContent', $methods, true) && !empty($name)) {
                        $models[] = $name;
                    }
                }
                if (!empty($models)) {
                    sort($models);
                    return [
                        'success' => true,
                        'models'  => $models,
                        'message' => 'Daftar model resmi Google Gemini berhasil dimuat.'
                    ];
                }
            }
        }

        return [
            'success' => false,
            'models'  => $fallbackModels,
            'message' => 'Menggunakan daftar preset model Google Gemini bawaan.'
        ];
    }
}
