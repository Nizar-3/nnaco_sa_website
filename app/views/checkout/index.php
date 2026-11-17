<div class="container py-4 mb-5">
    <div class="mb-3">
        <a href="<?= BASEURL; ?>/cart" class="btn btn-sm btn-white-corp mb-0">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Keranjang
        </a>
    </div>
    
    <div class="card shadow-xs border rounded-4 mb-4 bg-white p-4">
        <div class="d-flex align-items-center">
            <div class="icon icon-shape bg-slate-900 text-white rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-shield-check fs-5"></i>
            </div>
            <div>
                <h4 class="font-weight-bold text-slate-900 mb-0">Proses Pembayaran (Checkout)</h4>
                <small class="text-muted text-xs">Pilih metode pembayaran dan konfirmasi data pesanan Anda</small>
            </div>
        </div>
    </div>

    <?php Flasher::flash(); ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <!-- Customer Info Card -->
            <div class="card shadow-xs border rounded-4 bg-white p-4 mb-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon icon-shape bg-gray-100 text-dark rounded-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-person text-xs"></i>
                    </div>
                    <h6 class="font-weight-bold text-slate-900 mb-0">1. Data Pelanggan &amp; Pengiriman</h6>
                </div>
                
                <div class="alert alert-light border text-xs text-muted rounded-3 mb-3 p-3">
                    <i class="bi bi-info-circle text-primary me-1"></i> Pengiriman menggunakan ekspedisi standar resmi dengan asuransi keamanan penuh.
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-xs font-weight-bold text-slate-700">Nama Penerima</label>
                    <input type="text" class="form-control corp-search-input bg-gray-100" value="<?= htmlspecialchars($_SESSION['user_name'] ?? 'Pelanggan'); ?>" readonly>
                </div>
            </div>
            
            <!-- Payment Methods Form -->
            <form action="<?= BASEURL; ?>/checkout/process" method="POST" id="checkout-form">
                <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                
                <div class="card shadow-xs border rounded-4 bg-white p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon icon-shape bg-primary text-white rounded-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-credit-card text-xs"></i>
                        </div>
                        <h6 class="font-weight-bold text-slate-900 mb-0">2. Pilih Metode Pembayaran</h6>
                    </div>
                    
                    <?php foreach ($data['payment_methods'] as $category => $methods): ?>
                        <div class="mb-4">
                            <span class="text-xxs text-uppercase font-weight-bold text-primary d-block mb-2" style="letter-spacing: 0.8px;"><?= htmlspecialchars($category); ?></span>
                            <div class="row g-2">
                                <?php foreach ($methods as $value => $label): ?>
                                    <div class="col-md-6">
                                        <label class="border rounded-3 p-3 d-flex align-items-center w-100 bg-white" style="cursor: pointer; transition: all 0.2s ease;">
                                            <input class="form-check-input me-3" type="radio" name="payment_method" value="<?= htmlspecialchars($value); ?>" required>
                                            <span class="font-weight-semibold text-slate-800 text-sm"><?= htmlspecialchars($label); ?></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </form>
        </div>

        <!-- Order Summary Card -->
        <div class="col-lg-5">
            <div class="card shadow-xs border rounded-4 bg-white p-4 sticky-top" style="top: 90px;">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon icon-shape bg-slate-900 text-white rounded-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-receipt text-xs"></i>
                    </div>
                    <h6 class="font-weight-bold text-slate-900 mb-0">Ringkasan Pesanan</h6>
                </div>
                
                <div class="py-2 mb-3 border-bottom">
                    <?php foreach ($data['cart_items'] as $item) : ?>
                        <div class="d-flex justify-content-between align-items-center mb-2 text-sm">
                            <div>
                                <span class="font-weight-bold text-slate-800 d-block text-truncate" style="max-width: 220px;"><?= htmlspecialchars($item['product']['name']); ?></span>
                                <small class="text-muted text-xs"><?= $item['quantity']; ?> x Rp <?= number_format($item['product']['price'], 0, ',', '.'); ?></small>
                            </div>
                            <span class="font-weight-bold text-slate-900">Rp <?= number_format($item['subtotal'], 0, ',', '.'); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="d-flex justify-content-between text-sm mb-2 text-muted">
                    <span>Subtotal Produk</span>
                    <span class="font-weight-semibold text-dark">Rp <?= number_format($data['total'], 0, ',', '.'); ?></span>
                </div>
                <div class="d-flex justify-content-between text-sm mb-3 text-muted">
                    <span>Biaya Pengiriman</span>
                    <span class="badge badge-corp badge-corp-success">Gratis</span>
                </div>
                
                <hr class="my-3 opacity-25">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="font-weight-bold text-slate-800 text-sm">Total Bayar:</span>
                    <h5 class="font-weight-bold text-slate-900 mb-0">Rp <?= number_format($data['total'], 0, ',', '.'); ?></h5>
                </div>

                <button type="submit" form="checkout-form" class="btn btn-dark-corp w-100 py-3 mb-3 d-flex align-items-center justify-content-center shadow-xs">
                    <i class="bi bi-lock-fill me-2"></i> Konfirmasi &amp; Bayar Sekarang
                </button>
                
                <div class="text-center text-xs text-muted">
                    <i class="bi bi-shield-lock text-success me-1"></i> Transaksi Anda dijamin aman 100% dan terenkripsi.
                </div>
            </div>
        </div>
    </div>
</div>
