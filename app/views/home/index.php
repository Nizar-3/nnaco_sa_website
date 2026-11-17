<!-- Corporate UI Hero Section -->
<div class="py-5 mb-5 position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #774dd3 100%);">
    <div class="container py-4 position-relative z-index-1">
        <div class="row align-items-center g-5">
            <div class="col-lg-7 text-white">
                <div class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-10 border border-white border-opacity-15 rounded-pill px-3 py-1 mb-3">
                    <span class="badge badge-corp badge-corp-warning py-0 px-2" style="font-size: 0.65rem;">NEW</span>
                    <span class="text-xs text-white-50 font-weight-bold" style="letter-spacing: 1px;">CORPORATE COLLECTION 2026</span>
                </div>
                
                <h1 class="display-4 font-weight-bolder mb-3 text-white lh-sm" style="font-family: 'Noto Sans', sans-serif;">
                    Koleksi Aksesoris Mewah &amp; Elegan.
                </h1>
                
                <p class="text-white-50 mb-4 fs-6" style="max-width: 520px; line-height: 1.6;">
                    Temukan kalung, gelang, cincin, dan aksesoris eksklusif NNACO SA berkualitas tinggi dengan jaminan keaslian serta transaksi aman terverifikasi OTP.
                </p>
                
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="<?= BASEURL; ?>/product" class="btn btn-white-corp px-4 py-3 font-weight-bold d-inline-flex align-items-center shadow-sm">
                        <i class="bi bi-bag-check-fill me-2 text-primary"></i> Belanja Sekarang
                    </a>
                    <a href="<?= BASEURL; ?>/product?category=1" class="btn btn-dark-corp px-4 py-3 font-weight-bold d-inline-flex align-items-center" style="background: rgba(255, 255, 255, 0.15) !important; border-color: rgba(255, 255, 255, 0.2) !important;">
                        Lihat Koleksi Kalung <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>

                <!-- Features Mini Row -->
                <div class="row text-start g-3 pt-3 border-top border-white border-opacity-10">
                    <div class="col-sm-4 col-6">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-shape bg-white bg-opacity-10 text-white rounded-2 me-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                <i class="bi bi-shield-check text-success"></i>
                            </div>
                            <div class="lh-1">
                                <span class="d-block font-weight-bold text-xs text-white">100% Original</span>
                                <small class="text-white-50 text-xxs">Material Terpilih</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 col-6">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-shape bg-white bg-opacity-10 text-white rounded-2 me-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                <i class="bi bi-truck text-info"></i>
                            </div>
                            <div class="lh-1">
                                <span class="d-block font-weight-bold text-xs text-white">Pengiriman Cepat</span>
                                <small class="text-white-50 text-xxs">Seluruh Indonesia</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 col-12">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-shape bg-white bg-opacity-10 text-white rounded-2 me-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                <i class="bi bi-whatsapp text-success"></i>
                            </div>
                            <div class="lh-1">
                                <span class="d-block font-weight-bold text-xs text-white">Login OTP WA</span>
                                <small class="text-white-50 text-xxs">Aman &amp; Instan</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hero Image Banner Card -->
            <div class="col-lg-5 text-center position-relative">
                <div class="card shadow-lg border-0 rounded-4 overflow-hidden position-relative" style="background: rgba(255, 255, 255, 0.08); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.15) !important;">
                    <img src="https://images.unsplash.com/photo-1548036328-c9fa89d128fa?q=80&w=600&auto=format&fit=crop" class="img-fluid w-100" alt="Hero Products" style="max-height: 420px; object-fit: cover;">
                    <div class="position-absolute bottom-0 start-0 end-0 p-3 text-start" style="background: linear-gradient(to top, rgba(15, 23, 42, 0.95), transparent);">
                        <span class="badge badge-corp badge-corp-warning mb-1">PROMO SPESIAL</span>
                        <h6 class="text-white font-weight-bold mb-0">Diskon Aksesoris s.d. 20%</h6>
                        <small class="text-white-50 text-xs">Berlaku untuk seluruh koleksi kalung &amp; gelang pilihan.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Browse By Category (Corporate UI Style) -->
<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="text-xs text-uppercase font-weight-bold text-primary" style="letter-spacing: 1px;">KATEGORI PRODUK</span>
            <h4 class="font-weight-bold text-slate-900 mb-0">Jelajahi Berdasarkan Koleksi</h4>
        </div>
        <a href="<?= BASEURL; ?>/product" class="btn btn-sm btn-white-corp mb-0">
            Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="row g-3">
        <?php 
        $categoriesMock = [
            ['id' => 1, 'name' => 'Kalung (Necklaces)', 'icon' => 'bi-gem', 'color' => 'bg-primary'],
            ['id' => 2, 'name' => 'Gelang (Bracelets)', 'icon' => 'bi-circle', 'color' => 'bg-info'],
            ['id' => 3, 'name' => 'Anting (Earrings)', 'icon' => 'bi-stars', 'color' => 'bg-warning'],
            ['id' => 4, 'name' => 'Cincin (Rings)', 'icon' => 'bi-record-circle', 'color' => 'bg-success'],
            ['id' => 5, 'name' => 'Jepit Rambut (Clips)', 'icon' => 'bi-flower1', 'color' => 'bg-slate-900']
        ];
        foreach($categoriesMock as $cat): 
        ?>
        <div class="col-6 col-md-4 col-lg">
            <a href="<?= BASEURL; ?>/product?category=<?= $cat['id']; ?>" class="text-decoration-none">
                <div class="card shadow-xs border rounded-4 text-center p-3 h-100 transition-all bg-white" style="transition: transform 0.2s ease, box-shadow 0.2s ease;">
                    <div class="icon icon-shape <?= $cat['color']; ?> text-white rounded-3 mx-auto mb-2 d-flex align-items-center justify-content-center shadow-xs" style="width: 46px; height: 46px;">
                        <i class="bi <?= $cat['icon']; ?> fs-5"></i>
                    </div>
                    <span class="font-weight-bold text-slate-800 text-xs d-block"><?= htmlspecialchars($cat['name']); ?></span>
                    <small class="text-muted text-xxs">Lihat Koleksi</small>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Trending Products (Corporate UI Cards) -->
