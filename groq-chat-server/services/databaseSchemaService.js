/**
 * databaseSchemaService.js
 * Comprehensive Database Metadata & Schema Dictionary for nnaco_sa_db
 * Provides dynamic schema awareness, table descriptions, column specifications,
 * foreign keys, business definitions, and role-based schema filtering.
 */

const pool = require('../config/db');

class DatabaseSchemaService {
    constructor() {
        this.databaseName = 'nnaco_sa_db';
        this.tables = {
            categories: {
                tableName: 'categories',
                displayName: 'Kategori Produk',
                description: 'Menyimpan master klasifikasi kategori produk aksesoris (Gelang, Kalung, Cincin, Anting, Jepit Rambut).',
                menuRelation: 'Katalog Produk (/product), Admin Kategori (/admincategory)',
                sensitivity: 'public',
                allowedRoles: ['Customer', 'Operator', 'Admin', 'Owner'],
                primaryKey: 'id',
                columns: [
                    { name: 'id', type: 'int(11)', description: 'Primary Key identifier unik kategori', sensitivity: 'public' },
                    { name: 'name', type: 'varchar(100)', description: 'Nama kategori aksesoris', sensitivity: 'public' },
                    { name: 'description', type: 'text', description: 'Deskripsi singkat kategori', sensitivity: 'public' },
                    { name: 'created_at', type: 'timestamp', description: 'Waktu pembuatan kategori', sensitivity: 'public' },
                    { name: 'updated_at', type: 'timestamp', description: 'Waktu pembaruan terakhir', sensitivity: 'public' }
                ],
                foreignKeys: [],
                relationships: [
                    '1 kategori memiliki banyak produk (categories.id = products.category_id)'
                ],
                businessMeaning: 'Mengelompokkan produk aksesoris untuk navigasi pembeli dan filter pencarian.'
            },

            products: {
                tableName: 'products',
                displayName: 'Katalog Produk Aksesoris',
                description: 'Menyimpan data katalog perhiasan dan aksesoris, harga jual, stok gudang, dan status ketersediaan.',
                menuRelation: 'Katalog (/product), Detail Produk (/product/detail/:id), Admin Produk (/adminproduct), Operator Produk (/operatorproduct)',
                sensitivity: 'public',
                allowedRoles: ['Customer', 'Operator', 'Admin', 'Owner'],
                primaryKey: 'id',
                columns: [
                    { name: 'id', type: 'int(11)', description: 'Primary Key produk', sensitivity: 'public' },
                    { name: 'category_id', type: 'int(11)', description: 'Foreign Key merujuk ke categories(id)', sensitivity: 'public' },
                    { name: 'name', type: 'varchar(150)', description: 'Nama lengkap produk aksesoris', sensitivity: 'public' },
                    { name: 'description', type: 'text', description: 'Deskripsi bahan, spesifikasi, dan keunggulan produk', sensitivity: 'public' },
                    { name: 'price', type: 'decimal(10,2)', description: 'Harga satuan produk dalam Rupiah', sensitivity: 'public' },
                    { name: 'stock', type: 'int(11)', description: 'Jumlah unit stok fisik yang tersedia di inventaris', sensitivity: 'internal' },
                    { name: 'image', type: 'varchar(255)', description: 'Nama file gambar produk di img/products/', sensitivity: 'public' },
                    { name: 'status', type: "enum('Available','Out_of_Stock','Hidden')", description: 'Status ketersediaan produk di toko', sensitivity: 'public' },
                    { name: 'created_at', type: 'timestamp', description: 'Waktu produk ditambahkan', sensitivity: 'public' },
                    { name: 'updated_at', type: 'timestamp', description: 'Waktu update produk terakhir', sensitivity: 'public' }
                ],
                foreignKeys: [
                    { column: 'category_id', referencedTable: 'categories', referencedColumn: 'id' }
                ],
                relationships: [
                    'products.category_id = categories.id',
                    'products.id = order_details.product_id',
                    'products.id = reviews.product_id'
                ],
                businessMeaning: 'Koleksi barang dagangan aktif nnaco_sa. Produk siap dibeli jika status Available dan stock > 0.'
            },

            orders: {
                tableName: 'orders',
                displayName: 'Pesanan Pelanggan',
                description: 'Menyimpan header pesanan transaksi belanja pembeli, total nilai transaksi, tanggal order, dan status alur pemrosesan pesanan.',
                menuRelation: 'Riwayat Pesanan Customer (/customer/history), Detail Pesanan (/order/detail/:id), Admin Pesanan (/adminorder), Operator Pesanan (/operatororder)',
                sensitivity: 'confidential',
                allowedRoles: ['Customer', 'Operator', 'Admin', 'Owner'],
                rowLevelPolicy: 'Customer hanya boleh mengakses data pesanan miliknya sendiri (orders.user_id = authUser.id). Operator, Admin, Owner dapat melihat seluruh pesanan.',
                primaryKey: 'id',
                columns: [
                    { name: 'id', type: 'int(11)', description: 'Nomor ID pesanan unik (#ID)', sensitivity: 'confidential' },
                    { name: 'user_id', type: 'int(11)', description: 'Foreign Key pembeli merujuk ke users(id)', sensitivity: 'confidential' },
                    { name: 'order_date', type: 'timestamp', description: 'Tanggal dan waktu pemesanan dibuat', sensitivity: 'confidential' },
                    { name: 'total', type: 'decimal(12,2)', description: 'Total tagihan belanja pesanan dalam Rupiah', sensitivity: 'confidential' },
                    { name: 'status', type: "enum('MENUNGGU PEMBAYARAN','DIPROSES','DIKEMAS','DIKIRIM','SELESAI','DIBATALKAN')", description: 'Status tahapan pesanan saat ini', sensitivity: 'confidential' },
                    { name: 'created_at', type: 'timestamp', description: 'Waktu record dibuat', sensitivity: 'confidential' },
                    { name: 'updated_at', type: 'timestamp', description: 'Waktu perubahan status terakhir', sensitivity: 'confidential' }
                ],
                foreignKeys: [
                    { column: 'user_id', referencedTable: 'users', referencedColumn: 'id' }
                ],
                relationships: [
                    'orders.user_id = users.id',
                    'orders.id = order_details.order_id',
                    'orders.id = payments.order_id'
                ],
                businessMeaning: 'Siklus hidup pesanan: MENUNGGU PEMBAYARAN -> DIPROSES -> DIKEMAS -> DIKIRIM -> SELESAI. Jika pembayaran gagal/dibatalkan -> DIBATALKAN.'
            },

            order_details: {
                tableName: 'order_details',
                displayName: 'Rincian Item Pesanan',
                description: 'Menyimpan setiap item produk yang dibeli di dalam suatu pesanan, kuantitas unit, harga beli satuan, dan subtotal tagihan item.',
                menuRelation: 'Rincian Keranjang & Checkout, Invoice / Struk Pembelian, Laporan Item Terjual',
                sensitivity: 'confidential',
                allowedRoles: ['Customer', 'Operator', 'Admin', 'Owner'],
                rowLevelPolicy: 'Customer hanya boleh membaca item dari pesanan miliknya sendiri.',
                primaryKey: 'id',
                columns: [
                    { name: 'id', type: 'int(11)', description: 'Primary Key rincian pesanan', sensitivity: 'confidential' },
                    { name: 'order_id', type: 'int(11)', description: 'Foreign Key mengaitkan ke orders(id)', sensitivity: 'confidential' },
                    { name: 'product_id', type: 'int(11)', description: 'Foreign Key mengaitkan ke products(id)', sensitivity: 'confidential' },
                    { name: 'quantity', type: 'int(11)', description: 'Jumlah unit produk yang dibeli', sensitivity: 'confidential' },
                    { name: 'price', type: 'decimal(10,2)', description: 'Harga produk saat transaksi dilakukan', sensitivity: 'confidential' },
                    { name: 'subtotal', type: 'decimal(12,2)', description: 'Perhitungan quantity * price', sensitivity: 'confidential' }
                ],
                foreignKeys: [
                    { column: 'order_id', referencedTable: 'orders', referencedColumn: 'id' },
                    { column: 'product_id', referencedTable: 'products', referencedColumn: 'id' }
                ],
                relationships: [
                    'order_details.order_id = orders.id',
                    'order_details.product_id = products.id'
                ],
                businessMeaning: 'Rincian produk di balik nomor pesanan, dasar untuk menghitung produk terlaris dan kuantitas unit terjual.'
            },

            payments: {
                tableName: 'payments',
                displayName: 'Transaksi Pembayaran',
                description: 'Menyimpan catatan transaksi gateway pembayaran (GoPay, DANA, Transfer Bank, dll.), nominal bayar, status verifikasi, dan waktu pelunasan.',
                menuRelation: 'Checkout (/checkout), Simulasi Pembayaran (/payment/simulation), Laporan Keuangan Owner (/ownerreport)',
                sensitivity: 'confidential',
                allowedRoles: ['Customer', 'Operator', 'Admin', 'Owner'],
                rowLevelPolicy: 'Customer hanya dapat membaca status pembayaran pesanannya sendiri. Operator membaca status pembayaran operasional. Admin/Owner membaca seluruh pembayaran.',
                primaryKey: 'id',
                columns: [
                    { name: 'id', type: 'int(11)', description: 'Primary Key pembayaran', sensitivity: 'confidential' },
                    { name: 'order_id', type: 'int(11)', description: 'Foreign Key merujuk ke orders(id)', sensitivity: 'confidential' },
                    { name: 'payment_method', type: 'varchar(50)', description: 'Metode pembayaran yang dipilih (GoPay, DANA, OVO, Bank Transfer)', sensitivity: 'confidential' },
                    { name: 'gateway', type: 'varchar(50)', description: 'Gateway pemroses pembayaran (Midtrans, Manual Transfer, dll.)', sensitivity: 'confidential' },
                    { name: 'transaction_id', type: 'varchar(100)', description: 'Kode referensi transaksi pembayaran unik', sensitivity: 'confidential' },
                    { name: 'payment_url', type: 'varchar(255)', description: 'Tautan pembayaran jika menggunakan gateway digital', sensitivity: 'confidential' },
                    { name: 'amount', type: 'decimal(12,2)', description: 'Nominal pembayaran yang ditagihkan/dibayar', sensitivity: 'confidential' },
                    { name: 'status', type: "enum('PENDING','PAID','FAILED','EXPIRED','CANCELLED')", description: 'Status verifikasi pelunasan uang', sensitivity: 'confidential' },
                    { name: 'paid_at', type: 'timestamp', description: 'Waktu pembayaran terverifikasi lunas', sensitivity: 'confidential' },
                    { name: 'expired_at', type: 'timestamp', description: 'Waktu batas pembayaran kadaluarsa', sensitivity: 'confidential' },
                    { name: 'created_at', type: 'timestamp', description: 'Waktu tagihan pembayaran dibuat', sensitivity: 'confidential' },
                    { name: 'updated_at', type: 'timestamp', description: 'Waktu update status pembayaran', sensitivity: 'confidential' }
                ],
                foreignKeys: [
                    { column: 'order_id', referencedTable: 'orders', referencedColumn: 'id' }
                ],
                relationships: [
                    'payments.order_id = orders.id'
                ],
                businessMeaning: 'Verifikasi dana masuk. Status PAID mengonfirmasi pesanan siap dikemas dan dihitung dalam laporan omzet.'
            },

            users: {
                tableName: 'users',
                displayName: 'Pengguna & Akun Sistem',
                description: 'Menyimpan data pengguna terdaftar website beserta pembagian peran otentikasi (Customer, Operator, Admin, Owner).',
                menuRelation: 'Manajemen Pengguna Admin (/adminuser), Profil Akun (/profile), Otentikasi Login & Register (/auth)',
                sensitivity: 'confidential',
                allowedRoles: ['Customer', 'Admin', 'Owner'], // Customer only sees self; Operator cannot browse user management
                rowLevelPolicy: 'Customer hanya dapat membaca profil dirinya sendiri. Password hash dan OTP hash selalu disamarkan (******).',
                primaryKey: 'id',
                columns: [
                    { name: 'id', type: 'int(11)', description: 'Primary Key akun pengguna', sensitivity: 'public' },
                    { name: 'name', type: 'varchar(100)', description: 'Nama lengkap pengguna', sensitivity: 'internal' },
                    { name: 'email', type: 'varchar(100)', description: 'Alamat email terdaftar untuk login', sensitivity: 'internal' },
                    { name: 'phone', type: 'varchar(20)', description: 'Nomor telepon/WhatsApp terdaftar', sensitivity: 'internal' },
                    { name: 'password', type: 'varchar(255)', description: 'Hash password akun (DILARANG DIBOCORKAN / SELALU SAMARKAN)', sensitivity: 'secret' },
                    { name: 'role', type: "enum('Customer','Operator','Admin','Owner')", description: 'Tingkat hak akses pengguna dalam aplikasi', sensitivity: 'internal' },
                    { name: 'status', type: "enum('Active','Inactive','Banned')", description: 'Status keaktifan akun pengguna', sensitivity: 'internal' },
                    { name: 'theme_preference', type: "enum('light','dark','system')", description: 'Preferensi tampilan tema UI pengguna', sensitivity: 'internal' },
                    { name: 'created_at', type: 'timestamp', description: 'Waktu pendaftaran akun', sensitivity: 'internal' },
                    { name: 'updated_at', type: 'timestamp', description: 'Waktu update profil terakhir', sensitivity: 'internal' }
                ],
                foreignKeys: [],
                relationships: [
                    'users.id = orders.user_id',
                    'users.id = reviews.user_id',
                    'users.id = ai_conversations.user_id'
                ],
                businessMeaning: 'Entitas pemegang hak akses di nnaco_sa. Password hash tidak boleh diungkapkan ke siapa pun.'
            },

            reviews: {
                tableName: 'reviews',
                displayName: 'Ulasan & Rating Produk',
                description: 'Menyimpan testimoni, bintang kepuasan (1-5), dan komentar pengalaman pembeli terhadap produk yang telah dibeli.',
                menuRelation: 'Review & Testimoni pada Halaman Detail Produk (/product/detail/:id)',
                sensitivity: 'public',
                allowedRoles: ['Customer', 'Operator', 'Admin', 'Owner'],
                primaryKey: 'id',
                columns: [
                    { name: 'id', type: 'int(11)', description: 'Primary Key ulasan', sensitivity: 'public' },
                    { name: 'user_id', type: 'int(11)', description: 'Foreign Key pembeli yang memberi ulasan (users.id)', sensitivity: 'public' },
                    { name: 'product_id', type: 'int(11)', description: 'Foreign Key produk yang diulas (products.id)', sensitivity: 'public' },
                    { name: 'rating', type: 'int(11)', description: 'Skala nilai kepuasan produk (1 sampai 5 bintang)', sensitivity: 'public' },
                    { name: 'comment', type: 'text', description: 'Teks ulasan dan kepuasan pelanggan', sensitivity: 'public' },
                    { name: 'created_at', type: 'timestamp', description: 'Waktu ulasan dikirimkan', sensitivity: 'public' }
                ],
                foreignKeys: [
                    { column: 'user_id', referencedTable: 'users', referencedColumn: 'id' },
                    { column: 'product_id', referencedTable: 'products', referencedColumn: 'id' }
                ],
                relationships: [
                    'reviews.user_id = users.id',
                    'reviews.product_id = products.id'
                ],
                businessMeaning: 'Metrik kepuasan pembeli dan bukti sosial kualitas aksesoris nnaco_sa.'
            },

            system_settings: {
                tableName: 'system_settings',
                displayName: 'Pengaturan & Konfigurasi Sistem',
                description: 'Menyimpan konfigurasi dinamis aplikasi, identitas toko, provider AI, dan parameter operasional.',
                menuRelation: 'Pengaturan Sistem (/settings)',
                sensitivity: 'internal',
                allowedRoles: ['Admin', 'Owner'], // Customer & Operator cannot read system settings
                primaryKey: 'id',
                columns: [
                    { name: 'id', type: 'int(11)', description: 'Primary Key entri konfigurasi', sensitivity: 'internal' },
                    { name: 'setting_key', type: 'varchar(100)', description: 'Kunci unik konfigurasi sistem (misal ai.model, store.name)', sensitivity: 'internal' },
                    { name: 'setting_value', type: 'text', description: 'Nilai konfigurasi (Nilai rahasia seperti API key dan token di-masking)', sensitivity: 'internal' },
                    { name: 'setting_group', type: 'varchar(50)', description: 'Kategori kelompok pengaturan (ai, store, order, general)', sensitivity: 'internal' },
                    { name: 'is_secret', type: 'tinyint(1)', description: 'Flag 1 jika nilai mengandung rahasia/kredensial yang harus disamarkan', sensitivity: 'internal' },
                    { name: 'description', type: 'varchar(255)', description: 'Penjelasan fungsi setting', sensitivity: 'internal' },
                    { name: 'updated_by', type: 'int(11)', description: 'ID user yang terakhir mengubah konfigurasi', sensitivity: 'internal' },
                    { name: 'created_at', type: 'timestamp', description: 'Waktu pembuatan setting', sensitivity: 'internal' },
                    { name: 'updated_at', type: 'timestamp', description: 'Waktu update setting terakhir', sensitivity: 'internal' }
                ],
                foreignKeys: [],
                relationships: [],
                businessMeaning: 'Pusat pengaturan dinamis toko nnaco_sa tanpa perlu deploy ulang kode program.'
            },

            system_audit_logs: {
                tableName: 'system_audit_logs',
                displayName: 'Log Audit Keamanan Sistem',
                description: 'Mencatat jejak audit trail seluruh aktivitas perubahan data penting, modul yang diakses, status sukses/gagal, alamat IP, dan user agent.',
                menuRelation: 'Audit Trail Admin & Pemantauan Keamanan Sistem',
                sensitivity: 'internal',
                allowedRoles: ['Admin', 'Owner'],
                primaryKey: 'id',
                columns: [
                    { name: 'id', type: 'int(11)', description: 'Primary Key rekaman audit', sensitivity: 'internal' },
                    { name: 'user_id', type: 'int(11)', description: 'ID pengguna pelaku tindakan', sensitivity: 'internal' },
                    { name: 'user_name', type: 'varchar(100)', description: 'Nama pengguna pelaku tindakan', sensitivity: 'internal' },
                    { name: 'user_role', type: 'varchar(50)', description: 'Peran pengguna saat tindakan dilakukan', sensitivity: 'internal' },
                    { name: 'action', type: 'varchar(50)', description: 'Jenis aksi (CREATE, UPDATE, DELETE, LOGIN, SETTING_CHANGE)', sensitivity: 'internal' },
                    { name: 'category', type: 'varchar(50)', description: 'Kategori modul yang dimodifikasi', sensitivity: 'internal' },
                    { name: 'field_name', type: 'varchar(100)', description: 'Nama field data yang diubah jika relevan', sensitivity: 'internal' },
                    { name: 'old_value', type: 'text', description: 'Nilai data sebelum diubah', sensitivity: 'internal' },
                    { name: 'new_value', type: 'text', description: 'Nilai data setelah diubah', sensitivity: 'internal' },
                    { name: 'status', type: "enum('SUCCESS','FAILED')", description: 'Hasil eksekusi tindakan', sensitivity: 'internal' },
                    { name: 'details', type: 'text', description: 'Keterangan detail tambahan aktivitas', sensitivity: 'internal' },
                    { name: 'ip_address', type: 'varchar(45)', description: 'Alamat IP pengguna', sensitivity: 'internal' },
                    { name: 'user_agent', type: 'varchar(255)', description: 'Informasi browser / device pengguna', sensitivity: 'internal' },
                    { name: 'created_at', type: 'timestamp', description: 'Waktu pencatatan audit', sensitivity: 'internal' }
                ],
                foreignKeys: [],
                relationships: [],
                businessMeaning: 'Bukti akuntabilitas aktivitas pengguna untuk pencegahan fraud dan investigasi insiden.'
            },

            otp_requests: {
                tableName: 'otp_requests',
                displayName: 'Permintaan OTP WhatsApp',
                description: 'Menyimpan kode hash OTP untuk verifikasi otentikasi login atau reset sandi via WhatsApp.',
                menuRelation: 'Sistem Keamanan Otentikasi WhatsApp OTP',
                sensitivity: 'secret',
                allowedRoles: ['Admin', 'Owner'], // Only high-level monitoring, raw otp_hash is never revealed
                primaryKey: 'id',
                columns: [
                    { name: 'id', type: 'int(11)', description: 'Primary Key permintaan OTP', sensitivity: 'internal' },
                    { name: 'phone', type: 'varchar(20)', description: 'Nomor telepon tujuan pengiriman OTP', sensitivity: 'internal' },
                    { name: 'otp_hash', type: 'varchar(255)', description: 'Hash kode OTP (RAHASIA / SAMARKAN)', sensitivity: 'secret' },
                    { name: 'user_id', type: 'int(11)', description: 'ID pengguna jika sudah terdaftar', sensitivity: 'internal' },
                    { name: 'ip_address', type: 'varchar(45)', description: 'IP pengirim request', sensitivity: 'internal' },
                    { name: 'attempts', type: 'tinyint(3) unsigned', description: 'Jumlah percobaan verifikasi', sensitivity: 'internal' },
                    { name: 'status', type: "enum('pending','used','expired','cancelled')", description: 'Status kode OTP', sensitivity: 'internal' },
                    { name: 'expires_at', type: 'timestamp', description: 'Waktu kadaluarsa OTP (biasanya 5 menit)', sensitivity: 'internal' },
                    { name: 'used_at', type: 'timestamp', description: 'Waktu kode berhasil digunakan', sensitivity: 'internal' },
                    { name: 'created_at', type: 'timestamp', description: 'Waktu permintaan dibuat', sensitivity: 'internal' }
                ],
                foreignKeys: [],
                relationships: [],
                businessMeaning: 'Proses verifikasi kepemilikan nomor WhatsApp pengguna.'
            },

            whatsapp_notification_logs: {
                tableName: 'whatsapp_notification_logs',
                displayName: 'Log Notifikasi WhatsApp Gateway',
                description: 'Mencatat status pengiriman pesan notifikasi WhatsApp resmi toko ke nomor pelanggan melalui provider gateway (Fonnte).',
                menuRelation: 'Integrasi WhatsApp Gateway & Notifikasi Otomatis Pesanan',
                sensitivity: 'internal',
                allowedRoles: ['Admin', 'Owner'],
                primaryKey: 'id',
                columns: [
                    { name: 'id', type: 'int(11)', description: 'Primary Key log notifikasi', sensitivity: 'internal' },
                    { name: 'order_id', type: 'int(11)', description: 'ID pesanan yang dinotifikasikan', sensitivity: 'internal' },
                    { name: 'customer_id', type: 'int(11)', description: 'ID customer penerima notifikasi', sensitivity: 'internal' },
                    { name: 'event_type', type: 'varchar(50)', description: 'Jenis peristiwa (order_created, payment_success, shipped, dll)', sensitivity: 'internal' },
                    { name: 'recipient_phone', type: 'varchar(30)', description: 'Nomor WhatsApp penerima', sensitivity: 'internal' },
                    { name: 'message_content', type: 'text', description: 'Teks pesan yang dikirimkan', sensitivity: 'internal' },
                    { name: 'status', type: "enum('PENDING','SENT','FAILED')", description: 'Status transmisi pesan', sensitivity: 'internal' },
                    { name: 'attempt_count', type: 'int(11)', description: 'Jumlah percobaan kirim', sensitivity: 'internal' },
                    { name: 'provider_message_id', type: 'varchar(100)', description: 'ID pesan dari provider gateway', sensitivity: 'internal' },
                    { name: 'error_code', type: 'varchar(50)', description: 'Kode error jika pengiriman gagal', sensitivity: 'internal' },
                    { name: 'error_message', type: 'text', description: 'Deskripsi kendala pengiriman', sensitivity: 'internal' },
                    { name: 'idempotency_key', type: 'varchar(120)', description: 'Kunci unik pencegah duplikasi pesan', sensitivity: 'internal' },
                    { name: 'created_at', type: 'timestamp', description: 'Waktu log dibuat', sensitivity: 'internal' },
                    { name: 'sent_at', type: 'timestamp', description: 'Waktu pesan terkirim', sensitivity: 'internal' },
                    { name: 'updated_at', type: 'timestamp', description: 'Waktu status diperbarui', sensitivity: 'internal' }
                ],
                foreignKeys: [],
                relationships: [],
                businessMeaning: 'Pemantauan pengiriman pesan otomatis konfirmasi pesanan, resi pengiriman, dan status pembayaran.'
            },

            ai_conversations: {
                tableName: 'ai_conversations',
                displayName: 'Riwayat Percakapan AI Chatbot',
                description: 'Menyimpan riwayat tanya-jawab antara pengguna dengan nnaco AI Assistant untuk keperluan audit dan monitoring kualitas layanan.',
                menuRelation: 'Monitoring AI Chatbot Admin (/adminchatbot)',
                sensitivity: 'internal',
                allowedRoles: ['Admin', 'Owner'],
                primaryKey: 'id',
                columns: [
                    { name: 'id', type: 'int(11)', description: 'Primary Key sesi percakapan', sensitivity: 'internal' },
                    { name: 'user_id', type: 'int(11)', description: 'ID user yang bertanya (NULL jika Tamu/Guest)', sensitivity: 'internal' },
                    { name: 'question', type: 'text', description: 'Teks pertanyaan pengguna', sensitivity: 'internal' },
                    { name: 'answer', type: 'text', description: 'Teks jawaban yang diberikan oleh AI', sensitivity: 'internal' },
                    { name: 'created_at', type: 'timestamp', description: 'Waktu percakapan dicatat', sensitivity: 'internal' }
                ],
                foreignKeys: [
                    { column: 'user_id', referencedTable: 'users', referencedColumn: 'id' }
                ],
                relationships: [
                    'ai_conversations.user_id = users.id'
                ],
                businessMeaning: 'Arsip interaksi chatbot untuk pemantauan pertanyaan pengguna dan efektivitas asisten AI.'
            }
        };

        this.businessDefinitions = {
            omzet: "Total akumulasi nilai tagihan (orders.total) dari seluruh pesanan yang telah berstatus 'SELESAI' (atau payments.status = 'PAID'). Pesanan yang masih berstatus MENUNGGU PEMBAYARAN, DIPROSES, DIKEMAS, atau DIBATALKAN tidak dihitung sebagai omzet selesai.",
            stok_rendah: "Produk dengan kuantitas stok <= 5 unit dan status 'Available'. Memerlukan perhatian untuk restock sebelum kehabisan.",
            pesanan_perlu_diproses: "Pesanan dengan status 'MENUNGGU PEMBAYARAN' (menunggu verifikasi pembayaran), 'DIPROSES' (siap disiapkan barangnya), atau 'DIKEMAS' (sedang di-packing oleh operator).",
            produk_terlaris: "Produk dengan akumulasi jumlah unit terbanyak (SUM(order_details.quantity)) pada pesanan yang tidak dibatalkan.",
            pelanggan_terdaftar: "Akun pengguna di tabel users dengan role = 'Customer' dan status = 'Active'."
        };
    }

