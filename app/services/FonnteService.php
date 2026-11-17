<?php
/**
 * FonnteService - Service untuk mengirim OTP via WhatsApp menggunakan Fonnte API
 *
 * KEAMANAN:
 * - Token diambil secara aman dari SettingService (dengan fallback ke environment variable)
 * - OTP plaintext tidak pernah ditulis ke log
 * - Token Fonnte tidak pernah dikembalikan ke frontend
 * - Pengujian koneksi (/device) tidak mengirim OTP nyata
 */

require_once __DIR__ . '/SettingService.php';

class FonnteService {

    private string $token;
    private string $apiUrl;
    private string $deviceApiUrl;
    private int $timeout;

    public function __construct() {
        // Ambil dari SettingService dengan fallback ke .env
        $this->token = SettingService::getSecret('otp.api_token', getenv('FONNTE_TOKEN') ?: '');
        $this->apiUrl = SettingService::get('otp.api_url', getenv('FONNTE_API_URL') ?: 'https://api.fonnte.com/send');
        $this->deviceApiUrl = SettingService::get('otp.device_api_url', 'https://api.fonnte.com/device');
        $this->timeout = 15; // detik
    }

    /**
     * Cek apakah service dikonfigurasi dengan token valid
     */
    public function isConfigured(): bool {
        $token = $this->token;
        return !empty($token)
            && $token !== 'MASUKKAN_TOKEN_FONNTE_BARU_DI_SINI'
            && $token !== 'your_fonnte_token_here';
    }

    /**
     * Normalisasi nomor WhatsApp ke format internasional tanpa tanda +
     * Contoh: 08123456789 → 628123456789
     *
     * @param string $phone
     * @return string|false Nomor yang dinormalisasi, atau false jika tidak valid
     */
    public static function normalizePhone(string $phone): string|false {
        // Hapus semua karakter selain digit
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (empty($phone)) return false;

        // Format: sudah dimulai dengan 62
        if (str_starts_with($phone, '62')) {
            // Validasi panjang: 62 + 8-12 digit
            if (strlen($phone) < 10 || strlen($phone) > 15) return false;
            return $phone;
        }

        // Format: dimulai dengan 0 (Indonesia)
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
            if (strlen($phone) < 10 || strlen($phone) > 15) return false;
            return $phone;
        }

        // Format: dimulai dengan 8 (tanpa 0 maupun 62)
        if (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
            if (strlen($phone) < 10 || strlen($phone) > 15) return false;
            return $phone;
        }

