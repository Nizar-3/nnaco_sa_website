<?php
/**
 * SettingService - Service terpusat untuk konfigurasi sistem
 *
 * Mengelola:
 * - Pembacaan konfigurasi database dengan fallback ke environment variable
 * - Manajemen secret (enkripsi, dekripsi internal, masking keluar)
 * - Validasi batas konfigurasi AI dan OTP
 * - Invalidation cache konfigurasi
 */

require_once __DIR__ . '/SecretService.php';
require_once dirname(__DIR__) . '/models/SettingModel.php';
require_once dirname(__DIR__) . '/models/AuditLogModel.php';

class SettingService {
    private static ?SettingModel $model = null;
    private static ?AuditLogModel $audit = null;

    private static function getModel(): SettingModel {
        if (self::$model === null) {
            self::$model = new SettingModel();
        }
        return self::$model;
    }

    private static function getAudit(): AuditLogModel {
        if (self::$audit === null) {
            self::$audit = new AuditLogModel();
        }
        return self::$audit;
    }

    /**
     * Ambil nilai konfigurasi (string/int/bool).
     * Jika belum di-set di DB, fallback ke env atau default.
     */
    public static function get(string $key, mixed $default = null): mixed {
        $val = self::getModel()->getValue($key);
        if ($val !== null && $val !== '') {
            return $val;
        }

        // Fallback ke environment variable
        $envKey = strtoupper(str_replace('.', '_', $key));
        $envVal = getenv($envKey);
        if ($envVal !== false && $envVal !== '') {
            return $envVal;
        }

        return $default;
    }

    /**
     * Dapatkan secret (API Key, Token) dalam bentuk plaintext yang sudah didekripsi
     * HANYA untuk konsumsi internal backend service (GroqAIService, FonnteService).
     * JANGAN PERNAH panggil method ini untuk diteruskan ke View / AJAX Response!
     */
    public static function getSecret(string $key, string $default = ''): string {
        $raw = self::getModel()->getValue($key);
        if ($raw !== null && $raw !== '') {
            $decrypted = SecretService::decrypt($raw);
            if ($decrypted !== '') {
                return $decrypted;
            }
        }

        // Fallback ke env jika di database kosong
        $envMap = [
            'ai.api_key'  => 'GROQ_API_KEY',
            'otp.api_token' => 'FONNTE_TOKEN'
        ];
        if (isset($envMap[$key])) {
            $envVal = getenv($envMap[$key]);
            if ($envVal) return $envVal;
        }

        return $default;
    }

    /**
     * Cek apakah Chatbot AI diaktifkan
     */
    public static function isAiEnabled(): bool {
        $val = self::get('ai.enabled', '1');
        return in_array(strtolower((string)$val), ['1', 'true', 'on', 'yes'], true);
    }

    /**
     * Cek apakah Autentikasi OTP WhatsApp diaktifkan
     */
    public static function isOtpEnabled(): bool {
        $val = self::get('otp.enabled', '1');
        return in_array(strtolower((string)$val), ['1', 'true', 'on', 'yes'], true);
    }

    /**
     * Cek apakah Notifikasi WhatsApp Pesanan diaktifkan
     */
    public static function isOrderWaEnabled(): bool {
        $val = self::get('order_wa.enabled', '1');
        return in_array(strtolower((string)$val), ['1', 'true', 'on', 'yes'], true);
    }

    /**
     * Ambil seluruh konfigurasi yang aman disajikan ke UI
     * Secret otomatis disamarkan (••••••••••••)
     */
    public static function getAllForUI(): array {
        $rows = self::getModel()->getAllSettings();
        $output = [];

        foreach ($rows as $row) {
            $key = $row['setting_key'];
            $isSecret = (bool)($row['is_secret'] ?? 0);
            $val = $row['setting_value'] ?? '';

            if ($isSecret) {
                $hasConfigured = !empty($val) || !empty(self::getSecret($key));
                $output[$key] = [
                    'value'        => $hasConfigured ? SecretService::maskSecret($val) : '',
                    'is_secret'    => true,
                    'configured'   => $hasConfigured,
                    'group'        => $row['setting_group'] ?? 'general',
                    'description'  => $row['description'] ?? ''
                ];
            } else {
                $output[$key] = [
                    'value'        => $val,
                    'is_secret'    => false,
                    'group'        => $row['setting_group'] ?? 'general',
                    'description'  => $row['description'] ?? ''
                ];
            }
        }

        return $output;
    }

