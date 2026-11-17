const pool = require('../config/db');

class DatabaseRepository {
    async getDatabaseOverview() {
        // Query counts from all 12 tables
        const [prodCount] = await pool.query(`SELECT COUNT(*) as count, SUM(CASE WHEN status='Available' THEN 1 ELSE 0 END) as available, SUM(stock) as total_stock FROM products`);
        const [catCount] = await pool.query(`SELECT COUNT(*) as count FROM categories`);
        const [ordCount] = await pool.query(`SELECT COUNT(*) as count, SUM(total) as total_amount FROM orders`);
        const [detCount] = await pool.query(`SELECT COUNT(*) as count, SUM(quantity) as total_qty FROM order_details`);
        const [payCount] = await pool.query(`SELECT COUNT(*) as count, SUM(CASE WHEN status='PAID' THEN amount ELSE 0 END) as paid_amount FROM payments`);
        const [userCount] = await pool.query(`SELECT COUNT(*) as count, SUM(CASE WHEN status='Active' THEN 1 ELSE 0 END) as active FROM users`);
        const [revCount] = await pool.query(`SELECT COUNT(*) as count, COALESCE(AVG(rating), 0) as avg_rating FROM reviews`);
        const [settCount] = await pool.query(`SELECT COUNT(*) as count FROM system_settings`);
        const [auditCount] = await pool.query(`SELECT COUNT(*) as count FROM system_audit_logs`);
        const [otpCount] = await pool.query(`SELECT COUNT(*) as count FROM otp_requests`);
        const [waCount] = await pool.query(`SELECT COUNT(*) as count FROM whatsapp_notification_logs`);
        const [aiCount] = await pool.query(`SELECT COUNT(*) as count FROM ai_conversations`);

        return {
            database_name: 'nnaco_sa_db',
            total_tables: 12,
            tables: [
                {
                    table_name: 'products',
                    menu_relation: 'Katalog Produk (/product), Admin Produk (/adminproduct)',
                    description: 'Menyimpan data katalog aksesoris, harga, stok, gambar, dan status ketersediaan.',
                    total_rows: Number(prodCount[0]?.count || 0),
                    stats: `Tersedia: ${Number(prodCount[0]?.available || 0)} item, Total unit stok: ${Number(prodCount[0]?.total_stock || 0)}`
                },
                {
                    table_name: 'categories',
                    menu_relation: 'Kategori Produk, Filter Katalog, Admin Kategori (/admincategory)',
                    description: 'Menyimpan klasifikasi kategori produk aksesoris (Gelang, Kalung, Cincin, Anting, Jepit Rambut).',
                    total_rows: Number(catCount[0]?.count || 0),
                    stats: `Total kategori: ${Number(catCount[0]?.count || 0)}`
                },
                {
                    table_name: 'orders',
                    menu_relation: 'Riwayat Pesanan (/customer/history), Admin Pesanan (/adminorder)',
                    description: 'Menyimpan data transaksi order pembeli, tanggal pemesanan, total belanja, dan status pemrosesan.',
                    total_rows: Number(ordCount[0]?.count || 0),
                    stats: `Total transaksi order: ${Number(ordCount[0]?.count || 0)}, Nilai: Rp ${Number(ordCount[0]?.total_amount || 0).toLocaleString('id-ID')}`
                },
                {
                    table_name: 'order_details',
                    menu_relation: 'Detail Pesanan (/order/detail), Struk Belanja, Laporan Item Terjual',
                    description: 'Menyimpan rincian item per pesanan (produk, kuantitas, harga satuan, dan subtotal).',
                    total_rows: Number(detCount[0]?.count || 0),
                    stats: `Total baris rincian: ${Number(detCount[0]?.count || 0)}, Total item kuantitas: ${Number(detCount[0]?.total_qty || 0)} unit`
                },
                {
                    table_name: 'payments',
                    menu_relation: 'Checkout Pembayaran (/checkout), Simulasi Bayar (/payment/simulation), Laporan Omzet (/ownerreport)',
                    description: 'Menyimpan riwayat gateway pembayaran, metode bayar (Gopay/OVO/Transfer), status (PAID/PENDING), dan nominal.',
                    total_rows: Number(payCount[0]?.count || 0),
                    stats: `Total transaksi bayar: ${Number(payCount[0]?.count || 0)}, Omzet lunas: Rp ${Number(payCount[0]?.paid_amount || 0).toLocaleString('id-ID')}`
                },
                {
                    table_name: 'users',
                    menu_relation: 'Manajemen Pengguna (/adminuser), Profil Akun (/profile), Otentikasi Login/Register',
                    description: 'Menyimpan data akun pengguna dan peran sistem (Customer, Operator, Admin, Owner) serta nomor kontak.',
                    total_rows: Number(userCount[0]?.count || 0),
                    stats: `Total pengguna: ${Number(userCount[0]?.count || 0)}, Aktif: ${Number(userCount[0]?.active || 0)}`
                },
                {
                    table_name: 'reviews',
                    menu_relation: 'Ulasan Produk, Rating Bintang Kepuasan Pelanggan di Halaman Detail',
                    description: 'Menyimpan testimoni, rating bintang (1-5), dan komentar review produk dari pembeli.',
                    total_rows: Number(revCount[0]?.count || 0),
                    stats: `Total ulasan: ${Number(revCount[0]?.count || 0)}, Rata-rata: ${Number(revCount[0]?.avg_rating || 0).toFixed(1)} / 5.0 ⭐`
                },
                {
                    table_name: 'system_settings',
                    menu_relation: 'Pengaturan Sistem (/settings), Konfigurasi AI & Toko',
                    description: 'Menyimpan seluruh konfigurasi dinamis sistem toko, nama toko, provider AI, dan parameter operasional.',
                    total_rows: Number(settCount[0]?.count || 0),
                    stats: `Total entri pengaturan: ${Number(settCount[0]?.count || 0)}`
                },
                {
                    table_name: 'system_audit_logs',
                    menu_relation: 'Audit Trail Sistem, Pemantauan Keamanan Admin',
                    description: 'Mencatat riwayat aktivitas pengguna, modul yang diakses, aksi yang dilakukan, alamat IP, dan timestamp.',
                    total_rows: Number(auditCount[0]?.count || 0),
                    stats: `Total rekaman audit: ${Number(auditCount[0]?.count || 0)} log`
                },
                {
                    table_name: 'otp_requests',
                    menu_relation: 'Keamanan Otentikasi WhatsApp OTP',
                    description: 'Menyimpan log verifikasi kode OTP login/registrasi nomor WhatsApp pembeli.',
                    total_rows: Number(otpCount[0]?.count || 0),
                    stats: `Total permintaan OTP: ${Number(otpCount[0]?.count || 0)}`
                },
                {
                    table_name: 'whatsapp_notification_logs',
                    menu_relation: 'Integrasi WhatsApp Gateway (Fonnte)',
                    description: 'Mencatat status pengiriman pesan notifikasi WhatsApp resmi toko ke nomor pelanggan.',
                    total_rows: Number(waCount[0]?.count || 0),
                    stats: `Total pengiriman WA: ${Number(waCount[0]?.count || 0)} log`
                },
                {
                    table_name: 'ai_conversations',
                    menu_relation: 'Monitoring Chatbot AI (/admin/chatbot)',
                    description: 'Menyimpan arsip riwayat percakapan antara pengguna dengan nnaco AI Assistant.',
                    total_rows: Number(aiCount[0]?.count || 0),
                    stats: `Total interaksi chat: ${Number(aiCount[0]?.count || 0)} pesan`
                }
            ]
        };
    }

