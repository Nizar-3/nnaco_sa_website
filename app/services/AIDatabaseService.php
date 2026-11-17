<?php
/**
 * AIDatabaseService.php
 * Database Access Layer, Context Provider & Role-Based Data Security untuk AI Chatbot Sistem nnaco_sa
 *
 * Mampu membaca dan menjawab SELURUH isi 12 tabel basis data sistem secara dinamis:
 * 1. categories
 * 2. products
 * 3. orders
 * 4. order_details
 * 5. payments
 * 6. users
 * 7. reviews
 * 8. system_settings
 * 9. system_audit_logs
 * 10. otp_requests
 * 11. whatsapp_notification_logs
 * 12. ai_conversations
 */

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AIRolePermissionService.php';

class AIDatabaseService {
    private Database $db;

    public const ALL_SYSTEM_TABLES = [
        'categories', 'products', 'orders', 'order_details', 'payments',
        'users', 'reviews', 'system_settings', 'system_audit_logs',
        'otp_requests', 'whatsapp_notification_logs', 'ai_conversations'
    ];

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Deteksi maksud/intent pertanyaan pengguna seputar database & seluruh tabel nnaco_sa
     */
    public function detectIntent(string $message, string $role = 'Customer', ?int $userId = null): ?array {
        $text = strtolower(trim($message));
        $cleanText = preg_replace('/[^\p{L}\p{N}\s_]/u', ' ', $text);
        $cleanText = preg_replace('/\s+/', ' ', trim($cleanText));

        // 1. Identitas Akun & Profil Pribadi
        if (in_array($text, ['siapa saya', 'saya siapa', 'siapa aku', 'aku siapa', 'akun saya', 'profil saya', 'data akun saya', 'informasi akun saya', 'data saya'], true)) {
            return ['toolName' => 'getCurrentUserProfile', 'args' => []];
        }

        // 2. Ikhtisar SELURUH Isi Tabel pada Sistem
        if (str_contains($cleanText, 'semua isi tabel') || str_contains($cleanText, 'seluruh isi tabel') || str_contains($cleanText, 'isi seluruh tabel') || str_contains($cleanText, 'isi semua tabel') || str_contains($cleanText, 'semua data tabel') || str_contains($cleanText, 'seluruh tabel pada sistem') || str_contains($cleanText, 'semua tabel pada sistem') || str_contains($cleanText, 'daftar semua tabel')) {
            return ['toolName' => 'getAllTablesOverview', 'args' => []];
        }

        // 3. Deteksi Permintaan Membaca Isi Tabel Spesifik (Berdasarkan Nama Tabel / Sinonim)
        $tableMapping = [
            'categories'                  => ['categories', 'kategori', 'category'],
            'products'                    => ['products', 'produk', 'product', 'barang', 'katalog'],
            'orders'                      => ['orders', 'pesanan', 'order'],
            'order_details'               => ['order_details', 'detail pesanan', 'rincian pesanan', 'order detail', 'detail order'],
            'payments'                    => ['payments', 'pembayaran', 'payment'],
            'users'                       => ['users', 'pengguna', 'user', 'pelanggan', 'customer', 'daftar user'],
            'reviews'                     => ['reviews', 'ulasan', 'review', 'testimoni'],
            'system_settings'             => ['system_settings', 'settings', 'setting', 'pengaturan', 'konfigurasi'],
            'system_audit_logs'           => ['system_audit_logs', 'audit_logs', 'audit logs', 'log audit', 'audit log', 'riwayat audit'],
            'otp_requests'                => ['otp_requests', 'otp', 'permintaan otp', 'log otp'],
            'whatsapp_notification_logs'  => ['whatsapp_notification_logs', 'notifikasi whatsapp', 'whatsapp logs', 'log whatsapp', 'log wa', 'notifikasi wa'],
            'ai_conversations'            => ['ai_conversations', 'percakapan ai', 'chat ai', 'riwayat chat ai', 'percakapan chatbot']
        ];

        // Format pertanyaan: "tampilkan isi tabel [X]", "apa isi tabel [X]", "isi tabel [X]", "tabel [X]"
        $isTableQuery = str_contains($cleanText, 'tabel') || str_contains($cleanText, 'table') || str_contains($cleanText, 'isi data') || str_contains($cleanText, 'baca tabel') || str_contains($cleanText, 'tampilkan data') || str_contains($cleanText, 'lihat tabel') || str_contains($cleanText, 'daftar isi');

        if ($isTableQuery) {
            // Prioritas cek tabel kata majemuk (order_details, system_settings, system_audit_logs, whatsapp_notification_logs, ai_conversations, otp_requests)
            $priorityKeys = ['order_details', 'system_audit_logs', 'system_settings', 'whatsapp_notification_logs', 'ai_conversations', 'otp_requests'];
            foreach ($priorityKeys as $pKey) {
                foreach ($tableMapping[$pKey] as $kw) {
                    if (str_contains($cleanText, $kw)) {
                        return ['toolName' => 'readTableData', 'args' => ['table' => $pKey, 'limit' => 20]];
                    }
                }
            }

            // Cek tabel standar lainnya
            foreach ($tableMapping as $tbl => $keywords) {
                if (in_array($tbl, $priorityKeys, true)) continue;
                foreach ($keywords as $kw) {
                    if (str_contains($cleanText, $kw)) {
                        return ['toolName' => 'readTableData', 'args' => ['table' => $tbl, 'limit' => 20]];
                    }
                }
            }
        }

        // Cek struktur kolom tabel (describe table)
        if (str_contains($cleanText, 'struktur tabel') || str_contains($cleanText, 'kolom tabel') || str_contains($cleanText, 'skema tabel')) {
            foreach ($tableMapping as $tbl => $keywords) {
                foreach ($keywords as $kw) {
                    if (str_contains($cleanText, $kw)) {
                        return ['toolName' => 'describeTable', 'args' => ['table' => $tbl]];
                    }
                }
            }
        }

        // 4. Panduan Toko & Kebijakan
        if (str_contains($cleanText, 'cara memesan') || str_contains($cleanText, 'cara order') || str_contains($cleanText, 'cara beli') || str_contains($cleanText, 'alur pemesanan') || str_contains($cleanText, 'cara belanja')) {
            return [
                'type' => 'direct_reply',
                'reply' => "Berikut panduan cara melakukan pemesanan di website **nnaco_sa**:\n\n"
                    . "1. **Pilih Aksesoris**: Buka menu Katalog Belanja untuk melihat koleksi kalung, gelang, cincin, dan anting.\n"
                    . "2. **Masukkan ke Keranjang**: Klik tombol **'Tambah ke Keranjang'** pada perhiasan pilihan Anda.\n"
                    . "3. **Periksa Keranjang**: Masuk ke halaman Keranjang untuk memastikan jumlah dan rincian belanja Anda.\n"
                    . "4. **Checkout**: Lanjutkan ke halaman Checkout, lengkapi data penerima dan pilih metode pembayaran.\n"
                    . "5. **Konfirmasi & Bayar**: Selesaikan transaksi dan pantau status pesanan langsung dari Dashboard Anda."
            ];
        }

        if (str_contains($cleanText, 'retur') || str_contains($cleanText, 'pengembalian barang') || str_contains($cleanText, 'garansi')) {
            return [
                'type' => 'direct_reply',
                'reply' => "### 🛡️ Kebijakan Retur & Garansi nnaco_sa\n\n"
                    . "- **Garansi Retur 30 Hari**: Berlaku untuk produk cacat produksi atau pesanan yang tidak sesuai.\n"
                    . "- **Syarat Retur**: Wajib menyertakan video unboxing paket asli saat pertama kali dibuka.\n"
                    . "- **Hubungi Dukungan**: Silakan hubungi admin toko via WhatsApp atau email ke **support@nnaco.com**."
            ];
        }

        // 5. Omzet & Laporan Penjualan (Khusus Admin & Owner)
        if (str_contains($cleanText, 'omzet') || str_contains($cleanText, 'omset') || str_contains($cleanText, 'total penjualan') || str_contains($cleanText, 'laporan penjualan') || str_contains($cleanText, 'revenue') || str_contains($cleanText, 'keuntungan')) {
            $period = 'all_time';
            if (str_contains($cleanText, 'hari ini')) $period = 'today';
            elseif (str_contains($cleanText, 'bulan ini')) $period = 'this_month';
            elseif (str_contains($cleanText, 'tahun ini')) $period = 'this_year';
            return ['toolName' => 'getOmzetReport', 'args' => ['period' => $period]];
        }

        if (str_contains($cleanText, 'perbandingan penjualan') || str_contains($cleanText, 'bulan sebelumnya') || str_contains($cleanText, 'perkembangan penjualan')) {
            return ['toolName' => 'getSalesComparison', 'args' => []];
        }

        // 6. Analisis Lintas Database: Produk Terlaris & Omzet yang Dihasilkan
        if (str_contains($cleanText, 'terlaris') || str_contains($cleanText, 'paling banyak terjual') || str_contains($cleanText, 'banyak diminati') || str_contains($cleanText, 'penjualan terbesar')) {
            return ['toolName' => 'getTopSellingProducts', 'args' => []];
        }

        if (str_contains($cleanText, 'stok rendah tetapi penjualan') || str_contains($cleanText, 'stok menipis tapi laku') || str_contains($cleanText, 'stok sedikit penjualan tinggi')) {
            return ['toolName' => 'getLowStockHighSalesProducts', 'args' => []];
        }

        if (str_contains($cleanText, 'terjual berdasarkan kategori') || str_contains($cleanText, 'penjualan per kategori') || str_contains($cleanText, 'penjualan berdasarkan kategori')) {
            return ['toolName' => 'getSalesByCategory', 'args' => []];
        }

        // 7. Stok & Inventaris
        if (str_contains($cleanText, 'hampir habis') || str_contains($cleanText, 'stok menipis') || str_contains($cleanText, 'stok rendah') || str_contains($cleanText, 'stok tipis') || str_contains($cleanText, 'segera ditambahkan') || str_contains($cleanText, 'restock')) {
            return ['toolName' => 'getLowStockProducts', 'args' => ['threshold' => 5]];
        }
        if (str_contains($cleanText, 'stok habis') || str_contains($cleanText, 'sedang tidak tersedia') || str_contains($cleanText, 'produk habis') || str_contains($cleanText, 'kehabisan stok')) {
            return ['toolName' => 'getOutOfStockProducts', 'args' => []];
        }
        if (str_contains($cleanText, 'total stok') || str_contains($cleanText, 'jumlah stok seluruh') || str_contains($cleanText, 'total unit stok')) {
            return ['toolName' => 'getTotalStock', 'args' => []];
        }

        // 8. Cek Stok & Harga Produk Spesifik
        if (preg_match('/(apakah|cek|ada|stok|berapa harga)\s+(produk\s+)?(kalung|gelang|cincin|anting|jepit|bros|aksesoris)\s*([a-zA-Z0-9\s]*)/i', $text, $matches)) {
            $keyword = trim($matches[3] . ' ' . ($matches[4] ?? ''));
            return ['toolName' => 'checkProductStock', 'args' => ['query' => $keyword]];
        }

        // 9. Produk Termurah & Termahal
        if (str_contains($cleanText, 'paling murah') || str_contains($cleanText, 'termurah') || str_contains($cleanText, 'harga terendah')) {
            return ['toolName' => 'getCheapestProduct', 'args' => []];
        }
        if (str_contains($cleanText, 'paling mahal') || str_contains($cleanText, 'termahal') || str_contains($cleanText, 'harga tertinggi')) {
            return ['toolName' => 'getMostExpensiveProduct', 'args' => []];
        }

        // 10. Total Produk & Daftar Produk
        if ((str_contains($cleanText, 'berapa') || str_contains($cleanText, 'total')) && (str_contains($cleanText, 'produk') || str_contains($cleanText, 'barang') || str_contains($cleanText, 'aksesoris')) && !str_contains($cleanText, 'stok') && !str_contains($cleanText, 'kategori')) {
            return ['toolName' => 'getTotalStock', 'args' => []];
        }
        if (str_contains($cleanText, 'produk apa saja yang tersedia') || str_contains($cleanText, 'katalog produk') || str_contains($cleanText, 'produk yang tersedia')) {
            return ['toolName' => 'getAllProducts', 'args' => []];
        }

        // 11. Kategori Aksesoris
        if (str_contains($cleanText, 'kategori') && (str_contains($cleanText, 'berapa') || str_contains($cleanText, 'apa saja') || str_contains($cleanText, 'daftar') || str_contains($cleanText, 'jumlah produk dalam setiap'))) {
            return ['toolName' => 'getCategoryStats', 'args' => []];
        }

        // 12. Pesanan Tertentu (#ID) & Siapa Customer Pesanan
        if (preg_match('/(isi|detail|rincian|status)\s+pesanan\s*(?:nomor|no|#)?\s*(\d+)/i', $text, $m)) {
            return ['toolName' => 'getOrderDetails', 'args' => ['order_id' => (int)$m[2]]];
        }
        if (preg_match('/siapa\s+customer.*pesanan\s*(?:nomor|no|#)?\s*(\d+)/i', $text, $m)) {
            return ['toolName' => 'getOrderCustomer', 'args' => ['order_id' => (int)$m[1]]];
        }

        // 13. Pesanan Operasional Toko
        if (str_contains($cleanText, 'belum diproses') || str_contains($cleanText, 'perlu diproses') || str_contains($cleanText, 'dikerjakan operator') || str_contains($cleanText, 'belum ditangani') || str_contains($cleanText, 'antrean pesanan') || str_contains($cleanText, 'menunggu pengiriman')) {
            return ['toolName' => 'getOrdersNeedingProcessing', 'args' => []];
        }
        if (str_contains($cleanText, 'belum dibayar') || str_contains($cleanText, 'menunggu pembayaran')) {
            return ['toolName' => 'getPendingPaymentOrdersCount', 'args' => []];
        }
        if (str_contains($cleanText, 'pesanan yang dibatalkan') || str_contains($cleanText, 'pesanan batal')) {
            return ['toolName' => 'getOrdersByStatus', 'args' => ['status' => 'DIBATALKAN']];
        }
        if (str_contains($cleanText, 'pesanan yang sudah dikirim') || str_contains($cleanText, 'pesanan dikirim')) {
            return ['toolName' => 'getOrdersByStatus', 'args' => ['status' => 'DIKIRIM']];
        }
        if (str_contains($cleanText, 'pesanan selesai') || str_contains($cleanText, 'pesanan yang selesai')) {
            return ['toolName' => 'getOrdersByStatus', 'args' => ['status' => 'SELESAI']];
        }

        // 14. Pesanan Customer Sendiri vs Semua Pesanan
        if (str_contains($cleanText, 'pesanan saya') || str_contains($cleanText, 'status pesanan saya') || str_contains($cleanText, 'riwayat belanja saya') || str_contains($cleanText, 'order saya') || str_contains($cleanText, 'apakah pesanan saya')) {
            return ['toolName' => 'getMyOrders', 'args' => []];
        }
        if (str_contains($cleanText, 'semua pesanan') || str_contains($cleanText, 'daftar pesanan') || str_contains($cleanText, 'total pesanan hari ini') || (str_contains($cleanText, 'total pesanan') && !str_contains($cleanText, 'saya'))) {
            return ['toolName' => 'getAllOrders', 'args' => []];
        }

        // 15. Pembayaran (Payment Gateway)
        if (str_contains($cleanText, 'pembayaran') && (str_contains($cleanText, 'berhasil') || str_contains($cleanText, 'sukses') || str_contains($cleanText, 'total'))) {
            return ['toolName' => 'getPaymentSummary', 'args' => []];
        }
        if (str_contains($cleanText, 'metode pembayaran') || str_contains($cleanText, 'gopay') || str_contains($cleanText, 'dana') || str_contains($cleanText, 'transfer bank') || str_contains($cleanText, 'qris')) {
            return ['toolName' => 'getPaymentMethodsBreakdown', 'args' => []];
        }

        // 16. Customer & Pengguna Sistem
        if (str_contains($cleanText, 'customer baru') || str_contains($cleanText, 'pelanggan baru')) {
            return ['toolName' => 'getCustomerStatistics', 'args' => []];
        }
        if (str_contains($cleanText, 'customer terbanyak') || str_contains($cleanText, 'transaksi terbanyak') || str_contains($cleanText, 'paling banyak berbelanja') || str_contains($cleanText, 'top customer')) {
            return ['toolName' => 'getTopSpendingCustomer', 'args' => []];
        }

        // Jumlah / Statistik Pengguna (getUserManagementSummary)
        if ((str_contains($cleanText, 'pengguna') || str_contains($cleanText, 'user') || str_contains($cleanText, 'customer') || str_contains($cleanText, 'pelanggan') || str_contains($cleanText, 'akun')) && (str_contains($cleanText, 'berapa') || str_contains($cleanText, 'jumlah') || str_contains($cleanText, 'total') || str_contains($cleanText, 'statistik') || str_contains($cleanText, 'banyak'))) {
            return ['toolName' => 'getUserManagementSummary', 'args' => []];
        }

        // Daftar / Sebutkan / Rincian Pengguna (getAllUsers) dengan filter Role otomatis
        $isUserListQuery = (
            str_contains($cleanText, 'daftar user') || str_contains($cleanText, 'daftar pengguna') || str_contains($cleanText, 'daftar customer') ||
            str_contains($cleanText, 'daftar pelanggan') || str_contains($cleanText, 'daftar operator') || str_contains($cleanText, 'daftar admin') ||
            str_contains($cleanText, 'daftar owner') || str_contains($cleanText, 'daftar akun') || str_contains($cleanText, 'sebutkan user') ||
            str_contains($cleanText, 'sebutkan pengguna') || str_contains($cleanText, 'sebutkan customer') || str_contains($cleanText, 'sebutkan pelanggan') ||
            str_contains($cleanText, 'sebutkan operator') || str_contains($cleanText, 'sebutkan admin') || str_contains($cleanText, 'sebutkan owner') ||
            str_contains($cleanText, 'sebutkan akun') || str_contains($cleanText, 'siapa saja user') || str_contains($cleanText, 'siapa saja pengguna') ||
            str_contains($cleanText, 'siapa saja customer') || str_contains($cleanText, 'siapa saja pelanggan') || str_contains($cleanText, 'siapa saja operator') ||
            str_contains($cleanText, 'siapa saja admin') || str_contains($cleanText, 'siapa saja owner') || str_contains($cleanText, 'data user') ||
            str_contains($cleanText, 'data pengguna') || str_contains($cleanText, 'data customer') || str_contains($cleanText, 'data pelanggan') ||
            str_contains($cleanText, 'lihat user') || str_contains($cleanText, 'lihat pengguna') || str_contains($cleanText, 'tampilkan user') ||
            str_contains($cleanText, 'tampilkan pengguna') || str_contains($cleanText, 'semua user') || str_contains($cleanText, 'semua pengguna') ||
            str_contains($cleanText, 'seluruh user') || str_contains($cleanText, 'seluruh pengguna') || str_contains($cleanText, 'user terdaftar') ||
            str_contains($cleanText, 'pengguna terdaftar') || str_contains($cleanText, 'user dengan role') || str_contains($cleanText, 'pengguna dengan role') ||
            str_contains($cleanText, 'akun terdaftar') || str_contains($cleanText, 'rincian user') || str_contains($cleanText, 'rincian pengguna')
        );

        if ($isUserListQuery) {
            $roleFilter = null;
            if (str_contains($cleanText, 'customer') || str_contains($cleanText, 'pelanggan')) {
                $roleFilter = 'Customer';
            } elseif (str_contains($cleanText, 'operator')) {
                $roleFilter = 'Operator';
            } elseif (str_contains($cleanText, 'admin')) {
                $roleFilter = 'Admin';
            } elseif (str_contains($cleanText, 'owner') || str_contains($cleanText, 'pemilik')) {
                $roleFilter = 'Owner';
            }
            return ['toolName' => 'getAllUsers', 'args' => ['role' => $roleFilter]];
        }

        // 17. Operasional Toko
        if (str_contains($cleanText, 'aktivitas operasional') || str_contains($cleanText, 'ringkasan operasional')) {
            return ['toolName' => 'getOperationalQueue', 'args' => []];
        }

        // 18. Ikhtisar Database
        if (str_contains($cleanText, 'ikhtisar database') || str_contains($cleanText, 'skema database') || str_contains($cleanText, 'database nnaco')) {
            return ['toolName' => 'getDatabaseOverview', 'args' => []];
        }

        return null;
    }