    /**
     * Simpan konfigurasi AI setelah validasi ketat
     */
    public static function saveAiSettings(array $data, ?int $userId = null): array {
        $errors = [];

        // Validasi
        $rawEnabled = $data['ai_enabled'] ?? '0';
        $enabled = in_array(strtolower((string)$rawEnabled), ['1', 'true', 'on', 'yes'], true) ? '1' : '0';
        $provider = trim($data['ai_provider'] ?? 'groq');
        if (!in_array($provider, ['groq', 'gemini', 'mock'], true)) {
            $errors['ai_provider'] = 'Provider AI yang dipilih tidak valid.';
        }

        $model = trim($data['ai_model'] ?? '');
        if (empty($model)) {
            $errors['ai_model'] = 'Nama model AI wajib diisi.';
        } elseif (!preg_match('/^[a-zA-Z0-9_\-\.\/]+$/', $model)) {
            $errors['ai_model'] = 'Format nama model tidak valid.';
        }

        $apiBaseUrl = trim($data['ai_api_base_url'] ?? '');
        if (empty($apiBaseUrl)) {
            if ($provider === 'gemini') {
                $apiBaseUrl = 'https://generativelanguage.googleapis.com/v1beta/openai';
            } elseif ($provider === 'mock') {
                $apiBaseUrl = 'local://mock';
            } else {
                $apiBaseUrl = 'https://api.groq.com/openai/v1';
            }
        }
        if ($provider !== 'mock' && !empty($apiBaseUrl) && !filter_var($apiBaseUrl, FILTER_VALIDATE_URL)) {
            $errors['ai_api_base_url'] = 'Format Base URL API tidak valid.';
        }

        $temperature = filter_var($data['ai_temperature'] ?? '0.3', FILTER_VALIDATE_FLOAT);
        if ($temperature === false || $temperature < 0.0 || $temperature > 2.0) {
            $errors['ai_temperature'] = 'Temperature harus berupa angka antara 0.0 dan 2.0.';
        }

        $maxTokens = filter_var($data['ai_max_output_tokens'] ?? '1024', FILTER_VALIDATE_INT);
        if ($maxTokens === false || $maxTokens < 50 || $maxTokens > 8192) {
            $errors['ai_max_output_tokens'] = 'Max Output Tokens harus berupa bilangan bulat antara 50 dan 8192.';
        }

        $timeout = filter_var($data['ai_timeout_seconds'] ?? '30', FILTER_VALIDATE_INT);
        if ($timeout === false || $timeout < 5 || $timeout > 120) {
            $errors['ai_timeout_seconds'] = 'Timeout request harus antara 5 dan 120 detik.';
        }

        $maxMsgLen = filter_var($data['ai_max_user_message_length'] ?? '2000', FILTER_VALIDATE_INT);
        if ($maxMsgLen === false || $maxMsgLen < 100 || $maxMsgLen > 10000) {
            $errors['ai_max_user_message_length'] = 'Batas panjang pesan harus antara 100 dan 10.000 karakter.';
        }

        $assistantName = trim($data['ai_assistant_name'] ?? 'nnaco AI Assistant');
        if (empty($assistantName) || strlen($assistantName) > 100) {
            $errors['ai_assistant_name'] = 'Nama asisten tidak boleh kosong (maks 100 karakter).';
        }

        $primaryLang = trim($data['ai_primary_language'] ?? 'Bahasa Indonesia');
        $systemPrompt = trim($data['ai_system_prompt'] ?? '');
        if (empty($systemPrompt) || strlen($systemPrompt) < 30) {
            $errors['ai_system_prompt'] = 'System prompt tidak boleh kosong (minimal 30 karakter).';
        }

        $rejectionMsg = trim($data['ai_rejection_message'] ?? '');
        if (empty($rejectionMsg)) {
            $errors['ai_rejection_message'] = 'Pesan penolakan pertanyaan tidak boleh kosong.';
        }

        $maxHistory = filter_var($data['ai_max_history_messages'] ?? '10', FILTER_VALIDATE_INT);
        if ($maxHistory === false || $maxHistory < 2 || $maxHistory > 50) {
            $errors['ai_max_history_messages'] = 'Jumlah riwayat pesan harus antara 2 dan 50.';
        }

        $rateLimit = filter_var($data['ai_rate_limit_per_minute'] ?? '20', FILTER_VALIDATE_INT);
        if ($rateLimit === false || $rateLimit < 1 || $rateLimit > 300) {
            $errors['ai_rate_limit_per_minute'] = 'Batas request per menit harus antara 1 dan 300.';
        }

        // Kelola dan validasi API Key sesuai Provider terpilih
        $newApiKey = trim($data['ai_api_key'] ?? '');
        $isNewKeyProvided = !empty($newApiKey) && !SecretService::isMaskedPlaceholder($newApiKey);
        $currentSavedKey = self::getSecret('ai.api_key');
        $effectiveKey = $isNewKeyProvided ? $newApiKey : $currentSavedKey;

        if ($provider === 'gemini') {
            if (empty($effectiveKey)) {
                $errors['ai_api_key'] = 'API Key Google Gemini wajib diisi. Dapatkan API Key di Google AI Studio (aistudio.google.com).';
            } elseif (str_starts_with($effectiveKey, 'gsk_')) {
                $errors['ai_api_key'] = 'API Key yang tersimpan adalah kunci Groq Cloud (gsk_...). Provider Google Gemini memerlukan API Key dari Google AI Studio (aistudio.google.com). Silakan masukkan API Key Gemini Anda.';
            }
        } elseif ($provider === 'groq') {
            if (empty($effectiveKey)) {
                $errors['ai_api_key'] = 'API Key Groq Cloud wajib diisi. Dapatkan API Key di console.groq.com.';
            } elseif (str_starts_with($effectiveKey, 'AIzaSy') || str_starts_with($effectiveKey, 'AQ.')) {
                $errors['ai_api_key'] = 'API Key yang tersimpan adalah kunci Google Gemini. Provider Groq Cloud memerlukan API Key dari console.groq.com (gsk_...). Silakan masukkan API Key Groq Anda.';
            }
        }

        if (!empty($errors)) {
            return ['success' => false, 'message' => 'Validasi gagal. Periksa input Anda.', 'errors' => $errors];
        }

        // Siapkan update
        $updates = [
            'ai.enabled'                 => $enabled,
            'ai.provider'                => $provider,
            'ai.model'                   => $model,
            'ai.api_base_url'            => $apiBaseUrl,
            'ai.temperature'             => (string)$temperature,
            'ai.max_output_tokens'        => (string)$maxTokens,
            'ai.timeout_seconds'         => (string)$timeout,
            'ai.max_user_message_length' => (string)$maxMsgLen,
            'ai.assistant_name'          => $assistantName,
            'ai.primary_language'        => $primaryLang,
            'ai.system_prompt'           => $systemPrompt,
            'ai.rejection_message'       => $rejectionMsg,
            'ai.max_history_messages'    => (string)$maxHistory,
            'ai.rate_limit_per_minute'   => (string)$rateLimit,
        ];

        // Kelola API Key: Jika diisi nilai baru dan bukan masked placeholder, enkripsi dan simpan
        $apiKeyUpdated = false;
        if ($isNewKeyProvided) {
            $encryptedKey = SecretService::encrypt($newApiKey);
            $updates['ai.api_key'] = $encryptedKey;
            $apiKeyUpdated = true;
        }

        // Eksekusi update batch
        $ok = self::getModel()->updateBatch($updates, $userId);

        if ($ok) {
            // Sinkronkan ke environment file background AI server
            self::syncGroqServerEnv($enabled, $provider, $model, $apiKeyUpdated ? $newApiKey : null);

            // Catat ke Audit Log tanpa membocorkan key
            self::getAudit()->log(
                'UPDATE_AI_CONFIG',
                'ai',
                $apiKeyUpdated ? 'ai.api_key & general' : 'general',
                null,
                null,
                'SUCCESS',
                "Konfigurasi AI diperbarui (Model: $model, Provider: $provider, Status: " . ($enabled === '1' ? 'Aktif' : 'Nonaktif') . ($apiKeyUpdated ? ', API Key diubah' : '') . ")"
            );

            return ['success' => true, 'message' => 'Konfigurasi AI berhasil disimpan dan langsung aktif.'];
        }

        return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat menyimpan ke database.'];
    }

