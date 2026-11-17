<?php
/**
 * OrderNotificationService
 * Layanan terpusat untuk pengiriman notifikasi WhatsApp pesanan Customer melalui Fonnte API.
 *
 * FITUR UTAMA:
 * - Event-driven: Terpemicu secara eksklusif pada mutasi pesanan nyata di database.
 * - Non-blocking / Resilient: Kegagalan Fonnte API tidak menggagalkan transaksi checkout atau perubahan status pesanan.
 * - Idempotensi Ketat: Menjamin tidak ada pengiriman ganda akibat refresh halaman, double-click, atau retry.
 * - Template Dinamis: Mendukung placeholder transaksi aktual (nama, no pesanan, list produk, total, resi, dll).
 * - Privasi Data: Nomor tujuan selalu divalidasi dan disamarkan saat logging atau penyajian UI.
 */

require_once __DIR__ . '/FonnteService.php';
require_once __DIR__ . '/SettingService.php';
require_once dirname(__DIR__) . '/models/WhatsAppLogModel.php';
require_once dirname(__DIR__) . '/models/OrderModel.php';
require_once dirname(__DIR__) . '/models/PaymentModel.php';

class OrderNotificationService {

    private FonnteService $fonnte;
    private WhatsAppLogModel $logModel;
    private OrderModel $orderModel;
    private PaymentModel $paymentModel;

    public function __construct(
        ?FonnteService $fonnte = null,
        ?WhatsAppLogModel $logModel = null,
        ?OrderModel $orderModel = null,
        ?PaymentModel $paymentModel = null
    ) {
        $this->fonnte = $fonnte ?? new FonnteService();
        $this->logModel = $logModel ?? new WhatsAppLogModel();
        $this->orderModel = $orderModel ?? new OrderModel();
        $this->paymentModel = $paymentModel ?? new PaymentModel();
    }