    async getMenuOverview() {
        return {
            website_name: 'nnaco_sa Aksesoris Store',
            menus: [
                {
                    menu: 'Beranda (Home)',
                    url: '/',
                    description: 'Menampilkan hero promosi, produk unggulan terbaru, dan kategori aksesoris terpopuler.',
                    source_tables: ['products', 'categories', 'reviews']
                },
                {
                    menu: 'Katalog Produk (Product)',
                    url: '/product',
                    description: 'Daftar lengkap produk dengan filter pencarian, filter kategori, rentang harga, dan pagination.',
                    source_tables: ['products', 'categories']
                },
                {
                    menu: 'Keranjang & Checkout (Cart & Checkout)',
                    url: '/cart & /checkout',
                    description: 'Pemeriksaan keranjang belanja, penentuan alamat, dan pemilihan metode pembayaran.',
                    source_tables: ['products', 'orders', 'order_details', 'payments']
                },
                {
                    menu: 'Riwayat Pesanan (Customer Orders)',
                    url: '/customer/history',
                    description: 'Pelacakan status pesanan, rincian produk yang dibeli, dan bukti status pembayaran.',
                    source_tables: ['orders', 'order_details', 'payments']
                },
                {
                    menu: 'Profil Akun (Profile)',
                    url: '/profile',
                    description: 'Informasi biodata pengguna, email terdaftar, nomor WhatsApp untuk OTP, dan ganti kata sandi.',
                    source_tables: ['users']
                },
                {
                    menu: 'Dashboard Admin (Admin Portal)',
                    url: '/admin, /adminproduct, /admincategory, /adminorder, /adminuser',
                    description: 'Kontrol manajemen menyeluruh stok produk, kategori, seluruh pesanan masuk, dan daftar pengguna.',
                    source_tables: ['products', 'categories', 'orders', 'users', 'payments']
                },
                {
                    menu: 'Dashboard Operator (Operator Portal)',
                    url: '/operator, /operatororder, /operatorproduct',
                    description: 'Operasional gudang untuk verifikasi pembayaran, pengemasan pesanan, dan pemantauan stok menipis.',
                    source_tables: ['orders', 'products', 'payments']
                },
                {
                    menu: 'Dashboard Owner (Owner Portal & Finansial)',
                    url: '/owner, /ownerreport',
                    description: 'Analisis omzet keuangan, grafik performa penjualan berkala, dan produk terlaris.',
                    source_tables: ['payments', 'orders', 'order_details', 'products']
                },
                {
                    menu: 'Tentang Kami & Kontak (About & Contact)',
                    url: '/about & /contact',
                    description: 'Profil toko, cerita brand, saluran komunikasi bantuan customer service, dan FAQ.',
                    source_tables: ['-']
                }
            ]
        };
    }

