<?php
/**
 * GroqAIService.php
 * Layanan integrasi langsung ke Groq Cloud API dengan Full Database Access & Role-Based Permissions
 *
 * FITUR:
 * - Mengambil konfigurasi dinamis secara real-time dari SettingService
 * - Full Database Context & Schema Awareness via AIDatabaseService
 * - Pemeriksaan Otorisasi Hak Akses Role (Customer, Operator, Admin, Owner)
 * - Menjawab langsung apa yang ditanyakan ("jawab apa yang ditanya")
 * - Pengujian koneksi aman dan pengambilan model aktual
 */

require_once __DIR__ . '/AIServiceInterface.php';
require_once __DIR__ . '/SettingService.php';
require_once __DIR__ . '/AIDatabaseService.php';
require_once __DIR__ . '/AIRolePermissionService.php';

class GroqAIService implements AIServiceInterface {

    /**
     * Hasilkan respon chatbot untuk pesan user dengan otorisasi role & full database context
     */
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

        $apiKey = SettingService::getSecret('ai.api_key');
        if (empty($apiKey)) {
            return [
                'reply' => 'Konfigurasi API AI belum lengkap. Silakan hubungi administrator toko.',
                'recommended_product_ids' => []
            ];
        }

        // Validasi panjang pesan
        $maxLen = (int) SettingService::get('ai.max_user_message_length', 2000);
        if (mb_strlen($userMessage) > $maxLen) {
            return [
                'reply' => "Pesan Anda terlalu panjang. Maksimal $maxLen karakter.",
                'recommended_product_ids' => []
            ];
        }

        // Guardrail Relevansi Backend
        $rejection = $this->checkBackendRelevance($userMessage);
        if ($rejection !== null) {
            return [
                'reply' => $rejection,
                'recommended_product_ids' => []
            ];
        }

        $model = SettingService::get('ai.model', 'llama-3.3-70b-versatile');
        $rawBase = SettingService::get('ai.api_base_url', 'https://api.groq.com/openai/v1');
        if (empty($rawBase) || str_contains($rawBase, 'googleapis.com')) {
            $rawBase = 'https://api.groq.com/openai/v1';
        }
        $baseUrl = rtrim($rawBase, '/');
        $temperature = (float) SettingService::get('ai.temperature', 0.2);
        $maxTokens = (int) SettingService::get('ai.max_output_tokens', 1024);
        $timeout = (int) SettingService::get('ai.timeout_seconds', 25);
        $assistantName = SettingService::get('ai.assistant_name', 'nnaco AI Assistant');
        $customSystemPrompt = SettingService::get('ai.system_prompt', '');

        // Susun prompt sistem yang menyertakan database context & batasan role
        $fullSystemPrompt = $this->buildFullSystemPrompt($assistantName, $customSystemPrompt, $productCatalog, $uCtx, $aiDbService, $userMessage);

        // Susun riwayat percakapan (batasi sesuai setting)
        $maxHistory = (int) SettingService::get('ai.max_history_messages', 10);
        $messages = [
            ['role' => 'system', 'content' => $fullSystemPrompt]
        ];

        if (is_array($chatHistory)) {
            $recentHistory = array_slice($chatHistory, -$maxHistory);
            foreach ($recentHistory as $msg) {
                $mRole = ($msg['role'] ?? 'user') === 'assistant' ? 'assistant' : 'user';
                $content = trim($msg['content'] ?? '');
                $content = str_replace('[PROD_CARDS]', '', $content);
                if (!empty($content)) {
                    $messages[] = ['role' => $mRole, 'content' => $content];
                }
            }
        }

        // Tambah pesan user saat ini
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        $payload = [
            'model'       => $model,
            'messages'    => $messages,
            'temperature' => $temperature,
            'max_tokens'  => $maxTokens,
            'stream'      => false
        ];

        $apiUrl = $baseUrl . '/chat/completions';
        $res = $this->sendHttpRequest($apiUrl, $apiKey, $payload, $timeout);

        if (!$res['success']) {
            error_log('[GroqAIService] API error: ' . ($res['error'] ?? 'Unknown error'));
            // Fallback ke resolusi database lokal
            require_once __DIR__ . '/MockAIService.php';
            $mock = new MockAIService();
            return $mock->generateResponse($userMessage, $chatHistory, $productCatalog, $uCtx);
        }

        $replyText = $res['data']['choices'][0]['message']['content'] ?? '';
        if (empty($replyText)) {
            require_once __DIR__ . '/MockAIService.php';
            $mock = new MockAIService();
            return $mock->generateResponse($userMessage, $chatHistory, $productCatalog, $uCtx);
        }

