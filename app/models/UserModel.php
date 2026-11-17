<?php
class UserModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getUserByEmail($email) {
        $this->db->query("SELECT * FROM users WHERE email = :email");
        $this->db->bind('email', $email);
        return $this->db->single();
    }

    /**
     * Cari user berdasarkan nomor WhatsApp (format: 628xxx)
     * Digunakan oleh sistem OTP login
     */
    public function getUserByPhone(string $phone): array|false {
        $this->db->query("SELECT * FROM users WHERE phone = :phone LIMIT 1");
        $this->db->bind('phone', $phone);
        return $this->db->single();
    }

    public function registerCustomer($data) {
        // Cek email ganda
        $this->db->query("SELECT email FROM users WHERE email = :email");
        $this->db->bind('email', $data['email']);
        $this->db->execute();

        if ($this->db->rowCount() > 0) {
            return false;
        }

        $query = "INSERT INTO users (name, email, password, phone, role, status) 
                  VALUES (:name, :email, :password, :phone, 'Customer', 'Active')";
        
        $this->db->query($query);
        $this->db->bind('name', $data['name']);
        $this->db->bind('email', $data['email']);
        $this->db->bind('password', password_hash($data['password'], PASSWORD_DEFAULT));
        $this->db->bind('phone', $data['phone'] ?? null);
        
        $this->db->execute();

        return $this->db->rowCount();
    }

    // -- ADMIN METHODS --
    public function getAllUsersAdmin($keyword = '', $role = '', $status = '', $limit = 10, $offset = 0) {
        $query = "SELECT * FROM users WHERE 1=1";
        $binds = [];
        if (!empty($keyword)) {
            $query .= " AND (name LIKE :keyword OR email LIKE :keyword OR phone LIKE :keyword)";
            $binds['keyword'] = "%$keyword%";
        }
        if (!empty($role)) {
            $query .= " AND role = :role";
            $binds['role'] = $role;
        }
        if (!empty($status)) {
            $query .= " AND status = :status";
            $binds['status'] = $status;
        }
        $query .= " ORDER BY id DESC LIMIT :limit OFFSET :offset";
        
        $this->db->query($query);
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }
        $this->db->bind('limit', $limit, PDO::PARAM_INT);
        $this->db->bind('offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    public function getTotalUsersAdmin($keyword = '', $role = '', $status = '') {
        $query = "SELECT COUNT(id) as total FROM users WHERE 1=1";
        $binds = [];
        if (!empty($keyword)) {
            $query .= " AND (name LIKE :keyword OR email LIKE :keyword OR phone LIKE :keyword)";
            $binds['keyword'] = "%$keyword%";
        }
        if (!empty($role)) {
            $query .= " AND role = :role";
            $binds['role'] = $role;
        }
        if (!empty($status)) {
            $query .= " AND status = :status";
            $binds['status'] = $status;
        }
        $this->db->query($query);
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }
        $result = $this->db->single();
        return $result['total'] ?? 0;
    }

    public function getUserById($id) {
        $this->db->query("SELECT * FROM users WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function updateUserStatusAndRole($id, $status, $role) {
        $this->db->query("UPDATE users SET status = :status, role = :role WHERE id = :id");
        $this->db->bind('status', $status);
        $this->db->bind('role', $role);
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function isEmailTakenByOther($email, $userId) {
        $this->db->query("SELECT id FROM users WHERE email = :email AND id != :id");
        $this->db->bind('email', $email);
        $this->db->bind('id', $userId);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    public function updateProfile($id, $name, $email) {
        $this->db->query("UPDATE users SET name = :name, email = :email WHERE id = :id");
        $this->db->bind('name', $name);
        $this->db->bind('email', $email);
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updatePassword($id, $newPassword) {
        $this->db->query("UPDATE users SET password = :password WHERE id = :id");
        $this->db->bind('password', password_hash($newPassword, PASSWORD_DEFAULT));
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Update preferensi tema pengguna (light, dark, system)
     */
    public function updateThemePreference($id, $theme) {
        $allowed = ['light', 'dark', 'system'];
        if (!in_array($theme, $allowed, true)) {
            $theme = 'system';
        }
        $this->db->query("UPDATE users SET theme_preference = :theme WHERE id = :id");
        $this->db->bind('theme', $theme);
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Update data akun pengguna lengkap oleh Admin/Owner
     */
    public function updateUserAdminFull($id, $name, $email, $phone, $role, $status, $newPassword = null) {
        $sql = "UPDATE users SET name = :name, email = :email, phone = :phone, role = :role, status = :status";
        if (!empty($newPassword)) {
            $sql .= ", password = :password";
        }
        $sql .= " WHERE id = :id";

        $this->db->query($sql);
        $this->db->bind('name', $name);
        $this->db->bind('email', $email);
        $this->db->bind('phone', $phone);
        $this->db->bind('role', $role);
        $this->db->bind('status', $status);
        $this->db->bind('id', $id);
        if (!empty($newPassword)) {
            $this->db->bind('password', password_hash($newPassword, PASSWORD_DEFAULT));
        }
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Hapus pengguna permanen oleh Admin/Owner
     */
    public function deleteUser($id) {
        $this->db->query("DELETE FROM users WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount();
    }
}
