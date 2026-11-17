<?php
class ProfileController extends Controller {
    public function index() {
        Middleware::auth();

        $userId = $_SESSION['user_id'];
        $userModel = $this->model('UserModel');
        $user = $userModel->getUserById($userId);

        if (!$user) {
            header('Location: ' . BASEURL . '/auth/logout');
            exit;
        }

        $data['title'] = 'Kelola Profil - nnaco_sa';
        $data['user'] = $user;

        // Extra stats if Customer
        if ($user['role'] === 'Customer') {
            try {
                $orderModel = $this->model('OrderModel');
                $data['total_orders'] = count($orderModel->getOrdersByUserId($userId));
            } catch (Exception $e) {
                $data['total_orders'] = 0;
            }
        }

        $this->view('templates/header', $data);
        $this->view('profile/index', $data);
        $this->view('templates/footer', $data);
    }

    public function update() {
        Middleware::auth();
        Middleware::verifyCsrf();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if (empty($name) || empty($email)) {
                Flasher::setFlash('Gagal memperbarui profil:', 'Nama dan email tidak boleh kosong.', 'danger');
                header('Location: ' . BASEURL . '/profile');
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Flasher::setFlash('Gagal memperbarui profil:', 'Format alamat email tidak valid.', 'danger');
                header('Location: ' . BASEURL . '/profile');
                exit;
            }

            $userModel = $this->model('UserModel');
            if ($userModel->isEmailTakenByOther($email, $userId)) {
                Flasher::setFlash('Gagal memperbarui profil:', 'Email ' . htmlspecialchars($email) . ' sudah digunakan oleh akun lain.', 'danger');
                header('Location: ' . BASEURL . '/profile');
                exit;
            }

            $userModel->updateProfile($userId, $name, $email);

            // Sync session
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;

            Flasher::setFlash('Berhasil!', 'Data profil Anda telah diperbarui.', 'success');
            header('Location: ' . BASEURL . '/profile');
            exit;
        }

        header('Location: ' . BASEURL . '/profile');
        exit;
    }

    public function updatePassword() {
        Middleware::auth();
        Middleware::verifyCsrf();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                Flasher::setFlash('Gagal mengganti password:', 'Semua kolom password wajib diisi.', 'danger');
                header('Location: ' . BASEURL . '/profile');
                exit;
            }

            if (strlen($newPassword) < 6) {
                Flasher::setFlash('Gagal mengganti password:', 'Password baru minimal harus 6 karakter.', 'danger');
                header('Location: ' . BASEURL . '/profile');
                exit;
            }

            if ($newPassword !== $confirmPassword) {
                Flasher::setFlash('Gagal mengganti password:', 'Konfirmasi password baru tidak cocok.', 'danger');
                header('Location: ' . BASEURL . '/profile');
                exit;
            }

            $userModel = $this->model('UserModel');
            $user = $userModel->getUserById($userId);

            if (!password_verify($currentPassword, $user['password'])) {
                Flasher::setFlash('Gagal mengganti password:', 'Password saat ini yang Anda masukkan salah.', 'danger');
                header('Location: ' . BASEURL . '/profile');
                exit;
            }

            $userModel->updatePassword($userId, $newPassword);

            Flasher::setFlash('Berhasil!', 'Password akun Anda telah berhasil diperbarui.', 'success');
            header('Location: ' . BASEURL . '/profile');
            exit;
        }

        header('Location: ' . BASEURL . '/profile');
        exit;
    }

    /**
     * Endpoint untuk memperbarui preferensi tema pengguna
     * POST /profile/updateTheme
     */
    public function updateTheme() {
        Middleware::auth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rawInput = file_get_contents('php://input');
            $jsonData = json_decode($rawInput, true);
            $theme = trim($_POST['theme'] ?? $jsonData['theme'] ?? 'system');

            $allowed = ['light', 'dark', 'system'];
            if (!in_array($theme, $allowed, true)) {
                $theme = 'system';
            }

            $userId = $_SESSION['user_id'];
            $userModel = $this->model('UserModel');
            $userModel->updateThemePreference($userId, $theme);

            $_SESSION['user_theme'] = $theme;

            $isJson = (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
                      (!empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
                      isset($jsonData['theme']);

            if ($isJson) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'theme' => $theme,
                    'message' => 'Preferensi tema berhasil disimpan.'
                ]);
                exit;
            }

            Flasher::setFlash('Berhasil!', 'Preferensi tema berhasil disimpan.', 'success');
            header('Location: ' . BASEURL . '/profile');
            exit;
        }

        header('Location: ' . BASEURL . '/profile');
        exit;
    }
}
