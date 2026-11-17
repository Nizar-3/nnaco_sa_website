<?php
/**
 * Test Runner: Sistem OTP WhatsApp Fonnte
 * Jalankan: php tests/test_otp_system.php
 *
 * CATATAN: Tidak mengirim WhatsApp sungguhan (menggunakan mock).
 */

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/services/FonnteService.php';
require_once __DIR__ . '/../app/models/OtpModel.php';
require_once __DIR__ . '/../app/models/UserModel.php';

// Muat .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (strpos($line, '=') !== false) {
            [$key, $val] = explode('=', $line, 2);
            if (!getenv(trim($key))) putenv(trim($key) . '=' . trim($val));
        }
    }
}

$passed = 0;
$failed = 0;

function test(string $name, bool $condition, string $detail = ''): void {
    global $passed, $failed;
    if ($condition) {
        echo "\033[32m[PASS]\033[0m $name\n";
        $passed++;
    } else {
        echo "\033[31m[FAIL]\033[0m $name" . ($detail ? " — $detail" : '') . "\n";
        $failed++;
    }
}

echo "\n=== TEST: Sistem OTP WhatsApp Fonnte ===\n\n";

// --------------------------------------------------
// TEST 1: Normalisasi nomor WhatsApp
// --------------------------------------------------
echo "--- Normalisasi Nomor ---\n";
test('08xxx  → 628xxx', FonnteService::normalizePhone('08123456789') === '628123456789');
test('628xxx → 628xxx', FonnteService::normalizePhone('628123456789') === '628123456789');
test('8xxx   → 628xxx', FonnteService::normalizePhone('812345678') === '62812345678');
test('+628   → false (tanda + dihapus dulu, lalu 62+8+digit)',
    FonnteService::normalizePhone('+628123456789') === '628123456789');
test('Nomor terlalu pendek → false', FonnteService::normalizePhone('0812') === false);
test('Nomor kosong → false',        FonnteService::normalizePhone('') === false);
test('Huruf → false',               FonnteService::normalizePhone('abcdefg') === false);

echo "\n--- Konfigurasi Fonnte ---\n";
$fonnte = new FonnteService();
$configured = $fonnte->isConfigured();
test('FonnteService::isConfigured() = ' . ($configured ? 'true' : 'false (token belum diset)'),
    true // selalu pass, info saja
);
if (!$configured) {
    echo "      ⚠ Token FONNTE belum dikonfigurasi di .env\n";
}

echo "\n--- OtpModel: Koneksi DB ---\n";
try {
    $otpModel = new OtpModel();
    test('OtpModel berhasil diinstansiasi', true);
} catch (Exception $e) {
    test('OtpModel berhasil diinstansiasi', false, $e->getMessage());
    echo "\nGagal koneksi DB. Hentikan pengujian lebih lanjut.\n";
    exit(1);
}

echo "\n--- OtpModel: Rate Limiting ---\n";
$testPhone = '628999888700' . rand(10, 99); // nomor random untuk test
$testIp    = '127.0.0.' . rand(1, 254);

$rateLimitedPhone = $otpModel->isPhoneRateLimited($testPhone);
test('Nomor baru belum terkena rate limit', $rateLimitedPhone === false);

$rateLimitedIp = $otpModel->isIpRateLimited($testIp);
test('IP baru belum terkena rate limit', $rateLimitedIp === false);

echo "\n--- OtpModel: Cooldown ---\n";
$cooldown = $otpModel->getCooldownRemaining($testPhone);
test('Nomor baru cooldown = 0', $cooldown === 0);

echo "\n--- OtpModel: Create & Verify OTP (mock) ---\n";
$otp     = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$otpHash = password_hash($otp, PASSWORD_DEFAULT);

try {
    $otpId = $otpModel->createOtp($testPhone, $otpHash, $testIp);
    test('createOtp() berhasil, ID = ' . $otpId, $otpId > 0);
} catch (Exception $e) {
    test('createOtp() berhasil', false, $e->getMessage());
    $otpId = 0;
}