        return false;
    }

    /**
     * Kirim OTP via WhatsApp Fonnte
     *
     * @param string $phone   Nomor tujuan yang sudah dinormalisasi (628xxx)
     * @param string $otp     OTP plaintext (hanya digunakan untuk pesan, tidak dilog)
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendOtp(string $phone, string $otp): array {
        if (!SettingService::isOtpEnabled()) {
            return [
                'success' => false,
                'message' => 'Layanan login dengan OTP WhatsApp sedang dinonaktifkan oleh administrator.'
            ];
        }

        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Layanan OTP belum dikonfigurasi. Hubungi administrator.'
            ];
        }

        // Cek mode pengujian (test mode)
        $isTestMode = (string)SettingService::get('otp.test_mode', '0') === '1';
        if ($isTestMode) {
            // Simulasikan pengiriman sukses tanpa konsumsi kuota
            return [
                'success' => true,
                'message' => '[MODE SIMULASI] OTP berhasil dibuat (Pengiriman WhatsApp disimulasikan aktif).'
            ];
        }

        $expiry = (string)SettingService::get('otp.expiry_minutes', '5');
        $rawTemplate = SettingService::get(
            'otp.message_template',
            'Halo! Kode verifikasi login nnaco_sa Anda adalah *{OTP}*. Kode ini berlaku selama {EXPIRY} menit. Jangan bagikan kode ini kepada siapa pun.'
        );

        $message = str_replace(
            ['{OTP}', '{EXPIRY}', '{APP_NAME}'],
            [$otp, $expiry, 'nnaco_sa'],
            $rawTemplate
        );

        $payload = [
            'target'  => $phone,
            'message' => $message,
        ];

        return $this->postToFonnte($this->apiUrl, $this->token, $payload);
    }

    /**
     * Uji Koneksi ke API Fonnte tanpa mengirim OTP login (Menggunakan endpoint /device)
     */
    public function testConnection(?string $tokenOverride = null, ?string $urlOverride = null): array {
        $token = (!empty($tokenOverride) && !SecretService::isMaskedPlaceholder($tokenOverride))
            ? trim($tokenOverride)
            : $this->token;

        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Token API Fonnte belum diisi.'
            ];
        }

        $endpoint = (!empty($urlOverride)) ? trim($urlOverride) : $this->deviceApiUrl;

        if (!function_exists('curl_init')) {
            return ['success' => false, 'message' => 'cURL tidak tersedia di server ini.'];
        }

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: ' . $token,
            ],
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log('[FonnteService] testConnection cURL error: ' . $curlError);
            return [
                'success' => false,
                'message' => 'Gagal menghubungi server Fonnte (Network timeout atau koneksi internet terputus).'
            ];
        }

        if ($httpCode !== 200) {
            if ($httpCode === 401 || $httpCode === 403) {
                return [
                    'success' => false,
                    'message' => 'Token Fonnte tidak valid atau telah dicabut.'
                ];
            }
            return [
                'success' => false,
                'message' => "Server Fonnte merespon dengan status HTTP $httpCode."
            ];
        }

        $data = json_decode($response, true);
        if (!$data || !isset($data['status'])) {
            return [
                'success' => false,
                'message' => 'Format respons dari Fonnte tidak valid.'
            ];
        }

        if ($data['status'] === true || $data['status'] === 'true' || $data['status'] === 1) {
            $deviceStatus = $data['device_status'] ?? 'Unknown';
            $deviceNumber = $data['device'] ?? '-';
            $package = $data['package'] ?? 'Free';
            $quota = $data['quota'] ?? 0;

            return [
                'success'       => true,
                'device'        => $deviceNumber,
                'device_status' => $deviceStatus,
                'package'       => $package,
                'quota'         => $quota,
                'message'       => "Koneksi berhasil! Perangkat '$deviceNumber' terhubung ($deviceStatus). Sisa kuota pesan: $quota."
            ];
        }

        $reason = $data['reason'] ?? 'Token tidak dikenali atau akun Fonnte belum tersambung ke WhatsApp.';
        return [
            'success' => false,
            'message' => 'Fonnte merespon: ' . $reason
        ];
    }

    /**
     * Kirim Pesan Uji WhatsApp nyata ke nomor tujuan
     * (Hanya setelah konfirmasi eksplisit di UI, bukan OTP login)
     */
    public function sendTestMessage(string $targetPhone, ?string $tokenOverride = null): array {
        $phone = self::normalizePhone($targetPhone);
        if (!$phone) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp tujuan tidak valid. Gunakan format 08xx atau 628xx.'
            ];
        }

        $token = (!empty($tokenOverride) && !SecretService::isMaskedPlaceholder($tokenOverride))
            ? trim($tokenOverride)
            : $this->token;

        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Token API Fonnte belum diisi.'
            ];
        }

        // Rate limiting kirim pesan uji (maks 3x per 5 menit per user)
        $now = time();
        if (!isset($_SESSION['last_test_wa_times'])) {
            $_SESSION['last_test_wa_times'] = [];
        }
        // Filter yang lebih baru dari 5 menit
        $_SESSION['last_test_wa_times'] = array_filter(
            $_SESSION['last_test_wa_times'], 
            fn($t) => ($now - $t) < 300
        );
        if (count($_SESSION['last_test_wa_times']) >= 3) {
            return [
                'success' => false,
                'message' => 'Terlalu banyak permintaan pesan uji. Harap tunggu beberapa menit sebelum mencoba lagi.'
            ];
        }
        $_SESSION['last_test_wa_times'][] = $now;

        $timestamp = date('d-m-Y H:i:s');
        $message = "🔔 *[UJI KONEKSI SISTEM NNACO_SA]*\n\n"
                 . "Halo! Ini adalah pesan pengujian koneksi WhatsApp Fonnte dari menu Pengaturan Sistem nnaco_sa pada {$timestamp}.\n\n"
                 . "✅ Integrasi API Fonnte berfungsi dengan baik.\n"
                 . "ℹ️ *Catatan:* Ini BUKAN kode OTP login. Abaikan pesan ini jika Anda tidak sedang menguji sistem.";

        $payload = [
            'target'  => $phone,
            'message' => $message,
        ];

        return $this->postToFonnte($this->apiUrl, $token, $payload);
    }

    /**
     * Kirim pesan WhatsApp umum (untuk Notifikasi Pesanan & Transaksi)
     *
     * @param string $targetPhone Nomor WhatsApp tujuan (08xxx / 628xxx)
     * @param string $message     Isi teks pesan
     * @param string|null $tokenOverride Token opsional untuk override/pengujian
     * @return array ['success' => bool, 'message' => string, 'provider_id' => ?string, 'error_code' => ?string]
     */
    public function sendMessage(string $targetPhone, string $message, ?string $tokenOverride = null): array {
        $phone = self::normalizePhone($targetPhone);
        if (!$phone) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp tujuan tidak valid. Pastikan format nomor diawali 08xx atau 628xx.',
                'error_code' => 'INVALID_PHONE',
                'provider_id' => null
            ];
        }

        $token = (!empty($tokenOverride) && !SecretService::isMaskedPlaceholder($tokenOverride))
            ? trim($tokenOverride)
            : $this->token;

        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Token API Fonnte belum dikonfigurasi di Pengaturan Sistem.',
                'error_code' => 'MISSING_TOKEN',
                'provider_id' => null
            ];
        }

        // Cek mode simulasi (test_mode)
        $isTestMode = (string)SettingService::get('order_wa.test_mode', '0') === '1';
        if ($isTestMode) {
            return [
                'success' => true,
                'message' => '[MODE SIMULASI] Pesan WhatsApp pesanan disimulasikan sukses terkirim.',
                'provider_id' => 'SIMULATED-' . uniqid(),
                'error_code' => null
            ];
        }

        $payload = [
            'target'  => $phone,
            'message' => $message,
        ];

        return $this->postToFonnte($this->apiUrl, $token, $payload);
    }

    /**
     * Kirim HTTP POST ke Fonnte API
     */
    private function postToFonnte(string $url, string $token, array $payload): array {
        if (!function_exists('curl_init')) {
            return [
                'success' => false,
                'message' => 'cURL tidak tersedia di server ini.',
                'error_code' => 'NO_CURL',
                'provider_id' => null
            ];
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($payload),
            CURLOPT_HTTPHEADER     => [
                'Authorization: ' . $token,
            ],
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            error_log('[FonnteService] cURL error: ' . $curlError);
            return [
                'success' => false,
                'message' => 'Gagal menghubungi server WhatsApp Fonnte (Network timeout/terputus).',
                'error_code' => 'NETWORK_ERROR',
                'provider_id' => null,
                'http_code' => 0
            ];
        }

        if ($httpCode !== 200) {
            error_log('[FonnteService] HTTP ' . $httpCode . ' dari Fonnte API.');
            return [
                'success' => false,
                'message' => 'Layanan WhatsApp tidak tersedia saat ini (HTTP ' . $httpCode . ').',
                'error_code' => 'HTTP_' . $httpCode,
                'provider_id' => null,
                'http_code' => $httpCode
            ];
        }

        $data = json_decode($response, true);
        if (!$data) {
            error_log('[FonnteService] Respons tidak valid dari Fonnte.');
            return [
                'success' => false,
                'message' => 'Respons tidak valid dari server WhatsApp.',
                'error_code' => 'INVALID_JSON',
                'provider_id' => null,
                'http_code' => $httpCode
            ];
        }

        $providerId = null;
        if (!empty($data['id'])) {
            $providerId = is_array($data['id']) ? ($data['id'][0] ?? null) : (string)$data['id'];
        }

        $status = $data['status'] ?? false;
        if ($status === true || $status === 'true' || $status === 1) {
            return [
                'success' => true,
                'message' => 'Pesan berhasil dikirim ke WhatsApp tujuan.',
                'provider_id' => $providerId,
                'error_code' => null,
                'http_code' => $httpCode,
                'raw_response' => $data
            ];
        }

        $reason = $data['reason'] ?? 'unknown';
        error_log('[FonnteService] Fonnte gagal: ' . $reason);

        return [
            'success' => false,
            'message' => 'Gagal mengirim pesan: ' . $reason,
            'error_code' => 'PROVIDER_REJECTED',
            'provider_id' => $providerId,
            'http_code' => $httpCode,
            'raw_response' => $data
        ];
    }
}

