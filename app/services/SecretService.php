<?php
/**
 * SecretService
 * Mengelola enkripsi, dekripsi, dan masking secret (API keys, Token Fonnte)
 * Menggunakan AES-256-CBC dengan IV acak per enkripsi.
 *
 * KEAMANAN:
 * - Kunci enkripsi disimpan terpisah via environment / server config
 * - Tidak pernah menulis nilai plaintext secret ke log
 * - Mendukung masking seragam (••••••••••••)
 */
class SecretService {

    private static string $cipher = 'aes-256-cbc';

    /**
     * Dapatkan encryption key yang konsisten dari environment
     */
    private static function getKey(): string {
        $rawKey = getenv('APP_KEY') 
            ?: (defined('AI_AUTH_SECRET') ? AI_AUTH_SECRET : 'nnaco_secure_system_secret_key_2026');
        return hash('sha256', $rawKey, true); // 32-byte key
    }

    /**
     * Enkripsi string rahasia.
     * Mengembalikan string berformat "enc:v1:<base64(iv.":".ciphertext)>"
     */
    public static function encrypt(string $plainText): string {
        if ($plainText === '') {
            return '';
        }

        // Jangan mengenkripsi ulang jika sudah terenkripsi
        if (str_starts_with($plainText, 'enc:v1:')) {
            return $plainText;
        }

        $ivLength = openssl_cipher_iv_length(self::$cipher);
        $iv = openssl_random_pseudo_bytes($ivLength);

        $encrypted = openssl_encrypt(
            $plainText,
            self::$cipher,
            self::getKey(),
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($encrypted === false) {
            error_log('[SecretService] Gagal mengenkripsi secret data.');
            return $plainText; // fallback
        }

        return 'enc:v1:' . base64_encode($iv . '::' . $encrypted);
    }

    /**
     * Dekripsi string rahasia.
     * Mendukung data terenkripsi "enc:v1:" maupun plaintext legacy.
     */
    public static function decrypt(string $cipherText): string {
        if ($cipherText === '') {
            return '';
        }

        // Jika bukan format terenkripsi kami, anggap plaintext legacy
        if (!str_starts_with($cipherText, 'enc:v1:')) {
            return $cipherText;
        }

        $payload = substr($cipherText, strlen('enc:v1:'));
        $decoded = base64_decode($payload, true);
        if ($decoded === false || !str_contains($decoded, '::')) {
            error_log('[SecretService] Format ciphertext tidak valid saat dekripsi.');
            return '';
        }

        [$iv, $encrypted] = explode('::', $decoded, 2);

        $decrypted = openssl_decrypt(
            $encrypted,
            self::$cipher,
            self::getKey(),
            OPENSSL_RAW_DATA,
            $iv
        );

        if ($decrypted === false) {
            error_log('[SecretService] Gagal mendekripsi secret data.');
            return '';
        }

        return $decrypted;
    }

    /**
     * Masking nilai rahasia untuk tampilan UI
     * Contoh: "gsk_..." -> "••••••••••••"
     */
    public static function maskSecret(string $value): string {
        if (empty($value)) {
            return '';
        }
        return '••••••••••••';
    }

    /**
     * Cek apakah input dari form merupakan placeholder masked
     */
    public static function isMaskedPlaceholder(string $value): bool {
        $trimmed = trim($value);
        if ($trimmed === '') return false;
        // Jika seluruhnya terdiri dari karakter bullet • atau bintang *
        return preg_match('/^[•\*]{4,}$/u', $trimmed) === 1;
    }
}