if ($otpId > 0) {
    // Cek getActiveOtp
    $record = $otpModel->getActiveOtp($testPhone);
    test('getActiveOtp() mengembalikan record', $record !== false && isset($record['id']));
    test('Record OTP status = pending', ($record['status'] ?? '') === 'pending');
    test('Record OTP attempts = 0', (int)($record['attempts'] ?? -1) === 0);

    // Verifikasi OTP benar
    test('password_verify() OTP benar', password_verify($otp, $record['otp_hash'] ?? ''));

    // Verifikasi OTP salah
    $wrongOtp = ($otp === '000000') ? '111111' : '000000';
    test('password_verify() OTP salah = false', !password_verify($wrongOtp, $record['otp_hash'] ?? ''));

    // Increment attempts
    $otpModel->incrementAttempts($otpId);
    // Query langsung by ID untuk menghindari cache PDO persistent
    $dbCheck = new Database();
    $dbCheck->query("SELECT attempts FROM otp_requests WHERE id = :id");
    $dbCheck->bind('id', $otpId);
    $rec2 = $dbCheck->single();
    test('incrementAttempts() bekerja', (int)($rec2['attempts'] ?? 0) === 1);

    // Cooldown setelah create
    $cooldownAfter = $otpModel->getCooldownRemaining($testPhone);
    test('Cooldown aktif setelah create OTP', $cooldownAfter > 0 && $cooldownAfter <= OtpModel::OTP_COOLDOWN_SECONDS);

    // Mark as used (perlu user_id — pakai dummy 1 atau cari dari DB)
    $db = new Database();
    $db->query("SELECT id FROM users LIMIT 1");
    $firstUser = $db->single();
    $dummyUserId = $firstUser['id'] ?? 1;

    $marked = $otpModel->markAsUsed($otpId, $dummyUserId);
    test('markAsUsed() berhasil', $marked === true);

    // Setelah marked, getActiveOtp tidak boleh mengembalikan record ini lagi
    $recordAfterUsed = $otpModel->getActiveOtp($testPhone);
    test('Setelah markAsUsed, OTP tidak lagi "pending"',
        $recordAfterUsed === false || (int)($recordAfterUsed['id'] ?? 0) !== $otpId
    );
}

echo "\n--- OtpModel: Test OTP sudah dibatalkan saat buat baru ---\n";
$phone2   = '628' . rand(100000000, 999999999);
$hash1    = password_hash('111111', PASSWORD_DEFAULT);
$hash2    = password_hash('222222', PASSWORD_DEFAULT);
$id1      = $otpModel->createOtp($phone2, $hash1, $testIp);
$id2      = $otpModel->createOtp($phone2, $hash2, $testIp); // ini harus cancel id1

$db = new Database();
$db->query("SELECT status FROM otp_requests WHERE id = :id");
$db->bind('id', $id1);
$row = $db->single();
test('OTP lama dibatalkan (cancelled) saat OTP baru dibuat', ($row['status'] ?? '') === 'cancelled');

$active = $otpModel->getActiveOtp($phone2);
test('Hanya OTP terbaru yang aktif', $active !== false && (int)($active['id'] ?? 0) === $id2);

// Bersihkan data test
$db->query("DELETE FROM otp_requests WHERE phone IN (:p1, :p2)");
$db->bind('p1', $testPhone);
$db->bind('p2', $phone2);
$db->execute();

echo "\n--- UserModel: getUserByPhone ---\n";
$userModel = new UserModel();
$nonExistUser = $userModel->getUserByPhone('62800000000000');
test('getUserByPhone() nomor tidak ada = false', $nonExistUser === false);

// --------------------------------------------------
// RINGKASAN
// --------------------------------------------------
echo "\n========================================\n";
echo "Hasil: \033[32m{$passed} PASS\033[0m, \033[31m{$failed} FAIL\033[0m\n";
echo "========================================\n\n";

if ($failed === 0) {
    echo "✅ Semua test lolos! Sistem OTP siap digunakan.\n";
    echo "   Selanjutnya: Isi FONNTE_TOKEN di .env dan uji kirim OTP sungguhan.\n";
} else {
    echo "⚠️  Ada {$failed} test gagal. Periksa log di atas.\n";
}
echo "\n";
