<?php
class Middleware {
    public static function auth() {
        if (!isset($_SESSION['user_id'])) {
            Flasher::setFlash('Anda harus login', 'terlebih dahulu.', 'danger');
            header('Location: ' . BASEURL . '/auth/login');
            exit;
        }

        // Auto-heal / normalisasi role sesi ke TitleCase
        if (!empty($_SESSION['user_role']) && is_string($_SESSION['user_role'])) {
            $_SESSION['user_role'] = ucfirst(strtolower(trim($_SESSION['user_role'])));
        }
    }

    public static function role($allowedRole) {
        self::auth();
        
        $rawRole = $_SESSION['user_role'] ?? '';
        // Normalisasi role pengguna secara case-insensitive
        $userRoleLower = strtolower(trim($rawRole));

        if (is_array($allowedRole)) {
            $allowedLower = array_map(function($r) {
                return strtolower(trim($r));
            }, $allowedRole);
            $isAllowed = in_array($userRoleLower, $allowedLower, true);
        } else {
            $isAllowed = ($userRoleLower === strtolower(trim($allowedRole)));
        }

        if (!$isAllowed) {
            header('HTTP/1.0 403 Forbidden');
            if (self::isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error'   => 'Akses ditolak. Anda tidak memiliki izin untuk tindakan ini.'
                ]);
                exit;
            }
            header('Location: ' . BASEURL . '/errorpage/403');
            exit;
        }
    }

    public static function adminOrOwner() {
        self::role(['Admin', 'Owner']);
    }

    public static function guest() {
        if (isset($_SESSION['user_id'])) {
            $role = strtolower($_SESSION['user_role']);
            header('Location: ' . BASEURL . '/' . $role);
            exit;
        }
    }

    public static function isAjaxRequest(): bool {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }

    public static function verifyCsrf() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            
            if (empty($token)) {
                $headers = function_exists('getallheaders') ? getallheaders() : [];
                $token = $headers['X-CSRF-TOKEN'] ?? ($headers['x-csrf-token'] ?? '');
            }

            if (empty($token) && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
                $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
            }
            
            if (empty($token)) {
                $rawBody = file_get_contents('php://input');
                $input = json_decode($rawBody, true);
                $token = $input['csrf_token'] ?? '';
            }

            if (!CSRF::validateToken($token)) {
                header('HTTP/1.0 403 Forbidden');
                if (self::isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'error'   => 'Validasi keamanan (CSRF Token) gagal atau sesi Anda telah kadaluarsa. Silakan muat ulang halaman.'
                    ]);
                    exit;
                }
                Flasher::setFlash('Akses Ditolak', 'Token CSRF tidak valid!', 'danger');
                header('Location: ' . BASEURL . '/errorpage/403');
                exit;
            }
        }
    }
}