    async getTableCount(tableName) {
        const cleanTable = String(tableName || '').toLowerCase().trim();
        const allowedTables = [
            'products', 'categories', 'orders', 'order_details', 'payments', 'users',
            'reviews', 'system_settings', 'system_audit_logs', 'otp_requests',
            'whatsapp_notification_logs', 'ai_conversations'
        ];

        if (!allowedTables.includes(cleanTable)) {
            return {
                success: false,
                error: `Tabel '${tableName}' tidak ditemukan di database nnaco_sa_db.`
            };
        }

        const [rows] = await pool.query(`SELECT COUNT(*) as count FROM ??`, [cleanTable]);
        return {
            success: true,
            table: cleanTable,
            count: Number(rows[0]?.count || 0)
        };
    }

    async readTableData(tableName, limit = 10, filter = null, user = { role: 'Customer', id: null }) {
        const allowedTables = [
            'products', 'categories', 'orders', 'order_details', 'payments', 'users',
            'reviews', 'system_settings', 'system_audit_logs', 'otp_requests',
            'whatsapp_notification_logs', 'ai_conversations'
        ];
        const cleanTable = String(tableName || '').toLowerCase().trim();

        if (!allowedTables.includes(cleanTable)) {
            return {
                success: false,
                error: `Tabel '${tableName}' tidak diperbolehkan atau tidak tersedia. Tabel yang dapat dibaca: ${allowedTables.join(', ')}`
            };
        }

        const role = user?.role || 'Customer';

        // Strict RBAC table protection
        if (role === 'Customer') {
            if (['system_settings', 'system_audit_logs', 'otp_requests', 'whatsapp_notification_logs', 'ai_conversations'].includes(cleanTable)) {
                return {
                    success: false,
                    error: `Akses ditolak: Role Customer tidak memiliki izin untuk membaca data tabel '${cleanTable}'.`
                };
            }
        } else if (role === 'Operator') {
            if (['system_settings', 'system_audit_logs', 'otp_requests', 'whatsapp_notification_logs'].includes(cleanTable)) {
                return {
                    success: false,
                    error: `Akses ditolak: Role Operator tidak memiliki izin untuk membaca data konfigurasi/keamanan '${cleanTable}'.`
                };
            }
        }

        const safeLimit = Math.min(Math.max(parseInt(limit, 10) || 10, 1), 25);

        let sql = '';
        let params = [];

        switch (cleanTable) {
            case 'products':
                sql = `
                    SELECT p.id, p.name, c.name as category, p.price, p.stock, p.status, p.created_at
                    FROM products p
                    LEFT JOIN categories c ON p.category_id = c.id
                    ORDER BY p.id ASC
                    LIMIT ?
                `;
                params = [safeLimit];
                break;

            case 'categories':
                sql = `
                    SELECT c.id, c.name, c.description, COUNT(p.id) as product_count
                    FROM categories c
                    LEFT JOIN products p ON p.category_id = c.id
                    GROUP BY c.id, c.name, c.description
                    ORDER BY c.id ASC
                    LIMIT ?
                `;
                params = [safeLimit];
                break;

            case 'orders':
                if (role === 'Customer') {
                    sql = `
                        SELECT o.id, u.name as customer_name, o.total, o.status, o.order_date
                        FROM orders o
                        LEFT JOIN users u ON o.user_id = u.id
                        WHERE o.user_id = ?
                        ORDER BY o.order_date DESC
                        LIMIT ?
                    `;
                    params = [user.id || 0, safeLimit];
                } else {
                    sql = `
                        SELECT o.id, u.name as customer_name, o.total, o.status, o.order_date
                        FROM orders o
                        LEFT JOIN users u ON o.user_id = u.id
                        ORDER BY o.order_date DESC
                        LIMIT ?
                    `;
                    params = [safeLimit];
                }
                break;

            case 'order_details':
                if (role === 'Customer') {
                    sql = `
                        SELECT od.id, od.order_id, p.name as product_name, od.quantity, od.price, od.subtotal
                        FROM order_details od
                        JOIN orders o ON od.order_id = o.id
                        LEFT JOIN products p ON od.product_id = p.id
                        WHERE o.user_id = ?
                        ORDER BY od.id DESC
                        LIMIT ?
                    `;
                    params = [user.id || 0, safeLimit];
                } else {
                    sql = `
                        SELECT od.id, od.order_id, p.name as product_name, od.quantity, od.price, od.subtotal
                        FROM order_details od
                        LEFT JOIN products p ON od.product_id = p.id
                        ORDER BY od.id DESC
                        LIMIT ?
                    `;
                    params = [safeLimit];
                }
                break;

            case 'payments':
                if (role === 'Customer') {
                    sql = `
                        SELECT p.id, p.order_id, p.payment_method, p.amount, p.status, p.paid_at, p.created_at
                        FROM payments p
                        JOIN orders o ON p.order_id = o.id
                        WHERE o.user_id = ?
                        ORDER BY p.id DESC
                        LIMIT ?
                    `;
                    params = [user.id || 0, safeLimit];
                } else {
                    sql = `
                        SELECT id, order_id, payment_method, amount, status, paid_at, created_at
                        FROM payments
                        ORDER BY id DESC
                        LIMIT ?
                    `;
                    params = [safeLimit];
                }
                break;

            case 'users':
                // Strictly exclude password hash & apply customer self-only RLS
                if (role === 'Customer') {
                    sql = `
                        SELECT id, name, email, phone, role, status, created_at
                        FROM users
                        WHERE id = ?
                        LIMIT ?
                    `;
                    params = [user.id || 0, safeLimit];
                } else {
                    sql = `
                        SELECT id, name, email, phone, role, status, created_at
                        FROM users
                        ORDER BY id ASC
                        LIMIT ?
                    `;
                    params = [safeLimit];
                }
                break;

            case 'reviews':
                sql = `
                    SELECT r.id, p.name as product_name, u.name as user_name, r.rating, r.comment, r.created_at
                    FROM reviews r
                    LEFT JOIN products p ON r.product_id = p.id
                    LEFT JOIN users u ON r.user_id = u.id
                    ORDER BY r.id DESC
                    LIMIT ?
                `;
                params = [safeLimit];
                break;

            case 'system_settings':
                sql = `
                    SELECT id, setting_key, 
                           CASE WHEN setting_key LIKE '%key%' OR setting_key LIKE '%secret%' THEN '******' ELSE setting_value END as setting_value, 
                           setting_group, description, updated_at
                    FROM system_settings
                    ORDER BY id ASC
                    LIMIT ?
                `;
                params = [safeLimit];
                break;

            case 'system_audit_logs':
                sql = `
                    SELECT id, user_name, user_role, action, category, status, details, ip_address, created_at
                    FROM system_audit_logs
                    ORDER BY id DESC
                    LIMIT ?
                `;
                params = [safeLimit];
                break;

            case 'otp_requests':
                sql = `
                    SELECT id, phone, attempts, status, expires_at, created_at
                    FROM otp_requests
                    ORDER BY id DESC
                    LIMIT ?
                `;
                params = [safeLimit];
                break;

            case 'whatsapp_notification_logs':
                sql = `
                    SELECT id, order_id, recipient_phone, event_type, status, attempt_count, created_at
                    FROM whatsapp_notification_logs
                    ORDER BY id DESC
                    LIMIT ?
                `;
                params = [safeLimit];
                break;

            case 'ai_conversations':
                sql = `
                    SELECT id, user_id, SUBSTRING(question, 1, 60) as question, SUBSTRING(answer, 1, 60) as answer, created_at
                    FROM ai_conversations
                    ORDER BY id DESC
                    LIMIT ?
                `;
                params = [safeLimit];
                break;

            default:
                sql = `SELECT * FROM ?? LIMIT ?`;
                params = [cleanTable, safeLimit];
                break;
        }

        const [rows] = await pool.query(sql, params);
        return {
            success: true,
            table: cleanTable,
            count: rows.length,
            data: rows
        };
    }
}

module.exports = new DatabaseRepository();