    /**
     * Sinkronkan konfigurasi AI ke groq-chat-server/.env
     */
    public static function syncGroqServerEnv(string $enabled, string $provider, string $model, ?string $apiKey = null): void {
        $envPath = dirname(__DIR__, 2) . '/groq-chat-server/.env';
        if (!file_exists($envPath)) return;

        $content = file_get_contents($envPath);
        if ($content === false) return;

        $enabledStr = ($enabled === '1') ? 'true' : 'false';
        $content = preg_replace('/^AI_CHAT_ENABLED=.*$/m', 'AI_CHAT_ENABLED=' . $enabledStr, $content);
        $content = preg_replace('/^GROQ_MODEL=.*$/m', 'GROQ_MODEL=' . $model, $content);
        if (preg_match('/^AI_PROVIDER=/m', $content)) {
            $content = preg_replace('/^AI_PROVIDER=.*$/m', 'AI_PROVIDER=' . $provider, $content);
        } else {
            $content .= "\nAI_PROVIDER=" . $provider;
        }

        if ($apiKey !== null && !empty($apiKey) && !SecretService::isMaskedPlaceholder($apiKey)) {
            if ($provider === 'groq') {
                $content = preg_replace('/^GROQ_API_KEY=.*$/m', 'GROQ_API_KEY=' . $apiKey, $content);
            } elseif ($provider === 'gemini') {
                if (preg_match('/^GEMINI_API_KEY=/m', $content)) {
                    $content = preg_replace('/^GEMINI_API_KEY=.*$/m', 'GEMINI_API_KEY=' . $apiKey, $content);
                } else {
                    $content .= "\nGEMINI_API_KEY=" . $apiKey;
                }
            }
        }

        @file_put_contents($envPath, $content);
    }

