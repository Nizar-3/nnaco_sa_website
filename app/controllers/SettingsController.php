<?php
/**
 * SettingsController
 * Controller untuk mengelola menu Pengaturan Sistem (Konfigurasi AI & OTP Authentication)
 *
 * HAK AKSES:
 * - Hanya role Admin dan Owner yang diizinkan mengakses controller ini
 * - Customer dan Operator secara tegas ditolak (403 Forbidden)
 * - Seluruh mutasi dilindungi token CSRF dan dicatat ke Audit Log
 */
class SettingsController extends Controller {

    public function __construct() {
        // Otorisasi role ketat di backend
        Middleware::adminOrOwner();
    }

    /**
     * Halaman Utama Pengaturan Sistem
     */
    public function index() {
        $data['title'] = 'Pengaturan Sistem - NNACO SA Corporate';
        $data['is_dashboard'] = true;

        // Ambil data konfigurasi aman (secret tersamarkan)
        $data['settings'] = SettingService::getAllForUI();

        // Ambil riwayat audit log terbaru
        $auditModel = $this->model('AuditLogModel');
        $data['audit_logs'] = $auditModel->getRecentLogs(50);
        $data['total_logs'] = $auditModel->getTotalLogs();

        // Ambil riwayat log notifikasi WA pesanan
        $orderWaLogModel = $this->model('WhatsAppLogModel');
        $data['order_wa_logs'] = $orderWaLogModel->getRecentLogs(30);
        $data['total_order_wa_logs'] = $orderWaLogModel->getTotalLogs();

        // Active tab selector
        $data['active_tab'] = htmlspecialchars($_GET['tab'] ?? 'ai');

        $this->view('templates/header', $data);
        $this->view('settings/index', $data);
        $this->view('templates/footer', $data);
    }

    /**
     * API: Simpan Konfigurasi AI
     * POST /settings/saveAi
     */
    public function saveAi() {
        $this->requirePost();
        Middleware::verifyCsrf();

        $input = $this->getInputData();
        $userId = $_SESSION['user_id'] ?? null;

        $result = SettingService::saveAiSettings($input, $userId);

        $this->jsonResponse(
            $result['success'] ? 200 : 422,
            $result['success'],
            $result['message'],
            $result
        );
    }

    /**
     * API: Uji Koneksi AI
     * POST /settings/testAi
     */
    public function testAi() {
        $this->requirePost();
        Middleware::verifyCsrf();

        $input = $this->getInputData();
        $provider = strtolower(trim($input['ai_provider'] ?? SettingService::get('ai.provider', 'groq')));

        $override = [
            'api_key'      => $input['ai_api_key'] ?? null,
            'model'        => $input['ai_model'] ?? null,
            'api_base_url' => $input['ai_api_base_url'] ?? null,
            'timeout'      => (int)($input['ai_timeout_seconds'] ?? 15),
        ];

        if ($provider === 'mock') {
            $model = !empty($override['model']) ? $override['model'] : 'mock-ecommerce-fast';
            $testResult = [
                'success'    => true,
                'provider'   => 'Mock AI (Simulasi Offline)',
                'model'      => $model,
                'latency_ms' => 4,
                'tested_at'  => date('Y-m-d H:i:s'),
                'message'    => "Mode Simulasi Mock AI siap digunakan. Beroperasi offline membaca basis data toko lokal tanpa kuota."
            ];
        } elseif ($provider === 'gemini') {
            $geminiService = new GeminiAIService();
            $testResult = $geminiService->testConnection($override);
        } else {
            $groqService = new GroqAIService();
            $testResult = $groqService->testConnection($override);
        }

        // Catat pengujian ke audit log (tanpa membocorkan key)
        $auditModel = $this->model('AuditLogModel');
        $auditModel->log(
            'TEST_AI_CONNECTION',
            'ai',
            'connection_test',
            null,
            null,
            $testResult['success'] ? 'SUCCESS' : 'FAILED',
            "Pengujian koneksi " . ($testResult['provider'] ?? ucfirst($provider)) . " (Model: {$testResult['model']}, Latency: {$testResult['latency_ms']}ms, Status: " . ($testResult['success'] ? 'Berhasil' : 'Gagal') . ")"
        );

        $this->jsonResponse(
            $testResult['success'] ? 200 : 400,
            $testResult['success'],
            $testResult['message'],
            $testResult
        );
    }

