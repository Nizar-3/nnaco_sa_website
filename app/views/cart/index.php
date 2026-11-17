<div class="container py-4 mb-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="font-weight-bold text-slate-900 mb-0">Keranjang Belanja</h4>
            <small class="text-muted text-xs">Periksa daftar produk pilihan Anda sebelum melanjutkan ke pembayaran</small>
        </div>
        <span class="badge badge-corp badge-corp-dark">
            <?= count($data['cart_items']) ?> Item
        </span>
    </div>
    
    <?php Flasher::flash(); ?>

    <?php if (empty($data['cart_items'])) : ?>
        <div class="card shadow-xs border rounded-4 text-center py-5 bg-white">
            <div class="card-body py-5">
                <div class="icon icon-shape bg-gray-100 text-muted rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px;">
                    <i class="bi bi-cart-x fs-2"></i>
                </div>
                <h5 class="font-weight-bold text-slate-900 mb-1">Keranjang Belanja Kosong</h5>
                <p class="text-muted text-xs mb-4">Anda belum menambahkan aksesoris apapun ke dalam keranjang.</p>
                <a href="<?= BASEURL; ?>/product" class="btn btn-sm btn-dark-corp px-4 py-2">
                    <i class="bi bi-bag-check me-1"></i> Mulai Belanja Sekarang
                </a>
            </div>
        </div>
    <?php else : ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-xs border rounded-4 overflow-hidden bg-white mb-4">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-corporate align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Item Produk</th>
                                        <th>Harga</th>
                                        <th width="140" class="text-center">Kuantitas</th>
                                        <th class="text-end">Subtotal</th>
                                        <th class="pe-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data['cart_items'] as $item) : ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <?php $imgCart = !empty($item['product']['image']) ? BASEURL . '/img/products/' . $item['product']['image'] : 'https://via.placeholder.com/80?text=N'; ?>
                                                    <img src="<?= $imgCart ?>" class="rounded-3 me-3 shadow-xs" style="width: 54px; height: 54px; object-fit: cover;" alt="Product">
                                                    <div>
                                                        <h6 class="mb-1 font-weight-bold text-slate-900 text-sm"><?= htmlspecialchars($item['product']['name']); ?></h6>
                                                        <span class="badge badge-corp badge-corp-success" style="font-size: 0.65rem;">Stok: <?= $item['product']['stock']; ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-sm font-weight-semibold text-slate-700">Rp <?= number_format($item['product']['price'], 0, ',', '.'); ?></td>
                                            <td>
                                                <form action="<?= BASEURL; ?>/cart/update" method="post" class="m-0">
                                                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                                                    <input type="hidden" name="product_id" value="<?= $item['product']['id']; ?>">
                                                    <input type="number" name="quantity" value="<?= $item['quantity']; ?>" class="form-control corp-search-input form-control-sm text-center font-weight-bold mx-auto" style="max-width: 80px;" min="1" max="<?= $item['product']['stock']; ?>" onchange="this.form.submit()">
                                                </form>
                                            </td>
                                            <td class="text-end font-weight-bold text-sm text-slate-900">Rp <?= number_format($item['subtotal'], 0, ',', '.'); ?></td>
                                            <td class="pe-4 text-center">
                                                <a href="<?= BASEURL; ?>/cart/remove/<?= $item['product']['id']; ?>" class="btn btn-sm btn-white-corp text-danger p-2 mb-0" title="Hapus" onclick="return confirm('Hapus item ini dari keranjang?');">
                                                    <i class="bi bi-trash-fill"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-xs border rounded-4 bg-white sticky-top p-4" style="top: 90px;">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon icon-shape bg-slate-900 text-white rounded-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-receipt text-xs"></i>
                        </div>
                        <h6 class="font-weight-bold text-slate-900 mb-0">Ringkasan Belanja</h6>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2 text-sm text-muted">
                        <span>Total Kuantitas:</span>
                        <span class="font-weight-semibold text-dark"><?= count($data['cart_items']) ?> Item</span>
                    </div>
                    
                    <hr class="my-3 opacity-25">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="font-weight-bold text-slate-800 text-sm">Total Harga:</span>
                        <h5 class="font-weight-bold text-slate-900 mb-0">Rp <?= number_format($data['total'], 0, ',', '.'); ?></h5>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="<?= BASEURL; ?>/checkout" class="btn btn-dark-corp py-2 mb-0 d-flex align-items-center justify-content-center">
                            <i class="bi bi-credit-card me-1"></i> Lanjut ke Pembayaran
                        </a>
                        <a href="<?= BASEURL; ?>/product" class="btn btn-white-corp py-2 mb-0 text-center">
                            <i class="bi bi-arrow-left me-1"></i> Lanjutkan Belanja
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