    /**
     * Pemicu Notifikasi Pesanan (Trigger Dispatcher)
     *
     * @param string $eventType Jenis event ('order_created', 'payment_pending', 'payment_confirmed', 'order_processing', 'order_packed', 'order_shipped', 'order_completed', 'order_cancelled')
     * @param int $orderId ID Pesanan di tabel orders
     * @param array $extraData Data tambahan opsional (misal: tracking_number, courier, reason)
     * @return array ['success' => bool, 'message' => string, 'log_id' => ?int, 'skipped' => bool]
     */
    public function notify(string $eventType, int $orderId, array $extraData = []): array {
        try {
            // 1. Cek Master Switch Notifikasi WA Pesanan
            $masterEnabled = (string)SettingService::get('order_wa.enabled', '1') === '1';
            if (!$masterEnabled) {
                return [
                    'success' => false,
                    'message' => 'Layanan Notifikasi WhatsApp Pesanan sedang dinonaktifkan di Pengaturan Sistem.',
                    'skipped' => true
                ];
            }

            // 2. Cek Switch Per-Event
            $eventSettingKey = $this->getSettingKeyForEvent($eventType);
            if ($eventSettingKey && (string)SettingService::get($eventSettingKey, '1') !== '1') {
                return [
                    'success' => false,
                    'message' => "Notifikasi untuk event '{$eventType}' sedang dinonaktifkan di Pengaturan Sistem.",
                    'skipped' => true
                ];
            }

            // 3. Ambil data pesanan dan customer dari database
            $order = $this->orderModel->getOrderByIdAdmin($orderId);
            if (!$order) {
                return [
                    'success' => false,
                    'message' => "Pesanan #{$orderId} tidak ditemukan di database.",
                    'skipped' => true
                ];
            }

            // Ambil nomor telepon customer dari database (users.phone)
            $db = new Database();
            $db->query("SELECT phone, name, email FROM users WHERE id = :uid LIMIT 1");
            $db->bind('uid', $order['user_id']);
            $customer = $db->single();

            $customerPhone = $customer['phone'] ?? '';
            $customerId = (int)$order['user_id'];
            $normalizedPhone = FonnteService::normalizePhone((string)$customerPhone);

            // 4. Periksa Idempotency Key untuk mencegah pesan duplikat
            $idempotencyKey = "order_{$orderId}_{$eventType}";
            $existingLog = $this->logModel->findByIdempotencyKey($idempotencyKey);

            if ($existingLog && $existingLog['status'] === 'SENT' && !str_starts_with($existingLog['provider_message_id'] ?? '', 'SIMULATED')) {
                return [
                    'success' => true,
                    'message' => "Notifikasi untuk pesanan #{$orderId} pada event '{$eventType}' sudah pernah terkirim (Idempotent).",
                    'log_id' => (int)$existingLog['id'],
                    'skipped' => true
                ];
            }

            // 5. Validasi Nomor Telepon Customer
            if (!$normalizedPhone) {
                $errorMessage = "Customer #{$customerId} ({$customer['name']}) belum mendaftarkan nomor telepon atau format tidak valid: '{$customerPhone}'";
                error_log("[OrderNotificationService] {$errorMessage}");

                // Catat ke log kegagalan agar Admin dapat melihat di riwayat
                $logId = $this->logModel->createLog([
                    'order_id'            => $orderId,
                    'customer_id'         => $customerId,
                    'event_type'          => $eventType,
                    'recipient_phone'     => (string)$customerPhone,
                    'message_content'     => "Gagal merender pesan: Nomor WhatsApp tidak valid",
                    'status'              => 'FAILED',
                    'attempt_count'       => 1,
                    'provider_message_id' => null,
                    'error_code'          => 'INVALID_RECIPIENT_PHONE',
                    'error_message'       => $errorMessage,
                    'idempotency_key'     => $idempotencyKey,
                ]);

                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'log_id' => $logId,
                    'skipped' => false
                ];
            }

            // 6. Siapkan detail produk dan pembayaran
            $orderDetails = $this->orderModel->getOrderDetails($orderId);
            $rawPayment = $this->paymentModel->getPaymentByOrderId($orderId);
            $payment = is_array($rawPayment) ? $rawPayment : null;

            // 7. Render Template Pesan
            $messageContent = $this->renderMessage($eventType, $order, $customer, $orderDetails, $payment, $extraData);

            // 8. Buat entri log awal jika belum ada
            if ($existingLog) {
                $logId = (int)$existingLog['id'];
                $this->logModel->updateStatus($logId, 'PENDING', null, null, null, true);
            } else {
                $logId = $this->logModel->createLog([
                    'order_id'            => $orderId,
                    'customer_id'         => $customerId,
                    'event_type'          => $eventType,
                    'recipient_phone'     => $normalizedPhone,
                    'message_content'     => $messageContent,
                    'status'              => 'PENDING',
                    'attempt_count'       => 1,
                    'provider_message_id' => null,
                    'error_code'          => null,
                    'error_message'       => null,
                    'idempotency_key'     => $idempotencyKey,
                ]);
            }

            // 9. Kirim Pesan via Fonnte
            $sendResult = $this->fonnte->sendMessage($normalizedPhone, $messageContent);

            // 10. Perbarui Status Log Berdasarkan Hasil Fonnte
            if ($sendResult['success']) {
                $this->logModel->updateStatus(
                    $logId,
                    'SENT',
                    $sendResult['provider_id'] ?? null,
                    null,
                    null,
                    false
                );

                return [
                    'success' => true,
                    'message' => 'Notifikasi WhatsApp pesanan berhasil dikirim ke customer.',
                    'log_id' => $logId,
                    'skipped' => false
                ];
            } else {
                $this->logModel->updateStatus(
                    $logId,
                    'FAILED',
                    null,
                    $sendResult['error_code'] ?? 'SEND_FAILED',
                    $sendResult['message'] ?? 'Gagal mengirim notifikasi via Fonnte',
                    false
                );

                return [
                    'success' => false,
                    'message' => $sendResult['message'] ?? 'Gagal mengirim pesan WhatsApp.',
                    'log_id' => $logId,
                    'skipped' => false
                ];
            }

        } catch (\Throwable $e) {
            error_log("[OrderNotificationService] Exception on order #{$orderId} event {$eventType}: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat memproses notifikasi WhatsApp: ' . $e->getMessage(),
                'skipped' => false
            ];
        }
    }

    /**
     * Retry Manual Pengiriman Notifikasi dari Dashboard Admin
     */
    public function retryNotification(int $logId): array {
        $log = $this->logModel->getById($logId);
        if (!$log) {
            return ['success' => false, 'message' => 'Data log notifikasi tidak ditemukan.'];
        }

        if ($log['status'] === 'SENT') {
            return ['success' => false, 'message' => 'Notifikasi ini sudah berstatus terkirim (SENT).'];
        }

        $phone = FonnteService::normalizePhone($log['recipient_phone']);
        if (!$phone) {
            return ['success' => false, 'message' => 'Nomor WhatsApp tujuan tidak valid: ' . $log['recipient_phone']];
        }

        // Jalankan pengiriman
        $sendResult = $this->fonnte->sendMessage($phone, $log['message_content']);

        if ($sendResult['success']) {
            $this->logModel->updateStatus(
                $logId,
                'SENT',
                $sendResult['provider_id'] ?? null,
                null,
                null,
                true // increment attempt
            );

            return [
                'success' => true,
                'message' => 'Notifikasi WhatsApp berhasil dikirim ulang ke customer.'
            ];
        } else {
            $this->logModel->updateStatus(
                $logId,
                'FAILED',
                null,
                $sendResult['error_code'] ?? 'RETRY_FAILED',
                $sendResult['message'] ?? 'Gagal mengirim ulang pesan via Fonnte',
                true // increment attempt
            );

            return [
                'success' => false,
                'message' => 'Pengiriman ulang gagal: ' . ($sendResult['message'] ?? 'Unknown error')
            ];
        }
    }

    /**
     * Render teks pesan dengan data transaksi aktual
     */
    public function renderMessage(
        string $eventType,
        array $order,
        array $customer,
        array $orderDetails = [],
        mixed $payment = null,
        array $extraData = []
    ): string {
        $payment = is_array($payment) ? $payment : null;
        $templateKey = $this->getTemplateKeyForEvent($eventType);
        $rawTemplate = SettingService::get($templateKey, $this->getDefaultTemplate($eventType));

        $orderId = $order['id'];
        $orderCode = '#ORD-' . str_pad($orderId, 5, '0', STR_PAD_LEFT);
        $customerName = trim($customer['name'] ?? 'Pelanggan');
        $orderDate = !empty($order['order_date']) ? date('d M Y, H:i', strtotime($order['order_date'])) : date('d M Y, H:i');
        $totalFormatted = 'Rp ' . number_format((float)($order['total'] ?? 0), 0, ',', '.');
        $orderStatus = strtoupper($order['status'] ?? 'MENUNGGU PEMBAYARAN');

        // Ringkasan produk yang dipesan
        $productsText = '';
        if (!empty($orderDetails)) {
            $lines = [];
            foreach ($orderDetails as $item) {
                $pName = $item['product_name'] ?? 'Aksesoris';
                $qty = $item['quantity'] ?? 1;
                $subtotal = number_format((float)($item['subtotal'] ?? 0), 0, ',', '.');
                $lines[] = "• {$pName} ({$qty}x) - Rp {$subtotal}";
            }
            $productsText = implode("\n", $lines);
        } else {
            $productsText = "• Produk pesanan nnaco_sa";
        }

        // Info metode pembayaran
        $paymentMethod = $payment['payment_method'] ?? ($extraData['payment_method'] ?? 'Cash (Bayar di Toko / Tempat)');
        $paymentStatus = $payment['status'] ?? ($orderStatus === 'MENUNGGU PEMBAYARAN' ? 'PENDING' : 'LUNAS');

        // URL detail pesanan untuk customer
        $orderUrl = BASEURL . '/customer/orderDetail/' . $orderId;

        // Info tambahan
        $trackingNumber = !empty($extraData['tracking_number']) ? trim($extraData['tracking_number']) : 'Dalam proses';
        $courier = !empty($extraData['courier']) ? trim($extraData['courier']) : 'Ekspedisi Reguler Toko';
        $cancelReason = !empty($extraData['reason']) ? trim($extraData['reason']) : 'Permintaan pembatalan pesanan';

        $replacements = [
            '{{nama_customer}}'      => $customerName,
            '{{nomor_pesanan}}'      => $orderCode,
            '{{tanggal_pesanan}}'    => $orderDate,
            '{{total_pesanan}}'      => $totalFormatted,
            '{{daftar_produk}}'      => $productsText,
            '{{metode_pembayaran}}'  => $paymentMethod,
            '{{status_pembayaran}}'  => $paymentStatus,
            '{{status_pesanan}}'     => $orderStatus,
            '{{url_pesanan}}'        => $orderUrl,
            '{{nomor_resi}}'         => $trackingNumber,
            '{{jasa_pengiriman}}'    => $courier,
            '{{alasan_pembatalan}}'  => $cancelReason,
        ];

        $rendered = str_replace(array_keys($replacements), array_values($replacements), $rawTemplate);

        // Bersihkan token placeholder tak dikenal agar tidak ada teks {{...}} yang bocor
        $rendered = preg_replace('/\{\{[a-zA-Z0-9_\-]+\}\}/', '', $rendered);

        return trim($rendered);
    }

    /**
     * Samarkan nomor telepon untuk keamanan privasi (contoh: 62812****894)
     */
    public static function maskPhone(string $phone): string {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        $len = strlen($clean);
        if ($len <= 6) return $clean;
        return substr($clean, 0, 4) . '****' . substr($clean, -3);
    }

    /**
     * Pemetaan nama event ke setting toggle switch
     */
    private function getSettingKeyForEvent(string $eventType): ?string {
        return match ($eventType) {
            'order_created'     => 'order_wa.notify_created',
            'payment_pending'   => 'order_wa.notify_pending_payment',
            'payment_confirmed' => 'order_wa.notify_payment_success',
            'order_processing'  => 'order_wa.notify_processing',
            'order_packed'      => 'order_wa.notify_packed',
            'order_shipped'     => 'order_wa.notify_shipped',
            'order_completed'   => 'order_wa.notify_completed',
            'order_cancelled'   => 'order_wa.notify_cancelled',
            default             => null,
        };
    }

    /**
     * Pemetaan nama event ke template setting key
     */
    private function getTemplateKeyForEvent(string $eventType): string {
        return match ($eventType) {
            'order_created'     => 'order_wa.template_created',
            'payment_pending'   => 'order_wa.template_pending_payment',
            'payment_confirmed' => 'order_wa.template_payment_success',
            'order_processing'  => 'order_wa.template_processing',
            'order_packed'      => 'order_wa.template_packed',
            'order_shipped'     => 'order_wa.template_shipped',
            'order_completed'   => 'order_wa.template_completed',
            'order_cancelled'   => 'order_wa.template_cancelled',
            default             => 'order_wa.template_created',
        };
    }

    /**
     * Template default cadangan jika database belum terisi
     */
    private function getDefaultTemplate(string $eventType): string {
        return match ($eventType) {
            'order_created' => "Halo, Kak {{nama_customer}}! 👋\n\nPesanan Anda di *nnaco_sa* berhasil dibuat.\n\n📋 *Nomor Pesanan:* {{nomor_pesanan}}\n📅 *Waktu:* {{tanggal_pesanan}} WIB\n\n📦 *Ringkasan Produk:*\n{{daftar_produk}}\n\n💰 *Total Tagihan:* {{total_pesanan}}\n💳 *Metode Pembayaran:* {{metode_pembayaran}}\n📊 *Status Pesanan:* {{status_pesanan}}\n\nSilakan periksa detail pesanan melalui tautan:\n{{url_pesanan}}\n\nTerima kasih telah berbelanja di *nnaco_sa*! ✨",
            'payment_pending' => "Halo, Kak {{nama_customer}}! ⏳\n\nPesanan {{nomor_pesanan}} Anda di *nnaco_sa* sedang *Menunggu Pembayaran*.\n\n💰 *Total yang harus dibayar:* {{total_pesanan}}\n💳 *Metode:* {{metode_pembayaran}}\n\nSilakan selesaikan pembayaran Anda melalui tautan berikut:\n{{url_pesanan}}\n\nTerima kasih! 🙏",
            'payment_confirmed' => "Halo, Kak {{nama_customer}}! ✅\n\nPembayaran untuk pesanan {{nomor_pesanan}} telah *BERHASIL DIVERIFIKASI*.\n\n💰 *Jumlah:* {{total_pesanan}}\n💳 *Metode:* {{metode_pembayaran}}\n📊 *Status Pesanan:* Sedang Diproses\n\nPesanan Anda segera kami siapkan. Pantau perkembangannya di:\n{{url_pesanan}}\n\nTerima kasih atas kepercayaan Anda! ✨",
            'order_processing' => "Halo, Kak {{nama_customer}}! ⚙️\n\nKabar baik! Pesanan {{nomor_pesanan}} Anda di *nnaco_sa* saat ini *SEDANG DIPROSES*.\n\nTim kami sedang menyiapkan paket Anda dengan teliti.\n\nDetail pesanan: {{url_pesanan}}\n\nTerima kasih! 🙌",
            'order_packed' => "Halo, Kak {{nama_customer}}! 🎁\n\nPesanan {{nomor_pesanan}} Anda di *nnaco_sa* telah *SELESAI DIKEMAS*.\n\nBarang Anda telah dipacking dengan aman dan siap diserahkan ke jasa pengiriman.\n\nDetail pesanan: {{url_pesanan}}",
            'order_shipped' => "Halo, Kak {{nama_customer}}! 🚚\n\nPesanan {{nomor_pesanan}} Anda telah *DIKIRIM*!\n\n📦 *Jasa Pengiriman:* {{jasa_pengiriman}}\n🔖 *Nomor Resi:* {{nomor_resi}}\n\nAnda dapat memantau pesanan melalui tautan:\n{{url_pesanan}}\n\nMohon pastikan nomor telepon Anda aktif agar kurir mudah menghubungi Anda. Terima kasih! 📦",
            'order_completed' => "Halo, Kak {{nama_customer}}! 🎉\n\nPesanan {{nomor_pesanan}} Anda di *nnaco_sa* telah dinyatakan *SELESAI*.\n\nTerima kasih banyak telah memilih produk *nnaco_sa*. Kami harap Anda menyukai aksesoris kami!\n\nBantu kami memberikan layanan lebih baik dengan memberikan ulasan di:\n{{url_pesanan}}\n\nSampai jumpa di pesanan berikutnya! 💖",
            'order_cancelled' => "Halo, Kak {{nama_customer}}! ⚠️\n\nPesanan {{nomor_pesanan}} Anda di *nnaco_sa* telah *DIBATALKAN*.\n\n📌 *Alasan Pembatalan:* {{alasan_pembatalan}}\n\nJika ada pertanyaan mengenai pesanan ini, silakan hubungi customer service kami.\n\nDetail: {{url_pesanan}}",
            default => "Halo, Kak {{nama_customer}}! Pesanan {{nomor_pesanan}} Anda telah diperbarui menjadi {{status_pesanan}}.",
        };
    }
}
