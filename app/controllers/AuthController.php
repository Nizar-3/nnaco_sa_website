<?php
class AuthController extends Controller {
    public function index() {
        header('Location: ' . BASEURL . '/auth/login');
        exit;
    }

    // =========================================================
    // LOGIN DENGAN EMAIL & PASSWORD (TETAP DIPERTAHANKAN)
    // =========================================================
    public function login() {
        Middleware::guest(); // Hanya untuk yang belum login

        $data['title'] = 'Login - nnaco_sa';
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Middleware::verifyCsrf();
            
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'] ?? '';

            if (!$email || empty($password)) {
                Flasher::setFlash('Gagal', 'Email tidak valid atau password kosong', 'danger');
                header('Location: ' . BASEURL . '/auth/login');
                exit;
            }

            $userModel = $this->model('UserModel');
            $user = $userModel->getUserByEmail($email);

            if ($user) {
                if ($user['status'] !== 'Active') {
                    Flasher::setFlash('Akun anda', 'tidak aktif', 'danger');
                    header('Location: ' . BASEURL . '/auth/login');
                    exit;
                }

                if (password_verify($password, $user['password'])) {
                    // Prevent Session Fixation
                    session_regenerate_id(true);
                    
                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_theme'] = $user['theme_preference'] ?? 'system';

                    // Redirect berdasarkan role
                    $roleUrl = strtolower($user['role']);
                    header('Location: ' . BASEURL . '/' . $roleUrl);
                    exit;
                } else {
                    Flasher::setFlash('Password', 'salah', 'danger');
                    header('Location: ' . BASEURL . '/auth/login');
                    exit;
                }
            } else {
                Flasher::setFlash('Email', 'tidak ditemukan', 'danger');
                header('Location: ' . BASEURL . '/auth/login');
                exit;
            }
        }