    /**
     * Eksekusi tool database dengan pengujian otorisasi Role (RBAC) & Row-Level Security (RLS)
     */
    public function executeTool(string $toolName, array $args, array $user): array {
        $role = $user['role'] ?? 'Customer';
        $userId = isset($user['id']) ? (int)$user['id'] : null;

        // 1. Pemeriksaan Hak Akses Role (RBAC)
        if (!AIRolePermissionService::isToolAllowed($toolName, $role)) {
            return [
                'success' => false,
                'error'   => AIRolePermissionService::getDenialMessage($toolName, $role)
            ];
        }

        try {
            switch ($toolName) {
                // ==========================================
                // 1. MEMBACA ISI TABEL SPESIFIK (UNIVERSAL)
                // ==========================================
                case 'readTableData': {
                    $tableName = strtolower(trim($args['table'] ?? ''));
                    if (!in_array($tableName, self::ALL_SYSTEM_TABLES, true)) {
                        return [
                            'success' => false,
                            'error' => "Tabel `{$tableName}` tidak ditemukan di basis data sistem nnaco_sa."
                        ];
                    }

                    // Cek izin akses terhadap tabel
                    if (!AIRolePermissionService::isTableAccessAllowed($tableName, $role)) {
                        return [
                            'success' => false,
                            'error' => AIRolePermissionService::getDenialMessage('readTableData', $role, $tableName)
                        ];
                    }

                    $limit = min(50, max(1, (int)($args['limit'] ?? 20)));

                    // Hitung total baris di tabel
                    $this->db->query("SELECT COUNT(*) as c FROM `{$tableName}`");
                    $totalRows = (int)($this->db->single()['c'] ?? 0);

                    // Row-Level Security & Parameterized Query Sesuai Tabel
                    $sql = "SELECT * FROM `{$tableName}`";
                    $bindings = [];

                    if ($role === 'Customer') {
                        // Customer hanya boleh melihat data miliknya sendiri di tabel transaksional
                        if ($tableName === 'orders') {
                            $sql .= " WHERE user_id = :uid ORDER BY order_date DESC";
                            $bindings['uid'] = $userId;
                        } elseif ($tableName === 'order_details') {
                            $sql = "SELECT od.* FROM order_details od JOIN orders o ON od.order_id = o.id WHERE o.user_id = :uid ORDER BY od.id DESC";
                            $bindings['uid'] = $userId;
                        } elseif ($tableName === 'payments') {
                            $sql = "SELECT p.* FROM payments p JOIN orders o ON p.order_id = o.id WHERE o.user_id = :uid ORDER BY p.id DESC";
                            $bindings['uid'] = $userId;
                        } elseif ($tableName === 'users') {
                            $sql .= " WHERE id = :uid";
                            $bindings['uid'] = $userId;
                        } elseif ($tableName === 'ai_conversations') {
                            $sql .= " WHERE user_id = :uid ORDER BY created_at DESC";
                            $bindings['uid'] = $userId;
                        } else {
                            $sql .= " ORDER BY id ASC";
                        }
                    } else {
                        // Operator, Admin, Owner
                        $sql .= " ORDER BY id DESC";
                    }

                    $sql .= " LIMIT {$limit}";
                    $this->db->query($sql);
                    foreach ($bindings as $k => $v) {
                        $this->db->bind($k, $v);
                    }
                    $rows = $this->db->resultSet();

                    // Masking Data Sensitif (Password hash, secret key, OTP hash)
                    foreach ($rows as &$r) {
                        if (isset($r['password'])) {
                            $r['password'] = '[TERENKRIPSI BCRYPT]';
                        }
                        if (isset($r['otp_hash'])) {
                            $r['otp_hash'] = '[TERENKRIPSI HASH]';
                        }
                        if ($tableName === 'system_settings' && !empty($r['is_secret'])) {
                            $val = $r['setting_value'] ?? '';
                            $r['setting_value'] = strlen($val) > 8 ? substr($val, 0, 4) . '••••••••' : '••••••••';
                        }
                    }
                    unset($r);

                    return [
                        'success'    => true,
                        'table'      => $tableName,
                        'total_rows' => $totalRows,
                        'displayed'  => count($rows),
                        'data'       => $rows
                    ];
                }

                // ==========================================
                // 2. IKHTISAR SELURUH TABEL SISTEM
                // ==========================================
                case 'getAllTablesOverview': {
                    $tableMeta = [
                        'categories' => ['name' => 'Kategori Produk', 'desc' => 'Kelompok aksesoris (Kalung, Gelang, Cincin, Anting, Jepit Rambut)'],
                        'products' => ['name' => 'Katalog Produk', 'desc' => 'Daftar perhiasan, harga satuan, sisa stok, status ketersediaan'],
                        'orders' => ['name' => 'Pesanan Toko', 'desc' => 'Header transaksi pemesanan belanja customer dan status proses'],
                        'order_details' => ['name' => 'Rincian Pesanan', 'desc' => 'Item produk per pesanan, kuantitas belanja, dan subtotal harga'],
                        'payments' => ['name' => 'Pembayaran Gateway', 'desc' => 'Transaksi pembayaran Midtrans, metode bayar, dan status lunas'],
                        'users' => ['name' => 'Pengguna Sistem', 'desc' => 'Akun customer, operator, admin, owner, email, dan no telepon'],
                        'reviews' => ['name' => 'Ulasan Pelanggan', 'desc' => 'Testimoni, rating bintang, dan feedback kepuasan customer'],
                        'system_settings' => ['name' => 'Konfigurasi Sistem', 'desc' => 'Parameter AI provider, model, WhatsApp OTP, dan pengaturan toko'],
                        'system_audit_logs' => ['name' => 'Log Audit Sistem', 'desc' => 'Riwayat perubahan data, pengujian koneksi, dan catatan keamanan'],
                        'otp_requests' => ['name' => 'Permintaan OTP', 'desc' => 'Log verifikasi autentikasi OTP WhatsApp Fonnte pelanggan'],
                        'whatsapp_notification_logs' => ['name' => 'Notifikasi WhatsApp', 'desc' => 'Log pengiriman notifikasi status pesanan via gateway WA'],
                        'ai_conversations' => ['name' => 'Percakapan AI', 'desc' => 'Riwayat interaksi tanya-jawab antara pengguna dengan chatbot']
                    ];

                    $overview = [];
                    foreach (self::ALL_SYSTEM_TABLES as $tbl) {
                        $allowed = AIRolePermissionService::isTableAccessAllowed($tbl, $role);
                        $this->db->query("SELECT COUNT(*) as c FROM `{$tbl}`");
                        $c = (int)($this->db->single()['c'] ?? 0);

                        $overview[] = [
                            'table'       => $tbl,
                            'name'        => $tableMeta[$tbl]['name'] ?? $tbl,
                            'description' => $tableMeta[$tbl]['desc'] ?? '',
                            'row_count'   => $c,
                            'accessible'  => $allowed
                        ];
                    }

                    return ['success' => true, 'role' => $role, 'data' => $overview];
                }

                // ==========================================
                // 3. STRUKTUR KOLOM TABEL (DESCRIBE)
                // ==========================================
                case 'describeTable': {
                    $tableName = strtolower(trim($args['table'] ?? ''));
                    if (!in_array($tableName, self::ALL_SYSTEM_TABLES, true)) {
                        return ['success' => false, 'error' => "Tabel `{$tableName}` tidak dikenal."];
                    }
                    if (!AIRolePermissionService::isTableAccessAllowed($tableName, $role)) {
                        return ['success' => false, 'error' => AIRolePermissionService::getDenialMessage('readTableData', $role, $tableName)];
                    }

                    $this->db->query("DESCRIBE `{$tableName}`");
                    $cols = $this->db->resultSet();
                    return ['success' => true, 'table' => $tableName, 'columns' => $cols];
                }

                // ==========================================
                // 4. PROFIL & IDENTITAS AKTOR
                // ==========================================
                case 'getCurrentUserProfile': {
                    if (empty($userId)) {
                        return [
                            'success' => true,
                            'data' => [
                                'is_logged_in' => false,
                                'role' => 'Guest / Tamu',
                                'name' => 'Tamu'
                            ]
                        ];
                    }
                    $this->db->query("SELECT id, name, email, phone, role, status FROM users WHERE id = :id");
                    $this->db->bind('id', $userId);
                    $userData = $this->db->single();
                    return [
                        'success' => true,
                        'data' => array_merge($userData ?: [], ['is_logged_in' => true])
                    ];
                }

                // ==========================================
                // 5. OMZET & LAPORAN PENJUALAN
                // ==========================================
                case 'getOmzetReport':
                case 'getSalesSummary': {
                    $period = $args['period'] ?? 'all_time';
                    $this->db->query("
                        SELECT 
                            COALESCE(SUM(CASE WHEN DATE(order_date) = CURDATE() THEN total ELSE 0 END), 0) as omzet_today,
                            COALESCE(SUM(CASE WHEN MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE()) THEN total ELSE 0 END), 0) as omzet_this_month,
                            COALESCE(SUM(CASE WHEN YEAR(order_date) = YEAR(CURDATE()) THEN total ELSE 0 END), 0) as omzet_this_year,
                            COALESCE(SUM(total), 0) as omzet_all_time,
                            COUNT(*) as total_successful_orders,
                            COALESCE(AVG(total), 0) as average_order_value
                        FROM orders 
                        WHERE status IN ('PAID', 'DIPROSES', 'DIKEMAS', 'DIKIRIM', 'SELESAI')
                    ");
                    $stats = $this->db->single();

                    $this->db->query("
                        SELECT 
                            DATE_FORMAT(order_date, '%Y-%m') as ym,
                            SUM(total) as omzet,
                            COUNT(*) as orders_count
                        FROM orders
                        WHERE status IN ('PAID', 'DIPROSES', 'DIKEMAS', 'DIKIRIM', 'SELESAI')
                        GROUP BY ym
                        ORDER BY ym DESC
                        LIMIT 2
                    ");
                    $recentMonths = $this->db->resultSet();

                    return [
                        'success' => true,
                        'data' => [
                            'omzet_today'        => (float)($stats['omzet_today'] ?? 0),
                            'omzet_this_month'    => (float)($stats['omzet_this_month'] ?? 0),
                            'omzet_this_year'     => (float)($stats['omzet_this_year'] ?? 0),
                            'omzet_all_time'      => (float)($stats['omzet_all_time'] ?? 0),
                            'total_orders'        => (int)($stats['total_successful_orders'] ?? 0),
                            'average_order_value' => (float)($stats['average_order_value'] ?? 0),
                            'monthly_comparison'  => $recentMonths,
                            'period'              => $period
                        ]
                    ];
                }

                case 'getSalesComparison': {
                    $this->db->query("
                        SELECT 
                            DATE_FORMAT(order_date, '%Y-%m') as ym,
                            SUM(total) as omzet,
                            COUNT(*) as orders_count
                        FROM orders
                        WHERE status IN ('PAID', 'DIPROSES', 'DIKEMAS', 'DIKIRIM', 'SELESAI')
                        GROUP BY ym
                        ORDER BY ym DESC
                        LIMIT 2
                    ");
                    $months = $this->db->resultSet();
                    $cur = $months[0] ?? ['ym' => date('Y-m'), 'omzet' => 0, 'orders_count' => 0];
                    $prev = $months[1] ?? ['ym' => date('Y-m', strtotime('-1 month')), 'omzet' => 0, 'orders_count' => 0];

                    $diffOmzet = (float)$cur['omzet'] - (float)$prev['omzet'];
                    $pctDiff = $prev['omzet'] > 0 ? round(($diffOmzet / (float)$prev['omzet']) * 100, 1) : 0;

                    return [
                        'success' => true,
                        'data' => [
                            'current_month' => $cur,
                            'previous_month' => $prev,
                            'difference_omzet' => $diffOmzet,
                            'percentage_change' => $pctDiff
                        ]
                    ];
                }

                case 'getSalesByCategory': {
                    $this->db->query("
                        SELECT 
                            c.name as category_name,
                            COALESCE(SUM(od.quantity), 0) as units_sold,
                            COALESCE(SUM(od.subtotal), 0) as total_revenue
                        FROM categories c
                        LEFT JOIN products p ON c.id = p.category_id
                        LEFT JOIN order_details od ON p.id = od.product_id
                        LEFT JOIN orders o ON od.order_id = o.id AND o.status IN ('PAID', 'DIPROSES', 'DIKEMAS', 'DIKIRIM', 'SELESAI')
                        GROUP BY c.id, c.name
                        ORDER BY units_sold DESC
                    ");
                    $rows = $this->db->resultSet();
                    return ['success' => true, 'count' => count($rows), 'data' => $rows];
                }

                case 'getLowStockHighSalesProducts': {
                    $this->db->query("
                        SELECT 
                            p.id, p.name, c.name as category_name, p.stock, p.price,
                            COALESCE(SUM(od.quantity), 0) as total_sold
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        LEFT JOIN order_details od ON p.id = od.product_id
                        LEFT JOIN orders o ON od.order_id = o.id AND o.status IN ('PAID', 'DIPROSES', 'DIKEMAS', 'DIKIRIM', 'SELESAI')
                        WHERE p.stock <= 5
                        GROUP BY p.id
                        HAVING total_sold > 0
                        ORDER BY total_sold DESC, p.stock ASC
                    ");
                    $rows = $this->db->resultSet();
                    return ['success' => true, 'count' => count($rows), 'data' => $rows];
                }

                // ==========================================
                // 6. STOK & INVENTARIS PRODUK
                // ==========================================
                case 'getLowStockProducts': {
                    $threshold = (int)($args['threshold'] ?? 5);
                    $this->db->query("
                        SELECT p.id, p.name, c.name as category_name, p.stock, p.price, p.status
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE p.stock <= :th
                        ORDER BY p.stock ASC, p.name ASC
                    ");
                    $this->db->bind('th', $threshold);
                    $rows = $this->db->resultSet();
                    return ['success' => true, 'count' => count($rows), 'data' => $rows];
                }

                case 'getOutOfStockProducts': {
                    $this->db->query("
                        SELECT p.id, p.name, c.name as category_name, p.stock, p.price
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE p.stock = 0 OR p.status = 'Out_of_Stock'
                        ORDER BY p.name ASC
                    ");
                    $rows = $this->db->resultSet();
                    return ['success' => true, 'count' => count($rows), 'data' => $rows];
                }

                case 'getTotalStock': {
                    $this->db->query("
                        SELECT 
                            COUNT(*) as total_products,
                            COALESCE(SUM(stock), 0) as total_stock_units,
                            SUM(CASE WHEN stock <= 5 THEN 1 ELSE 0 END) as low_stock_products,
                            SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) as out_of_stock_products
                        FROM products
                    ");
                    return ['success' => true, 'data' => $this->db->single()];
                }

                case 'checkProductStock':
                case 'searchProducts': {
                    $query = trim($args['query'] ?? '');
                    $this->db->query("
                        SELECT p.id, p.name, c.name as category_name, p.price, p.stock, p.status
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE p.name LIKE :q OR c.name LIKE :q OR p.description LIKE :q
                        ORDER BY p.stock DESC
                        LIMIT 10
                    ");
                    $this->db->bind('q', '%' . $query . '%');
                    $rows = $this->db->resultSet();
                    return ['success' => true, 'query' => $query, 'count' => count($rows), 'data' => $rows];
                }

                case 'getAllProducts': {
                    $this->db->query("
                        SELECT p.id, p.name, c.name as category_name, p.price, p.stock, p.status
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE p.status != 'Hidden'
                        ORDER BY c.name ASC, p.price ASC
                        LIMIT 30
                    ");
                    $rows = $this->db->resultSet();
                    return ['success' => true, 'count' => count($rows), 'data' => $rows];
                }

                case 'getCheapestProduct': {
                    $this->db->query("
                        SELECT p.id, p.name, c.name as category_name, p.price, p.stock
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE p.status = 'Available' AND p.stock > 0
                        ORDER BY p.price ASC
                        LIMIT 1
                    ");
                    return ['success' => true, 'data' => $this->db->single()];
                }

                case 'getMostExpensiveProduct': {
                    $this->db->query("
                        SELECT p.id, p.name, c.name as category_name, p.price, p.stock
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        ORDER BY p.price DESC
                        LIMIT 1
                    ");
                    return ['success' => true, 'data' => $this->db->single()];
                }

                case 'getTopSellingProducts': {
                    $this->db->query("
                        SELECT p.name, c.name as category_name, SUM(od.quantity) as total_sold, SUM(od.subtotal) as total_revenue
                        FROM order_details od
                        JOIN products p ON od.product_id = p.id
                        LEFT JOIN categories c ON p.category_id = c.id
                        JOIN orders o ON od.order_id = o.id
                        WHERE o.status IN ('PAID', 'DIPROSES', 'DIKEMAS', 'DIKIRIM', 'SELESAI')
                        GROUP BY p.id
                        ORDER BY total_sold DESC
                        LIMIT 5
                    ");
                    $rows = $this->db->resultSet();
                    return ['success' => true, 'count' => count($rows), 'data' => $rows];
                }

                case 'getCategoryStats': {
                    $this->db->query("
                        SELECT c.name, COUNT(p.id) as product_count, COALESCE(SUM(p.stock), 0) as total_stock
                        FROM categories c
                        LEFT JOIN products p ON p.category_id = c.id
                        GROUP BY c.id, c.name
                        ORDER BY product_count DESC
                    ");
                    $rows = $this->db->resultSet();
                    return ['success' => true, 'count' => count($rows), 'data' => $rows];
                }

                // ==========================================
                // 7. PESANAN
                // ==========================================
                case 'getOrdersNeedingProcessing': {
                    $this->db->query("
                        SELECT o.id, o.user_id, u.name as customer_name, o.total, o.status, o.order_date
                        FROM orders o
                        LEFT JOIN users u ON o.user_id = u.id
                        WHERE o.status IN ('DIPROSES', 'DIKEMAS')
                        ORDER BY o.order_date ASC
                    ");
                    $rows = $this->db->resultSet();
                    return ['success' => true, 'count' => count($rows), 'data' => $rows];
                }

                case 'getPendingPaymentOrdersCount': {
                    $this->db->query("SELECT COUNT(*) as pending_count FROM orders WHERE status = 'MENUNGGU PEMBAYARAN'");
                    $row = $this->db->single();
                    return ['success' => true, 'pending_payment_count' => (int)($row['pending_count'] ?? 0)];
                }

                case 'getOrdersByStatus': {
                    $st = strtoupper(trim($args['status'] ?? 'SELESAI'));
                    $this->db->query("
                        SELECT o.id, o.user_id, u.name as customer_name, o.total, o.status, o.order_date
                        FROM orders o
                        LEFT JOIN users u ON o.user_id = u.id
                        WHERE o.status = :st
                        ORDER BY o.order_date DESC
                        LIMIT 20
                    ");
                    $this->db->bind('st', $st);
                    $rows = $this->db->resultSet();
                    return ['success' => true, 'status' => $st, 'count' => count($rows), 'data' => $rows];
                }

                case 'getMyOrders': {
                    if (empty($userId)) {
                        return [
                            'success' => false,
                            'error' => 'Anda sedang mengakses sebagai Tamu. Silakan login ke akun Anda untuk melihat riwayat pesanan pribadi.'
                        ];
                    }
                    $this->db->query("
                        SELECT id, total, status, order_date
                        FROM orders
                        WHERE user_id = :uid
                        ORDER BY order_date DESC
                        LIMIT 10
                    ");
                    $this->db->bind('uid', $userId);
                    $rows = $this->db->resultSet();
                    return ['success' => true, 'count' => count($rows), 'data' => $rows];
                }

                case 'getAllOrders': {
                    $this->db->query("
                        SELECT o.id, o.user_id, u.name as customer_name, o.total, o.status, o.order_date
                        FROM orders o
                        LEFT JOIN users u ON o.user_id = u.id
                        ORDER BY o.order_date DESC
                        LIMIT 20
                    ");
                    $rows = $this->db->resultSet();
                    return ['success' => true, 'count' => count($rows), 'data' => $rows];
                }

                case 'getOrderDetails': {
                    $orderId = (int)($args['order_id'] ?? 0);
                    if ($orderId <= 0) {
                        return ['success' => false, 'error' => 'Nomor ID pesanan tidak valid.'];
                    }

                    $this->db->query("
                        SELECT o.id, o.user_id, u.name as customer_name, u.email as customer_email, o.total, o.status, o.order_date
                        FROM orders o
                        LEFT JOIN users u ON o.user_id = u.id
                        WHERE o.id = :oid
                    ");
                    $this->db->bind('oid', $orderId);
                    $orderHeader = $this->db->single();

                    if (!$orderHeader) {
                        return ['success' => false, 'error' => "Pesanan dengan nomor #{$orderId} tidak ditemukan di database."];
                    }

                    if ($role === 'Customer' && (int)$orderHeader['user_id'] !== $userId) {
                        return [
                            'success' => false,
                            'error' => "Maaf, pesanan #{$orderId} bukan milik akun Anda. Anda hanya memiliki izin untuk melihat rincian pesanan Anda sendiri."
                        ];
                    }

                    $this->db->query("
                        SELECT od.product_id, p.name as product_name, od.quantity, od.price, od.subtotal
                        FROM order_details od
                        LEFT JOIN products p ON od.product_id = p.id
                        WHERE od.order_id = :oid
                    ");
                    $this->db->bind('oid', $orderId);
                    $items = $this->db->resultSet();

                    return [
                        'success' => true,
                        'order' => $orderHeader,
                        'items' => $items
                    ];
                }

                case 'getOrderCustomer': {
                    $orderId = (int)($args['order_id'] ?? 0);
                    $this->db->query("
                        SELECT o.id, o.user_id, u.name as customer_name, u.email as customer_email, u.phone as customer_phone, o.total, o.status
                        FROM orders o
                        LEFT JOIN users u ON o.user_id = u.id
                        WHERE o.id = :oid
                    ");
                    $this->db->bind('oid', $orderId);
                    $row = $this->db->single();
                    if (!$row) {
                        return ['success' => false, 'error' => "Pesanan #{$orderId} tidak ditemukan."];
                    }
                    return ['success' => true, 'data' => $row];
                }

                // ==========================================
                // 8. PEMBAYARAN (PAYMENT GATEWAY)
                // ==========================================
                case 'getPaymentSummary': {
                    $this->db->query("
                        SELECT 
                            COUNT(*) as total_payments,
                            COALESCE(SUM(CASE WHEN status = 'PAID' THEN 1 ELSE 0 END), 0) as paid_count,
                            COALESCE(SUM(CASE WHEN status = 'PENDING' THEN 1 ELSE 0 END), 0) as pending_count,
                            COALESCE(SUM(CASE WHEN status = 'FAILED' THEN 1 ELSE 0 END), 0) as failed_count,
                            COALESCE(SUM(CASE WHEN status = 'PAID' THEN amount ELSE 0 END), 0) as total_paid_amount
                        FROM payments
                    ");
                    $stats = $this->db->single();
                    return ['success' => true, 'data' => $stats];
                }

                case 'getPaymentMethodsBreakdown': {
                    $this->db->query("
                        SELECT 
                            COALESCE(payment_method, 'Belum Ditentukan') as method,
                            COUNT(*) as transaction_count,
                            COALESCE(SUM(amount), 0) as total_amount
                        FROM payments
                        GROUP BY payment_method
                        ORDER BY transaction_count DESC
                    ");
                    $rows = $this->db->resultSet();
                    return ['success' => true, 'count' => count($rows), 'data' => $rows];
                }

                // ==========================================
                // 9. PENGGUNA SISTEM
                // ==========================================
                case 'getUserManagementSummary': {
                    $this->db->query("SELECT role, status, COUNT(*) as count FROM users GROUP BY role, status");
                    $rows = $this->db->resultSet();
                    $total = 0;
                    $byRole = ['Customer' => 0, 'Operator' => 0, 'Admin' => 0, 'Owner' => 0];
                    foreach ($rows as $r) {
                        $cnt = (int)$r['count'];
                        $total += $cnt;
                        $rName = $r['role'] ?? 'Customer';
                        $byRole[$rName] = ($byRole[$rName] ?? 0) + $cnt;
                    }
                    return [
                        'success' => true,
                        'total_users' => $total,
                        'by_role' => $byRole
                    ];
                }

                case 'getCustomerStatistics': {
                    $this->db->query("
                        SELECT 
                            COUNT(*) as total_customers,
                            COALESCE(SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END), 0) as new_this_month
                        FROM users
                        WHERE role = 'Customer'
                    ");
                    return ['success' => true, 'data' => $this->db->single()];
                }

                case 'getAllUsers': {
                    $roleFilter = !empty($args['role']) ? trim($args['role']) : null;
                    $sql = "SELECT id, name, email, phone, role, status FROM users WHERE 1=1";
                    if (!empty($roleFilter)) {
                        $sql .= " AND LOWER(role) = LOWER(:role)";
                    }
                    $sql .= " ORDER BY role ASC, id ASC LIMIT 50";
                    $this->db->query($sql);
                    if (!empty($roleFilter)) {
                        $this->db->bind('role', $roleFilter);
                    }
                    $rows = $this->db->resultSet();
                    return ['success' => true, 'count' => count($rows), 'data' => $rows, 'role' => $roleFilter];
                }

                case 'getTopSpendingCustomer': {
                    $this->db->query("
                        SELECT u.name, u.email, COUNT(o.id) as total_orders, SUM(o.total) as total_spent
                        FROM orders o
                        JOIN users u ON o.user_id = u.id
                        WHERE o.status IN ('PAID', 'DIPROSES', 'DIKEMAS', 'DIKIRIM', 'SELESAI')
                        GROUP BY u.id
                        ORDER BY total_spent DESC
                        LIMIT 5
                    ");
                    $rows = $this->db->resultSet();
                    return ['success' => true, 'count' => count($rows), 'data' => $rows];
                }

                // ==========================================
                // 10. OPERASIONAL TOKO
                // ==========================================
                case 'getOperationalQueue': {
                    $this->db->query("
                        SELECT 
                            SUM(CASE WHEN status = 'MENUNGGU PEMBAYARAN' THEN 1 ELSE 0 END) as pending_payment,
                            SUM(CASE WHEN status = 'DIPROSES' THEN 1 ELSE 0 END) as in_process,
                            SUM(CASE WHEN status = 'DIKEMAS' THEN 1 ELSE 0 END) as packing,
                            SUM(CASE WHEN status = 'DIKIRIM' THEN 1 ELSE 0 END) as in_shipping,
                            SUM(CASE WHEN status = 'SELESAI' THEN 1 ELSE 0 END) as completed,
                            SUM(CASE WHEN status = 'DIBATALKAN' THEN 1 ELSE 0 END) as cancelled
                        FROM orders
                    ");
                    $queue = $this->db->single();
                    return ['success' => true, 'data' => $queue];
                }

                // ==========================================
                // 11. IKHTISAR DATABASE
                // ==========================================
                case 'getDatabaseOverview': {
                    $this->db->query("SHOW TABLES");
                    $tableRows = $this->db->resultSet();
                    $overview = [];
                    foreach ($tableRows as $tr) {
                        $tableName = array_values($tr)[0];
                        $this->db->query("SELECT COUNT(*) as c FROM `{$tableName}`");
                        $c = (int)($this->db->single()['c'] ?? 0);
                        $overview[] = [
                            'table_name' => $tableName,
                            'total_rows' => $c
                        ];
                    }
                    return ['success' => true, 'data' => $overview];
                }

                default:
                    return ['success' => false, 'error' => "Tool '$toolName' belum didukung."];
            }
        } catch (Throwable $e) {
            error_log('[AIDatabaseService ERROR]: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Gagal membaca data database: ' . $e->getMessage()];
        }
    }

    /**
     * Format data hasil query database langsung menjadi respons Markdown to-the-point ("jawab apa yang ditanya")
     */
    public function formatDirectDataResponse(string $toolName, array $result, string $userMessage = ''): string {
        $q = strtolower($userMessage);
        $data = $result['data'] ?? $result;

        // ==========================================
        // 1. FORMAT BACA ISI TABEL SPESIFIK
        // ==========================================
        if ($toolName === 'readTableData') {
            $tableName = $result['table'] ?? '';
            $totalRows = $result['total_rows'] ?? 0;
            $rows = is_array($data) ? $data : [];

            if (empty($rows)) {
                return "Tabel `{$tableName}` saat ini **masih kosong** (0 baris data tersimpan).";
            }

            $md = "### 📋 Isi Tabel `{$tableName}` (Total {$totalRows} baris, ditampilkan " . count($rows) . " data):\n\n";

            switch ($tableName) {
                case 'categories':
                    $md .= "| ID | Nama Kategori | Deskripsi | Dibuat Pada |\n";
                    $md .= "| :---: | :--- | :--- | :---: |\n";
                    foreach ($rows as $r) {
                        $tgl = substr($r['created_at'] ?? '', 0, 10);
                        $md .= "| #{$r['id']} | **{$r['name']}** | {$r['description']} | {$tgl} |\n";
                    }
                    break;

                case 'products':
                    $md .= "| ID | Nama Produk | Harga | Sisa Stok | Status Produk |\n";
                    $md .= "| :---: | :--- | :---: | :---: | :---: |\n";
                    foreach ($rows as $r) {
                        $prc = number_format($r['price'], 0, ',', '.');
                        $stk = ($r['stock'] > 0) ? "**{$r['stock']} unit**" : "<span class='text-danger'>Habis</span>";
                        $md .= "| #{$r['id']} | **{$r['name']}** | Rp {$prc} | {$stk} | `{$r['status']}` |\n";
                    }
                    break;

                case 'orders':
                    $md .= "| No. Pesanan | ID User | Tanggal Transaksi | Total Belanja | Status Pesanan |\n";
                    $md .= "| :---: | :---: | :---: | :---: | :---: |\n";
                    foreach ($rows as $r) {
                        $tot = number_format($r['total'], 0, ',', '.');
                        $tgl = substr($r['order_date'] ?? '', 0, 16);
                        $md .= "| #{$r['id']} | User #{$r['user_id']} | {$tgl} | Rp {$tot} | **{$r['status']}** |\n";
                    }
                    break;

                case 'order_details':
                    $md .= "| ID | No. Pesanan | ID Produk | Kuantitas (Qty) | Harga Satuan | Subtotal |\n";
                    $md .= "| :---: | :---: | :---: | :---: | :---: | :---: |\n";
                    foreach ($rows as $r) {
                        $prc = number_format($r['price'], 0, ',', '.');
                        $sub = number_format($r['subtotal'], 0, ',', '.');
                        $md .= "| #{$r['id']} | Pesanan #{$r['order_id']} | Produk #{$r['product_id']} | **{$r['quantity']}x** | Rp {$prc} | Rp {$sub} |\n";
                    }
                    break;

                case 'payments':
                    $md .= "| ID | No. Pesanan | Metode Pembayaran | Gateway | Jumlah | Status |\n";
                    $md .= "| :---: | :---: | :--- | :---: | :---: | :---: |\n";
                    foreach ($rows as $r) {
                        $amt = number_format($r['amount'], 0, ',', '.');
                        $md .= "| #{$r['id']} | Pesanan #{$r['order_id']} | **{$r['payment_method']}** | {$r['gateway']} | Rp {$amt} | `{$r['status']}` |\n";
                    }
                    break;

                case 'users':
                    $md .= "| ID | Nama Lengkap | Email Terdaftar | Nomor Telepon | Peran (Role) | Status |\n";
                    $md .= "| :---: | :--- | :--- | :--- | :---: | :---: |\n";
                    foreach ($rows as $r) {
                        $md .= "| #{$r['id']} | **{$r['name']}** | `{$r['email']}` | " . ($r['phone'] ?: '-') . " | **{$r['role']}** | `{$r['status']}` |\n";
                    }
                    break;

                case 'system_settings':
                    $md .= "| ID | Kunci Pengaturan (Key) | Nilai Setting | Grup | Deskripsi |\n";
                    $md .= "| :---: | :--- | :--- | :---: | :--- |\n";
                    foreach ($rows as $r) {
                        $val = htmlspecialchars($r['setting_value'] ?? '');
                        if (strlen($val) > 40) $val = substr($val, 0, 37) . '...';
                        $md .= "| #{$r['id']} | `{$r['setting_key']}` | **{$val}** | `{$r['setting_group']}` | {$r['description']} |\n";
                    }
                    break;

                case 'system_audit_logs':
                    $md .= "| ID | Aktor | Role | Aksi | Kategori | Status | Keterangan |\n";
                    $md .= "| :---: | :--- | :---: | :--- | :---: | :---: | :--- |\n";
                    foreach ($rows as $r) {
                        $det = htmlspecialchars($r['details'] ?? '');
                        if (strlen($det) > 45) $det = substr($det, 0, 42) . '...';
                        $md .= "| #{$r['id']} | **{$r['user_name']}** | {$r['user_role']} | `{$r['action']}` | {$r['category']} | `{$r['status']}` | {$det} |\n";
                    }
                    break;

                case 'whatsapp_notification_logs':
                    $md .= "| ID | No. Pesanan | Tipe Event | No. HP Tujuan | Status Kirim | Waktu |\n";
                    $md .= "| :---: | :---: | :--- | :--- | :---: | :---: |\n";
                    foreach ($rows as $r) {
                        $tgl = substr($r['created_at'] ?? '', 0, 16);
                        $md .= "| #{$r['id']} | Pesanan #{$r['order_id']} | `{$r['event_type']}` | {$r['recipient_phone']} | **{$r['status']}** | {$tgl} |\n";
                    }
                    break;

                case 'otp_requests':
                    $md .= "| ID | No. HP Tujuan | ID User | Percobaan | Status | Waktu Request |\n";
                    $md .= "| :---: | :--- | :---: | :---: | :---: | :---: |\n";
                    foreach ($rows as $r) {
                        $tgl = substr($r['created_at'] ?? '', 0, 16);
                        $md .= "| #{$r['id']} | {$r['phone']} | User #" . ($r['user_id'] ?: '-') . " | {$r['attempts']}x | `{$r['status']}` | {$tgl} |\n";
                    }
                    break;

                case 'ai_conversations':
                    $md .= "| ID | ID User | Pertanyaan Pengguna | Cuplikan Jawaban AI | Waktu |\n";
                    $md .= "| :---: | :---: | :--- | :--- | :---: |\n";
                    foreach ($rows as $r) {
                        $qSnippet = htmlspecialchars(mb_substr($r['question'] ?? '', 0, 35));
                        $aSnippet = htmlspecialchars(mb_substr(strip_tags($r['answer'] ?? ''), 0, 40));
                        $tgl = substr($r['created_at'] ?? '', 0, 16);
                        $md .= "| #{$r['id']} | #" . ($r['user_id'] ?: 'Tamu') . " | \"{$qSnippet}...\" | {$aSnippet}... | {$tgl} |\n";
                    }
                    break;

                case 'reviews':
                    $md .= "| ID | ID User | ID Produk | Rating | Komentar | Tanggal |\n";
                    $md .= "| :---: | :---: | :---: | :---: | :--- | :---: |\n";
                    foreach ($rows as $r) {
                        $md .= "| #{$r['id']} | User #{$r['user_id']} | Produk #{$r['product_id']} | {$r['rating']} ⭐ | {$r['comment']} | {$r['created_at']} |\n";
                    }
                    break;

                default:
                    $firstRow = $rows[0];
                    $cols = array_keys($firstRow);
                    $md .= "| " . implode(' | ', $cols) . " |\n";
                    $md .= "| " . implode(' | ', array_fill(0, count($cols), ':---')) . " |\n";
                    foreach ($rows as $r) {
                        $vals = array_map(fn($v) => htmlspecialchars(substr((string)$v, 0, 30)), array_values($r));
                        $md .= "| " . implode(' | ', $vals) . " |\n";
                    }
                    break;
            }

            return $md;
        }

        // ==========================================
        // 2. FORMAT IKHTISAR SELURUH TABEL SISTEM
        // ==========================================
        if ($toolName === 'getAllTablesOverview') {
            $tables = is_array($data) ? $data : [];
            $md = "### 🗄️ Seluruh Isi & Struktur Tabel Sistem `nnaco_sa_db`\n\n"
                . "Berikut ringkasan seluruh **12 tabel** di basis data toko beserta jumlah data aktual dan hak akses peran Anda:\n\n"
                . "| No | Nama Tabel | Deskripsi & Fungsi | Jumlah Baris Data | Status Hak Akses |\n"
                . "| :---: | :--- | :--- | :---: | :---: |\n";
            $i = 1;
            foreach ($tables as $t) {
                $accessBadge = $t['accessible'] ? "<span class='badge bg-success-subtle text-success'>Diizinkan</span>" : "<span class='badge bg-danger-subtle text-danger'>Terbatas (Rahasia)</span>";
                $md .= "| " . ($i++) . " | `{$t['table']}` | **{$t['name']}**: {$t['description']} | **{$t['row_count']}** baris | {$accessBadge} |\n";
            }
            $md .= "\n*Untuk melihat data lengkap pada tabel tertentu, Anda cukup mengetik: `tampilkan isi tabel [nama_tabel]` (misal: `tampilkan isi tabel products` atau `isi tabel categories`).*";
            return $md;
        }

        // ==========================================
        // 3. FORMAT STRUKTUR KOLOM TABEL (DESCRIBE)
        // ==========================================
        if ($toolName === 'describeTable') {
            $tableName = $result['table'] ?? '';
            $cols = $result['columns'] ?? [];
            $md = "### 📐 Struktur Kolom Tabel `{$tableName}`:\n\n";
            $md .= "| Kolom | Tipe Data | Nullable | Primary/Key | Default |\n";
            $md .= "| :--- | :---: | :---: | :---: | :---: |\n";
            foreach ($cols as $c) {
                $md .= "| **{$c['Field']}** | `{$c['Type']}` | {$c['Null']} | `{$c['Key']}` | " . ($c['Default'] ?? 'NULL') . " |\n";
            }
            return $md;
        }

        // 4. Profil Pribadi (getCurrentUserProfile)
        if ($toolName === 'getCurrentUserProfile') {
            if (empty($data['is_logged_in'])) {
                return "Anda saat ini mengakses website **nnaco_sa** sebagai **Tamu (Guest)** / belum login. Silakan login untuk melihat riwayat pesanan dan profil Anda.";
            }
            return "Halo **{$data['name']}**! 👋\n\n"
                . "Saat ini Anda sedang login ke website **nnaco_sa** dengan akun berikut:\n\n"
                . "| Data Akun | Informasi Pengguna |\n"
                . "| :--- | :--- |\n"
                . "| **ID Pengguna** | #{$data['id']} |\n"
                . "| **Nama Lengkap** | **{$data['name']}** |\n"
                . "| **Email Terdaftar** | `{$data['email']}` |\n"
                . "| **Nomor Telepon** | " . ($data['phone'] ?: '-') . " |\n"
                . "| **Peran (Role)** | **{$data['role']}** |\n"
                . "| **Status Akun** | `{$data['status']}` |\n\n"
                . "Sebagai **{$data['role']}**, Anda memiliki hak akses sesuai otorisasi sistem.";
        }

        // 5. Total Pengguna (getUserManagementSummary)
        if ($toolName === 'getUserManagementSummary') {
            $total = $result['total_users'] ?? 0;
            $byRole = $result['by_role'] ?? [];
            return "Saat ini terdapat total **{$total} pengguna** terdaftar di sistem **nnaco_sa**:\n"
                . "- **" . ($byRole['Customer'] ?? 0) . "** Customer\n"
                . "- **" . ($byRole['Operator'] ?? 0) . "** Operator\n"
                . "- **" . ($byRole['Admin'] ?? 0) . "** Admin\n"
                . "- **" . ($byRole['Owner'] ?? 0) . "** Owner";
        }

        // 6. Statistik Customer (getCustomerStatistics)
        if ($toolName === 'getCustomerStatistics') {
            $tot = $data['total_customers'] ?? 0;
            $new = $data['new_this_month'] ?? 0;
            return "Terdapat total **{$tot} Customer** terdaftar di toko nnaco_sa, dengan **{$new} Customer baru** mendaftar pada bulan ini.";
        }

        // 7. Daftar Pengguna (getAllUsers)
        if ($toolName === 'getAllUsers') {
            $users = is_array($data) ? $data : [];
            $roleFilter = $result['role'] ?? null;
            if (empty($users)) {
                $roleLabel = $roleFilter ? "dengan role **{$roleFilter}**" : "";
                return "Tidak ditemukan pengguna {$roleLabel} di basis data sistem **nnaco_sa**.";
            }
            $roleLabel = $roleFilter ? " dengan role **{$roleFilter}**" : "";
            $totalCount = count($users);
            $md = "Berikut data pengguna{$roleLabel} yang terdaftar di sistem **nnaco_sa** (Total: {$totalCount} akun):\n\n";
            $md .= "| ID | Nama Lengkap | Email Terdaftar | Nomor Telepon / WA | Role | Status |\n";
            $md .= "| :---: | :--- | :--- | :--- | :---: | :---: |\n";
            foreach ($users as $u) {
                $ph = !empty($u['phone']) ? $u['phone'] : '-';
                $md .= "| #{$u['id']} | **{$u['name']}** | `{$u['email']}` | {$ph} | **{$u['role']}** | {$u['status']} |\n";
            }
            return $md;
        }

        // 8. Omzet & Penjualan (getOmzetReport / getSalesSummary)
        if ($toolName === 'getOmzetReport' || $toolName === 'getSalesSummary') {
            $today = number_format($data['omzet_today'] ?? 0, 0, ',', '.');
            $thisMonth = number_format($data['omzet_this_month'] ?? 0, 0, ',', '.');
            $thisYear = number_format($data['omzet_this_year'] ?? 0, 0, ',', '.');
            $allTime = number_format($data['omzet_all_time'] ?? 0, 0, ',', '.');
            $totalOrders = $data['total_orders'] ?? 0;
            $aov = number_format($data['average_order_value'] ?? 0, 0, ',', '.');

            if (str_contains($q, 'hari ini')) {
                return "Total omzet penjualan toko **hari ini** adalah **Rp {$today}**.";
            }
            if (str_contains($q, 'bulan ini')) {
                return "Total omzet penjualan toko **bulan ini** adalah **Rp {$thisMonth}** (dari akumulasi pesanan berhasil).";
            }
            if (str_contains($q, 'tahun ini')) {
                return "Total omzet penjualan toko **tahun ini** adalah **Rp {$thisYear}**.";
            }
            if (str_contains($q, 'rata rata')) {
                return "Rata-rata nilai transaksi (Average Order Value) adalah **Rp {$aov}** dari total {$totalOrders} pesanan berhasil.";
            }

            return "### 💰 Ringkasan Laporan Omzet Penjualan nnaco_sa\n\n"
                . "- **Omzet Hari Ini**: Rp {$today}\n"
                . "- **Omzet Bulan Ini**: Rp {$thisMonth}\n"
                . "- **Omzet Tahun Ini**: Rp {$thisYear}\n"
                . "- **Total Akumulasi Transaksi Berhasil**: **{$totalOrders} pesanan** (Total: **Rp {$allTime}**)\n"
                . "- **Rata-rata Nilai Transaksi**: Rp {$aov}\n\n"
                . "*Data dihitung real-time dari pesanan berstatus dibayar/diproses.*";
        }

        // 9. Perbandingan Penjualan (getSalesComparison)
        if ($toolName === 'getSalesComparison') {
            $cur = $data['current_month'] ?? [];
            $prev = $data['previous_month'] ?? [];
            $diff = number_format(abs($data['difference_omzet'] ?? 0), 0, ',', '.');
            $pct = $data['percentage_change'] ?? 0;
            $direction = ($data['difference_omzet'] >= 0) ? 'meningkat' : 'menurun';

            $curOmzet = number_format($cur['omzet'] ?? 0, 0, ',', '.');
            $prevOmzet = number_format($prev['omzet'] ?? 0, 0, ',', '.');

            return "### 📈 Perbandingan Penjualan Bulan Ini vs Bulan Sebelumnya\n\n"
                . "- **Bulan Ini ({$cur['ym']})**: Rp {$curOmzet} ({$cur['orders_count']} pesanan)\n"
                . "- **Bulan Lalu ({$prev['ym']})**: Rp {$prevOmzet} ({$prev['orders_count']} pesanan)\n"
                . "- **Perubahan**: Penjualan **{$direction}** sebesar **Rp {$diff} ({$pct}%)** dibanding bulan lalu.";
        }

        // 10. Penjualan per Kategori (getSalesByCategory)
        if ($toolName === 'getSalesByCategory') {
            $rows = is_array($data) ? $data : [];
            if (empty($rows)) return "Belum ada data penjualan per kategori.";
            $md = "Berikut data jumlah produk terjual berdasarkan kategori:\n\n";
            $md .= "| Kategori | Total Unit Terjual | Akumulasi Pendapatan |\n";
            $md .= "| :--- | :---: | :---: |\n";
            foreach ($rows as $r) {
                $rev = number_format($r['total_revenue'], 0, ',', '.');
                $md .= "| **{$r['category_name']}** | {$r['units_sold']} unit | Rp {$rev} |\n";
            }
            return $md;
        }

        // 11. Stok Rendah tapi Penjualan Tinggi (getLowStockHighSalesProducts)
        if ($toolName === 'getLowStockHighSalesProducts') {
            $rows = is_array($data) ? $data : [];
            if (empty($rows)) return "Tidak ditemukan produk dengan kombinasi stok rendah dan penjualan tinggi saat ini.";
            $md = "Berikut produk yang memiliki **stok menipis (≤ 5 unit)** namun memiliki **penjualan tinggi** (prioritas restock):\n\n";
            $md .= "| ID | Nama Produk | Kategori | Sisa Stok | Total Terjual |\n";
            $md .= "| :---: | :--- | :--- | :---: | :---: |\n";
            foreach ($rows as $r) {
                $md .= "| #{$r['id']} | **{$r['name']}** | {$r['category_name']} | **{$r['stock']} unit** | **{$r['total_sold']} unit** |\n";
            }
            return $md;
        }

        // 12. Produk Stok Menipis (getLowStockProducts)
        if ($toolName === 'getLowStockProducts') {
            $items = is_array($data) ? $data : [];
            if (empty($items)) return "Seluruh produk memiliki stok aman di atas batas minimum (tidak ada stok menipis).";
            $md = "Berikut produk aksesoris dengan stok menipis (≤ 5 unit):\n\n";
            $md .= "| ID | Nama Produk | Kategori | Sisa Stok | Harga Satuan |\n";
            $md .= "| :---: | :--- | :--- | :---: | :---: |\n";
            foreach ($items as $p) {
                $prc = number_format($p['price'], 0, ',', '.');
                $md .= "| #{$p['id']} | **{$p['name']}** | {$p['category_name']} | **{$p['stock']} unit** | Rp {$prc} |\n";
            }
            return $md;
        }

        // 13. Produk Stok Habis (getOutOfStockProducts)
        if ($toolName === 'getOutOfStockProducts') {
            $items = is_array($data) ? $data : [];
            if (empty($items)) return "Saat ini tidak ada produk yang habis. Semua produk memiliki stok.";
            $md = "Berikut produk aksesoris yang sedang **kehabisan stok (0 unit)**:\n\n";
            $md .= "| ID | Nama Produk | Kategori | Harga Satuan |\n";
            $md .= "| :---: | :--- | :--- | :---: |\n";
            foreach ($items as $p) {
                $prc = number_format($p['price'], 0, ',', '.');
                $md .= "| #{$p['id']} | **{$p['name']}** | {$p['category_name']} | Rp {$prc} |\n";
            }
            return $md;
        }

        // 14. Total Stok (getTotalStock)
        if ($toolName === 'getTotalStock') {
            $units = number_format($data['total_stock_units'] ?? 0, 0, ',', '.');
            $totalProd = $data['total_products'] ?? 0;
            $low = $data['low_stock_products'] ?? 0;
            $out = $data['out_of_stock_products'] ?? 0;
            return "Saat ini terdapat total **{$units} unit stok** aksesoris dari **{$totalProd} varian produk** di toko **nnaco_sa** ({$low} produk stok menipis, {$out} produk habis).";
        }

        // 15. Cek Stok Produk Spesifik (checkProductStock)
        if ($toolName === 'checkProductStock') {
            $items = is_array($data) ? $data : [];
            if (empty($items)) {
                return "Maaf, tidak ditemukan produk yang cocok dengan pencarian '{$result['query']}' di katalog toko **nnaco_sa**.";
            }
            $md = "Berikut informasi ketersediaan stok untuk produk '{$result['query']}':\n\n";
            $md .= "| ID | Nama Produk | Kategori | Harga | Sisa Stok | Status |\n";
            $md .= "| :---: | :--- | :--- | :--- | :---: | :---: |\n";
            foreach ($items as $p) {
                $prc = number_format($p['price'], 0, ',', '.');
                $stk = ($p['stock'] > 0) ? "**{$p['stock']} unit**" : "<span class='text-danger'>Habis</span>";
                $md .= "| #{$p['id']} | **{$p['name']}** | {$p['category_name']} | Rp {$prc} | {$stk} | `{$p['status']}` |\n";
            }
            return $md;
        }

        // 16. Daftar Seluruh Produk (getAllProducts)
        if ($toolName === 'getAllProducts') {
            $items = is_array($data) ? $data : [];
            if (empty($items)) return "Saat ini belum ada produk yang tersedia di katalog toko.";
            $md = "Berikut daftar produk aksesoris yang tersedia di toko **nnaco_sa**:\n\n";
            $md .= "| ID | Nama Produk | Kategori | Harga Satuan | Status Stok |\n";
            $md .= "| :---: | :--- | :--- | :---: | :---: |\n";
            foreach ($items as $p) {
                $prc = number_format($p['price'], 0, ',', '.');
                $stk = ($p['stock'] > 0) ? "Tersedia ({$p['stock']} unit)" : "Habis";
                $md .= "| #{$p['id']} | **{$p['name']}** | {$p['category_name']} | Rp {$prc} | {$stk} |\n";
            }
            return $md;
        }

        // 17. Pesanan Perlu Diproses (getOrdersNeedingProcessing)
        if ($toolName === 'getOrdersNeedingProcessing') {
            $orders = is_array($data) ? $data : [];
            if (empty($orders)) return "Saat ini seluruh pesanan operasional telah ditangani (tidak ada pesanan yang menunggu proses).";
            $md = "Terdapat **" . count($orders) . " pesanan** yang perlu segera ditangani Operator:\n\n";
            $md .= "| No. Pesanan | Pelanggan | Tanggal | Total Belanja | Status |\n";
            $md .= "| :---: | :--- | :---: | :---: | :---: |\n";
            foreach ($orders as $o) {
                $tot = number_format($o['total'], 0, ',', '.');
                $tgl = substr($o['order_date'] ?? '', 0, 10);
                $md .= "| #{$o['id']} | **{$o['customer_name']}** | {$tgl} | Rp {$tot} | **{$o['status']}** |\n";
            }
            return $md;
        }

        // 18. Pending Payment Count
        if ($toolName === 'getPendingPaymentOrdersCount') {
            $cnt = $result['pending_payment_count'] ?? 0;
            return "Saat ini terdapat **{$cnt} pesanan** yang masih berstatus **MENUNGGU PEMBAYARAN**.";
        }

        // 19. Pesanan Berdasarkan Status (getOrdersByStatus)
        if ($toolName === 'getOrdersByStatus') {
            $orders = is_array($data) ? $data : [];
            $st = $result['status'] ?? '';
            if (empty($orders)) return "Tidak ditemukan pesanan dengan status **{$st}**.";
            $md = "Terdapat **" . count($orders) . " pesanan** dengan status **{$st}**:\n\n";
            $md .= "| No. Pesanan | Pelanggan | Tanggal | Total Belanja |\n";
            $md .= "| :---: | :--- | :---: | :---: |\n";
            foreach ($orders as $o) {
                $tot = number_format($o['total'], 0, ',', '.');
                $tgl = substr($o['order_date'] ?? '', 0, 10);
                $md .= "| #{$o['id']} | **{$o['customer_name']}** | {$tgl} | Rp {$tot} |\n";
            }
            return $md;
        }

        // 20. Detail Pesanan Spesifik (getOrderDetails)
        if ($toolName === 'getOrderDetails') {
            $order = $result['order'] ?? [];
            $items = $result['items'] ?? [];
            $tot = number_format($order['total'] ?? 0, 0, ',', '.');
            $tgl = substr($order['order_date'] ?? '', 0, 16);

            $md = "### 📦 Detail Pesanan #{$order['id']}\n\n"
                . "- **Pelanggan**: **{$order['customer_name']}** ({$order['customer_email']})\n"
                . "- **Tanggal Pesanan**: {$tgl}\n"
                . "- **Status Saat Ini**: **{$order['status']}**\n"
                . "- **Total Pembayaran**: **Rp {$tot}**\n\n"
                . "#### Rincian Produk:\n"
                . "| No | Nama Produk | Jumlah | Harga Satuan | Subtotal |\n"
                . "| :---: | :--- | :---: | :---: | :---: |\n";
            $i = 1;
            foreach ($items as $it) {
                $prc = number_format($it['price'], 0, ',', '.');
                $sub = number_format($it['subtotal'], 0, ',', '.');
                $md .= "| " . ($i++) . " | **{$it['product_name']}** | {$it['quantity']}x | Rp {$prc} | Rp {$sub} |\n";
            }
            return $md;
        }

        // 21. Siapa Customer Pesanan (getOrderCustomer)
        if ($toolName === 'getOrderCustomer') {
            return "Pesanan **#{$data['id']}** dilakukan oleh Customer **{$data['customer_name']}** (Email: `{$data['customer_email']}`, No. HP: " . ($data['customer_phone'] ?: '-') . "). Total belanja: **Rp " . number_format($data['total'], 0, ',', '.') . "** (Status: **{$data['status']}**).";
        }

        // 22. Pesanan Customer Sendiri (getMyOrders)
        if ($toolName === 'getMyOrders') {
            $orders = is_array($data) ? $data : [];
            if (empty($orders)) return "Anda belum memiliki riwayat pesanan di toko nnaco_sa.";
            $md = "Berikut riwayat pesanan belanja Anda:\n\n";
            $md .= "| No. Pesanan | Tanggal | Total Belanja | Status Pesanan |\n";
            $md .= "| :---: | :---: | :---: | :---: |\n";
            foreach ($orders as $o) {
                $tot = number_format($o['total'], 0, ',', '.');
                $tgl = substr($o['order_date'] ?? '', 0, 10);
                $md .= "| #{$o['id']} | {$tgl} | Rp {$tot} | **{$o['status']}** |\n";
            }
            return $md;
        }

        // 23. Semua Pesanan (getAllOrders)
        if ($toolName === 'getAllOrders') {
            $orders = is_array($data) ? $data : [];
            if (empty($orders)) return "Belum ada pesanan yang tercatat di database toko.";
            $md = "Berikut daftar transaksi pesanan di sistem **nnaco_sa**:\n\n";
            $md .= "| No. Pesanan | Pelanggan | Tanggal | Total | Status |\n";
            $md .= "| :---: | :--- | :---: | :---: | :---: |\n";
            foreach ($orders as $o) {
                $tot = number_format($o['total'], 0, ',', '.');
                $tgl = substr($o['order_date'] ?? '', 0, 10);
                $md .= "| #{$o['id']} | **{$o['customer_name']}** | {$tgl} | Rp {$tot} | **{$o['status']}** |\n";
            }
            return $md;
        }

        // 24. Ringkasan Pembayaran (getPaymentSummary)
        if ($toolName === 'getPaymentSummary') {
            $paid = $data['paid_count'] ?? 0;
            $pend = $data['pending_count'] ?? 0;
            $fail = $data['failed_count'] ?? 0;
            $amt = number_format($data['total_paid_amount'] ?? 0, 0, ',', '.');
            return "### 💳 Ringkasan Pembayaran Transaksi\n\n"
                . "- **Pembayaran Berhasil**: **{$paid} transaksi** (Total: **Rp {$amt}**)\n"
                . "- **Menunggu Pembayaran**: **{$pend} transaksi**\n"
                . "- **Gagal / Dibatalkan**: **{$fail} transaksi**";
        }

        // 25. Metode Pembayaran Terbanyak (getPaymentMethodsBreakdown)
        if ($toolName === 'getPaymentMethodsBreakdown') {
            $rows = is_array($data) ? $data : [];
            if (empty($rows)) return "Belum ada transaksi pembayaran yang tercatat di sistem.";
            $md = "Berikut rincian penggunaan metode pembayaran:\n\n";
            $md .= "| Metode Pembayaran | Jumlah Transaksi | Akumulasi Nilai |\n";
            $md .= "| :--- | :---: | :---: |\n";
            foreach ($rows as $r) {
                $amt = number_format($r['total_amount'], 0, ',', '.');
                $md .= "| **{$r['method']}** | {$r['transaction_count']} transaksi | Rp {$amt} |\n";
            }
            return $md;
        }

        // 26. Kategori Aksesoris (getCategoryStats)
        if ($toolName === 'getCategoryStats') {
            $cats = is_array($data) ? $data : [];
            if (str_contains($q, 'berapa') && !str_contains($q, 'apa saja') && !str_contains($q, 'detail')) {
                return "Saat ini terdapat **" . count($cats) . " kategori** aksesoris di toko **nnaco_sa**.";
            }
            $md = "Berikut daftar kategori aksesoris di toko **nnaco_sa**:\n\n";
            $md .= "| No | Kategori | Jumlah Varian Produk | Total Unit Stok |\n";
            $md .= "| :---: | :--- | :---: | :---: |\n";
            $i = 1;
            foreach ($cats as $c) {
                $md .= "| " . ($i++) . " | **{$c['name']}** | {$c['product_count']} produk | {$c['total_stock']} unit |\n";
            }
            return $md;
        }

        // 27. Produk Termurah & Termahal
        if ($toolName === 'getCheapestProduct') {
            $prc = number_format($data['price'] ?? 0, 0, ',', '.');
            return "Produk paling terjangkau di katalog toko **nnaco_sa** saat ini adalah **{$data['name']}** seharga **Rp {$prc}** (Kategori: **{$data['category_name']}**, Sisa Stok: **{$data['stock']} unit**).";
        }
        if ($toolName === 'getMostExpensiveProduct') {
            $prc = number_format($data['price'] ?? 0, 0, ',', '.');
            return "Produk termahal di katalog toko **nnaco_sa** saat ini adalah **{$data['name']}** seharga **Rp {$prc}** (Kategori: **{$data['category_name']}**, Sisa Stok: **{$data['stock']} unit**).";
        }

        // 28. Produk Terlaris (getTopSellingProducts)
        if ($toolName === 'getTopSellingProducts') {
            $items = is_array($data) ? $data : [];
            if (empty($items)) return "Belum ada produk yang terjual dari pesanan yang telah dikonfirmasi.";
            $md = "Berikut produk yang paling banyak terjual di toko **nnaco_sa**:\n\n";
            $md .= "| No | Produk Terlaris | Kategori | Total Terjual | Akumulasi Penjualan |\n";
            $md .= "| :---: | :--- | :--- | :---: | :---: |\n";
            $i = 1;
            foreach ($items as $it) {
                $rev = number_format($it['total_revenue'], 0, ',', '.');
                $md .= "| " . ($i++) . " | **{$it['name']}** | {$it['category_name']} | **{$it['total_sold']} unit** | Rp {$rev} |\n";
            }
            return $md;
        }

        // 29. Top Spending Customer
        if ($toolName === 'getTopSpendingCustomer') {
            $items = is_array($data) ? $data : [];
            if (empty($items)) return "Belum ada data akumulasi transaksi customer.";
            $md = "Berikut pelanggan (Customer) dengan nilai belanja terbanyak di **nnaco_sa**:\n\n";
            $md .= "| No | Nama Pelanggan | Email | Total Pesanan | Akumulasi Belanja |\n";
            $md .= "| :---: | :--- | :--- | :---: | :---: |\n";
            $i = 1;
            foreach ($items as $c) {
                $spent = number_format($c['total_spent'], 0, ',', '.');
                $md .= "| " . ($i++) . " | **{$c['name']}** | {$c['email']} | {$c['total_orders']} pesanan | **Rp {$spent}** |\n";
            }
            return $md;
        }

        // 30. Antrean Operasional (getOperationalQueue)
        if ($toolName === 'getOperationalQueue') {
            return "### 📋 Ringkasan Aktivitas Operasional Pesanan Hari Ini\n\n"
                . "- ⏳ **Menunggu Pembayaran**: " . ($data['pending_payment'] ?? 0) . " pesanan\n"
                . "- ⚙️ **Sedang Diproses Operator**: **" . ($data['in_process'] ?? 0) . " pesanan**\n"
                . "- 📦 **Sedang Dikemas**: **" . ($data['packing'] ?? 0) . " pesanan**\n"
                . "- 🚚 **Sedang Dalam Pengiriman**: " . ($data['in_shipping'] ?? 0) . " pesanan\n"
                . "- ✅ **Pesanan Selesai**: " . ($data['completed'] ?? 0) . " pesanan\n"
                . "- ❌ **Pesanan Dibatalkan**: " . ($data['cancelled'] ?? 0) . " pesanan";
        }

        // 31. Ikhtisar Database
        if ($toolName === 'getDatabaseOverview') {
            $tables = is_array($data) ? $data : [];
            $md = "### 📊 Ikhtisar Database `nnaco_sa_db`\n\n";
            $md .= "| Nama Tabel | Jumlah Baris Data |\n";
            $md .= "| :--- | :---: |\n";
            foreach ($tables as $t) {
                $md .= "| `{$t['table_name']}` | **{$t['total_rows']}** baris |\n";
            }
            return $md;
        }

        return "Data berhasil ditemukan di database.";
    }

    /**
     * Menghasilkan ringkasan konteks database faktual terstruktur untuk disuntikkan ke LLM (Gemini, Groq, Mock)
     * Konteks ini difilter secara ketat berdasarkan Role pengguna (RBAC & RLS)
     */
    public function getDatabaseContextForAI(string $userRole = 'Customer', ?int $userId = null, string $userMessage = ''): string {
        $ctx = [];
        $ctx[] = "=== INFORMASI SISTEM & DATABASE NNACO_SA ===";
        $ctx[] = "Website: nnaco_sa (E-Commerce Perhiasan & Aksesoris Premium: Kalung, Gelang, Cincin, Anting, Jepit Rambut)";
        $ctx[] = "Pengguna Aktif: Role = " . $userRole . ($userId ? ", UserID = #$userId" : " (Tamu/Guest)");

        // 1. Skema Ringkas Seluruh 12 Tabel Database
        $ctx[] = "\n--- DAFTAR SELURUH 12 TABEL DATABASE NNACO_SA ---";
        $ctx[] = "1. categories: Kategori perhiasan (id, name, description)";
        $ctx[] = "2. products: Katalog aksesoris (id, category_id -> categories.id, name, price, stock, status: Available/Out_of_Stock/Hidden)";
        $ctx[] = "3. orders: Header pesanan (id, user_id -> users.id, order_date, total, status: 'MENUNGGU PEMBAYARAN'/'DIPROSES'/'DIKEMAS'/'DIKIRIM'/'SELESAI'/'DIBATALKAN')";
        $ctx[] = "4. order_details: Item rincian pesanan (id, order_id -> orders.id, product_id -> products.id, quantity, price, subtotal)";
        $ctx[] = "5. payments: Transaksi pembayaran (id, order_id -> orders.id, payment_method, gateway, amount, status: PENDING/PAID/FAILED/EXPIRED/CANCELLED)";
        $ctx[] = "6. users: Data akun (id, name, email, phone, role: Customer/Operator/Admin/Owner, status: Active/Inactive/Banned)";
        $ctx[] = "7. reviews: Ulasan pelanggan (id, user_id -> users.id, product_id -> products.id, rating, comment)";
        $ctx[] = "8. system_settings: Pengaturan sistem (id, setting_key, setting_value, setting_group, is_secret, description)";
        $ctx[] = "9. system_audit_logs: Log audit sistem (id, user_id, user_name, user_role, action, category, status, details)";
        $ctx[] = "10. otp_requests: Log verifikasi OTP WA (id, phone, user_id, attempts, status, expires_at)";
        $ctx[] = "11. whatsapp_notification_logs: Log notifikasi WA pesanan (id, order_id, recipient_phone, event_type, status)";
        $ctx[] = "12. ai_conversations: Riwayat percakapan chatbot (id, user_id, question, answer, created_at)";

        // 2. Data Produk & Kategori (Boleh diakses semua aktor)
        try {
            $this->db->query("SELECT COUNT(*) as c, SUM(stock) as s FROM products WHERE status = 'Available'");
            $pStats = $this->db->single();
            $ctx[] = "\n--- DATA KATALOG PRODUK AKTIF ---";
            $ctx[] = "Total Produk Tersedia: " . ($pStats['c'] ?? 0) . " varian (Total Unit Stok: " . ($pStats['s'] ?? 0) . " unit).";

            $this->db->query("SELECT p.id, p.name, c.name as category, p.price, p.stock FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 'Available' LIMIT 15");
            $prods = $this->db->resultSet();
            $pLines = [];
            foreach ($prods as $p) {
                $pLines[] = "#{$p['id']} {$p['name']} ({$p['category']}) - Rp " . number_format($p['price'], 0, ',', '.') . " [Stok: {$p['stock']}]";
            }
            $ctx[] = "Sampel Produk:\n" . implode("\n", $pLines);
        } catch (Throwable $e) {}

        // 3. Data Khusus Sesuai Role
        if ($userRole === 'Customer') {
            $ctx[] = "\n--- ATURAN BATASAN ROLE CUSTOMER ---";
            $ctx[] = "PERMITTED: Katalog produk, harga, ketersediaan perhiasan, panduan belanja toko, dan HANYA pesanan milik Customer ini sendiri.";
            $ctx[] = "FORBIDDEN: Data keuangan omzet toko, keuntungan, pesanan orang lain, data user lain, log sistem, tabel system_settings/audit_logs/otp. TOLAK DENGAN SOPAN jika Customer bertanya hal tersebut!";

            if ($userId) {
                try {
                    $this->db->query("SELECT id, total, status, order_date FROM orders WHERE user_id = :uid ORDER BY order_date DESC LIMIT 5");
                    $this->db->bind('uid', $userId);
                    $myOrders = $this->db->resultSet();
                    if (!empty($myOrders)) {
                        $ctx[] = "\nRiwayat Pesanan Akun Customer Ini:";
                        foreach ($myOrders as $mo) {
                            $ctx[] = "- Pesanan #{$mo['id']}: Rp " . number_format($mo['total'], 0, ',', '.') . " Status: {$mo['status']} ({$mo['order_date']})";
                        }
                    } else {
                        $ctx[] = "\nCustomer ini belum memiliki riwayat pesanan.";
                    }
                } catch (Throwable $e) {}
            }
        } elseif ($userRole === 'Operator') {
            $ctx[] = "\n--- DATA OPERASIONAL UNTUK OPERATOR ---";
            $ctx[] = "PERMITTED: Antrean pesanan untuk diproses/dikemas/dikirim, stok produk menipis, verifikasi customer pemesan, tabel whatsapp_notification_logs.";
            $ctx[] = "FORBIDDEN: Total omzet keuangan tahunan/bulanan, laporan keuntungan, tabel system_settings (kunci rahasia), otp_requests. TOLAK DENGAN SOPAN jika Operator bertanya omzet keuangan total toko.";

            try {
                $this->db->query("SELECT id, name, stock FROM products WHERE stock <= 5 ORDER BY stock ASC");
                $lowStk = $this->db->resultSet();
                $ctx[] = "Produk Stok Menipis (<=5): " . count($lowStk) . " item.";
                foreach ($lowStk as $ls) {
                    $ctx[] = "- #{$ls['id']} {$ls['name']} (Sisa {$ls['stock']} unit)";
                }

                $this->db->query("SELECT o.id, u.name as cust, o.total, o.status FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.status IN ('DIPROSES', 'DIKEMAS')");
                $opsOrders = $this->db->resultSet();
                $ctx[] = "Antrean Pesanan Operasional Perlu Diproses: " . count($opsOrders) . " pesanan.";
                foreach ($opsOrders as $oo) {
                    $ctx[] = "- Pesanan #{$oo['id']} ({$oo['cust']}): Rp " . number_format($oo['total'], 0, ',', '.') . " [{$oo['status']}]";
                }
            } catch (Throwable $e) {}
        } elseif ($userRole === 'Admin' || $userRole === 'Owner') {
            $ctx[] = "\n--- DATA MANAJEMEN & KEUANGAN (ADMIN & OWNER) ---";
            $ctx[] = "FULL ACCESS: Diizinkan membaca seluruh 12 tabel database: omzet, penjualan, semua user, semua pesanan, system_settings, system_audit_logs, whatsapp_notification_logs, otp_requests, ai_conversations.";

            try {
                $this->db->query("
                    SELECT 
                        COALESCE(SUM(CASE WHEN DATE(order_date) = CURDATE() THEN total ELSE 0 END), 0) as omzet_today,
                        COALESCE(SUM(CASE WHEN MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE()) THEN total ELSE 0 END), 0) as omzet_this_month,
                        COALESCE(SUM(total), 0) as omzet_all_time,
                        COUNT(*) as total_orders
                    FROM orders WHERE status IN ('PAID', 'DIPROSES', 'DIKEMAS', 'DIKIRIM', 'SELESAI')
                ");
                $omz = $this->db->single();
                $ctx[] = "Omzet Hari Ini: Rp " . number_format($omz['omzet_today'], 0, ',', '.');
                $ctx[] = "Omzet Bulan Ini: Rp " . number_format($omz['omzet_this_month'], 0, ',', '.');
                $ctx[] = "Omzet Total Sepanjang Waktu: Rp " . number_format($omz['omzet_all_time'], 0, ',', '.') . " ({$omz['total_orders']} pesanan berhasil)";

                $this->db->query("SELECT role, COUNT(*) as c FROM users GROUP BY role");
                $uRoles = $this->db->resultSet();
                $uStr = [];
                foreach ($uRoles as $ur) { $uStr[] = "{$ur['c']} {$ur['role']}"; }
                $ctx[] = "Total Pengguna Terdaftar: " . implode(', ', $uStr);

                // Admin dan Owner diizinkan melihat data seluruh akun pengguna untuk keperluan monitoring & pengelolaan sistem
                $this->db->query("SELECT id, name, email, phone, role, status FROM users ORDER BY role ASC, id ASC");
                $allUsers = $this->db->resultSet();
                $uLines = [];
                foreach ($allUsers as $u) {
                    $ph = !empty($u['phone']) ? $u['phone'] : '-';
                    $uLines[] = "- #{$u['id']} {$u['name']} (Email: {$u['email']}, HP: {$ph}, Role: {$u['role']}, Status: {$u['status']})";
                }
                $ctx[] = "Daftar Akun Pengguna Terdaftar (Izin Akses Penuh untuk Admin/Owner):\n" . implode("\n", $uLines);
            } catch (Throwable $e) {}
        }

        return implode("\n", $ctx);
    }
}
