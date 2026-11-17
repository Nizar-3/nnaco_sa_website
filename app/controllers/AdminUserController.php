<?php
class AdminUserController extends Controller {
    public function __construct() {
        Middleware::adminOrOwner();
    }

    public function index() {
        $data['title'] = 'Manajemen User - Admin';
        
        $keyword = trim($_GET['q'] ?? '');
        $role = trim($_GET['role'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $userModel = $this->model('UserModel');
        $data['users'] = $userModel->getAllUsersAdmin($keyword, $role, $status, $limit, $offset);
        $totalUsers = $userModel->getTotalUsersAdmin($keyword, $role, $status);
        
        $data['total_pages'] = ceil($totalUsers / $limit);
        $data['current_page'] = $page;
        $data['total_users'] = $totalUsers;
        $data['keyword'] = htmlspecialchars($keyword);
        $data['selected_role'] = $role;
        $data['selected_status'] = $status;

        if ($this->isAjax()) {
            $this->view('admin/user/index', $data);
            return;
        }

        $this->view('templates/header', $data);
        $this->view('admin/user/index', $data);
        $this->view('templates/footer', $data);
    }

    public function show($id = null) {
        if (is_null($id)) {
            header('Location: ' . BASEURL . '/adminuser');
            exit;
        }

        $data['title'] = 'Detail User - Admin';
        $data['user'] = $this->model('UserModel')->getUserById($id);

        if (!$data['user']) {
            Flasher::setFlash('User', 'tidak ditemukan', 'danger');
            header('Location: ' . BASEURL . '/adminuser');
            exit;
        }

        $this->view('templates/header', $data);
        $this->view('admin/user/show', $data);
        $this->view('templates/footer', $data);
    }

    public function update($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !is_null($id)) {
            Middleware::verifyCsrf();
            $userModel = $this->model('UserModel');
            $currentUser = $userModel->getUserById($id);
            if (!$currentUser) {
                Flasher::setFlash('Pengguna', 'tidak ditemukan', 'danger');
                header('Location: ' . BASEURL . '/adminuser');
                exit;
            }

            $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (BASEURL . '/adminuser');

            // Validation: Prevent self-demotion or self-deactivation
            $isSelf = ($id == $_SESSION['user_id']);
            $currentSessionRole = strtolower($_SESSION['user_role'] ?? '');

            $name = trim($_POST['name'] ?? $currentUser['name']);
            $email = trim($_POST['email'] ?? $currentUser['email']);
            $phone = trim($_POST['phone'] ?? ($currentUser['phone'] ?? ''));
            $role = trim($_POST['role'] ?? $currentUser['role']);
            $status = trim($_POST['status'] ?? $currentUser['status']);
            $newPassword = !empty($_POST['password']) ? trim($_POST['password']) : null;

            if ($isSelf && ($status !== 'Active' || $role !== $currentUser['role'])) {
                Flasher::setFlash('Gagal', 'Anda tidak dapat mengubah status atau peran diri sendiri!', 'danger');
                header('Location: ' . $redirect);
                exit;
            }

            // Only Owner can promote to Owner or change an Owner's role
            if ($currentSessionRole !== 'owner' && ($role === 'Owner' || $currentUser['role'] === 'Owner')) {
                if ($role !== $currentUser['role']) {
                    Flasher::setFlash('Gagal', 'Hanya Owner yang berhak mengubah atau menetapkan peran Owner!', 'danger');
                    header('Location: ' . $redirect);
                    exit;
                }
            }

            $validStatuses = ['Active', 'Inactive', 'Banned'];
            $validRoles = ['Customer', 'Operator', 'Admin', 'Owner'];

            if (!in_array($status, $validStatuses, true) || !in_array($role, $validRoles, true)) {
                Flasher::setFlash('Gagal', 'Status atau Peran (Role) tidak valid!', 'danger');
                header('Location: ' . $redirect);
                exit;
            }

            if (empty($name)) {
                Flasher::setFlash('Gagal', 'Nama lengkap pengguna wajib diisi!', 'danger');
                header('Location: ' . $redirect);
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Flasher::setFlash('Gagal', 'Format email tidak valid!', 'danger');
                header('Location: ' . $redirect);
                exit;
            }

            if ($userModel->isEmailTakenByOther($email, $id)) {
                Flasher::setFlash('Gagal', 'Email sudah digunakan oleh akun lain!', 'danger');
                header('Location: ' . $redirect);
                exit;
            }

            if ($userModel->updateUserAdminFull($id, $name, $email, $phone, $role, $status, $newPassword) >= 0) {
                if ($isSelf) {
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                }
                Flasher::setFlash('Data pengguna #' . $id, 'berhasil diperbarui', 'success');
            } else {
                Flasher::setFlash('Data pengguna #' . $id, 'gagal diperbarui', 'danger');
            }

            header('Location: ' . $redirect);
            exit;
        }
    }

    public function delete($id = null) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !is_null($id)) {
            Middleware::verifyCsrf();
            $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (BASEURL . '/adminuser');

            if ($id == $_SESSION['user_id']) {
                Flasher::setFlash('Gagal', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif!', 'danger');
                header('Location: ' . $redirect);
                exit;
            }

            $userModel = $this->model('UserModel');
            $targetUser = $userModel->getUserById($id);

            if (!$targetUser) {
                Flasher::setFlash('Gagal', 'Pengguna tidak ditemukan!', 'danger');
                header('Location: ' . $redirect);
                exit;
            }

            $currentSessionRole = strtolower($_SESSION['user_role'] ?? '');

            // Non-Owner cannot delete Owner account
            if ($targetUser['role'] === 'Owner' && $currentSessionRole !== 'owner') {
                Flasher::setFlash('Gagal', 'Hanya Owner yang memiliki wewenang menghapus akun Owner!', 'danger');
                header('Location: ' . $redirect);
                exit;
            }

            if ($userModel->deleteUser($id) > 0) {
                Flasher::setFlash('Akun pengguna #' . $id . ' (' . htmlspecialchars($targetUser['name']) . ')', 'berhasil dihapus secara permanen', 'success');
            } else {
                Flasher::setFlash('Gagal', 'Terjadi kesalahan saat menghapus pengguna', 'danger');
            }

            header('Location: ' . $redirect);
            exit;
        }
    }
}