    /**
     * API: Ambil Daftar Model Aktual dari Provider Terpilih (Groq, Gemini, Mock)
     * POST /settings/getAiModels
     */
    public function getAiModels() {
        $input = $this->getInputData();
        $provider = strtolower(trim($input['ai_provider'] ?? SettingService::get('ai.provider', 'groq')));
        $apiKey = $input['ai_api_key'] ?? null;

        if ($provider === 'mock') {
            $res = [
                'success' => true,
                'models'  => [
                    'mock-ecommerce-fast',
                    'mock-analytics-smart'
                ],
                'message' => 'Preset model Mock AI dimuat.'
            ];
        } elseif ($provider === 'gemini') {
            $geminiService = new GeminiAIService();
            $res = $geminiService->fetchAvailableModels($apiKey);
        } else {
            $groqService = new GroqAIService();
            $res = $groqService->fetchAvailableModels($apiKey);
        }

        $this->jsonResponse(
            200,
            $res['success'],
            $res['message'],
            [
                'provider' => $provider,
                'models'   => $res['models'] ?? []
            ]
        );
    }

    /**
     * API: Simpan Konfigurasi OTP WhatsApp
     * POST /settings/saveOtp
     */
    public function saveOtp() {
        $this->requirePost();
        Middleware::verifyCsrf();

        $input = $this->getInputData();
        $userId = $_SESSION['user_id'] ?? null;

        $result = SettingService::saveOtpSettings($input, $userId);

        $this->jsonResponse(
            $result['success'] ? 200 : 422,
            $result['success'],
            $result['message'],
            $result
        );
    }

    /**
     * API: Uji Koneksi Fonnte (Periksa Akun & Device)
     * POST /settings/testOtp
     */
    public function testOtp() {
        $this->requirePost();
        Middleware::verifyCsrf();

        $input = $this->getInputData();
        $overrideToken = $input['otp_api_token'] ?? null;
        $overrideUrl = $input['otp_device_api_url'] ?? null;

        $fonnteService = new FonnteService();
        $res = $fonnteService->testConnection($overrideToken, $overrideUrl);

        // Catat ke audit log
        $auditModel = $this->model('AuditLogModel');
        $auditModel->log(
            'TEST_FONNTE_CONNECTION',
            'otp',
            'device_check',
            null,
            null,
            $res['success'] ? 'SUCCESS' : 'FAILED',
            "Uji koneksi Fonnte: " . ($res['success'] ? "Perangkat '{$res['device']}' status '{$res['device_status']}'" : 'Gagal menghubungi Fonnte')
        );

        $this->jsonResponse(
            $res['success'] ? 200 : 400,
            $res['success'],
            $res['message'],
            $res
        );
    }

    /**
     * API: Kirim Pesan Uji WhatsApp
     * POST /settings/sendTestOtpMessage
     */
    public function sendTestOtpMessage() {
        $this->requirePost();
        Middleware::verifyCsrf();

        $input = $this->getInputData();
        $targetPhone = trim($input['target_phone'] ?? '');
        $overrideToken = $input['otp_api_token'] ?? null;
        $isConfirmed = !empty($input['confirmed']);

        if (!$isConfirmed) {
            $this->jsonResponse(400, false, 'Konfirmasi eksplisit diperlukan sebelum mengirim pesan uji WhatsApp.');
        }

        if (empty($targetPhone)) {
            $this->jsonResponse(400, false, 'Nomor WhatsApp tujuan wajib diisi.');
        }

        $fonnteService = new FonnteService();
        $res = $fonnteService->sendTestMessage($targetPhone, $overrideToken);

        // Catat ke audit log tanpa mencatat isi pesan penuh
        $maskedPhone = substr($targetPhone, 0, 4) . '****' . substr($targetPhone, -3);
        $auditModel = $this->model('AuditLogModel');
        $auditModel->log(
            'SEND_TEST_WHATSAPP',
            'otp',
            'test_message',
            null,
            null,
            $res['success'] ? 'SUCCESS' : 'FAILED',
            "Pesan uji WhatsApp dikirim ke $maskedPhone (Hasil: " . ($res['success'] ? 'Sukses' : 'Gagal') . ")"
        );

        $this->jsonResponse(
            $res['success'] ? 200 : 400,
            $res['success'],
            $res['message'],
            $res
        );
    }

