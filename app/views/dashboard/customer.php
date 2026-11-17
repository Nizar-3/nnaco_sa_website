<div class="container-fluid py-3 px-4">
    <!-- Welcome Corporate Banner -->
    <div class="card shadow-xs border rounded-4 mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #774dd3 100%);">
        <div class="card-body p-4 text-white">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-10 py-1 px-3 text-xs font-weight-bold rounded-pill mb-2">
                        <i class="bi bi-star-fill text-warning me-1"></i> Area Pelanggan
                    </span>
                    <h3 class="text-white font-weight-bold mb-1">Selamat Datang, <?= htmlspecialchars($_SESSION['user_name']); ?>!</h3>
                    <p class="text-white-50 text-sm mb-0">Jelajahi koleksi aksesoris premium terbaru, pantau status pesanan, dan nikmati pengalaman berbelanja terbaik.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="<?= BASEURL; ?>/product" class="btn btn-sm btn-white-corp mb-0 me-2">
                        <i class="bi bi-bag-check me-1 text-primary"></i> Mulai Belanja
                    </a>
                    <a href="<?= BASEURL; ?>/profile" class="btn btn-sm btn-dark-corp mb-0">
                        <i class="bi bi-person-gear me-1"></i> Profil
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Metric Cards (Corporate UI Dashboard Laravel Style) -->
    <div class="row g-3 mb-4">
        <!-- Katalog Produk -->
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-corporate">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-title">Koleksi Aksesoris</p>
                        <h4 class="stat-value">Katalog</h4>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge badge-corp badge-corp-success">Tersedia</span>
                            <span class="text-xs text-muted">Model terbaru</span>
                        </div>
                    </div>
                    <div class="stat-icon-wrapper bg-slate-900 text-white shadow-sm">
                        <i class="bi bi-grid fs-5"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top">
                    <a href="<?= BASEURL; ?>/product" class="text-xs font-weight-bold text-slate-700 text-decoration-none d-flex align-items-center justify-content-between">
                        <span>Lihat Semua Produk</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Keranjang Belanja -->
        <div class="col-xl-3 col-sm-6">
            <?php $cartItemsCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>
            <div class="stat-card-corporate">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-title">Keranjang Belanja</p>
                        <h4 class="stat-value"><?= $cartItemsCount; ?> <span class="text-xs font-weight-normal text-muted">Item</span></h4>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge badge-corp <?= $cartItemsCount > 0 ? 'badge-corp-info' : 'badge-corp-dark'; ?>">
                                <?= $cartItemsCount > 0 ? 'Siap Checkout' : 'Kosong'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="stat-icon-wrapper bg-primary text-white shadow-sm">
                        <i class="bi bi-cart3 fs-5"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top">
                    <a href="<?= BASEURL; ?>/cart" class="text-xs font-weight-bold text-slate-700 text-decoration-none d-flex align-items-center justify-content-between">
                        <span>Buka Keranjang</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Riwayat Pesanan -->
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-corporate">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-title">Riwayat Pesanan</p>
                        <h4 class="stat-value">Pesanan</h4>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge badge-corp badge-corp-warning">Realtime</span>
                            <span class="text-xs text-muted">Lacak status paket</span>
                        </div>
                    </div>
                    <div class="stat-icon-wrapper bg-info text-white shadow-sm">
                        <i class="bi bi-clock-history fs-5"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top">
                    <a href="<?= BASEURL; ?>/customer/history" class="text-xs font-weight-bold text-slate-700 text-decoration-none d-flex align-items-center justify-content-between">
                        <span>Cek Riwayat Belanja</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Profil Akun -->
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card-corporate">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stat-title">Akun & Profil</p>
                        <h4 class="stat-value">Profil</h4>
                        <div class="d-flex align-items-center gap-1">
                            <span class="badge badge-corp badge-corp-dark">Akun Saya</span>
                            <span class="text-xs text-muted">Pengaturan data</span>
                        </div>
                    </div>
                    <div class="stat-icon-wrapper bg-slate-900 text-white shadow-sm">
                        <i class="bi bi-person-gear fs-5"></i>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top">
                    <a href="<?= BASEURL; ?>/profile" class="text-xs font-weight-bold text-slate-700 text-decoration-none d-flex align-items-center justify-content-between">
                        <span>Kelola Profil</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Features & Actions -->
    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card shadow-xs border rounded-4 bg-white p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                        <div class="icon icon-shape bg-slate-900 text-white rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="bi bi-lightning-charge fs-5"></i>
                        </div>
                        <div>
                            <h6 class="font-weight-bold text-slate-900 mb-0">Aktivitas &amp; Fitur Cepat</h6>
                            <small class="text-muted text-xs">Pintasan praktis untuk kebutuhan belanja dan akun Anda</small>
                        </div>
                    </div>
                    <a href="<?= BASEURL; ?>/product" class="btn btn-sm btn-white-corp mb-0">Belanja Sekarang</a>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-gray-100 h-100">
                            <h6 class="font-weight-bold text-slate-900 text-sm mb-1"><i class="bi bi-truck text-primary me-2"></i>Pengiriman Cepat &amp; Aman</h6>
                            <p class="text-xs text-muted mb-0">Semua pesanan dikemas rapi dengan bubble wrap tebal dan asuransi pengiriman resmi.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 bg-gray-100 h-100">
                            <h6 class="font-weight-bold text-slate-900 text-sm mb-1"><i class="bi bi-whatsapp text-success me-2"></i>Notifikasi WhatsApp</h6>
                            <p class="text-xs text-muted mb-0">Dapatkan update status pesanan dan kode OTP masuk langsung ke nomor WhatsApp Anda.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-xs border rounded-4 bg-white p-4 text-center">
                <div class="icon icon-shape bg-slate-900 text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center shadow-xs" style="width: 54px; height: 54px;">
                    <i class="bi bi-gem fs-4"></i>
                </div>
                <h6 class="font-weight-bold text-slate-900 mb-1">Koleksi Terpopuler</h6>
                <p class="text-muted text-xs mb-3">Temukan perhiasan dan aksesoris rambut eksklusif paling diminati saat ini.</p>
                <a href="<?= BASEURL; ?>/product" class="btn btn-sm btn-dark-corp w-100 mb-0">
                    <i class="bi bi-grid me-1"></i> Jelajahi Katalog
                </a>
            </div>
        </div>
    </div>
</div>