    /**
     * Get list of all known table names in database
     */
    getAllTableNames() {
        return Object.keys(this.tables);
    }

    /**
     * Get table schema metadata by table name
     */
    getTableMetadata(tableName) {
        const clean = String(tableName || '').toLowerCase().trim();
        return this.tables[clean] || null;
    }

    /**
     * Returns schema summary tailored to the user's role
     */
    getSchemaForRole(role = 'Customer') {
        const result = {
            databaseName: this.databaseName,
            role: role,
            tables: [],
            businessDefinitions: this.businessDefinitions
        };

        for (const [key, tbl] of Object.entries(this.tables)) {
            if (!tbl.allowedRoles.includes(role)) {
                continue; // Skip restricted table for this role
            }

            // Filter columns based on role sensitivity
            const visibleColumns = tbl.columns
                .filter(col => {
                    if (role === 'Customer' && col.sensitivity !== 'public') return false;
                    if (role === 'Operator' && col.sensitivity === 'secret') return false;
                    if (col.sensitivity === 'secret' && col.name === 'password') return false; // Never include password
                    return true;
                })
                .map(col => ({
                    name: col.name,
                    type: col.type,
                    description: col.description
                }));

            result.tables.push({
                tableName: tbl.tableName,
                displayName: tbl.displayName,
                description: tbl.description,
                menuRelation: tbl.menuRelation,
                primaryKey: tbl.primaryKey,
                foreignKeys: tbl.foreignKeys,
                relationships: tbl.relationships,
                columns: visibleColumns,
                rowLevelPolicy: tbl.rowLevelPolicy || 'Semua baris dapat diakses sesuai izin role.'
            });
        }

        return result;
    }