    /**
     * API: Simpan Pengaturan Keamanan
     * POST /settings/saveSecurity
     */
    public function saveSecurity() {
        $this->requirePost();
        Middleware::verifyCsrf();

        $input = $this->getInputData();
        $userId = $_SESSION['user_id'] ?? null;

        $result = SettingService::saveSecuritySettings($input, $userId);

        $this->jsonResponse(
            $result['success'] ? 200 : 400,
            $result['success'],
            $result['message']
        );
    }

    /**
     * API: Simpan Pengaturan Tema Default
     * POST /settings/saveTheme
     */
    public function saveTheme() {
        $this->requirePost();
        Middleware::verifyCsrf();

        $input = $this->getInputData();
        $userId = $_SESSION['user_id'] ?? null;

        $result = SettingService::saveThemeSettings($input, $userId);

        $this->jsonResponse(
            $result['success'] ? 200 : 422,
            $result['success'],
            $result['message'],
            $result
        );
    }

    /**
     * API: Ambil Riwayat Audit Log (untuk AJAX refresh / filter)
     * GET /settings/auditLogs
     */
    public function auditLogs() {
        $category = $_GET['category'] ?? null;
        if (!in_array($category, ['ai', 'otp', 'security'], true)) {
            $category = null;
        }
        $limit = min(100, max(10, (int)($_GET['limit'] ?? 50)));

        $auditModel = $this->model('AuditLogModel');
        $logs = $auditModel->getRecentLogs($limit, $category);

        $this->jsonResponse(200, true, 'Audit log berhasil dimuat.', ['logs' => $logs]);
    }

    /**
     * API: Simpan Pengaturan Notifikasi WhatsApp Pesanan
     * POST /settings/saveOrderWa
     */
    public function saveOrderWa() {
        $this->requirePost();
        Middleware::verifyCsrf();

        $input = $this->getInputData();
        $userId = $_SESSION['user_id'] ?? null;

        $result = SettingService::saveOrderWaSettings($input, $userId);

        $this->jsonResponse(
            $result['success'] ? 200 : 422,
            $result['success'],
            $result['message'],
            $result
        );
    }

    /**
     * API: Ambil Riwayat Log Notifikasi WhatsApp Pesanan (untuk AJAX refresh / filter)
     * GET /settings/orderWaLogs
     */
    public function orderWaLogs() {
        $status = $_GET['status'] ?? null;
        $search = trim($_GET['search'] ?? '');
        $limit = min(100, max(10, (int)($_GET['limit'] ?? 50)));

        $logModel = $this->model('WhatsAppLogModel');
        $logs = $logModel->getRecentLogs($limit, $status, $search);

        foreach ($logs as &$log) {
            $log['recipient_phone_masked'] = OrderNotificationService::maskPhone($log['recipient_phone']);
        }

        $this->jsonResponse(200, true, 'Log notifikasi berhasil dimuat.', [
            'logs' => $logs,
            'total' => $logModel->getTotalLogs($status, $search)
        ]);
    }

