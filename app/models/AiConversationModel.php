<?php
/**
 * AiConversationModel
 * Mengelola penyimpanan dan pengambilan riwayat percakapan Chatbot AI
 */
class AiConversationModel {
    private Database $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Simpan interaksi percakapan chatbot baru
     *
     * @param int|null $userId ID Pengguna jika sudah login, atau NULL jika tamu
     * @param string $question Pertanyaan dari pengguna
     * @param string $answer Jawaban dari AI
     * @return int|false ID baris yang baru disimpan
     */
    public function recordConversation(?int $userId, string $question, string $answer): int|false {
        $query = "INSERT INTO ai_conversations (user_id, question, answer, created_at) 
                  VALUES (:user_id, :question, :answer, NOW())";

        $this->db->query($query);
        $this->db->bind('user_id', $userId);
        $this->db->bind('question', $question);
        $this->db->bind('answer', $answer);

        $this->db->execute();
        $insertId = (int)$this->db->lastInsertId();
        return $insertId > 0 ? $insertId : false;
    }

    /**
     * Ambil daftar riwayat percakapan dengan join ke tabel users
     *
     * @param string $keyword Pencarian pada pertanyaan, jawaban, nama, atau email
     * @param string|null $date Filter tanggal (format Y-m-d)
     * @param string|null $userType Filter tipe ('all', 'registered', 'guest')
     * @param int $limit Batas data per halaman
     * @param int $offset Offset data
     * @return array
     */
    public function getAllConversations(
        string $keyword = '',
        ?string $date = null,
        ?string $userType = null,
        int $limit = 15,
        int $offset = 0
    ): array {
        $query = "SELECT c.id, c.user_id, c.question, c.answer, c.created_at,
                         u.name as user_name, u.email as user_email, u.phone as user_phone, u.role as user_role
                  FROM ai_conversations c
                  LEFT JOIN users u ON c.user_id = u.id
                  WHERE 1=1";

        $binds = [];

        if (!empty($keyword)) {
            $query .= " AND (c.question LIKE :kw OR c.answer LIKE :kw OR u.name LIKE :kw OR u.email LIKE :kw)";
            $binds['kw'] = "%$keyword%";
        }

        if (!empty($date)) {
            $query .= " AND DATE(c.created_at) = :conv_date";
            $binds['conv_date'] = $date;
        }

        if ($userType === 'registered') {
            $query .= " AND c.user_id IS NOT NULL";
        } elseif ($userType === 'guest') {
            $query .= " AND c.user_id IS NULL";
        }

        $query .= " ORDER BY c.created_at DESC, c.id DESC LIMIT :limit OFFSET :offset";

        $this->db->query($query);
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }
        $this->db->bind('limit', $limit, PDO::PARAM_INT);
        $this->db->bind('offset', $offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    /**
     * Hitung total percakapan berdasarkan filter
     */
    public function getTotalConversations(
        string $keyword = '',
        ?string $date = null,
        ?string $userType = null
    ): int {
        $query = "SELECT COUNT(*) as total 
                  FROM ai_conversations c
                  LEFT JOIN users u ON c.user_id = u.id
                  WHERE 1=1";

        $binds = [];

        if (!empty($keyword)) {
            $query .= " AND (c.question LIKE :kw OR c.answer LIKE :kw OR u.name LIKE :kw OR u.email LIKE :kw)";
            $binds['kw'] = "%$keyword%";
        }

        if (!empty($date)) {
            $query .= " AND DATE(c.created_at) = :conv_date";
            $binds['conv_date'] = $date;
        }

        if ($userType === 'registered') {
            $query .= " AND c.user_id IS NOT NULL";
        } elseif ($userType === 'guest') {
            $query .= " AND c.user_id IS NULL";
        }

        $this->db->query($query);
        foreach ($binds as $key => $val) {
            $this->db->bind($key, $val);
        }

        $row = $this->db->single();
        return (int)($row['total'] ?? 0);
    }

    /**
     * Ambil detail percakapan tunggal berdasarkan ID
     */
    public function getConversationById(int $id): ?array {
        $query = "SELECT c.id, c.user_id, c.question, c.answer, c.created_at,
                         u.name as user_name, u.email as user_email, u.phone as user_phone, u.role as user_role
                  FROM ai_conversations c
                  LEFT JOIN users u ON c.user_id = u.id
                  WHERE c.id = :id
                  LIMIT 1";

        $this->db->query($query);
        $this->db->bind('id', $id);
        $row = $this->db->single();
        return $row ?: null;
    }

    /**
     * Hapus satu riwayat percakapan
     */
    public function deleteConversation(int $id): bool {
        $this->db->query("DELETE FROM ai_conversations WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    /**
     * Kosongkan semua riwayat atau yang lebih lama dari tanggal tertentu
     */
    public function clearConversations(?string $dateBefore = null): int {
        if (!empty($dateBefore)) {
            $this->db->query("DELETE FROM ai_conversations WHERE created_at < :date_before");
            $this->db->bind('date_before', $dateBefore);
        } else {
            $this->db->query("DELETE FROM ai_conversations");
        }
        $this->db->execute();
        return $this->db->rowCount();
    }

    /**
     * Dapatkan statistik ringkas untuk kartu analitik
     */
    public function getStats(): array {
        // Total percakapan
        $this->db->query("SELECT COUNT(*) as total FROM ai_conversations");
        $totalAll = (int)($this->db->single()['total'] ?? 0);

        // Percakapan hari ini
        $this->db->query("SELECT COUNT(*) as total FROM ai_conversations WHERE DATE(created_at) = CURDATE()");
        $totalToday = (int)($this->db->single()['total'] ?? 0);

        // Pengguna terdaftar unik yang bertanya
        $this->db->query("SELECT COUNT(DISTINCT user_id) as total FROM ai_conversations WHERE user_id IS NOT NULL");
        $totalUniqueUsers = (int)($this->db->single()['total'] ?? 0);

        // Percakapan oleh tamu (guest)
        $this->db->query("SELECT COUNT(*) as total FROM ai_conversations WHERE user_id IS NULL");
        $totalGuest = (int)($this->db->single()['total'] ?? 0);

        return [
            'total_all'          => $totalAll,
            'total_today'        => $totalToday,
            'total_unique_users' => $totalUniqueUsers,
            'total_guest'        => $totalGuest,
        ];
    }
}