    /**
     * Dynamic database synchronization:
     * Inspects information_schema to verify row counts, column counts, and any newly created tables.
     */
    async syncWithDatabase() {
        try {
            const [dbTables] = await pool.query(
                "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?",
                [this.databaseName]
            );

            const discovered = [];
            for (const row of dbTables) {
                const tName = row.TABLE_NAME;
                discovered.push(tName);
                if (!this.tables[tName]) {
                    // Newly added table detected! Add dynamic entry
                    const [cols] = await pool.query(
                        "SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?",
                        [this.databaseName, tName]
                    );
                    this.tables[tName] = {
                        tableName: tName,
                        displayName: `Tabel ${tName}`,
                        description: `Tabel dinamis ${tName} yang terdaftar di database nnaco_sa_db.`,
                        menuRelation: 'Sistem Terintegrasi',
                        sensitivity: 'internal',
                        allowedRoles: ['Admin', 'Owner'],
                        primaryKey: 'id',
                        columns: cols.map(c => ({
                            name: c.COLUMN_NAME,
                            type: c.COLUMN_TYPE || c.DATA_TYPE,
                            description: c.COLUMN_COMMENT || `Kolom ${c.COLUMN_NAME}`,
                            sensitivity: 'internal'
                        })),
                        foreignKeys: [],
                        relationships: []
                    };
                    console.log(`[SCHEMA SYNC] Discovered new table: ${tName}`);
                }
            }

            return {
                success: true,
                totalTables: discovered.length,
                tables: discovered
            };
        } catch (e) {
            console.error('[SCHEMA SYNC ERROR]:', e.message);
            return { success: false, error: e.message };
        }
    }
}

module.exports = new DatabaseSchemaService();