<div class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="text-xs text-uppercase font-weight-bold text-primary" style="letter-spacing: 1px;">TRENDING SEKARANG</span>
            <h4 class="font-weight-bold text-slate-900 mb-0">Aksesoris Terlaris Minggu Ini</h4>
        </div>
        <a href="<?= BASEURL; ?>/product" class="btn btn-sm btn-white-corp mb-0">
            Katalog Lengkap <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <div class="row g-4">
        <?php if (!empty($data['latest_products'])) : ?>
            <?php foreach ($data['latest_products'] as $product) : ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="card card-product border-0 h-100 rounded-4 bg-white p-3 shadow-xs position-relative d-flex flex-column">
                        <div class="position-relative bg-gray-100 rounded-3 overflow-hidden mb-3 text-center" style="height: 220px;">
                            <?php if ($product['stock'] <= 0) : ?>
                                <span class="badge badge-corp badge-corp-danger position-absolute top-0 end-0 m-2">STOK HABIS</span>
                            <?php else: ?>
                                <span class="badge badge-corp badge-corp-success position-absolute top-0 end-0 m-2">TERSEDIA</span>
                            <?php endif; ?>
                            
                            <?php $img = !empty($product['image']) ? BASEURL . '/img/products/' . $product['image'] : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=400&auto=format&fit=crop'; ?>
                            <img src="<?= $img ?>" class="img-fluid w-100 h-100" style="object-fit: cover;" alt="<?= htmlspecialchars($product['name']); ?>">
                        </div>
                        
                        <div class="d-flex flex-column flex-grow-1 text-start">
                            <span class="text-xxs text-uppercase font-weight-bold text-muted mb-1"><?= htmlspecialchars($product['category_name'] ?? 'Aksesoris'); ?></span>
                            <h6 class="font-weight-bold text-slate-900 text-truncate mb-1" title="<?= htmlspecialchars($product['name']); ?>">
                                <?= htmlspecialchars($product['name']); ?>
                            </h6>
                            
                            <div class="d-flex align-items-center mb-3">
                                <span class="font-weight-bold text-slate-900 fs-6 me-2">Rp <?= number_format($product['price'], 0, ',', '.'); ?></span>
                                <small class="text-muted text-decoration-line-through text-xs">Rp <?= number_format($product['price'] * 1.15, 0, ',', '.'); ?></small>
                            </div>
                            
                            <div class="mt-auto pt-2 border-top">
                                <?php if ($product['stock'] > 0) : ?>
                                    <a href="<?= BASEURL; ?>/product/detail/<?= $product['id']; ?>" class="btn btn-sm btn-dark-corp w-100 mb-0 d-flex align-items-center justify-content-center">
                                        <i class="bi bi-cart3 me-1"></i> Beli Sekarang
                                    </a>
                                <?php else : ?>
                                    <button class="btn btn-sm btn-white-corp w-100 mb-0 text-muted" disabled>Stok Habis</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="col-12">
                <div class="card shadow-xs border rounded-4 text-center py-5 bg-white">
                    <p class="text-muted text-sm mb-0">Belum ada produk untuk ditampilkan.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Corporate Trust Banner -->
<div class="container mb-5">
    <div class="card shadow-xs border rounded-4 bg-white p-4">
        <div class="row g-4 align-items-center">
            <div class="col-md-3 col-6 text-center text-md-start">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon icon-shape bg-slate-900 text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-shield-check fs-5"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold text-slate-900 text-sm mb-0">Garansi 100%</h6>
                        <small class="text-muted text-xs">Kualitas produk terjamin</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 text-center text-md-start">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon icon-shape bg-primary text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-truck fs-5"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold text-slate-900 text-sm mb-0">Pengiriman Aman</h6>
                        <small class="text-muted text-xs">Asuransi &amp; packaging rapi</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 text-center text-md-start">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon icon-shape bg-info text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-whatsapp fs-5"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold text-slate-900 text-sm mb-0">WhatsApp Login</h6>
                        <small class="text-muted text-xs">Verifikasi OTP Fonnte</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6 text-center text-md-start">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon icon-shape bg-success text-white rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-robot fs-5"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold text-slate-900 text-sm mb-0">AI Assistant 24/7</h6>
                        <small class="text-muted text-xs">Bantuan belanja cerdas</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
