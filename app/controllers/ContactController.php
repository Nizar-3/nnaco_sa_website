<?php
class ContactController extends Controller {
    public function index() {
        $data['title'] = 'Hubungi Kami - nnaco_sa';

        $this->view('templates/header', $data);
        $this->view('contact/index', $data);
        $this->view('templates/footer', $data);
    }

    public function send() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');

            if (empty($name) || empty($email) || empty($message)) {
                Flasher::setFlash('Gagal mengirim:', 'Mohon lengkapi nama, email, dan pesan Anda.', 'danger');
            } else {
                Flasher::setFlash('Terima kasih ' . htmlspecialchars($name) . '!', 'Pesan Anda berhasil dikirim. Tim NNACO SA akan segera membalas ke email Anda.', 'success');
            }
        }
        header('Location: ' . BASEURL . '/contact');
        exit;
    }
}