        // Deteksi rekomendasi produk jika ada dalam format teks
        $recIds = $this->extractProductIds($replyText, $productCatalog);

        return [
            'reply' => $replyText,
            'recommended_product_ids' => $recIds
        ];
    }

    /**
     * Uji Koneksi ke Groq API secara aman dan minimal
     */
    public function testConnection(array $override = []): array {
        $startTime = microtime(true);

        $apiKey = !empty($override['api_key']) && !SecretService::isMaskedPlaceholder($override['api_key'])
            ? trim($override['api_key'])
            : SettingService::getSecret('ai.api_key');

        $model = !empty($override['model'])
            ? trim($override['model'])
            : SettingService::get('ai.model', 'llama-3.3-70b-versatile');

        if (empty($apiKey)) {
            return [
                'success'    => false,
                'provider'   => 'Groq Cloud AI',
                'model'      => $model,
                'latency_ms' => 0,
                'tested_at'  => date('Y-m-d H:i:s'),
                'message'    => 'API Key belum diisi. Masukkan API Key Groq Anda.'
            ];
        }

        $rawBase = SettingService::get('ai.api_base_url', 'https://api.groq.com/openai/v1');
        if (empty($rawBase) || str_contains($rawBase, 'googleapis.com')) {
            $rawBase = 'https://api.groq.com/openai/v1';
        }
        $baseUrl = rtrim($rawBase, '/');
        $timeout = (int)($override['timeout'] ?? SettingService::get('ai.timeout_seconds', 30));

        $testPayload = [
            'model'       => $model,
            'messages'    => [
                ['role' => 'system', 'content' => 'Respond with single word: OK'],
                ['role' => 'user', 'content' => 'ping']
            ],
            'max_tokens'  => 5,
            'temperature' => 0.1
        ];

        $apiUrl = $baseUrl . '/chat/completions';
        $res = $this->sendHttpRequest($apiUrl, $apiKey, $testPayload, $timeout);
        $latencyMs = round((microtime(true) - $startTime) * 1000);

        if ($res['success']) {
            return [
                'success'    => true,
                'provider'   => 'Groq Cloud AI',
                'model'      => $model,
                'latency_ms' => $latencyMs,
                'tested_at'  => date('Y-m-d H:i:s'),
                'message'    => "Koneksi Groq berhasil! Model '$model' merespon dalam {$latencyMs}ms."
            ];
        }

        $httpCode = $res['http_code'] ?? 0;
        $errMsg = 'Koneksi gagal: ' . ($res['error'] ?? 'Terjadi kesalahan tidak diketahui.');

        if ($httpCode === 401) {
            $errMsg = 'Autentikasi gagal: Groq API Key tidak valid. Silakan periksa kembali key Anda.';
        } elseif ($httpCode === 404) {
            $errMsg = "Model '$model' tidak ditemukan atau tidak tersedia di Groq Cloud.";
        } elseif ($httpCode === 429) {
            $errMsg = 'Batas kuota (Rate Limit) Groq tercapai. Silakan coba sesaat lagi.';
        }

        return [
            'success'    => false,
            'provider'   => 'Groq Cloud AI',
            'model'      => $model,
            'latency_ms' => $latencyMs,
            'tested_at'  => date('Y-m-d H:i:s'),
            'message'    => $errMsg
        ];
    }

    /**
     * Ambil daftar model aktual dari Groq API
     */
    public function fetchAvailableModels(?string $apiKey = null): array {
        if (empty($apiKey) || SecretService::isMaskedPlaceholder($apiKey)) {
            $apiKey = SettingService::getSecret('ai.api_key');
        }

        $fallbackModels = [
            'llama-3.3-70b-versatile',
            'llama-3.1-8b-instant',
            'qwen/qwen3.8-27b',
            'mixtral-8x7b-32768',
            'gemma2-9b-it'
        ];

        if (empty($apiKey)) {
            return [
                'success' => false,
                'models'  => $fallbackModels,
                'message' => 'API Key belum ada. Menggunakan daftar model cadangan.'
            ];
        }

        $rawBase = SettingService::get('ai.api_base_url', 'https://api.groq.com/openai/v1');
        if (empty($rawBase) || str_contains($rawBase, 'googleapis.com')) {
            $rawBase = 'https://api.groq.com/openai/v1';
        }
        $baseUrl = rtrim($rawBase, '/');
        $apiUrl = $baseUrl . '/models';

        $res = $this->sendHttpRequest($apiUrl, $apiKey, null, 10, 'GET');

        if ($res['success'] && isset($res['data']['data']) && is_array($res['data']['data'])) {
            $models = [];
            foreach ($res['data']['data'] as $item) {
                $id = $item['id'] ?? '';
                if (!empty($id) && !str_contains($id, 'whisper') && !str_contains($id, 'prompt-guard')) {
                    $models[] = $id;
                }
            }
            sort($models);
            if (!empty($models)) {
                return [
                    'success' => true,
                    'models'  => $models,
                    'message' => 'Daftar model berhasil diperbarui dari Groq Cloud.'
                ];
            }
        }

        return [
            'success' => false,
            'models'  => $fallbackModels,
            'message' => 'Gagal mengambil model dari provider. Menggunakan daftar cadangan.'
        ];
    }

    /**
     * Guardrail relevansi di sisi backend
     */
    private function checkBackendRelevance(string $message): ?string {
        $rejectionMsg = SettingService::get(
            'ai.rejection_message',
            'Maaf, saya adalah asisten toko aksesoris nnaco_sa. Saya hanya dapat membantu pertanyaan seputar produk, katalog, stok, pesanan, dan kebijakan toko kami.'
        );

        $lower = strtolower($message);

        $codeKeywords = [
            'buatkan kode', 'tulis kode', 'buat script', 'write code', 'write script',
            'def ', 'function()', 'console.log', 'import ', '<?php', 'select * from',
            'bikin program', 'source code', 'codingkan', 'coding'
        ];
        foreach ($codeKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                return $rejectionMsg;
            }
        }

        return null;
    }

    /**
     * Susun System Prompt lengkap dengan database context & batasan role
     */
    private function buildFullSystemPrompt(string $name, string $custom, array $catalog, array $userContext = [], ?AIDatabaseService $aiDb = null, string $userMessage = ''): string {
        $role = $userContext['role'] ?? 'Customer';
        $userId = $userContext['id'] ?? null;
        $userName = $userContext['name'] ?? 'Tamu';

        $prompt = "Kamu adalah {$name}, asisten resmi toko aksesoris nnaco_sa.\n";
        $prompt .= "PRINSIP UTAMA:\n";
        $prompt .= "1. JAWAB APA YANG DITANYA: Jawab langsung, akurat, dan to-the-point tanpa khotbah atau sapaan berulang.\n";
        $prompt .= "2. FULL DATABASE AWARENESS: Gunakan fakta dari data aktual database yang disertakan di bawah. Jangan mengarang data.\n";
        $prompt .= "3. ROLE-BASED ACCESS CONTROL (RBAC): Pengguna saat ini adalah {$userName} dengan Role: '{$role}' (ID: #{$userId}).\n";
        $prompt .= "- Customer: Hanya boleh mengakses katalog produk dan pesanan miliknya sendiri. Tolak dengan sopan jika bertanya omzet toko atau data customer lain.\n";
        $prompt .= "- Operator: Boleh mengakses pesanan operasional, antrean pengiriman, dan stok produk. Tolak jika bertanya omzet finansial total toko.\n";
        $prompt .= "- Admin & Owner: Memiliki akses penuh ke seluruh data database toko.\n\n";

        if (!empty($custom)) {
            $prompt .= "INSTRUKSI KHUSUS:\n{$custom}\n\n";
        }

        if ($aiDb) {
            $prompt .= $aiDb->getDatabaseContextForAI($role, $userId, $userMessage) . "\n\n";
        }

        return $prompt;
    }

    /**
     * Ekstraksi ID produk yang direkomendasikan
     */
    private function extractProductIds(string $text, array $catalog): array {
        $found = [];
        foreach ($catalog as $p) {
            if (stripos($text, $p['name']) !== false) {
                $found[] = (int)$p['id'];
                if (count($found) >= 3) break;
            }
        }
        return array_values(array_unique($found));
    }

    /**
     * Kirim HTTP Request menggunakan cURL
     */
    private function sendHttpRequest(string $url, string $apiKey, ?array $payload, int $timeout, string $method = 'POST'): array {
        if (!function_exists('curl_init')) {
            return ['success' => false, 'error' => 'cURL extension tidak aktif di PHP.'];
        }

        $ch = curl_init($url);
        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($payload !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            }
        } else {
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return [
                'success'   => false,
                'http_code' => 0,
                'error'     => 'cURL Error: ' . $curlErr
            ];
        }

        $data = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success'      => true,
                'http_code'    => $httpCode,
                'data'         => $data,
                'raw_response' => $response
            ];
        }

        return [
            'success'      => false,
            'http_code'    => $httpCode,
            'error'        => 'HTTP ' . $httpCode . ' dari server provider',
            'data'         => $data,
            'raw_response' => $response
        ];
    }
}
