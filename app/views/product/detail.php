<div class="container py-4 mb-5">
    <div class="mb-3">
        <a href="<?= BASEURL; ?>/product" class="btn btn-sm btn-white-corp mb-0">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
        </a>
    </div>
    
    <div class="card shadow-xs border rounded-4 bg-white p-4 p-md-5">
        <div class="row align-items-center g-5">
            <!-- Image Area -->
            <div class="col-md-6">
                <div class="card border rounded-4 overflow-hidden position-relative bg-gray-100 text-center shadow-xs">
                    <?php if ($data['product']['stock'] <= 0) : ?>
                        <span class="badge badge-corp badge-corp-danger position-absolute top-0 start-0 m-3 fs-6">STOK HABIS</span>
                    <?php else: ?>
                        <span class="badge badge-corp badge-corp-success position-absolute top-0 start-0 m-3">PRODUK ORIGINAL</span>
                    <?php endif; ?>
                    
                    <?php $imgDetail = !empty($data['product']['image']) ? BASEURL . '/img/products/' . $data['product']['image'] : 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?q=80&w=600&auto=format&fit=crop'; ?>
                    <img src="<?= $imgDetail ?>" class="img-fluid w-100" alt="<?= htmlspecialchars($data['product']['name']); ?>" style="max-height: 480px; object-fit: cover;">
                </div>
            </div>
            
            <!-- Product Info Area -->
            <div class="col-md-6">
                <span class="badge badge-corp badge-corp-dark mb-2 text-uppercase font-weight-bold" style="letter-spacing: 1px;">
                    <?= htmlspecialchars($data['product']['category_name'] ?? 'Aksesoris'); ?>
                </span>
                
                <h2 class="font-weight-bold text-slate-900 mb-2" style="font-family: 'Noto Sans', sans-serif;">
                    <?= htmlspecialchars($data['product']['name']); ?>
                </h2>
                
                <div class="d-flex align-items-center mb-4">
                    <h3 class="font-weight-bold text-slate-900 mb-0 me-3">Rp <?= number_format($data['product']['price'], 0, ',', '.'); ?></h3>
                    <span class="text-muted text-decoration-line-through text-sm">Rp <?= number_format($data['product']['price'] * 1.15, 0, ',', '.'); ?></span>
                    <span class="badge badge-corp badge-corp-warning ms-2">-15% OFF</span>
                </div>
                
                <div class="p-3 bg-gray-100 rounded-3 mb-4 text-sm text-slate-700" style="line-height: 1.7;">
                    <?= nl2br(htmlspecialchars($data['product']['description'])); ?>
                </div>
                
                <div class="d-flex justify-content-between align-items-center p-3 border rounded-3 mb-4 bg-white">
                    <span class="text-xs font-weight-bold text-muted"><i class="bi bi-box-seam me-1 text-primary"></i> Status Ketersediaan</span>
                    <?php if ($data['product']['stock'] > 0) : ?>
                        <span class="badge badge-corp badge-corp-success font-weight-bold">Tersedia: <?= $data['product']['stock']; ?> Unit</span>
                    <?php else : ?>
                        <span class="badge badge-corp badge-corp-danger font-weight-bold">Stok Kosong</span>
                    <?php endif; ?>
                </div>
                
                <?php if ($data['product']['stock'] > 0) : ?>
                    <form action="<?= BASEURL; ?>/cart/add" method="POST" class="d-flex align-items-center gap-3">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                        <input type="hidden" name="product_id" value="<?= $data['product']['id']; ?>">
                        
                        <div class="d-flex align-items-center gap-2">
                            <label class="text-xs font-weight-bold text-muted mb-0">Qty:</label>
                            <input type="number" name="quantity" class="form-control corp-search-input text-center font-weight-bold" value="1" min="1" max="<?= $data['product']['stock']; ?>" style="width: 80px;">
                        </div>
                        
                        <button type="submit" class="btn btn-dark-corp flex-grow-1 py-3 mb-0 d-flex align-items-center justify-content-center shadow-xs">
                            <i class="bi bi-cart-plus me-2 fs-6"></i> Tambahkan ke Keranjang
                        </button>
                    </form>
                <?php else : ?>
                    <button class="btn btn-white-corp text-muted w-100 py-3 mb-0" disabled>
                        Barang Sedang Habis
                    </button>
                <?php endif; ?>

                <div class="d-flex align-items-center gap-3 mt-4 pt-3 border-top text-xs text-muted">
                    <span><i class="bi bi-shield-check text-success me-1"></i> Garansi Asli</span>
                    <span>&bull;</span>
                    <span><i class="bi bi-truck text-info me-1"></i> Pengiriman Cepat</span>
                    <span>&bull;</span>
                    <span><i class="bi bi-whatsapp text-success me-1"></i> Layanan CS 24 Jam</span>
                </div>
            </div>
        </div>
    </div>
</div>