    /**
     * Simpan konfigurasi OTP setelah validasi ketat
     */
    public static function saveOtpSettings(array $data, ?int $userId = null): array {
        $errors = [];

        $enabled = !empty($data['otp_enabled']) ? '1' : '0';
        $provider = trim($data['otp_provider'] ?? 'fonnte');
        $apiUrl = trim($data['otp_api_url'] ?? 'https://api.fonnte.com/send');
        if (!filter_var($apiUrl, FILTER_VALIDATE_URL)) {
            $errors['otp_api_url'] = 'URL API Fonnte tidak valid.';
        }

        $deviceApiUrl = trim($data['otp_device_api_url'] ?? 'https://api.fonnte.com/device');
        if (!empty($deviceApiUrl) && !filter_var($deviceApiUrl, FILTER_VALIDATE_URL)) {
            $errors['otp_device_api_url'] = 'URL API Cek Perangkat tidak valid.';
        }

        $senderName = trim($data['otp_sender_name'] ?? 'nnaco_sa');
        if (strlen($senderName) > 50) {
            $errors['otp_sender_name'] = 'Nama pengirim maksimal 50 karakter.';
        }

        $otpLength = filter_var($data['otp_length'] ?? '6', FILTER_VALIDATE_INT);
        if ($otpLength === false || $otpLength < 4 || $otpLength > 8) {
            $errors['otp_length'] = 'Panjang OTP harus antara 4 dan 8 digit.';
        }

        $expiry = filter_var($data['otp_expiry_minutes'] ?? '5', FILTER_VALIDATE_INT);
        if ($expiry === false || $expiry < 1 || $expiry > 60) {
            $errors['otp_expiry_minutes'] = 'Masa berlaku OTP harus antara 1 dan 60 menit.';
        }

        $maxAttempts = filter_var($data['otp_max_attempts'] ?? '5', FILTER_VALIDATE_INT);
        if ($maxAttempts === false || $maxAttempts < 1 || $maxAttempts > 10) {
            $errors['otp_max_attempts'] = 'Maksimal percobaan harus antara 1 dan 10 kali.';
        }

        $cooldown = filter_var($data['otp_resend_cooldown_seconds'] ?? '60', FILTER_VALIDATE_INT);
        if ($cooldown === false || $cooldown < 10 || $cooldown > 300) {
            $errors['otp_resend_cooldown_seconds'] = 'Cooldown kirim ulang harus antara 10 dan 300 detik.';
        }

        $ratePhone = filter_var($data['otp_rate_limit_phone_per_hour'] ?? '5', FILTER_VALIDATE_INT);
        if ($ratePhone === false || $ratePhone < 1 || $ratePhone > 50) {
            $errors['otp_rate_limit_phone_per_hour'] = 'Batas nomor per jam harus antara 1 dan 50.';
        }

        $rateIp = filter_var($data['otp_rate_limit_ip_per_hour'] ?? '5', FILTER_VALIDATE_INT);
        if ($rateIp === false || $rateIp < 1 || $rateIp > 100) {
            $errors['otp_rate_limit_ip_per_hour'] = 'Batas IP per jam harus antara 1 dan 100.';
        }

        $sessionExpiry = filter_var($data['otp_session_expiry_minutes'] ?? '120', FILTER_VALIDATE_INT);
        if ($sessionExpiry === false || $sessionExpiry < 15 || $sessionExpiry > 1440) {
            $errors['otp_session_expiry_minutes'] = 'Masa berlaku sesi harus antara 15 dan 1440 menit.';
        }

        $template = trim($data['otp_message_template'] ?? '');
        if (empty($template)) {
            $errors['otp_message_template'] = 'Template pesan OTP tidak boleh kosong.';
        } elseif (!str_contains($template, '{OTP}')) {
            $errors['otp_message_template'] = 'Template wajib mengandung placeholder {OTP}.';
        }

        $testMode = !empty($data['otp_test_mode']) ? '1' : '0';

        if (!empty($errors)) {
            return ['success' => false, 'message' => 'Validasi gagal. Periksa input OTP Anda.', 'errors' => $errors];
        }

        $updates = [
            'otp.enabled'                    => $enabled,
            'otp.provider'                   => $provider,
            'otp.api_url'                    => $apiUrl,
            'otp.device_api_url'             => $deviceApiUrl,
            'otp.sender_name'                => $senderName,
            'otp.otp_length'                 => (string)$otpLength,
            'otp.expiry_minutes'             => (string)$expiry,
            'otp.max_attempts'               => (string)$maxAttempts,
            'otp.resend_cooldown_seconds'    => (string)$cooldown,
            'otp.rate_limit_phone_per_hour'  => (string)$ratePhone,
            'otp.rate_limit_ip_per_hour'     => (string)$rateIp,
            'otp.session_expiry_minutes'     => (string)$sessionExpiry,
            'otp.message_template'           => $template,
            'otp.test_mode'                  => $testMode,
        ];

        // Token Fonnte
        $newToken = trim($data['otp_api_token'] ?? '');
        $tokenUpdated = false;
        if (!empty($newToken) && !SecretService::isMaskedPlaceholder($newToken)) {
            $encryptedToken = SecretService::encrypt($newToken);
            $updates['otp.api_token'] = $encryptedToken;
            $tokenUpdated = true;
        }

        $ok = self::getModel()->updateBatch($updates, $userId);

        if ($ok) {
            self::getAudit()->log(
                'UPDATE_OTP_CONFIG',
                'otp',
                $tokenUpdated ? 'otp.api_token & general' : 'general',
                null,
                null,
                'SUCCESS',
                "Pengaturan OTP WhatsApp diperbarui (Status: " . ($enabled === '1' ? 'Aktif' : 'Nonaktif') . ", Expiry: {$expiry}m, Max Attempts: {$maxAttempts}" . ($tokenUpdated ? ', Token Fonnte diubah' : '') . ")"
            );

            return ['success' => true, 'message' => 'Konfigurasi OTP WhatsApp berhasil disimpan dan langsung aktif.'];
        }

        return ['success' => false, 'message' => 'Terjadi kesalahan sistem saat menyimpan ke database.'];
    }

