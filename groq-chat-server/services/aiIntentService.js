const aiDatabaseService = require('./aiDatabaseService');

const ALL_TOOLS = [
    {
        type: 'function',
        function: {
            name: 'searchProducts',
            description: 'Mencari daftar aksesoris/produk di toko nnaco_sa berdasarkan kata kunci nama, kategori, atau filter harga (misal harga di bawah X, atau rentang harga).',
            parameters: {
                type: 'object',
                properties: {
                    query: { type: 'string', description: 'Kata kunci nama produk, contoh: gelang, kalung, kacamata' },
                    category: { type: 'string', description: 'Nama kategori produk jika spesifik' },
                    minPrice: { type: 'number', description: 'Batas harga minimum dalam Rupiah' },
                    maxPrice: { type: 'number', description: 'Batas harga maksimum dalam Rupiah, misal 50000 untuk di bawah Rp50.000' },
                    sortBy: { type: 'string', enum: ['price_asc', 'price_desc', 'newest'], description: 'Urutan hasil pencarian' },
                    limit: { type: 'integer', description: 'Maksimal jumlah produk yang ditampilkan (1-10)' }
                }
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getProductDetails',
            description: 'Mendapatkan informasi detail dan ketersediaan stok produk tertentu berdasarkan nama produk.',
            parameters: {
                type: 'object',
                properties: {
                    productName: { type: 'string', description: 'Nama produk yang dicari, contoh: Kalung Titanium Elegan' }
                },
                required: ['productName']
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getProductCategories',
            description: 'Mendapatkan daftar seluruh kategori aksesoris yang tersedia di toko nnaco_sa.',
            parameters: {
                type: 'object',
                properties: {}
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getCheapestProduct',
            description: 'Mencari produk/aksesoris dengan harga paling murah/terjangkau yang masih tersedia stoknya.',
            parameters: {
                type: 'object',
                properties: {
                    categoryName: { type: 'string', description: 'Filter kategori tertentu jika diminta, contoh: gelang, kalung' }
                }
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getLatestProducts',
            description: 'Mendapatkan daftar produk-produk terbaru yang baru ditambahkan ke katalog toko nnaco_sa.',
            parameters: {
                type: 'object',
                properties: {
                    limit: { type: 'integer', description: 'Jumlah produk terbaru (1-10)' }
                }
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getMyOrders',
            description: 'Melihat riwayat pesanan milik customer yang sedang login.',
            parameters: {
                type: 'object',
                properties: {}
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getMyOrderStatus',
            description: 'Mengecek status terkini dari pesanan customer yang sedang login.',
            parameters: {
                type: 'object',
                properties: {
                    orderId: { type: 'integer', description: 'ID pesanan jika customer menyebutkan nomor pesanan tertentu' }
                }
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getCurrentUserProfile',
            description: 'Melihat profil dan informasi akun pengguna yang sedang berinteraksi/login saat ini (nama, email, role, status akun).',
            parameters: {
                type: 'object',
                properties: {}
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getLowStockProducts',
            description: 'Melihat daftar produk yang stoknya menipis atau hampir habis (stok <= batas tertentu). Hanya untuk Operator, Admin, Owner.',
            parameters: {
                type: 'object',
                properties: {
                    threshold: { type: 'integer', description: 'Batas stok minimum yang dianggap menipis, default 5' }
                }
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getOrdersNeedingProcessing',
            description: 'Melihat pesanan yang sudah dibayar dan perlu diproses atau dikemas. Hanya untuk Operator, Admin, Owner.',
            parameters: {
                type: 'object',
                properties: {}
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getPendingPaymentOrdersCount',
            description: 'Melihat jumlah pesanan yang masih berstatus menunggu pembayaran. Hanya untuk Operator, Admin, Owner.',
            parameters: {
                type: 'object',
                properties: {}
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getActiveProductCount',
            description: 'Melihat jumlah total produk aktif, produk dengan stok tipis, dan produk habis di katalog. Hanya untuk Admin dan Owner.',
            parameters: {
                type: 'object',
                properties: {}
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getOrderManagementSummary',
            description: 'Melihat ringkasan seluruh pesanan di sistem dikelompokkan berdasarkan status. Hanya untuk Admin dan Owner.',
            parameters: {
                type: 'object',
                properties: {}
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getUserManagementSummary',
            description: 'Melihat ringkasan jumlah pengguna sistem berdasarkan peran (role) dan status keaktifan. Hanya untuk Admin dan Owner.',
            parameters: {
                type: 'object',
                properties: {}
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getSalesSummary',
            description: 'Melihat ringkasan omzet dan jumlah transaksi yang berhasil dibayar (hanya status PAID). Khusus Owner.',
            parameters: {
                type: 'object',
                properties: {
                    period: { type: 'string', enum: ['this_month', 'today', 'all_time'], description: 'Periode laporan omzet' }
                }
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getTopSellingProducts',
            description: 'Melihat daftar produk yang paling banyak terjual dari pesanan yang sudah dibayar. Khusus Owner.',
            parameters: {
                type: 'object',
                properties: {
                    limit: { type: 'integer', description: 'Maksimal jumlah produk (1-10)' }
                }
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getPaymentSummary',
            description: 'Melihat ringkasan transaksi pembayaran berdasarkan status. Khusus Owner.',
            parameters: {
                type: 'object',
                properties: {}
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getAllProducts',
            description: 'Membaca seluruh daftar produk aksesoris lengkap dengan kategori, harga, stok, dan status ketersediaan dari tabel products.',
            parameters: {
                type: 'object',
                properties: {
                    category: { type: 'string', description: 'Filter nama kategori' },
                    status: { type: 'string', enum: ['Available', 'Out_of_Stock', 'Hidden'], description: 'Filter status produk' },
                    limit: { type: 'integer', description: 'Jumlah produk yang ditampilkan (1-30)' }
                }
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getCategoryStats',
            description: 'Membaca data seluruh kategori aksesoris dari tabel categories, termasuk jumlah varian produk dan total unit stok di tiap kategori.',
            parameters: {
                type: 'object',
                properties: {}
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getCategoryDetails',
            description: 'Membaca rincian satu kategori tertentu dan daftar produk di dalamnya dari tabel categories dan products.',
            parameters: {
                type: 'object',
                properties: {
                    categoryName: { type: 'string', description: 'Nama kategori yang dicari, contoh: Gelang, Kalung' }
                },
                required: ['categoryName']
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getAllOrders',
            description: 'Membaca daftar pesanan toko dari tabel orders dan payments, lengkap dengan nama customer, tanggal, nominal, dan status pesanan.',
            parameters: {
                type: 'object',
                properties: {
                    status: { type: 'string', description: 'Filter status pesanan: MENUNGGU PEMBAYARAN, DIPROSES, DIKEMAS, DIKIRIM, SELESAI, DIBATALKAN' },
                    limit: { type: 'integer', description: 'Jumlah pesanan (1-25)' }
                }
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getOrderDetails',
            description: 'Membaca rincian lengkap pesanan dari tabel orders dan order_details (item produk, kuantitas, harga, subtotal, customer, pembayaran).',
            parameters: {
                type: 'object',
                properties: {
                    orderId: { type: 'integer', description: 'ID pesanan' }
                },
                required: ['orderId']
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getAllUsers',
            description: 'Membaca daftar akun pengguna toko dari tabel users (ID, nama, email, nomor HP, role, status aktif) secara aman tanpa password.',
            parameters: {
                type: 'object',
                properties: {
                    search: { type: 'string', description: 'Kata kunci pencarian nama pengguna / username / email spesifik' },
                    role: { type: 'string', enum: ['Customer', 'Operator', 'Admin', 'Owner'], description: 'Filter peran pengguna' },
                    status: { type: 'string', enum: ['Active', 'Inactive', 'Banned'], description: 'Filter status akun' },
                    limit: { type: 'integer', description: 'Jumlah pengguna (1-25)' }
                }
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getAllPayments',
            description: 'Membaca riwayat transaksi pembayaran dari tabel payments (ID, order ID, metode pembayaran, nominal, status bayar, tanggal).',
            parameters: {
                type: 'object',
                properties: {
                    status: { type: 'string', enum: ['PENDING', 'PAID', 'FAILED', 'EXPIRED', 'CANCELLED'], description: 'Filter status pembayaran' },
                    limit: { type: 'integer', description: 'Jumlah transaksi (1-25)' }
                }
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getProductReviews',
            description: 'Membaca ulasan dan rating kepuasan pelanggan untuk produk tertentu dari tabel reviews.',
            parameters: {
                type: 'object',
                properties: {
                    productName: { type: 'string', description: 'Nama atau ID produk' },
                    limit: { type: 'integer', description: 'Jumlah ulasan (1-10)' }
                },
                required: ['productName']
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getReviewSummary',
            description: 'Membaca ringkasan ulasan seluruh toko dari tabel reviews, rata-rata rating bintang, dan jumlah testimoni.',
            parameters: {
                type: 'object',
                properties: {}
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getAllReviews',
            description: 'Membaca daftar testimoni dan komentar terbaru pembeli dari tabel reviews.',
            parameters: {
                type: 'object',
                properties: {
                    limit: { type: 'integer', description: 'Jumlah ulasan (1-25)' }
                }
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getDatabaseOverview',
            description: 'Membaca ringkasan menyeluruh database nnaco_sa_db: daftar seluruh tabel (products, categories, orders, order_details, payments, users, reviews), jumlah baris data, dan keterkaitan menu website.',
            parameters: {
                type: 'object',
                properties: {}
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getMenuOverview',
            description: 'Membaca pemetaan seluruh menu website nnaco_sa (Beranda, Katalog, Cart/Checkout, Riwayat Pesanan, Profil, Admin, Operator, Owner) beserta tabel database sumbernya.',
            parameters: {
                type: 'object',
                properties: {}
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'readTableData',
            description: 'Membaca data langsung dari tabel database tertentu secara aman (tabel: products, categories, orders, order_details, payments, users, reviews).',
            parameters: {
                type: 'object',
                properties: {
                    tableName: { type: 'string', enum: ['products', 'categories', 'orders', 'order_details', 'payments', 'users', 'reviews', 'system_settings', 'system_audit_logs', 'otp_requests', 'whatsapp_notification_logs', 'ai_conversations'], description: 'Nama tabel yang ingin dibaca' },
                    limit: { type: 'integer', description: 'Jumlah baris data (1-25)' }
                },
                required: ['tableName']
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getMostExpensiveProduct',
            description: 'Mencari produk/aksesoris dengan harga paling mahal/tertinggi di katalog toko nnaco_sa.',
            parameters: { type: 'object', properties: {} }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getTotalStock',
            description: 'Melihat total unit stok dari seluruh produk aksesoris di toko nnaco_sa.',
            parameters: { type: 'object', properties: {} }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getOutOfStockProducts',
            description: 'Melihat daftar produk aksesoris yang stoknya habis (stok = 0).',
            parameters: {
                type: 'object',
                properties: {
                    limit: { type: 'integer', description: 'Maksimal jumlah produk (1-20)' }
                }
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getRestockPriorityProducts',
            description: 'Melihat daftar produk prioritas restock (stok menipis dan diminati).',
            parameters: {
                type: 'object',
                properties: {
                    limit: { type: 'integer', description: 'Maksimal jumlah produk (1-20)' }
                }
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'checkProductStock',
            description: 'Mengecek ketersediaan dan sisa unit stok produk tertentu.',
            parameters: {
                type: 'object',
                properties: {
                    productName: { type: 'string', description: 'Nama produk yang dicari stoknya' }
                },
                required: ['productName']
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getStockSoldUnits',
            description: 'Melihat total unit barang/produk yang telah terjual di toko nnaco_sa.',
            parameters: { type: 'object', properties: {} }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getOrdersByStatus',
            description: 'Melihat daftar pesanan berdasarkan status tertentu (MENUNGGU PEMBAYARAN, DIPROSES, DIKEMAS, DIKIRIM, SELESAI, DIBATALKAN).',
            parameters: {
                type: 'object',
                properties: {
                    status: { type: 'string', description: 'Status pesanan' },
                    limit: { type: 'integer', description: 'Jumlah pesanan (1-25)' }
                }
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getOrderContents',
            description: 'Melihat detail isi, kuantitas item, harga, dan subtotal dari pesanan tertentu.',
            parameters: {
                type: 'object',
                properties: {
                    orderId: { type: 'integer', description: 'Nomor / ID pesanan' }
                },
                required: ['orderId']
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getOperationalQueue',
            description: 'Melihat ringkasan antrean operasional toko: pesanan menunggu bayar, dikemas, dikirim, dan selesai.',
            parameters: { type: 'object', properties: {} }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getPaymentsAnalytics',
            description: 'Melihat statistik lengkap transaksi pembayaran (menunggu, berhasil/PAID, gagal, kedaluwarsa).',
            parameters: { type: 'object', properties: {} }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getPaymentMethodsBreakdown',
            description: 'Melihat rincian metode pembayaran yang digunakan customer (GoPay, DANA, Transfer Bank, dll).',
            parameters: { type: 'object', properties: {} }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getOmzetReport',
            description: 'Melihat laporan omzet dan performa penjualan toko (hari ini, bulan ini, tahun ini, total).',
            parameters: {
                type: 'object',
                properties: {
                    period: { type: 'string', enum: ['today', 'this_month', 'this_year', 'all_time'], description: 'Periode laporan omzet' }
                }
            }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getSalesComparison',
            description: 'Melihat perbandingan performa penjualan bulan ini dengan bulan sebelumnya.',
            parameters: { type: 'object', properties: {} }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getCustomerStatistics',
            description: 'Melihat statistik pelanggan (total pelanggan terdaftar, pelanggan baru bulan ini, pelanggan aktif).',
            parameters: { type: 'object', properties: {} }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getTopSpendingCustomer',
            description: 'Melihat pelanggan (customer) dengan nilai akumulasi transaksi belanja terbanyak.',
            parameters: { type: 'object', properties: {} }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getSalesByCategory',
            description: 'Melihat jumlah produk yang terjual dan total omzet per kategori produk aksesoris.',
            parameters: { type: 'object', properties: {} }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getLowStockHighSalesProducts',
            description: 'Melihat produk yang stoknya menipis (≤ 5) tetapi memiliki angka penjualan tinggi.',
            parameters: { type: 'object', properties: {} }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getAuditLogsToday',
            description: 'Melihat log audit aktivitas operasional toko yang terjadi hari ini.',
            parameters: { type: 'object', properties: {} }
        }
    },
    {
        type: 'function',
        function: {
            name: 'getDatabaseSchema',
            description: 'Melihat skema dan struktur tabel database yang diizinkan untuk role saat ini.',
            parameters: { type: 'object', properties: {} }
        }
    }
];

class AiIntentService {
    getToolsForRole(role = 'Customer') {
        const allowedToolNames = aiDatabaseService.getAllowedToolsForRole(role);
        return ALL_TOOLS.filter(t => allowedToolNames.includes(t.function.name));
    }

    // Direct Intent Fallback Planner (detects obvious intents if LLM doesn't call tool)
    detectIntent(message, role = 'Customer') {
        const text = message.toLowerCase().trim();

        // 0. Panduan Website / Kebijakan Toko (Alur Pemesanan, Retur, Pengiriman)
        if (text.includes('cara memesan') || text.includes('cara pesan') || text.includes('cara order') || text.includes('cara beli') || text.includes('alur pemesanan') || text.includes('cara belanja') || text.includes('bagaimana memesan')) {
            return {
                type: 'store_guide',
                guideType: 'order_flow',
                reply: `Berikut panduan cara melakukan pemesanan di website nnaco_sa:
1. **Pilih Aksesoris**: Jelajahi katalog produk di menu Shop/Katalog.
2. **Tambah ke Keranjang**: Klik tombol **"Tambah ke Keranjang"** pada aksesoris favorit Anda.
3. **Periksa Keranjang**: Buka halaman Keranjang untuk memastikan jumlah dan rincian belanja Anda.
4. **Checkout**: Lanjutkan ke halaman Checkout, lengkapi alamat pengiriman, dan pilih metode pembayaran (Gopay, OVO, atau transfer bank).
5. **Konfirmasi & Pantau**: Selesaikan pembayaran. Status pesanan dapat dipantau langsung melalui menu Dashboard Customer!`
            };
        }

        if (text.includes('kebijakan retur') || text.includes('pengembalian barang') || text.includes('retur') || text.includes('pengembalian produk')) {
            return {
                type: 'store_guide',
                guideType: 'return_policy',
                reply: `Berikut kebijakan pengembalian produk (retur) di nnaco_sa:
- nnaco_sa menyediakan kebijakan retur 30 hari (*30-day return policy*) untuk produk yang mengalami cacat atau tidak sesuai pesanan.
- Produk harus dalam kondisi belum dipakai beserta kemasan aslinya.
- Silakan hubungi layanan pelanggan di **support@nnacosa.com** dengan melampirkan nomor pesanan dan foto produk.`
            };
        }

        if (text.includes('kebijakan pengiriman') || text.includes('ongkir') || text.includes('gratis ongkir') || text.includes('pengiriman')) {
            return {
                type: 'store_guide',
                guideType: 'shipping_policy',
                reply: `Berikut informasi pengiriman di toko nnaco_sa:
- Pesanan diproses dan dikirim oleh tim operasional setelah status pembayaran terverifikasi (*PAID*).
- Kami menyediakan program gratis ongkir (*Free Shipping*) untuk pesanan tertentu sesuai promo yang berlaku di website.
- Anda dapat memantau status pengiriman pesanan kapan saja melalui Dashboard akun Anda.`
            };
        }

        if (text.includes('fitur website') || text.includes('apa saja fitur') || text.includes('fitur apa saja') || text.includes('fitur di website') || text.includes('fitur sistem')) {
            return {
                type: 'store_guide',
                guideType: 'website_features',
                reply: `Website **nnaco_sa** dilengkapi dengan berbagai fitur modern untuk kemudahan belanja aksesoris Anda:
1. **Katalog & Filter Interaktif (/product)**: Temukan gelang, kalung, cincin, dan anting dengan filter kategori dan rentang harga real-time.
2. **Keranjang Belanja Real-Time (/cart)**: Periksa item belanja dan hitung total biaya secara otomatis.
3. **Checkout Praktis (/checkout)**: Isi alamat pengiriman dan pilih metode pembayaran (Gopay, OVO, transfer bank).
4. **Riwayat & Pelacakan Pesanan (/customer/history)**: Pantau status pesanan mulai dari Menunggu Pembayaran, Diproses, Dikemas, hingga Dikirim.
5. **Ulasan & Rating Bintang**: Berikan testimoni dan baca ulasan pembeli lain di setiap halaman produk aksesoris.
6. **Multi-Role Portal**: Dashboard terpisah untuk Customer, Operator toko, Admin, dan Owner.
7. **nnaco AI Assistant**: Asisten virtual 24/7 yang dapat membaca database dan membantu menjawab segala pertanyaan Anda seputar nnaco_sa!`
            };
        }

        if (text.includes('tentang website') || text.includes('jelaskan tentang website') || text.includes('website nnaco_sa') || text.includes('tentang toko') || text.includes('apa itu nnaco') || text.includes('mengenai website')) {
            return {
                type: 'store_guide',
                guideType: 'about_website',
                reply: `**nnaco_sa** adalah platform e-commerce resmi toko aksesoris yang menyediakan koleksi perhiasan dan aksesoris fashion berkualitas tinggi, mulai dari gelang titanium, kalung elegan, cincin estetis, hingga anting modern.

Keunggulan sistem website nnaco_sa:
- **Terintegrasi Real-Time dengan Database MySQL (nnaco_sa_db)**: Informasi katalog, stok produk, harga, dan pesanan selalu mutakhir.
- **Dukungan Pembayaran Digital**: Mendukung berbagai metode pembayaran populer seperti Gopay, OVO, dan transfer perbankan.
- **Sistem Keamanan Terpadu**: Otentikasi aman berbasis role (Customer, Operator, Admin, Owner) dengan perlindungan privasi data.
- **nnaco AI Assistant**: Terhubung langsung dengan basis data toko untuk memberikan pelayanan cepat dan akurat bagi pelanggan.`
            };
        }

        // 1. Status pesanan
        if (text.includes('status pesanan') || text.includes('pesanan saya') || text.includes('lacak pesanan') || text.includes('order saya') || text.includes('pesanan nizar') || text.includes('pesanan david') || text.includes('pesanan user')) {
            const matchId = text.match(/#?(\d+)/);
            const orderId = matchId ? parseInt(matchId[1], 10) : null;

            // Admin, Operator, Owner: dapat mencari pesanan customer tertentu
            if (role === 'Admin' || role === 'Operator' || role === 'Owner') {
                if (orderId) {
                    return { toolName: 'getOrderDetails', args: { orderId } };
                }
                const nameMatch = text.match(/(?:pesanan|order)\s+(?:dari\s+|milik\s+|an\s+|atas\s+nama\s+)?([a-zA-Z0-9_\-\.]+)/i);
                const excludeWords = new Set(['saya', 'terakhir', 'ini', 'itu', 'terbaru', 'hari', 'semua', 'toko', 'saat', 'yang', 'customer', 'pelanggan']);
                if (nameMatch && !excludeWords.has(nameMatch[1].toLowerCase())) {
                    return { toolName: 'getAllOrders', args: { search: nameMatch[1], limit: 10 } };
                }
                return { toolName: 'getAllOrders', args: { limit: 10 } };
            }

            return {
                toolName: 'getMyOrderStatus',
                args: orderId ? { orderId } : {}
            };
        }


        // 2. Aksesoris tersedia / katalog
        if (text.includes('apa saja aksesoris yang tersedia') || text.includes('koleksi aksesoris') || text.includes('daftar aksesoris') || text.includes('produk yang tersedia')) {
            return {
                toolName: 'searchProducts',
                args: { limit: 8 }
            };
        }

        // 3. Kategori aksesoris & Penjualan per Kategori
        if (text.includes('kategori')) {
            if (text.includes('terjual') || text.includes('penjualan') || text.includes('omzet')) {
                return {
                    toolName: 'getSalesByCategory',
                    args: {}
                };
            }
            if (text.includes('berapa') || text.includes('jumlah') || text.includes('total') || text.includes('ada berapa')) {
                return {
                    toolName: 'getProductCategories',
                    args: { countOnly: true }
                };
            }
            if (text.includes('apa saja') || text.includes('tersedia') || text.includes('daftar') || text.includes('sebutkan') || text.includes('koleksi') || text.includes('semua')) {
                return {
                    toolName: 'getProductCategories',
                    args: {}
                };
            }
        }

        // 4. Paling mahal / harga tertinggi
        if (text.includes('paling mahal') || text.includes('harga tertinggi') || text.includes('termahal') || text.includes('produk termahal')) {
            return {
                toolName: 'getMostExpensiveProduct',
                args: {}
            };
        }

        // 5. Paling murah / termurah
        if (text.includes('paling murah') || text.includes('termurah') || text.includes('harga terendah') || text.includes('paling terjangkau')) {
            let cat = null;
            if (text.includes('gelang')) cat = 'gelang';
            else if (text.includes('kalung')) cat = 'kalung';
            else if (text.includes('cincin')) cat = 'cincin';
            else if (text.includes('anting')) cat = 'anting';
            return {
                toolName: 'getCheapestProduct',
                args: cat ? { categoryName: cat } : {}
            };
        }

        // 6. Produk Terlaris / Paling Banyak Diminati / Paling Banyak Terjual
        if (text.includes('paling banyak diminati') || text.includes('paling diminati') || text.includes('terlaris') || text.includes('paling laku') || text.includes('paling banyak terjual') || text.includes('produk terlaris')) {
            return {
                toolName: 'getTopSellingProducts',
                args: { limit: 5 }
            };
        }

        // 7. Kategori & Jumlah Produk Per Kategori
        if (text.includes('produk dalam setiap kategori') || text.includes('produk per kategori') || text.includes('jumlah produk dalam setiap kategori') || text.includes('stok per kategori')) {
            return {
                toolName: 'getCategoryStats',
                args: {}
            };
        }

        // 8. Stok Habis / Sedang Tidak Tersedia
        if (text.includes('stoknya habis') || text.includes('stok habis') || text.includes('sedang tidak tersedia') || text.includes('out of stock') || text.includes('produk apa yang habis')) {
            return {
                toolName: 'getOutOfStockProducts',
                args: { limit: 10 }
            };
        }

        // 9. Prioritas Restock
        if (text.includes('segera ditambahkan stoknya') || text.includes('segera restock') || text.includes('prioritas restock') || text.includes('perlu restock') || text.includes('harus segera ditambah')) {
            return {
                toolName: 'getRestockPriorityProducts',
                args: { limit: 10 }
            };
        }

        // 10. Stok Menipis / Hampir Habis
        if (text.includes('stoknya hampir habis') || text.includes('stok menipis') || text.includes('stoknya menipis') || text.includes('stok tipis') || text.includes('stok sedikit') || text.includes('stok paling sedikit')) {
            return {
                toolName: 'getLowStockProducts',
                args: { threshold: 5 }
            };
        }

        // 11. Total Stok Seluruh Produk
        if (text.includes('total stok seluruh produk') || text.includes('total stok produk') || text.includes('total unit stok') || text.includes('berapa total stok') || text.includes('stok seluruh produk')) {
            return {
                toolName: 'getTotalStock',
                args: {}
            };
        }

        // 12. Perubahan Stok / Total Unit Terjual
        if (text.includes('perubahan stok') || text.includes('unit terjual') || text.includes('total produk terjual') || text.includes('barang terjual')) {
            return {
                toolName: 'getStockSoldUnits',
                args: {}
            };
        }

        // 13. Penurunan Stok Paling Besar / Stok Rendah Tapi Penjualan Tinggi
        if (text.includes('penurunan stok paling besar') || text.includes('stok rendah tetapi penjualan') || text.includes('stok rendah tapi penjualan') || text.includes('stok tipis tapi laris') || text.includes('stok sedikit tapi laris')) {
            return {
                toolName: 'getLowStockHighSalesProducts',
                args: {}
            };
        }

        // 14. Cek Stok Produk Tertentu
        if (text.includes('apakah') && (text.includes('stok') || text.includes('tersedia')) && (text.includes('masih') || text.includes('punya') || text.includes('memiliki'))) {
            let clean = text
                .replace(/\b(apakah|ada|masih|memiliki|punya|sisa|stok|stoknya|tersedia|berapa|unit|produk|aksesoris|toko|di)\b/gi, ' ')
                .replace(/[^a-zA-Z0-9\s]/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();
            if (clean.length > 2) {
                return {
                    toolName: 'checkProductStock',
                    args: { productName: clean }
                };
            }
        }

        // 15. Produk di bawah harga X (e.g. di bawah 50.000)
        const underPriceMatch = text.match(/(?:di\s*bawah|kurang\s*dari|<)\s*(?:rp\.?\s*)?(\d+(?:[\.,]\d+)?)\s*(?:k|ribu)?/i);
        if (underPriceMatch) {
            let rawVal = underPriceMatch[1].replace(/[\.,]/g, '');
            let maxPrice = parseInt(rawVal, 10);
            if (rawVal.length <= 3) maxPrice = maxPrice * 1000;
            
            let query = '';
            if (text.includes('gelang')) query = 'gelang';
            else if (text.includes('kalung')) query = 'kalung';
            else if (text.includes('cincin')) query = 'cincin';
            else if (text.includes('anting')) query = 'anting';

            return {
                toolName: 'searchProducts',
                args: { maxPrice, query, limit: 6 }
            };
        }

        // 16. Produk terbaru
        if (text.includes('produk terbaru') || text.includes('terbaru di nnaco_sa') || text.includes('koleksi baru')) {
            return {
                toolName: 'getLatestProducts',
                args: { limit: 5 }
            };
        }

        // 17. Total Produk (Database products)
        if (text.includes('jumlah produk yang aktif') || text.includes('produk aktif') || text.includes('total produk') || text.includes('berapa produk') || text.includes('banyak produk') || text.includes('jumlah produk')) {
            return {
                toolName: 'getActiveProductCount',
                args: {}
            };
        }

        // 18. Pesanan: Isi pesanan nomor #X
        const orderContentMatch = text.match(/(?:apa\s+isi\s+pesanan|isi\s+pesanan|rincian\s+pesanan|detail\s+pesanan)\s*(?:nomor|no|id)?\s*#?(\d+)/i);
        if (orderContentMatch) {
            const orderId = parseInt(orderContentMatch[1], 10);
            return {
                toolName: 'getOrderContents',
                args: { orderId }
            };
        }

        // 19. Pesanan: Total pesanan hari ini
        if (text.includes('total pesanan hari ini') || text.includes('pesanan hari ini')) {
            return {
                toolName: 'getOrdersByStatus',
                args: { status: 'TODAY' }
            };
        }

        // 20. Pesanan: Belum dibayar
        if (text.includes('menunggu pembayaran') || text.includes('pending payment') || text.includes('belum dibayar') || text.includes('pesanan pending')) {
            return {
                toolName: 'getPendingPaymentOrdersCount',
                args: {}
            };
        }

        // 21. Pesanan: Sedang diproses / belum diproses
        if (text.includes('sedang diproses') || text.includes('belum diproses') || text.includes('pesanan diproses')) {
            return {
                toolName: 'getOrdersByStatus',
                args: { status: 'DIPROSES' }
            };
        }

        // 22. Pesanan: Sedang dikemas / menunggu pengiriman
        if (text.includes('menunggu pengiriman') || text.includes('sedang dikemas') || text.includes('pesanan dikemas')) {
            return {
                toolName: 'getOrdersByStatus',
                args: { status: 'DIKEMAS' }
            };
        }

        // 23. Pesanan: Sudah dikirim
        if (text.includes('sudah dikirim') || text.includes('pesanan dikirim')) {
            return {
                toolName: 'getOrdersByStatus',
                args: { status: 'DIKIRIM' }
            };
        }

        // 24. Pesanan: Dibatalkan
        if (text.includes('pesanan dibatalkan') || text.includes('dibatalkan')) {
            return {
                toolName: 'getOrdersByStatus',
                args: { status: 'DIBATALKAN' }
            };
        }

        // 25. Pesanan: Selesai
        if (text.includes('pesanan selesai') || text.includes('jumlah pesanan selesai')) {
            return {
                toolName: 'getOrdersByStatus',
                args: { status: 'SELESAI' }
            };
        }

        // 26. Pembayaran: Total pembayaran berhasil / konfirmasi
        if (text.includes('pembayaran yang berhasil') || text.includes('pembayaran berhasil') || text.includes('menunggu konfirmasi') || text.includes('total pembayaran')) {
            return {
                toolName: 'getPaymentsAnalytics',
                args: {}
            };
        }

        // 27. Pembayaran: Metode pembayaran
        if (text.includes('metode pembayaran apa yang paling banyak') || text.includes('metode pembayaran apa') || text.includes('transaksi menggunakan gopay') || text.includes('transaksi menggunakan dana') || text.includes('transaksi transfer bank') || (text.includes('metode pembayaran') && text.includes('digunakan'))) {
            return {
                toolName: 'getPaymentMethodsBreakdown',
                args: {}
            };
        }

        // 28. Penjualan & Laporan: Perbandingan penjualan
        if (text.includes('perbandingan penjualan') || text.includes('bandingkan jumlah pesanan') || text.includes('perkembangan penjualan')) {
            return {
                toolName: 'getSalesComparison',
                args: {}
            };
        }

        // 29. Penjualan & Laporan: Omzet
        if (text.includes('omzet') || text.includes('omset') || text.includes('ringkasan penjualan') || text.includes('total penjualan')) {
            if (text.includes('hari ini')) {
                return {
                    toolName: 'getOmzetReport',
                    args: { period: 'today' }
                };
            }
            if (text.includes('tahun ini')) {
                return {
                    toolName: 'getOmzetReport',
                    args: { period: 'this_year' }
                };
            }
            if (text.includes('semua') || text.includes('total') || text.includes('seluruh')) {
                return {
                    toolName: 'getOmzetReport',
                    args: { period: 'all_time' }
                };
            }
            return {
                toolName: 'getOmzetReport',
                args: { period: 'this_month' }
            };
        }

        // 30. Rata-rata nilai transaksi
        if (text.includes('rata-rata nilai transaksi') || text.includes('rata-rata transaksi')) {
            return {
                toolName: 'getOmzetReport',
                args: { period: 'this_month' }
            };
        }

        // 31. Customer: Transaksi terbanyak / top spending
        if (text.includes('customer dengan transaksi terbanyak') || text.includes('paling banyak berbelanja') || text.includes('customer yang paling banyak')) {
            return {
                toolName: 'getTopSpendingCustomer',
                args: {}
            };
        }

        // 32. Customer: Jumlah Customer terdaftar & Baru bulan ini
        if (text.includes('customer baru bulan ini') || text.includes('customer baru') || text.includes('jumlah customer terdaftar') || text.includes('jumlah customer') || text.includes('total customer')) {
            return {
                toolName: 'getCustomerStatistics',
                args: {}
            };
        }

        // 33. Operasional: Antrean Operator
        if (text.includes('dikerjakan operator') || text.includes('antrean operasional') || text.includes('antrian operasional') || text.includes('pekerjaan operator')) {
            return {
                toolName: 'getOperationalQueue',
                args: {}
            };
        }

        // 34. Operasional: Aktivitas hari ini / audit logs
        if (text.includes('aktivitas operasional yang terjadi hari ini') || text.includes('aktivitas operasional') || text.includes('log audit') || text.includes('aktivitas hari ini')) {
            return {
                toolName: 'getAuditLogsToday',
                args: {}
            };
        }

        // 35. Cross-database: Produk terjual berdasarkan kategori
        if (text.includes('produk terjual berdasarkan kategori') || text.includes('penjualan berdasarkan kategori') || text.includes('omzet per kategori')) {
            return {
                toolName: 'getSalesByCategory',
                args: {}
            };
        }

        // 36. Skema & Metadata Database
        if (text.includes('skema database') || text.includes('metadata database') || text.includes('struktur database') || text.includes('data dictionary') || text.includes('kamus data')) {
            return {
                toolName: 'getDatabaseSchema',
                args: {}
            };
        }

        // Pengguna terdaftar / ringkasan user / total pengguna
        if (
            text.includes('pengguna yang terdaftar') ||
            text.includes('user yang terdaftar') ||
            text.includes('berapa pengguna') ||
            text.includes('total pengguna') ||
            text.includes('jumlah pengguna') ||
            text.includes('total user') ||
            text.includes('jumlah user') ||
            text.includes('berapa user') ||
            text.includes('ada berapa user') ||
            text.includes('ada berapa pengguna') ||
            text.includes('berapa total user') ||
            text.includes('berapa customer') ||
            text.includes('total customer') ||
            text.includes('jumlah customer') ||
            text.includes('berapa pelanggan') ||
            text.includes('total pelanggan') ||
            text.includes('jumlah pelanggan') ||
            text.includes('banyak user') ||
            text.includes('banyak pengguna') ||
            ((text.includes('apakah') || text.includes('adakah')) && (
                text.includes('total user') ||
                text.includes('data user') ||
                text.includes('data pengguna') ||
                (text.includes('user') && text.includes('database')) ||
                (text.includes('pengguna') && text.includes('database'))
            ))
        ) {
            return {
                toolName: 'getUserManagementSummary',
                args: {}
            };
        }

        // Semua / Seluruh Produk Aksesoris
        if (text.includes('semua produk') || text.includes('seluruh produk') || text.includes('semua aksesoris') || text.includes('seluruh aksesoris') || text.includes('katalog lengkap') || text.includes('daftar produk')) {
            return {
                toolName: 'getAllProducts',
                args: { limit: 20 }
            };
        }

        // Operator: Pesanan yang sudah dibayar dan perlu diproses / belum ditangani
        if ((role === 'Operator' || role === 'Admin' || role === 'Owner') && (text.includes('perlu diproses') || text.includes('sudah dibayar dan perlu diproses') || text.includes('pesanan diproses') || text.includes('belum ditangani') || text.includes('belum diproses') || text.includes('perlu ditangani') || text.includes('mana yang belum ditangani'))) {
            return {
                toolName: 'getOrdersNeedingProcessing',
                args: { limit: 10 }
            };
        }

        // 8. Database Overview & Schema (pertanyaan seputar membaca database / tabel / setiap menu atau tabel)
        if (text.includes('baca database') || text.includes('keseluruhan database') || text.includes('setiap menu atau pun tabel') || text.includes('setiap menu dan tabel') || text.includes('daftar tabel') || text.includes('tabel apa saja') || text.includes('database apa saja') || text.includes('ringkasan database') || text.includes('struktur tabel') || text.includes('tabel di database') || text.includes('semua tabel') || text.includes('seluruh tabel') || text.includes('database nnaco')) {
            return {
                toolName: 'getDatabaseOverview',
                args: {}
            };
        }

        // 9. Menu Overview (pertanyaan seputar setiap menu website)
        if (text.includes('menu apa saja') || text.includes('setiap menu') || text.includes('daftar menu') || text.includes('menu website') || text.includes('menu toko') || text.includes('seluruh menu') || text.includes('menu yang ada') || text.includes('ada menu apa')) {
            return {
                toolName: 'getMenuOverview',
                args: {}
            };
        }

        // 10. Specific Table Data Reading & Row Count (Seluruh Database)
        const allKnownTables = [
            'products', 'categories', 'orders', 'order_details', 'payments', 'users',
            'reviews', 'system_settings', 'system_audit_logs', 'otp_requests',
            'whatsapp_notification_logs', 'ai_conversations'
        ];

        // Hitung baris tabel spesifik
        for (const tbl of allKnownTables) {
            const isTableCountQuery = (
                (text.includes(tbl) || (tbl === 'products' && text.includes('produk')) || (tbl === 'categories' && text.includes('kategori')) || (tbl === 'payments' && text.includes('pembayaran')) || (tbl === 'orders' && text.includes('pesanan'))) &&
                (text.includes('berapa baris') || text.includes('berapa data') || text.includes('jumlah baris') || text.includes('jumlah data') || text.includes('total baris'))
            );
            if (isTableCountQuery) {
                return {
                    toolName: 'getTableCount',
                    args: { tableName: tbl }
                };
            }
        }

        // Baca isi tabel spesifik
        for (const tbl of allKnownTables) {
            const aliasMatch = (
                text.includes(`tabel ${tbl}`) || text.includes(`table ${tbl}`) ||
                text.includes(`isi tabel ${tbl}`) || text.includes(`data tabel ${tbl}`) ||
                text.includes(`baca tabel ${tbl}`) || text.includes(`lihat tabel ${tbl}`) ||
                (tbl === 'products' && (text.includes('tabel produk') || text.includes('isi tabel produk'))) ||
                (tbl === 'categories' && (text.includes('tabel kategori') || text.includes('isi tabel kategori'))) ||
                (tbl === 'orders' && (text.includes('tabel pesanan') || text.includes('isi tabel pesanan'))) ||
                (tbl === 'order_details' && (text.includes('tabel detail pesanan') || text.includes('tabel rincian pesanan'))) ||
                (tbl === 'payments' && (text.includes('tabel pembayaran') || text.includes('isi tabel pembayaran'))) ||
                (tbl === 'users' && (text.includes('tabel user') || text.includes('tabel pengguna'))) ||
                (tbl === 'reviews' && (text.includes('tabel ulasan') || text.includes('tabel review'))) ||
                (tbl === 'system_settings' && (text.includes('tabel setting') || text.includes('tabel pengaturan') || text.includes('isi tabel setting'))) ||
                (tbl === 'system_audit_logs' && (text.includes('tabel audit') || text.includes('tabel log'))) ||
                (tbl === 'otp_requests' && text.includes('tabel otp')) ||
                (tbl === 'whatsapp_notification_logs' && text.includes('tabel wa')) ||
                (tbl === 'ai_conversations' && text.includes('tabel chat'))
            );
            if (aliasMatch) {
                return {
                    toolName: 'readTableData',
                    args: { tableName: tbl, limit: 15 }
                };
            }
        }

        // 11. Ulasan / Review / Rating Produk
        if (text.includes('ulasan') || text.includes('review') || text.includes('rating') || text.includes('kepuasan') || text.includes('testimoni')) {
            let prodName = null;
            if (text.includes('gelang')) prodName = 'gelang';
            else if (text.includes('kalung')) prodName = 'kalung';
            else if (text.includes('cincin')) prodName = 'cincin';
            else if (text.includes('anting')) prodName = 'anting';

            if (prodName) {
                return {
                    toolName: 'getProductReviews',
                    args: { productName: prodName, limit: 5 }
                };
            }
            return {
                toolName: 'getReviewSummary',
                args: {}
            };
        }

        // 12. Kategori Statistik (jumlah produk per kategori)
        if (text.includes('kategori') && (text.includes('stok') || text.includes('jumlah') || text.includes('statistik') || text.includes('detail'))) {
            return {
                toolName: 'getCategoryStats',
                args: {}
            };
        }

        // 13. Daftar / Pencarian Pengguna / User (Admin / Owner)
        const isUserIntent = (role === 'Admin' || role === 'Owner') && (
            text.includes('user') ||
            text.includes('pengguna') ||
            text.includes('username') ||
            text.includes('operator') ||
            text.includes('admin') ||
            text.includes('owner') ||
            text.includes('customer') ||
            text.includes('pelanggan')
        );

        if (isUserIntent && !text.includes('total') && !text.includes('berapa')) {
            let roleFilter = null;
            if (/\b(?:operator|operasional)\b/i.test(text)) roleFilter = 'Operator';
            else if (/\b(?:admin|administrator)\b/i.test(text)) roleFilter = 'Admin';
            else if (/\b(?:owner|pemilik)\b/i.test(text)) roleFilter = 'Owner';
            else if (/\b(?:customer|pelanggan|pembeli|konsumen)\b/i.test(text)) roleFilter = 'Customer';

            const userStopWords = new Set([
                'semua', 'seluruh', 'total', 'jumlah', 'terdaftar', 'toko', 'nnaco', 'sistem',
                'database', 'yang', 'ada', 'aktif', 'ini', 'apa', 'saja', 'daftar', 'data',
                'baru', 'lama', 'detail', 'lengkap', 'akun', 'user', 'users', 'pengguna',
                'nama', 'username', 'email', 'profil', 'info', 'informasi', 'tabel', 'menu',
                'operator', 'admin', 'owner', 'customer', 'pelanggan',
                'dengan', 'role', 'peran', 'sebagai', 'level', 'tipe', 'hak', 'akses', 'status',
                'pada', 'di', 'ke', 'dari', 'tentang', 'dan', 'atau', 'untuk', 'itu', 'tersebut',
                'nya', 'ya', 'dong', 'sih', 'lah', 'kah', 'orang', 'member', 'anggota',
                'siapa', 'siapakah', 'mana', 'apakah', 'sebutkan', 'tampilkan', 'tunjukkan',
                'lihat', 'cari', 'cek', 'temukan', 'berikan', 'tolong', 'bantu', 'list',
                'semuamnama', 'semuanama', 'namanama'
            ]);

            let searchUser = null;
            // 1. Explicit search key with username/email/bernama
            const explicitNameMatch = text.match(/(?:\busername\b|\bemail\b|\bbernama\b|\bnama\s+lengkap\b|\bnama\b)\s*[:=]?\s*([a-zA-Z0-9_\-\.@]+)/i);
            if (explicitNameMatch && !userStopWords.has(explicitNameMatch[1].toLowerCase())) {
                searchUser = explicitNameMatch[1];
            } else {
                // 2. "cari user X" or "cek akun X"
                const searchMatch = text.match(/(?:cari|cek|temukan|lihat\s+profil|info\s+user|info\s+pengguna)\s+(?:akun|user|pengguna)?\s*([a-zA-Z0-9_\-\.@]+)/i);
                if (searchMatch && !userStopWords.has(searchMatch[1].toLowerCase())) {
                    searchUser = searchMatch[1];
                }
            }

            const isExplicitUserListOrSearch = (
                searchUser !== null ||
                roleFilter !== null ||
                text.includes('user') ||
                text.includes('pengguna') ||
                text.includes('daftar') ||
                text.includes('semua') ||
                text.includes('siapa')
            );

            if (isExplicitUserListOrSearch) {
                const args = { limit: searchUser ? 10 : 15 };
                if (roleFilter) args.role = roleFilter;
                if (searchUser) args.search = searchUser;

                return {
                    toolName: 'getAllUsers',
                    args
                };
            }
        }

        // 14. Semua Pesanan Toko (Admin / Operator / Owner)
        if ((role === 'Admin' || role === 'Operator' || role === 'Owner') && (text.includes('semua pesanan') || text.includes('daftar pesanan toko') || text.includes('seluruh pesanan') || text.includes('daftar order'))) {
            return {
                toolName: 'getAllOrders',
                args: { limit: 15 }
            };
        }

        // 15. Semua Transaksi Pembayaran (Admin / Owner)
        if ((role === 'Admin' || role === 'Owner') && (text.includes('daftar pembayaran') || text.includes('riwayat transaksi pembayaran') || text.includes('data pembayaran'))) {
            return {
                toolName: 'getAllPayments',
                args: { limit: 15 }
            };
        }

        return null;
    }
}

module.exports = new AiIntentService();