    /**
     * API: Retry Manual Notifikasi WhatsApp Pesanan yang Gagal
     * POST /settings/retryWaNotification
     */
    public function retryWaNotification() {
        $this->requirePost();
        Middleware::verifyCsrf();

        $input = $this->getInputData();
        $logId = (int)($input['log_id'] ?? 0);

        if ($logId <= 0) {
            $this->jsonResponse(400, false, 'ID log notifikasi tidak valid.');
        }

        $service = new OrderNotificationService();
        $res = $service->retryNotification($logId);

        // Audit log
        $auditModel = $this->model('AuditLogModel');
        $auditModel->log(
            'RETRY_ORDER_WA',
            'order_wa',
            'log_' . $logId,
            null,
            null,
            $res['success'] ? 'SUCCESS' : 'FAILED',
            "Pengiriman ulang notifikasi WA pesanan (Log ID: {$logId}, Status: " . ($res['success'] ? 'Sukses' : 'Gagal') . ")"
        );

        $this->jsonResponse(
            $res['success'] ? 200 : 400,
            $res['success'],
            $res['message'],
            $res
        );
    }

    /**
     * API: Kirim Pesan Uji Notifikasi WhatsApp Pesanan (Preview Template Aktual)
     * POST /settings/testOrderWa
     */
    public function testOrderWa() {
        $this->requirePost();
        Middleware::verifyCsrf();

        $input = $this->getInputData();
        $targetPhone = trim($input['target_phone'] ?? '');
        $overrideToken = $input['otp_api_token'] ?? null;
        $eventType = trim($input['event_type'] ?? 'order_created');

        if (empty($targetPhone)) {
            $this->jsonResponse(400, false, 'Nomor WhatsApp tujuan pengujian wajib diisi.');
        }

        $phone = FonnteService::normalizePhone($targetPhone);
        if (!$phone) {
            $this->jsonResponse(400, false, 'Format nomor WhatsApp tidak valid. Gunakan 08xx atau 628xx.');
        }

        $fonnteService = new FonnteService();
        $service = new OrderNotificationService($fonnteService);

        // Mock pesanan untuk preview template yang valid
        $mockOrder = [
            'id' => 9999,
            'user_id' => $_SESSION['user_id'] ?? 1,
            'order_date' => date('Y-m-d H:i:s'),
            'total' => 150000,
            'status' => 'DIPROSES'
        ];
        $mockCustomer = [
            'name' => $_SESSION['user_name'] ?? 'Tester nnaco_sa',
            'phone' => $phone
        ];
        $mockDetails = [
            [
                'product_name' => 'Kalung Titanium Elegan (Uji Notifikasi)',
                'quantity' => 1,
                'price' => 150000,
                'subtotal' => 150000
            ]
        ];

        $rendered = $service->renderMessage(
            $eventType,
            $mockOrder,
            $mockCustomer,
            $mockDetails,
            ['payment_method' => 'Cash / Store Simulator', 'status' => 'PAID'],
            ['tracking_number' => 'TEST-RESI-12345', 'courier' => 'JNE Reguler', 'reason' => 'Uji Pembatalan Sistem']
        );

        $res = $fonnteService->sendMessage($phone, $rendered, $overrideToken);

        // Audit log
        $maskedPhone = OrderNotificationService::maskPhone($targetPhone);
        $auditModel = $this->model('AuditLogModel');
        $auditModel->log(
            'TEST_ORDER_WA_MESSAGE',
            'order_wa',
            'test_message',
            null,
            null,
            $res['success'] ? 'SUCCESS' : 'FAILED',
            "Pengujian pesan WhatsApp pesanan dikirim ke {$maskedPhone} untuk event {$eventType} (Hasil: " . ($res['success'] ? 'Sukses' : 'Gagal') . ")"
        );

        $this->jsonResponse(
            $res['success'] ? 200 : 400,
            $res['success'],
            $res['message'],
            array_merge($res, ['rendered_preview' => $rendered])
        );
    }

    // =========================================================
    // HELPER METHODS
    // =========================================================

    private function requirePost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(405, false, 'Method tidak diizinkan. Gunakan POST.');
        }
    }

    private function getInputData(): array {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            return json_decode($raw, true) ?? [];
        }
        return $_POST;
    }

    private function jsonResponse(int $httpCode, bool $success, string $message, array $extra = []): never {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message,
        ], $extra));
        exit;
    }
}