    /**
     * Simpan pengaturan Keamanan
     */
    public static function saveSecuritySettings(array $data, ?int $userId = null): array {
        $secEncrypt = !empty($data['security_secret_encryption_enabled']) ? '1' : '0';
        $forceHttps = !empty($data['security_force_https']) ? '1' : '0';
        $sessionLife = filter_var($data['security_session_lifetime_minutes'] ?? '120', FILTER_VALIDATE_INT);
        if ($sessionLife === false || $sessionLife < 15 || $sessionLife > 1440) {
            return ['success' => false, 'message' => 'Masa sesi tidak aktif harus antara 15 dan 1440 menit.'];
        }

        $updates = [
            'security.secret_encryption_enabled' => $secEncrypt,
            'security.force_https'               => $forceHttps,
            'security.session_lifetime_minutes'  => (string)$sessionLife
        ];

        $ok = self::getModel()->updateBatch($updates, $userId);
        if ($ok) {
            self::getAudit()->log(
                'UPDATE_SECURITY_CONFIG',
                'security',
                'security_policy',
                null,
                null,
                'SUCCESS',
                "Pengaturan keamanan diperbarui (Enkripsi: $secEncrypt, Session: {$sessionLife}m)"
            );
            return ['success' => true, 'message' => 'Pengaturan keamanan berhasil diperbarui.'];
        }

        return ['success' => false, 'message' => 'Gagal memperbarui pengaturan keamanan.'];
    }

    /**
     * Dapatkan tema default sistem
     */
    public static function getDefaultTheme(): string {
        $theme = self::get('theme.default', 'system');
        return in_array($theme, ['light', 'dark', 'system'], true) ? $theme : 'system';
    }

