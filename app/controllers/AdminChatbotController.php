<?php
/**
 * AdminChatbotController
 * Mengelola riwayat percakapan chatbot AI (Hanya dapat diakses oleh Admin)
 */
class AdminChatbotController extends Controller {

    public function __construct() {
        // Hak akses: Admin dan Owner dapat mengakses riwayat percakapan chatbot
        Middleware::adminOrOwner();
    }

    /**
     * Halaman utama daftar riwayat percakapan chatbot AI
     */
    public function index() {
        $data['title'] = 'Riwayat Chatbot AI - Admin';

        $keyword  = trim($_GET['q'] ?? '');
        $date     = trim($_GET['date'] ?? '');
        $userType = trim($_GET['user_type'] ?? '');
        $page     = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit    = 15;
        $offset   = ($page - 1) * $limit;

        // Normalisasi format tanggal Y-m-d
        $filterDate = !empty($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : null;

        /** @var AiConversationModel $aiModel */
        $aiModel = $this->model('AiConversationModel');

        $conversations = $aiModel->getAllConversations($keyword, $filterDate, $userType, $limit, $offset);
        $totalRecords  = $aiModel->getTotalConversations($keyword, $filterDate, $userType);
        $totalPages    = ceil($totalRecords / $limit);
        $stats         = $aiModel->getStats();

        $data['conversations'] = $conversations;
        $data['total_records'] = $totalRecords;
        $data['total_pages']   = max(1, $totalPages);
        $data['current_page']  = $page;
        $data['limit']         = $limit;
        $data['stats']         = $stats;

        // Query state untuk form & pagination links
        $data['keyword']   = htmlspecialchars($keyword);
        $data['date']      = htmlspecialchars($date);
        $data['user_type'] = htmlspecialchars($userType);

        if ($this->isAjax()) {
            $this->view('admin/chatbot/index', $data);
            return;
        }

        $this->view('templates/header', $data);
        $this->view('admin/chatbot/index', $data);
        $this->view('templates/footer', $data);
    }

    /**
     * Endpoint detail percakapan (AJAX untuk Corporate Modal)
     */
    public function detail($id = null) {
        header('Content-Type: application/json');

        if (empty($id) || !is_numeric($id)) {
            echo json_encode(['success' => false, 'error' => 'ID percakapan tidak valid.']);
            exit;
        }

        /** @var AiConversationModel $aiModel */
        $aiModel = $this->model('AiConversationModel');
        $item = $aiModel->getConversationById((int)$id);

        if (!$item) {
            echo json_encode(['success' => false, 'error' => 'Data percakapan tidak ditemukan.']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'data'    => [
                'id'         => $item['id'],
                'user_id'    => $item['user_id'],
                'user_name'  => $item['user_name'] ?? 'Tamu / Pengunjung Web',
                'user_email' => $item['user_email'] ?? '-',
                'user_phone' => $item['user_phone'] ?? '-',
                'user_role'  => $item['user_role'] ?? 'Guest',
                'question'   => $item['question'],
                'answer'     => $item['answer'],
                'created_at' => date('d M Y, H:i:s', strtotime($item['created_at'])),
                'raw_time'   => $item['created_at']
            ]
        ]);
        exit;
    }

    /**
     * Hapus satu entri riwayat percakapan
     */
    public function delete($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/adminchatbot');
            exit;
        }

        Middleware::verifyCsrf();

        if (empty($id) || !is_numeric($id)) {
            Flasher::setFlash('Gagal menghapus', 'ID percakapan tidak valid.', 'danger');
            header('Location: ' . BASEURL . '/adminchatbot');
            exit;
        }

        /** @var AiConversationModel $aiModel */
        $aiModel = $this->model('AiConversationModel');
        $deleted = $aiModel->deleteConversation((int)$id);

        if ($deleted) {
            Flasher::setFlash('Berhasil', 'Riwayat percakapan berhasil dihapus.', 'success');
        } else {
            Flasher::setFlash('Gagal', 'Riwayat percakapan tidak dapat dihapus.', 'danger');
        }

        header('Location: ' . BASEURL . '/adminchatbot');
        exit;
    }

    /**
     * Bersihkan riwayat percakapan (Opsional: semua atau lebih lama dari X hari)
     */
    public function clear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASEURL . '/adminchatbot');
            exit;
        }

        Middleware::verifyCsrf();

        $days = isset($_POST['days']) && is_numeric($_POST['days']) ? (int)$_POST['days'] : null;
        $dateBefore = null;
        if ($days && $days > 0) {
            $dateBefore = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        }

        /** @var AiConversationModel $aiModel */
        $aiModel = $this->model('AiConversationModel');
        $deletedCount = $aiModel->clearConversations($dateBefore);

        if ($days) {
            Flasher::setFlash('Pembersihan Berhasil', "Sebanyak {$deletedCount} data riwayat lebih dari {$days} hari berhasil dibersihkan.", 'info');
        } else {
            Flasher::setFlash('Pembersihan Berhasil', "Seluruh riwayat percakapan ({$deletedCount} pesan) telah dibersihkan.", 'warning');
        }

        header('Location: ' . BASEURL . '/adminchatbot');
        exit;
    }
}