        $this->view('templates/header', $data);
        $this->view('auth/login', $data);
        $this->view('templates/footer', $data);
    }

    // =========================================================
    // LOGIN DENGAN OTP WHATSAPP — HALAMAN UTAMA
    // =========================================================
    public function loginOtp() {
        Middleware::guest();

        $data['title'] = 'Login via WhatsApp OTP - nnaco_sa';

        $this->view('templates/header', $data);
        $this->view('auth/login_otp', $data);
        $this->view('templates/footer', $data);
    }

    // =========================================================
    // API: REQUEST OTP
    // POST /auth/requestOtp
    // =========================================================
    public function requestOtp() {
        Middleware::guest();
        $this->requirePostJson();

        // Cek status aktif OTP dari konfigurasi sistem
        if (!SettingService::isOtpEnabled()) {
            $this->jsonResponse(403, false, 'Layanan autentikasi dengan OTP WhatsApp saat ini sedang dinonaktifkan oleh administrator.');
        }

        $input = $this->getJsonInput();
        $rawPhone = trim($input['phone'] ?? '');

        // Validasi & normalisasi nomor
        $phone = FonnteService::normalizePhone($rawPhone);
        if (!$phone) {
            $this->jsonResponse(400, false, 'Nomor WhatsApp tidak valid. Gunakan format: 08xx, 628xx, atau 8xx.');
        }

        $ip = $this->getClientIp();

        $otpModel = $this->model('OtpModel');

        // Rate limit per IP
        if ($otpModel->isIpRateLimited($ip)) {
            $this->jsonResponse(429, false, 'Terlalu banyak permintaan. Coba lagi dalam 1 jam.');
        }

        // Rate limit per nomor
        if ($otpModel->isPhoneRateLimited($phone)) {
            $this->jsonResponse(429, false, 'Nomor ini sudah terlalu sering meminta OTP. Coba lagi dalam 1 jam.');
        }

        // Cooldown
        $cooldown = $otpModel->getCooldownRemaining($phone);
        if ($cooldown > 0) {
            $this->jsonResponse(429, false, "Tunggu {$cooldown} detik sebelum meminta OTP baru.", ['cooldown' => $cooldown]);
        }

        // Cek apakah nomor terdaftar sebagai user aktif
        $userModel = $this->model('UserModel');
        $user = $userModel->getUserByPhone($phone);

        if (!$user) {
            // Pesan aman: tidak bocorkan apakah nomor terdaftar atau tidak
            $this->jsonResponse(404, false, 'Nomor WhatsApp tidak terdaftar. Silakan daftar terlebih dahulu atau login dengan email.');
        }

        if ($user['status'] !== 'Active') {
            $this->jsonResponse(403, false, 'Akun Anda tidak aktif. Hubungi administrator.');
        }

        // Generate OTP sesuai panjang yang dikonfigurasi dengan secure random
        $otp = $this->generateSecureOtp();
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);

        // Simpan OTP ke database
        $otpModel->createOtp($phone, $otpHash, $ip);

        // Kirim via Fonnte
        $fonnte = new FonnteService();
        $sendResult = $fonnte->sendOtp($phone, $otp);

        // Hapus OTP dari memory sesegera mungkin
        unset($otp);

        if (!$sendResult['success']) {
            $this->jsonResponse(503, false, $sendResult['message']);
        }

        $this->jsonResponse(200, true, 'Kode OTP telah dikirim ke WhatsApp Anda.', [
            'cooldown' => $otpModel->getCooldownSeconds(),
            'expires'  => $otpModel->getExpiryMinutes() * 60,
        ]);
    }

    // =========================================================
    // API: VERIFY OTP
    // POST /auth/verifyOtp
    // =========================================================
    public function verifyOtp() {
        Middleware::guest();
        $this->requirePostJson();

        // Cek status aktif OTP
        if (!SettingService::isOtpEnabled()) {
            $this->jsonResponse(403, false, 'Layanan autentikasi dengan OTP WhatsApp saat ini sedang dinonaktifkan.');
        }

        $input = $this->getJsonInput();
        $rawPhone = trim($input['phone'] ?? '');
        $otpInput = trim($input['otp'] ?? '');

        // Validasi input
        $phone = FonnteService::normalizePhone($rawPhone);
        if (!$phone) {
            $this->jsonResponse(400, false, 'Nomor WhatsApp tidak valid.');
        }

        // Validasi format OTP: digit sesuai konfigurasi aktif
        $otpLen = (int) SettingService::get('otp.otp_length', 6);
        if (!preg_match('/^\d{' . $otpLen . '}$/', $otpInput)) {
            $this->jsonResponse(400, false, "Format OTP tidak valid. OTP harus {$otpLen} digit angka.");
        }

        $otpModel = $this->model('OtpModel');

        // Ambil OTP aktif dari database
        $otpRecord = $otpModel->getActiveOtp($phone);

        if (!$otpRecord) {
            $this->jsonResponse(400, false, 'OTP tidak ditemukan atau sudah kadaluarsa. Minta kode baru.');
        }

        $maxAttempts = $otpModel->getMaxAttempts();

        // Cek batas percobaan
        if ((int)$otpRecord['attempts'] >= $maxAttempts) {
            $otpModel->markAsExpired((int)$otpRecord['id']);
            $this->jsonResponse(429, false, 'Terlalu banyak percobaan. Minta kode OTP baru.');
        }

        // Verifikasi hash OTP
        if (!password_verify($otpInput, $otpRecord['otp_hash'])) {
            // Tambah jumlah percobaan gagal
            $otpModel->incrementAttempts((int)$otpRecord['id']);

            $remaining = $maxAttempts - ((int)$otpRecord['attempts'] + 1);
            if ($remaining <= 0) {
                $otpModel->markAsExpired((int)$otpRecord['id']);
                $this->jsonResponse(429, false, 'Terlalu banyak percobaan. Minta kode OTP baru.');
            }

            $this->jsonResponse(400, false, "OTP salah. Sisa percobaan: {$remaining}.", ['remaining' => $remaining]);
        }

        // OTP VALID — ambil data user dari database (bukan dari frontend!)
        $userModel = $this->model('UserModel');
        $user = $userModel->getUserByPhone($phone);

        if (!$user || $user['status'] !== 'Active') {
            $this->jsonResponse(403, false, 'Akun tidak dapat diakses.');
        }

        // Tandai OTP sebagai terpakai (atomik)
        $marked = $otpModel->markAsUsed((int)$otpRecord['id'], (int)$user['id']);
        if (!$marked) {
            // Race condition: OTP sudah dipakai oleh request lain
            $this->jsonResponse(400, false, 'OTP sudah tidak berlaku. Minta kode baru.');
        }

        // SESSION — hanya dibuat setelah OTP valid dan ditandai
        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_role']  = $user['role'];   // Dari DB, bukan frontend
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_theme'] = $user['theme_preference'] ?? 'system';

        $roleUrl = strtolower($user['role']);

        $this->jsonResponse(200, true, 'Login berhasil!', [
            'redirect' => BASEURL . '/' . $roleUrl,
        ]);
    }

    // =========================================================
    // API: RESEND OTP
    // POST /auth/resendOtp
    // =========================================================
    public function resendOtp() {
        Middleware::guest();
        $this->requirePostJson();

        // Cek status aktif OTP
        if (!SettingService::isOtpEnabled()) {
            $this->jsonResponse(403, false, 'Layanan autentikasi dengan OTP WhatsApp saat ini sedang dinonaktifkan.');
        }

        $input = $this->getJsonInput();
        $rawPhone = trim($input['phone'] ?? '');

        $phone = FonnteService::normalizePhone($rawPhone);
        if (!$phone) {
            $this->jsonResponse(400, false, 'Nomor WhatsApp tidak valid.');
        }

        $ip = $this->getClientIp();
        $otpModel = $this->model('OtpModel');

        // Rate limit per IP
        if ($otpModel->isIpRateLimited($ip)) {
            $this->jsonResponse(429, false, 'Terlalu banyak permintaan. Coba lagi dalam 1 jam.');
        }

        // Rate limit per nomor
        if ($otpModel->isPhoneRateLimited($phone)) {
            $this->jsonResponse(429, false, 'Nomor ini sudah terlalu sering meminta OTP. Coba lagi dalam 1 jam.');
        }

        // Cek cooldown
        $cooldown = $otpModel->getCooldownRemaining($phone);
        if ($cooldown > 0) {
            $this->jsonResponse(429, false, "Tunggu {$cooldown} detik sebelum meminta OTP baru.", ['cooldown' => $cooldown]);
        }

        // Cek user
        $userModel = $this->model('UserModel');
        $user = $userModel->getUserByPhone($phone);
        if (!$user || $user['status'] !== 'Active') {
            $this->jsonResponse(404, false, 'Nomor WhatsApp tidak terdaftar atau akun tidak aktif.');
        }

        // Generate & kirim OTP baru
        $otp = $this->generateSecureOtp();
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);

        $otpModel->createOtp($phone, $otpHash, $ip);

        $fonnte = new FonnteService();
        $sendResult = $fonnte->sendOtp($phone, $otp);
        unset($otp);

        if (!$sendResult['success']) {
            $this->jsonResponse(503, false, $sendResult['message']);
        }

        $this->jsonResponse(200, true, 'Kode OTP baru telah dikirim ke WhatsApp Anda.', [
            'cooldown' => $otpModel->getCooldownSeconds(),
            'expires'  => $otpModel->getExpiryMinutes() * 60,
        ]);
    }

    // =========================================================
    // REGISTER
    // =========================================================
    public function register() {
        Middleware::guest();

        $data['title'] = 'Register - nnaco_sa';
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Middleware::verifyCsrf();
            
            $name = trim($_POST['name'] ?? '');
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            $rawPhone = trim($_POST['phone'] ?? '');
            
            if (empty($name) || !$email || empty($password)) {
                Flasher::setFlash('Gagal', 'Semua field wajib diisi dengan benar', 'danger');
                header('Location: ' . BASEURL . '/auth/register');
                exit;
            }
            
            if (strlen($password) < 6) {
                Flasher::setFlash('Gagal', 'Password minimal 6 karakter', 'danger');
                header('Location: ' . BASEURL . '/auth/register');
                exit;
            }
            
            if ($password !== $confirm) {
                Flasher::setFlash('Gagal', 'Konfirmasi password tidak cocok', 'danger');
                header('Location: ' . BASEURL . '/auth/register');
                exit;
            }

            // Normalisasi nomor WhatsApp jika diisi
            $phone = null;
            if (!empty($rawPhone)) {
                $phone = FonnteService::normalizePhone($rawPhone);
                if (!$phone) {
                    Flasher::setFlash('Gagal', 'Format nomor WhatsApp tidak valid', 'danger');
                    header('Location: ' . BASEURL . '/auth/register');
                    exit;
                }
            }

            $userModel = $this->model('UserModel');

            // Cek apakah nomor WhatsApp sudah terdaftar
            if ($phone) {
                $existingPhone = $userModel->getUserByPhone($phone);
                if ($existingPhone) {
                    Flasher::setFlash('Gagal', 'Nomor WhatsApp sudah terdaftar', 'danger');
                    header('Location: ' . BASEURL . '/auth/register');
                    exit;
                }
            }

            $postData = $_POST;
            $postData['phone'] = $phone;

            if ($userModel->registerCustomer($postData) > 0) {
                Flasher::setFlash('Registrasi berhasil,', 'silakan login', 'success');
                header('Location: ' . BASEURL . '/auth/login');
                exit;
            } else {
                Flasher::setFlash('Registrasi gagal,', 'email mungkin sudah terdaftar', 'danger');
                header('Location: ' . BASEURL . '/auth/register');
                exit;
            }
        }

        $this->view('templates/header', $data);
        $this->view('auth/register', $data);
        $this->view('templates/footer', $data);
    }

    // =========================================================
    // LOGOUT
    // =========================================================
    public function logout() {
        if (!session_id()) {
            session_start();
        }
        
        // Unset all session variables
        $_SESSION = [];
        
        // Destroy the session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy the session
        session_unset();
        session_destroy();
        
        // Prevent browser caching of the redirect
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        
        header('Location: ' . BASEURL . '/auth/login');
        exit;
    }

    public function google() {
        // Mock UI for Google Login
        Flasher::setFlash('Fitur Login dengan Google', 'membutuhkan API Key OAuth dari Google Cloud Console dan akan aktif pada tahap produksi.', 'info');
        header('Location: ' . BASEURL . '/auth/login');
        exit;
    }

    // =========================================================
    // HELPER METHODS (PRIVATE)
    // =========================================================

    /**
     * Generate OTP menggunakan secure random sesuai konfigurasi sistem
     */
    private function generateSecureOtp(?int $length = null): string {
        if ($length === null) {
            $length = (int) SettingService::get('otp.otp_length', 6);
        }
        $length = max(4, min(8, $length));
        $max = (10 ** $length) - 1;
        return str_pad((string) random_int(0, $max), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Ambil IP client yang sesungguhnya
     */
    private function getClientIp(): string {
        $headers = [
            'HTTP_CF_CONNECTING_IP',   // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];
        foreach ($headers as $h) {
            if (!empty($_SERVER[$h])) {
                $ip = trim(explode(',', $_SERVER[$h])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }

    /**
     * Wajibkan POST + JSON content type, atau kembalikan 405/400
     */
    private function requirePostJson(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(405, false, 'Method not allowed.');
        }
        // Verifikasi CSRF via header atau body JSON
        Middleware::verifyCsrf();
    }

    /**
     * Ambil dan parse JSON body dari request
     */
    private function getJsonInput(): array {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            return json_decode($raw, true) ?? [];
        }
        // Fallback ke POST form data
        return $_POST;
    }

    /**
     * Kirim respons JSON dan hentikan eksekusi
     *
     * @param int    $httpCode
     * @param bool   $success
     * @param string $message
     * @param array  $extra   Data tambahan (opsional)
     */
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