    /**
     * Simpan pengaturan Tema Default Sistem
     */
    public static function saveThemeSettings(array $data, ?int $userId = null): array {
        $theme = trim($data['theme_default'] ?? 'system');
        if (!in_array($theme, ['light', 'dark', 'system'], true)) {
            return ['success' => false, 'message' => 'Nilai tema tidak valid. Pilih antara Light, Dark, atau System.'];
        }

        $ok = self::getModel()->set('theme.default', $theme, $userId);
        if ($ok) {
            self::getAudit()->log(
                'UPDATE_THEME_CONFIG',
                'theme',
                'theme.default',
                null,
                null,
                'SUCCESS',
                "Tema default website diperbarui menjadi: " . ucfirst($theme)
            );
            return ['success' => true, 'message' => 'Pengaturan tema default website berhasil disimpan.'];
        }

        return ['success' => false, 'message' => 'Gagal memperbarui pengaturan tema default.'];
    }

    /**
     * Simpan pengaturan Notifikasi WhatsApp Pesanan
     */
    public static function saveOrderWaSettings(array $data, ?int $userId = null): array {
        $defaultTemplates = [
            'created' => "Halo, Kak {{nama_customer}}! 👋\n\nPesanan Anda di *nnaco_sa* berhasil dibuat.\n\n📋 *Nomor Pesanan:* {{nomor_pesanan}}\n📅 *Waktu:* {{tanggal_pesanan}} WIB\n\n📦 *Ringkasan Produk:*\n{{daftar_produk}}\n\n💰 *Total Tagihan:* {{total_pesanan}}\n💳 *Metode Pembayaran:* {{metode_pembayaran}}\n📊 *Status Pesanan:* {{status_pesanan}}\n\nSilakan periksa detail pesanan melalui tautan:\n{{url_pesanan}}\n\nTerima kasih telah berbelanja di *nnaco_sa*! ✨",
            'pending' => "Halo, Kak {{nama_customer}}! ⏳\n\nPesanan {{nomor_pesanan}} Anda di *nnaco_sa* sedang *Menunggu Pembayaran*.\n\n💰 *Total yang harus dibayar:* {{total_pesanan}}\n💳 *Metode:* {{metode_pembayaran}}\n\nSilakan selesaikan pembayaran Anda melalui tautan berikut:\n{{url_pesanan}}\n\nTerima kasih! 🙏",
            'payment_success' => "Halo, Kak {{nama_customer}}! ✅\n\nPembayaran untuk pesanan {{nomor_pesanan}} telah *BERHASIL DIVERIFIKASI*.\n\n💰 *Jumlah:* {{total_pesanan}}\n💳 *Metode:* {{metode_pembayaran}}\n📊 *Status Pesanan:* Sedang Diproses\n\nPesanan Anda segera kami siapkan. Pantau perkembangannya di:\n{{url_pesanan}}\n\nTerima kasih atas kepercayaan Anda! ✨",
            'processing' => "Halo, Kak {{nama_customer}}! ⚙️\n\nKabar baik! Pesanan {{nomor_pesanan}} Anda di *nnaco_sa* saat ini *SEDANG DIPROSES*.\n\nTim kami sedang menyiapkan paket Anda dengan teliti.\n\nDetail pesanan: {{url_pesanan}}\n\nTerima kasih! 🙌",
            'packed' => "Halo, Kak {{nama_customer}}! 🎁\n\nPesanan {{nomor_pesanan}} Anda di *nnaco_sa* telah *SELESAI DIKEMAS*.\n\nBarang Anda telah dipacking dengan aman dan siap diserahkan ke jasa pengiriman.\n\nDetail pesanan: {{url_pesanan}}",
            'shipped' => "Halo, Kak {{nama_customer}}! 🚚\n\nPesanan {{nomor_pesanan}} Anda telah *DIKIRIM*!\n\n📦 *Jasa Pengiriman:* {{jasa_pengiriman}}\n🔖 *Nomor Resi:* {{nomor_resi}}\n\nAnda dapat memantau pesanan melalui tautan:\n{{url_pesanan}}\n\nMohon pastikan nomor telepon Anda aktif agar kurir mudah menghubungi Anda. Terima kasih! 📦",
            'completed' => "Halo, Kak {{nama_customer}}! 🎉\n\nPesanan {{nomor_pesanan}} Anda di *nnaco_sa* telah dinyatakan *SELESAI*.\n\nTerima kasih banyak telah memilih produk *nnaco_sa*. Kami harap Anda menyukai aksesoris kami!\n\nBantu kami memberikan layanan lebih baik dengan memberikan ulasan di:\n{{url_pesanan}}\n\nSampai jumpa di pesanan berikutnya! 💖",
            'cancelled' => "Halo, Kak {{nama_customer}}! ⚠️\n\nPesanan {{nomor_pesanan}} Anda di *nnaco_sa* telah *DIBATALKAN*.\n\n📌 *Alasan Pembatalan:* {{alasan_pembatalan}}\n\nJika ada pertanyaan mengenai pesanan ini, silakan hubungi customer service kami.\n\nDetail: {{url_pesanan}}"
        ];

        $enabled = array_key_exists('order_wa_enabled', $data)
            ? (!empty($data['order_wa_enabled']) ? '1' : '0')
            : (string)self::get('order_wa.enabled', '1');

        $testMode = array_key_exists('order_wa_test_mode', $data)
            ? (!empty($data['order_wa_test_mode']) ? '1' : '0')
            : (string)self::get('order_wa.test_mode', '0');

        $notifyCreated = array_key_exists('order_wa_notify_created', $data)
            ? (!empty($data['order_wa_notify_created']) ? '1' : '0')
            : (string)self::get('order_wa.notify_created', '1');

        $notifyPending = array_key_exists('order_wa_notify_pending_payment', $data)
            ? (!empty($data['order_wa_notify_pending_payment']) ? '1' : '0')
            : (string)self::get('order_wa.notify_pending_payment', '1');

        $notifyPaymentSuccess = array_key_exists('order_wa_notify_payment_success', $data)
            ? (!empty($data['order_wa_notify_payment_success']) ? '1' : '0')
            : (string)self::get('order_wa.notify_payment_success', '1');

        $notifyProcessing = array_key_exists('order_wa_notify_processing', $data)
            ? (!empty($data['order_wa_notify_processing']) ? '1' : '0')
            : (string)self::get('order_wa.notify_processing', '1');

        $notifyPacked = array_key_exists('order_wa_notify_packed', $data)
            ? (!empty($data['order_wa_notify_packed']) ? '1' : '0')
            : (string)self::get('order_wa.notify_packed', '1');

        $notifyShipped = array_key_exists('order_wa_notify_shipped', $data)
            ? (!empty($data['order_wa_notify_shipped']) ? '1' : '0')
            : (string)self::get('order_wa.notify_shipped', '1');

        $notifyCompleted = array_key_exists('order_wa_notify_completed', $data)
            ? (!empty($data['order_wa_notify_completed']) ? '1' : '0')
            : (string)self::get('order_wa.notify_completed', '1');

        $notifyCancelled = array_key_exists('order_wa_notify_cancelled', $data)
            ? (!empty($data['order_wa_notify_cancelled']) ? '1' : '0')
            : (string)self::get('order_wa.notify_cancelled', '1');

        // Templates (fallback ke nilai DB saat ini atau default template jika belum ada)
        $templateCreated = array_key_exists('order_wa_template_created', $data)
            ? trim((string)$data['order_wa_template_created'])
            : (string)self::get('order_wa.template_created', $defaultTemplates['created']);
        if ($templateCreated === '') $templateCreated = $defaultTemplates['created'];

        $templatePending = array_key_exists('order_wa_template_pending_payment', $data)
            ? trim((string)$data['order_wa_template_pending_payment'])
            : (string)self::get('order_wa.template_pending_payment', $defaultTemplates['pending']);
        if ($templatePending === '') $templatePending = $defaultTemplates['pending'];

        $templatePaymentSuccess = array_key_exists('order_wa_template_payment_success', $data)
            ? trim((string)$data['order_wa_template_payment_success'])
            : (string)self::get('order_wa.template_payment_success', $defaultTemplates['payment_success']);
        if ($templatePaymentSuccess === '') $templatePaymentSuccess = $defaultTemplates['payment_success'];

        $templateProcessing = array_key_exists('order_wa_template_processing', $data)
            ? trim((string)$data['order_wa_template_processing'])
            : (string)self::get('order_wa.template_processing', $defaultTemplates['processing']);
        if ($templateProcessing === '') $templateProcessing = $defaultTemplates['processing'];

        $templatePacked = array_key_exists('order_wa_template_packed', $data)
            ? trim((string)$data['order_wa_template_packed'])
            : (string)self::get('order_wa.template_packed', $defaultTemplates['packed']);
        if ($templatePacked === '') $templatePacked = $defaultTemplates['packed'];

        $templateShipped = array_key_exists('order_wa_template_shipped', $data)
            ? trim((string)$data['order_wa_template_shipped'])
            : (string)self::get('order_wa.template_shipped', $defaultTemplates['shipped']);
        if ($templateShipped === '') $templateShipped = $defaultTemplates['shipped'];

        $templateCompleted = array_key_exists('order_wa_template_completed', $data)
            ? trim((string)$data['order_wa_template_completed'])
            : (string)self::get('order_wa.template_completed', $defaultTemplates['completed']);
        if ($templateCompleted === '') $templateCompleted = $defaultTemplates['completed'];

        $templateCancelled = array_key_exists('order_wa_template_cancelled', $data)
            ? trim((string)$data['order_wa_template_cancelled'])
            : (string)self::get('order_wa.template_cancelled', $defaultTemplates['cancelled']);
        if ($templateCancelled === '') $templateCancelled = $defaultTemplates['cancelled'];

        // Validasi: template tidak boleh kosong
        $errors = [];
        if ($notifyCreated === '1' && empty($templateCreated)) {
            $errors['order_wa_template_created'] = 'Template Pesanan Dibuat tidak boleh kosong.';
        }
        if ($notifyPending === '1' && empty($templatePending)) {
            $errors['order_wa_template_pending_payment'] = 'Template Menunggu Pembayaran tidak boleh kosong.';
        }
        if ($notifyPaymentSuccess === '1' && empty($templatePaymentSuccess)) {
            $errors['order_wa_template_payment_success'] = 'Template Pembayaran Berhasil tidak boleh kosong.';
        }
        if ($notifyProcessing === '1' && empty($templateProcessing)) {
            $errors['order_wa_template_processing'] = 'Template Pesanan Diproses tidak boleh kosong.';
        }
        if ($notifyPacked === '1' && empty($templatePacked)) {
            $errors['order_wa_template_packed'] = 'Template Pesanan Dikemas tidak boleh kosong.';
        }
        if ($notifyShipped === '1' && empty($templateShipped)) {
            $errors['order_wa_template_shipped'] = 'Template Pesanan Dikirim tidak boleh kosong.';
        }
        if ($notifyCompleted === '1' && empty($templateCompleted)) {
            $errors['order_wa_template_completed'] = 'Template Pesanan Selesai tidak boleh kosong.';
        }
        if ($notifyCancelled === '1' && empty($templateCancelled)) {
            $errors['order_wa_template_cancelled'] = 'Template Pesanan Dibatalkan tidak boleh kosong.';
        }

        if (!empty($errors)) {
            return ['success' => false, 'message' => 'Validasi gagal. Periksa template notifikasi Anda.', 'errors' => $errors];
        }

        $updates = [
            'order_wa.enabled'                  => $enabled,
            'order_wa.test_mode'                => $testMode,
            'order_wa.notify_created'           => $notifyCreated,
            'order_wa.notify_pending_payment'   => $notifyPending,
            'order_wa.notify_payment_success'   => $notifyPaymentSuccess,
            'order_wa.notify_processing'        => $notifyProcessing,
            'order_wa.notify_packed'            => $notifyPacked,
            'order_wa.notify_shipped'           => $notifyShipped,
            'order_wa.notify_completed'         => $notifyCompleted,
            'order_wa.notify_cancelled'         => $notifyCancelled,
            'order_wa.template_created'         => $templateCreated,
            'order_wa.template_pending_payment' => $templatePending,
            'order_wa.template_payment_success' => $templatePaymentSuccess,
            'order_wa.template_processing'      => $templateProcessing,
            'order_wa.template_packed'          => $templatePacked,
            'order_wa.template_shipped'         => $templateShipped,
            'order_wa.template_completed'       => $templateCompleted,
            'order_wa.template_cancelled'       => $templateCancelled,
        ];

        // Jika ada pembaruan token Fonnte dari form ini
        $newToken = trim($data['otp_api_token'] ?? '');
        $tokenUpdated = false;
        if (!empty($newToken) && !SecretService::isMaskedPlaceholder($newToken)) {
            $encryptedToken = SecretService::encrypt($newToken);
            $updates['otp.api_token'] = $encryptedToken;
            $tokenUpdated = true;
        }

        $ok = self::getModel()->updateBatch($updates, $userId);

        if ($ok) {
            self::getAudit()->log(
                'UPDATE_ORDER_WA_CONFIG',
                'order_wa',
                $tokenUpdated ? 'otp.api_token & templates' : 'templates',
                null,
                null,
                'SUCCESS',
                "Pengaturan Notifikasi WhatsApp Pesanan diperbarui (Master: " . ($enabled === '1' ? 'Aktif' : 'Nonaktif') . ", Test Mode: " . ($testMode === '1' ? 'Aktif' : 'Nonaktif') . ($tokenUpdated ? ', Token Fonnte diubah' : '') . ")"
            );

            return ['success' => true, 'message' => 'Pengaturan Notifikasi WhatsApp Pesanan berhasil disimpan.'];
        }

        return ['success' => false, 'message' => 'Gagal menyimpan pengaturan notifikasi ke database.'];
    }
}

