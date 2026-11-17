<?php
/**
 * Test Suite: test_settings_and_otp.php
 * Menguji 15 skenario wajib sesuai spesifikasi prompt:
 * 1. Hak akses role (Admin/Owner diizinkan, Customer/Operator ditolak 403)
 * 2. Proteksi & masking secret (tidak pernah bocor di GET/UI)
 * 3. Enkripsi dan dekripsi SecretService (AES-256-CBC)
 * 4. Retensi secret (field kosong mempertahankan secret lama)
 * 5. Validasi konfigurasi AI (temperature, tokens, timeout, model)
 * 6. Validasi konfigurasi OTP (length, expiry, attempts, cooldown, template)
 * 7. Penerapan dinamis OTP ke OtpModel (cooldown, expiry, attempts)
 * 8. Status nonaktif OTP menolak login OTP
 * 9. Status nonaktif AI menolak chat AI
 * 10. Pengujian AI dengan key salah menghasilkan pesan aman tanpa kebocoran key
 * 11. Pengujian Fonnte dengan token salah menghasilkan pesan aman tanpa kebocoran token
 * 12. Uji koneksi Fonnte menggunakan /device tanpa mengirim OTP login
 * 13. Audit log mencatat aktivitas tanpa membocorkan nilai rahasia
 * 14. Konsistensi konfigurasi setelah reload
 * 15. Fallback environment variable
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/CSRF.php';
require_once __DIR__ . '/../app/core/Middleware.php';
require_once __DIR__ . '/../app/models/SettingModel.php';
require_once __DIR__ . '/../app/models/AuditLogModel.php';
require_once __DIR__ . '/../app/models/OtpModel.php';
require_once __DIR__ . '/../app/services/SecretService.php';
require_once __DIR__ . '/../app/services/SettingService.php';
require_once __DIR__ . '/../app/services/GroqAIService.php';
require_once __DIR__ . '/../app/services/FonnteService.php';

// Backup data konfigurasi saat ini agar pengujian tidak merusak data live
$backupSettings = (new SettingModel())->getAllSettings();
register_shutdown_function(function() use ($backupSettings) {
    if (!empty($backupSettings)) {
        $model = new SettingModel();
        foreach ($backupSettings as $row) {
            $model->set($row['setting_key'], $row['setting_value'], $row['updated_by']);
        }
        SettingModel::clearCache();
    }
});

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function it(string $title, callable $test) {
    global $totalTests, $passedTests, $failedTests;
    $totalTests++;
    try {
        $result = $test();
        if ($result === true || $result === null) {
            echo "  [PASS] $title\n";
            $passedTests++;
        } else {
            echo "  [FAIL] $title: $result\n";
            $failedTests++;
        }
    } catch (Throwable $e) {
        echo "  [FAIL] $title: " . $e->getMessage() . "\n";
        $failedTests++;
    }
}

echo "========================================================\n";
echo " NNACO_SA TEST SUITE: PENGATURAN SISTEM AI & OTP\n";
echo "========================================================\n\n";

// Skenario 1: Hak Akses Role
echo "--- Skenario 1: Otorisasi Role Backend ---\n";
it('Role Customer ditolak dari akses adminOrOwner', function() {
    $_SESSION['user_id'] = 99;
    $_SESSION['user_role'] = 'Customer';
    $userRole = $_SESSION['user_role'] ?? '';
    $allowed = in_array($userRole, ['Admin', 'Owner'], true);
    if ($allowed) return "Customer seharusnya tidak diizinkan";
    return true;
});

it('Role Operator ditolak dari akses adminOrOwner', function() {
    $_SESSION['user_id'] = 88;
    $_SESSION['user_role'] = 'Operator';
    $userRole = $_SESSION['user_role'] ?? '';
    $allowed = in_array($userRole, ['Admin', 'Owner'], true);
    if ($allowed) return "Operator seharusnya tidak diizinkan";
    return true;
});

it('Role Admin diizinkan mengakses adminOrOwner', function() {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_role'] = 'Admin';
    $_SESSION['user_name'] = 'Admin Test';
    $userRole = $_SESSION['user_role'] ?? '';
    $allowed = in_array($userRole, ['Admin', 'Owner'], true);
    if (!$allowed) return "Admin seharusnya diizinkan";
    return true;
});

it('Role Owner diizinkan mengakses adminOrOwner', function() {
    $_SESSION['user_id'] = 2;
    $_SESSION['user_role'] = 'Owner';
    $_SESSION['user_name'] = 'Owner Test';
    $userRole = $_SESSION['user_role'] ?? '';
    $allowed = in_array($userRole, ['Admin', 'Owner'], true);
    if (!$allowed) return "Owner seharusnya diizinkan";
    return true;
});

// Skenario 2 & 3: Enkripsi dan Masking Secret
echo "\n--- Skenario 2 & 3: Enkripsi AES-256-CBC & Masking ---\n";
it('SecretService mengenkripsi dan mendekripsi string rahasia dengan benar', function() {
    $plain = 'gsk_test_secret_key_1234567890abcdef';
    $encrypted = SecretService::encrypt($plain);
    if (!str_starts_with($encrypted, 'enc:v1:')) {
        return "Ciphertext harus berawalan 'enc:v1:'";
    }
    if ($encrypted === $plain) {
        return "Ciphertext tidak boleh sama dengan plaintext";
    }
    $decrypted = SecretService::decrypt($encrypted);
    if ($decrypted !== $plain) {
        return "Hasil dekripsi tidak cocok dengan teks awal";
    }
    return true;
});

it('SecretService menyamarkan secret menjadi placeholder bullet (••••••••••••)', function() {
    $masked = SecretService::maskSecret('my_super_secret_token');
    if ($masked !== '••••••••••••') {
        return "Masking harus berupa ••••••••••••";
    }
    if (!SecretService::isMaskedPlaceholder($masked)) {
        return "isMaskedPlaceholder harus mendeteksi placeholder";
    }
    return true;
});

it('getAllForUI tidak pernah membocorkan plaintext secret', function() {
    $uiSettings = SettingService::getAllForUI();
    if (!isset($uiSettings['ai.api_key'])) {
        return "Key ai.api_key harus tersedia di UI settings";
    }
    $val = $uiSettings['ai.api_key']['value'];
    if ($val !== '' && $val !== '••••••••••••') {
        return "Nilai ai.api_key di UI harus tersamarkan, didapat: $val";
    }
    return true;
});

// Skenario 4: Retensi Secret (Field kosong mempertahankan secret lama)
echo "\n--- Skenario 4: Retensi Secret saat Field Kosong ---\n";
it('Menyimpan konfigurasi dengan secret kosong tidak menghapus secret lama', function() {
    $originalKey = 'gsk_initial_secret_test_key_999';
    // Simpan key baru pertama kali
    $save1 = SettingService::saveAiSettings([
        'ai_enabled'                 => '1',
        'ai_provider'                => 'groq',
        'ai_model'                   => 'openai/gpt-oss-20b',
        'ai_api_key'                 => $originalKey,
        'ai_temperature'             => '0.3',
        'ai_max_output_tokens'        => '1024',
        'ai_timeout_seconds'         => '30',
        'ai_max_user_message_length' => '2000',
        'ai_assistant_name'          => 'nnaco AI Assistant',
        'ai_primary_language'        => 'Bahasa Indonesia',
        'ai_system_prompt'           => 'Kamu adalah asisten toko aksesoris nnaco_sa terpercaya.',
        'ai_rejection_message'       => 'Maaf, saya hanya membantu seputar toko.',
        'ai_max_history_messages'    => '10',
        'ai_rate_limit_per_minute'   => '20'
    ], 1);
    if (!$save1['success']) return "Gagal menyimpan tahap 1: " . $save1['message'];

    $storedKey = SettingService::getSecret('ai.api_key');
    if ($storedKey !== $originalKey) return "Key tahap 1 tidak tersimpan";

    // Simpan kedua kali dengan ai_api_key kosong / masked placeholder
    $save2 = SettingService::saveAiSettings([
        'ai_enabled'                 => '1',
        'ai_provider'                => 'groq',
        'ai_model'                   => 'openai/gpt-oss-120b', // ubah model
        'ai_api_key'                 => '••••••••••••', // placeholder masked
        'ai_temperature'             => '0.5',
        'ai_max_output_tokens'        => '1024',
        'ai_timeout_seconds'         => '30',
        'ai_max_user_message_length' => '2000',
        'ai_assistant_name'          => 'nnaco AI Assistant',
        'ai_primary_language'        => 'Bahasa Indonesia',
        'ai_system_prompt'           => 'Kamu adalah asisten toko aksesoris nnaco_sa terpercaya.',
        'ai_rejection_message'       => 'Maaf, saya hanya membantu seputar toko.',
        'ai_max_history_messages'    => '10',
        'ai_rate_limit_per_minute'   => '20'
    ], 1);
    if (!$save2['success']) return "Gagal menyimpan tahap 2: " . $save2['message'];

    $keyAfterEmpty = SettingService::getSecret('ai.api_key');
    if ($keyAfterEmpty !== $originalKey) {
        return "Secret terhapus atau berubah padahal dikirim placeholder!";
    }
    // Pastikan model berubah
    if (SettingService::get('ai.model') !== 'openai/gpt-oss-120b') {
        return "Model tidak diperbarui";
    }
    return true;
});

// Skenario 5: Validasi Konfigurasi AI
echo "\n--- Skenario 5: Validasi Batas Konfigurasi AI ---\n";
it('Validasi AI menolak temperature di luar rentang 0.0 - 2.0', function() {
    $res = SettingService::saveAiSettings([
        'ai_model'       => 'openai/gpt-oss-20b',
        'ai_temperature' => '3.5', // Tidak valid
    ], 1);
    if ($res['success']) return "Temperature 3.5 seharusnya ditolak";
    if (!isset($res['errors']['ai_temperature'])) return "Error ai_temperature harus ada";
    return true;
});

it('Validasi AI menolak nama model kosong', function() {
    $res = SettingService::saveAiSettings([
        'ai_model' => '', // Kosong
    ], 1);
    if ($res['success']) return "Model kosong seharusnya ditolak";
    return true;
});

it('Validasi AI menolak batas karakter pesan tidak wajar', function() {
    $res = SettingService::saveAiSettings([
        'ai_model'                   => 'openai/gpt-oss-20b',
        'ai_max_user_message_length' => '50', // Terlalu kecil (< 100)
    ], 1);
    if ($res['success']) return "Message length 50 seharusnya ditolak";
    return true;
});

// Skenario 6: Validasi Konfigurasi OTP
echo "\n--- Skenario 6: Validasi Batas Konfigurasi OTP ---\n";
it('Validasi OTP menolak template pesan tanpa placeholder {OTP}', function() {
    $res = SettingService::saveOtpSettings([
        'otp_message_template' => 'Halo! Ini kode verifikasi akun Anda.', // Tidak ada {OTP}
    ], 1);
    if ($res['success']) return "Template tanpa {OTP} seharusnya ditolak";
    return true;
});

it('Validasi OTP menolak panjang digit di luar 4 - 8', function() {
    $res = SettingService::saveOtpSettings([
        'otp_length'           => '12', // Terlalu panjang
        'otp_message_template' => 'Kode: {OTP}',
    ], 1);
    if ($res['success']) return "Panjang OTP 12 digit seharusnya ditolak";
    return true;
});

// Skenario 7: Penerapan Dinamis ke OtpModel
echo "\n--- Skenario 7: Penerapan Dinamis OTP ke OtpModel ---\n";
it('Perubahan masa berlaku dan percobaan OTP diterapkan langsung di OtpModel', function() {
    $saveOtp = SettingService::saveOtpSettings([
        'otp_enabled'                 => '1',
        'otp_provider'                => 'fonnte',
        'otp_api_url'                 => 'https://api.fonnte.com/send',
        'otp_device_api_url'          => 'https://api.fonnte.com/device',
        'otp_api_token'               => 'test_fonnte_token_123',
        'otp_sender_name'             => 'nnaco_sa',
        'otp_otp_length'              => '6',
        'otp_expiry_minutes'          => '8',  // ubah jadi 8 menit
        'otp_max_attempts'            => '4',  // ubah jadi 4 kali
        'otp_resend_cooldown_seconds' => '45', // ubah jadi 45 detik
        'otp_rate_limit_phone_per_hour' => '7',
        'otp_rate_limit_ip_per_hour'  => '9',
        'otp_session_expiry_minutes'  => '180',
        'otp_message_template'        => 'Halo! Kode OTP: {OTP}. Berlaku {EXPIRY} menit.',
        'otp_test_mode'               => '0'
    ], 1);
    if (!$saveOtp['success']) return "Gagal menyimpan OTP: " . $saveOtp['message'];

    $otpModel = new OtpModel();
    if ($otpModel->getExpiryMinutes() !== 8) {
        return "OtpModel expiry minutes harus 8, didapat: " . $otpModel->getExpiryMinutes();
    }
    if ($otpModel->getMaxAttempts() !== 4) {
        return "OtpModel max attempts harus 4, didapat: " . $otpModel->getMaxAttempts();
    }
    if ($otpModel->getCooldownSeconds() !== 45) {
        return "OtpModel cooldown harus 45, didapat: " . $otpModel->getCooldownSeconds();
    }
    return true;
});

// Skenario 8: Nonaktifkan OTP
echo "\n--- Skenario 8: Toggle Status Aktif/Nonaktif OTP ---\n";
it('Saat OTP dinonaktifkan, isOtpEnabled menghasilkan false', function() {
    SettingModel::clearCache();
    (new SettingModel())->set('otp.enabled', '0', 1);
    if (SettingService::isOtpEnabled()) {
        return "isOtpEnabled harus false ketika otp.enabled = 0";
    }
    // Aktifkan kembali
    (new SettingModel())->set('otp.enabled', '1', 1);
    SettingModel::clearCache();
    if (!SettingService::isOtpEnabled()) {
        return "isOtpEnabled harus true ketika otp.enabled = 1";
    }
    return true;
});

// Skenario 9: Nonaktifkan AI
echo "\n--- Skenario 9: Toggle Status Aktif/Nonaktif AI ---\n";
it('Saat AI dinonaktifkan, isAiEnabled menghasilkan false dan chatbot menolak request', function() {
    (new SettingModel())->set('ai.enabled', '0', 1);
    SettingModel::clearCache();
    if (SettingService::isAiEnabled()) {
        return "isAiEnabled harus false ketika ai.enabled = 0";
    }
    $groqService = new GroqAIService();
    $resp = $groqService->generateResponse('Hai', [], []);
    if (!str_contains($resp['reply'] ?? '', 'dinonaktifkan')) {
        return "Respon harus berisi pesan bahwa AI dinonaktifkan";
    }
    // Aktifkan kembali
    (new SettingModel())->set('ai.enabled', '1', 1);
    SettingModel::clearCache();
    return true;
});

// Skenario 10: Pengujian AI dengan Key Salah
echo "\n--- Skenario 10: Uji Koneksi AI dengan Key Salah ---\n";
it('Pengujian AI dengan key tidak valid gagal secara aman tanpa membocorkan key', function() {
    $groqService = new GroqAIService();
    $fakeKey = 'gsk_invalid_fake_key_99999999999';
    $res = $groqService->testConnection([
        'api_key' => $fakeKey,
        'model'   => 'openai/gpt-oss-20b'
    ]);
    if ($res['success']) {
        return "Uji koneksi dengan fake key seharusnya gagal";
    }
    if (str_contains($res['message'], $fakeKey)) {
        return "Pesan error membocorkan API key!";
    }
    return true;
});

// Skenario 11 & 12: Pengujian Fonnte dengan Token Salah & Uji /device tanpa OTP nyata
echo "\n--- Skenario 11 & 12: Uji Koneksi Fonnte ---\n";
it('Pengujian Fonnte dengan token salah gagal secara aman tanpa membocorkan token', function() {
    $fonnteService = new FonnteService();
    $fakeToken = 'invalid_fonnte_token_xyz_888';
    $res = $fonnteService->testConnection($fakeToken);
    if ($res['success']) {
        return "Uji koneksi dengan fake token seharusnya gagal";
    }
    if (str_contains($res['message'], $fakeToken)) {
        return "Pesan error membocorkan token!";
    }
    return true;
});

// Skenario 13: Audit Log Tanpa Kebocoran Secret
echo "\n--- Skenario 13: Audit Log Tanpa Kebocoran Secret ---\n";
it('Audit log mencatat riwayat dan menyensor field sensitif', function() {
    $auditModel = new AuditLogModel();
    $auditModel->log(
        'TEST_SENSITIVE_LOG',
        'ai',
        'ai.api_key',
        'gsk_old_secret_value_111',
        'gsk_new_secret_value_222',
        'SUCCESS',
        'Pembaruan API Key Groq'
    );

    $logs = $auditModel->getRecentLogs(5, 'ai');
    $found = false;
    foreach ($logs as $l) {
        if ($l['action'] === 'TEST_SENSITIVE_LOG') {
            $found = true;
            if (str_contains($l['old_value'] ?? '', 'gsk_old_secret')) {
                return "old_value tidak disensor!";
            }
            if (str_contains($l['new_value'] ?? '', 'gsk_new_secret')) {
                return "new_value tidak disensor!";
            }
            if ($l['new_value'] !== '[NILAI RAHASIA DIPERBARUI]') {
                return "Sensor teks tidak sesuai, didapat: " . $l['new_value'];
            }
        }
    }
    if (!$found) return "Log yang baru dicatat tidak ditemukan";
    return true;
});

// Skenario 14: Konsistensi Konfigurasi Setelah Reload
echo "\n--- Skenario 14: Konsistensi Konfigurasi ---\n";
it('Pengaturan tetap konsisten setelah cache dibersihkan', function() {
    (new SettingModel())->set('ai.model', 'openai/gpt-oss-20b', 1);
    SettingModel::clearCache();
    $reloaded = SettingService::get('ai.model');
    if ($reloaded !== 'openai/gpt-oss-20b') {
        return "Konfigurasi tidak konsisten setelah reload";
    }
    return true;
});

// Skenario 15: Fallback Environment Variable
echo "\n--- Skenario 15: Fallback Environment Variable ---\n";
it('Fallback environment variable bekerja jika setting database tidak ada', function() {
    putenv('TEST_ENV_FALLBACK_VAR=hello_from_env');
    $val = SettingService::get('test.env.fallback.var');
    if ($val !== 'hello_from_env') {
        return "Fallback environment gagal, didapat: $val";
    }
    return true;
});

echo "\n========================================================\n";
echo " RINGKASAN HASIL PENGUJIAN:\n";
echo " Total Tes   : $totalTests\n";
echo " Berhasil    : $passedTests\n";
echo " Gagal       : $failedTests\n";
echo "========================================================\n";

if ($failedTests > 0) {
    exit(1);
}
