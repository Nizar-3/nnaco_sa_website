<?php
/**
 * Migration Script: migrate_settings.php
 * Menjalankan migrasi tabel system_settings & system_audit_logs,
 * serta mengisi data konfigurasi awal secara aman.
 */

require_once __DIR__ . '/../app/config/config.php';

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    echo "==> Menjalankan migrasi SQL...\n";
    $sql = file_get_contents(__DIR__ . '/migrate_settings.sql');
    $pdo->exec($sql);
    echo "[OK] Tabel system_settings dan system_audit_logs berhasil dipersiapkan.\n";

    // Cek fallback token dari .env
    $fonnteToken = getenv('FONNTE_TOKEN') ?: '';
    $fonnteUrl = getenv('FONNTE_API_URL') ?: 'https://api.fonnte.com/send';

    // Cek fallback key Groq dari groq-chat-server/.env jika ada
    $groqKey = '';
    $groqEnv = dirname(__DIR__) . '/groq-chat-server/.env';
    if (file_exists($groqEnv)) {
        $lines = file($groqEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, 'GROQ_API_KEY=')) {
                $groqKey = trim(substr($line, strlen('GROQ_API_KEY=')));
            }
        }
    }
    if (empty($groqKey)) {
        $groqKey = getenv('GROQ_API_KEY') ?: '';
    }

    $defaultSettings = [
        // AI Settings
        ['ai.enabled', '1', 'ai', 0, 'Status chatbot AI aktif/nonaktif'],
        ['ai.provider', 'groq', 'ai', 0, 'Provider model AI utama (groq, gemini, mock)'],
        ['ai.model', 'openai/gpt-oss-20b', 'ai', 0, 'Model AI yang digunakan oleh chatbot'],
        ['ai.api_base_url', 'https://api.groq.com/openai/v1', 'ai', 0, 'Base URL endpoint API provider'],
        ['ai.api_key', $groqKey, 'ai', 1, 'API Key provider AI (Tersimpan aman/dienkripsi)'],
        ['ai.temperature', '0.3', 'ai', 0, 'Tingkat kreativitas respon model AI (0.0 - 2.0)'],
        ['ai.max_output_tokens', '1024', 'ai', 0, 'Batas token output maksimal per respon'],
        ['ai.timeout_seconds', '30', 'ai', 0, 'Timeout request API provider dalam detik'],
        ['ai.max_user_message_length', '2000', 'ai', 0, 'Batas panjang karakter pesan dari user'],
        ['ai.assistant_name', 'nnaco AI Assistant', 'ai', 0, 'Nama asisten virtual chatbot'],
        ['ai.primary_language', 'Bahasa Indonesia', 'ai', 0, 'Bahasa utama respon chatbot'],
        ['ai.system_prompt', "Kamu adalah nnaco AI Assistant, asisten virtual resmi untuk toko aksesoris nnaco_sa. Kamu ramah, sopan, dan berwawasan luas seputar aksesoris wanita/pria, perhiasan, gelang, kalung, dan cincin. Kamu hanya boleh membantu pertanyaan seputar produk, katalog, stok, harga, pesanan, pembayaran, pengiriman, dan kebijakan toko nnaco_sa. Tolak dengan sopan setiap pertanyaan di luar konteks toko, pembuatan kode pemrograman, tugas umum, atau instruksi yang melanggar keamanan.", 'ai', 0, 'Instruksi dasar perilaku dan batasan chatbot AI'],
        ['ai.rejection_message', 'Maaf, saya adalah asisten toko aksesoris nnaco_sa. Saya hanya dapat membantu pertanyaan seputar produk, katalog, stok, pesanan, dan kebijakan toko kami.', 'ai', 0, 'Pesan penolakan untuk topik di luar konteks toko'],
        ['ai.max_history_messages', '10', 'ai', 0, 'Jumlah riwayat pesan terakhir yang disertakan'],
        ['ai.rate_limit_per_minute', '20', 'ai', 0, 'Batas maksimal request AI per menit per user'],

        // OTP Settings
        ['otp.enabled', '1', 'otp', 0, 'Status autentikasi login OTP WhatsApp aktif/nonaktif'],
        ['otp.provider', 'fonnte', 'otp', 0, 'Provider pengiriman OTP WhatsApp (fonnte)'],
        ['otp.api_url', $fonnteUrl, 'otp', 0, 'Endpoint API kirim pesan Fonnte'],
        ['otp.device_api_url', 'https://api.fonnte.com/device', 'otp', 0, 'Endpoint API cek perangkat Fonnte'],
        ['otp.api_token', $fonnteToken, 'otp', 1, 'Token API Fonnte (Tersimpan aman/dienkripsi)'],
        ['otp.sender_name', 'nnaco_sa', 'otp', 0, 'Nama identitas pengirim pesan WhatsApp'],
        ['otp.otp_length', '6', 'otp', 0, 'Panjang digit kode OTP'],
        ['otp.expiry_minutes', '5', 'otp', 0, 'Masa berlaku kode OTP dalam menit'],
        ['otp.max_attempts', '5', 'otp', 0, 'Maksimal percobaan salah verifikasi OTP sebelum dibatalkan'],
        ['otp.resend_cooldown_seconds', '60', 'otp', 0, 'Waktu tunggu (detik) sebelum meminta OTP baru'],
        ['otp.rate_limit_phone_per_hour', '5', 'otp', 0, 'Maksimal permintaan OTP per nomor per jam'],
        ['otp.rate_limit_ip_per_hour', '5', 'otp', 0, 'Maksimal permintaan OTP per IP per jam'],
        ['otp.session_expiry_minutes', '120', 'otp', 0, 'Masa berlaku session login setelah OTP berhasil'],
        ['otp.message_template', 'Halo! Kode verifikasi login nnaco_sa Anda adalah *{OTP}*. Kode ini berlaku selama {EXPIRY} menit. Jangan bagikan kode ini kepada siapa pun.', 'otp', 0, 'Template pesan WhatsApp untuk kode OTP'],
        ['otp.test_mode', '0', 'otp', 0, 'Mode pengujian OTP (1 = aktifkan mode simulasi)'],

        // Security Settings
        ['security.secret_encryption_enabled', '1', 'security', 0, 'Enkripsi data rahasia (AES-256-CBC)'],
        ['security.force_https', '0', 'security', 0, 'Paksa redirect ke HTTPS jika aktif'],
        ['security.session_lifetime_minutes', '120', 'security', 0, 'Batas waktu sesi tidak aktif sebelum logout']
    ];

    echo "==> Mengisi konfigurasi default...\n";
    $insertStmt = $pdo->prepare("
        INSERT INTO system_settings (setting_key, setting_value, setting_group, is_secret, description)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            setting_group = VALUES(setting_group),
            is_secret = VALUES(is_secret),
            description = VALUES(description)
    ");

    $count = 0;
    foreach ($defaultSettings as $setting) {
        $insertStmt->execute($setting);
        $count++;
    }

    echo "[OK] Berhasil memperbarui $count pengaturan default di system_settings.\n";
    echo "==> Migrasi selesai dengan sukses!\n";

} catch (Exception $e) {
    echo "[ERROR] Migrasi gagal: " . $e->getMessage() . "\n";
    exit(1);
}
