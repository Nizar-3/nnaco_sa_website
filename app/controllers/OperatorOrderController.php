<?php
class OperatorOrderController extends Controller {
    public function __construct() {
        Middleware::role('Operator');
    }

    public function index() {
        $data['title'] = 'Manajemen Pesanan - Operator';
        
        $keyword = $_GET['q'] ?? '';
        $statusFilter = $_GET['status'] ?? '';
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $orderModel = $this->model('OrderModel');
        // Operator uses the same read method as Admin for getting orders
        $data['orders'] = $orderModel->getAllOrdersAdmin($keyword, $statusFilter, $limit, $offset);
        $totalOrders = $orderModel->getTotalOrdersAdmin($keyword, $statusFilter);
        
        $data['total_pages'] = ceil($totalOrders / $limit);
        $data['current_page'] = $page;
        $data['total_orders'] = $totalOrders;
        $data['keyword'] = htmlspecialchars($keyword);
        $data['statusFilter'] = htmlspecialchars($statusFilter);

        if ($this->isAjax()) {
            $this->view('operator/order/index', $data);
            return;
        }

        $this->view('templates/header', $data);
        $this->view('operator/order/index', $data);
        $this->view('templates/footer', $data);
    }

    public function show($id = null) {
        if (is_null($id)) {
            header('Location: ' . BASEURL . '/operatororder');
            exit;
        }

        $data['title'] = 'Detail Pesanan - Operator';
        $orderModel = $this->model('OrderModel');
        
        $data['order'] = $orderModel->getOrderByIdAdmin($id);
        
        if (!$data['order']) {
            Flasher::setFlash('Pesanan', 'tidak ditemukan', 'danger');
            header('Location: ' . BASEURL . '/operatororder');
            exit;
        }

        $data['order_details'] = $orderModel->getOrderDetails($id);

        $this->view('templates/header', $data);
        $this->view('operator/order/show', $data);
        $this->view('templates/footer', $data);
    }

    public function updateStatus($id = null) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !is_null($id)) {
            Middleware::verifyCsrf();
            $newStatus = $_POST['status'];
            
            // Valid statuses for Operator
            $validStatuses = ['MENUNGGU PEMBAYARAN', 'DIPROSES', 'DIKEMAS', 'DIKIRIM', 'SELESAI', 'DIBATALKAN'];
            
            if (!in_array($newStatus, $validStatuses)) {
                Flasher::setFlash('Gagal', 'Status pesanan tidak valid', 'danger');
                header('Location: ' . BASEURL . '/operatororder/show/' . $id);
                exit;
            }

            $orderModel = $this->model('OrderModel');
            $currentOrder = $orderModel->getOrderByIdAdmin($id);
            
            // Operator should ideally only move forward, or not be able to change Dibatalkan/Selesai back.
            // Let's protect Dibatalkan and Selesai statuses from being altered by operator unless strictly necessary.
            if ($currentOrder['status'] == 'DIBATALKAN' || $currentOrder['status'] == 'SELESAI') {
                if ($newStatus != $currentOrder['status']) {
                    Flasher::setFlash('Gagal', 'Operator tidak dapat mengubah pesanan yang sudah Selesai atau Dibatalkan.', 'danger');
                    header('Location: ' . BASEURL . '/operatororder/show/' . $id);
                    exit;
                }
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
                        error_log('[OperatorOrderController] Gagal notifikasi WA: ' . $e->getMessage());
                    }
                }

                Flasher::setFlash('Status pesanan', 'berhasil diperbarui' . $waNotice, 'success');
            } else {
                Flasher::setFlash('Status pesanan', 'gagal diperbarui', 'danger');
            }
            
            $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (BASEURL . '/operatororder/show/' . $id);
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
        $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : (BASEURL . '/operatororder');
        header('Location: ' . $redirect);
        exit;
    }
}
