<?php
class AdminOrderController extends Controller {
    public function __construct() {
        Middleware::adminOrOwner();
    }

    public function index() {
        $data['title'] = 'Manajemen Pesanan - Admin';
        
        $keyword = $_GET['q'] ?? '';
        $statusFilter = $_GET['status'] ?? '';
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $orderModel = $this->model('OrderModel');
        $data['orders'] = $orderModel->getAllOrdersAdmin($keyword, $statusFilter, $limit, $offset);
        $totalOrders = $orderModel->getTotalOrdersAdmin($keyword, $statusFilter);
        
        $data['total_pages'] = ceil($totalOrders / $limit);
        $data['current_page'] = $page;
        $data['total_orders'] = $totalOrders;
        $data['keyword'] = htmlspecialchars($keyword);
        $data['statusFilter'] = htmlspecialchars($statusFilter);

        if ($this->isAjax()) {
            $this->view('admin/order/index', $data);
            return;
        }

        $this->view('templates/header', $data);
        $this->view('admin/order/index', $data);
        $this->view('templates/footer', $data);
    }

    public function show($id = null) {
        if (is_null($id)) {
            header('Location: ' . BASEURL . '/adminorder');
            exit;
        }

        $data['title'] = 'Detail Pesanan - Admin';
        $orderModel = $this->model('OrderModel');
        
        $data['order'] = $orderModel->getOrderByIdAdmin($id);
        
        if (!$data['order']) {
            Flasher::setFlash('Pesanan', 'tidak ditemukan', 'danger');
            header('Location: ' . BASEURL . '/adminorder');
            exit;
        }

        $data['order_details'] = $orderModel->getOrderDetails($id);

        $this->view('templates/header', $data);
        $this->view('admin/order/show', $data);
        $this->view('templates/footer', $data);
    }

    public function updateStatus($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !is_null($id)) {
            Middleware::verifyCsrf();
            $newStatus = $_POST['status'];
            
            $validStatuses = ['MENUNGGU PEMBAYARAN', 'DIPROSES', 'DIKEMAS', 'DIKIRIM', 'SELESAI', 'DIBATALKAN'];
            
            if (!in_array($newStatus, $validStatuses)) {
                Flasher::setFlash('Gagal', 'Status pesanan tidak valid', 'danger');
                header('Location: ' . BASEURL . '/adminorder/show/' . $id);
                exit;
            }

            // Optional Validation: Prevent changing status if already Selesai/Dibatalkan
            $orderModel = $this->model('OrderModel');
            $currentOrder = $orderModel->getOrderByIdAdmin($id);
            
            if ($currentOrder['status'] == 'SELESAI' || $currentOrder['status'] == 'DIBATALKAN') {
                // Let's allow Admin to override if really needed, or we can block it.
                // For this implementation, we will allow Admin to fix mistakes, 
                // but you can uncomment below to strictly block it.
                /*
                if ($newStatus != $currentOrder['status']) {
                    Flasher::setFlash('Gagal', 'Pesanan yang sudah Selesai/Dibatalkan tidak dapat diubah statusnya.', 'danger');
                    header('Location: ' . BASEURL . '/adminorder/show/' . $id);
                    exit;
                }
                */
            }

            if ($orderModel->updateOrderStatus($id, $newStatus) >= 0) {
                $waNotice = '';
                // Kirim notifikasi WhatsApp jika status benar-benar berubah
                $oldStatusNorm = strtoupper(trim($currentOrder['status'] ?? ''));
                $newStatusNorm = strtoupper(trim($newStatus));

                if ($currentOrder && $oldStatusNorm !== $newStatusNorm) {
                    try {
                        $waService = new OrderNotificationService();
                        $eventType = match ($newStatusNorm) {
                            'MENUNGGU PEMBAYARAN' => 'payment_pending',
                            'DIPROSES'           => 'order_processing',
                            'DIKEMAS'             => 'order_packed',
                            'DIKIRIM'             => 'order_shipped',
                            'SELESAI'             => 'order_completed',
                            'DIBATALKAN'          => 'order_cancelled',
                            default               => null,
                        };

                        if ($eventType) {
                            $extra = [
                                'tracking_number' => $_POST['tracking_number'] ?? null,
                                'courier'         => $_POST['courier'] ?? null,
                                'reason'          => $_POST['cancel_reason'] ?? ($_POST['reason'] ?? null)
                            ];
                            $notifResult = $waService->notify($eventType, (int)$id, $extra);
                            if (!empty($notifResult['success'])) {
                                $waNotice = ' (Notifikasi WhatsApp terkirim ke customer)';
                            } elseif (!empty($notifResult['message'])) {
                                $waNotice = ' (' . $notifResult['message'] . ')';
                            }
                        }
                    } catch (\Throwable $e) {
                        error_log('[AdminOrderController] Gagal notifikasi WA: ' . $e->getMessage());
                    }
                }

                Flasher::setFlash('Status pesanan', 'berhasil diperbarui' . $waNotice, 'success');
            } else {
                Flasher::setFlash('Status pesanan', 'gagal diperbarui', 'danger');
            }
            
            $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (BASEURL . '/adminorder/show/' . $id);
            header('Location: ' . $redirect);
            exit;
        }
    }

    public function delete($id = null) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !is_null($id)) {
            Middleware::verifyCsrf();
            $orderModel = $this->model('OrderModel');
            if ($orderModel->deleteOrder($id) > 0) {
                Flasher::setFlash('Pesanan #' . $id, 'berhasil dihapus', 'success');
            } else {
                Flasher::setFlash('Pesanan #' . $id, 'gagal dihapus', 'danger');
            }
        }
        $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (BASEURL . '/adminorder');
        header('Location: ' . $redirect);
        exit;
    }
}
