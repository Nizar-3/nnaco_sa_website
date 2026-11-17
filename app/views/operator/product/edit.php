<!-- Corporate UI Dashboard: Operator Update Stock -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-md-flex align-items-center justify-content-between">
            <div class="mb-3 mb-md-0">
                <h3 class="font-weight-bold text-dark mb-1">Perbarui Stok Produk</h3>
                <p class="text-secondary text-sm mb-0">Perbarui kuantitas inventaris fisik dan status etalase produk.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASEURL; ?>/operatorproduct" class="btn btn-sm btn-white-corp mb-0 d-inline-flex align-items-center">
                    <i class="bi bi-arrow-left me-1"></i> Batal & Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Product Preview Card -->
    <div class="col-lg-5">
        <div class="card shadow-xs border h-100">
            <div class="card-header border-bottom">
                <h6 class="font-weight-semibold text-dark mb-0">Preview Produk</h6>
                <p class="text-xs text-secondary mb-0">Informasi identitas barang</p>
            </div>
            <div class="card-body p-4 text-center">
                <?php if ($data['product']['image']) : ?>
                    <img src="<?= BASEURL; ?>/img/products/<?= htmlspecialchars($data['product']['image']); ?>" alt="Gambar Produk" class="avatar avatar-xxl rounded-3 border shadow-xs object-fit-cover mb-3" style="width: 150px; height: 150px;">
                <?php else : ?>
                    <div class="bg-gray-100 border rounded-3 d-flex align-items-center justify-content-center mb-3 mx-auto" style="width: 150px; height: 150px;">
                        <span class="text-xs text-muted">Belum ada foto</span>
                    </div>
                <?php endif; ?>

                <h5 class="font-weight-bold text-dark mb-1"><?= htmlspecialchars($data['product']['name']); ?></h5>
                <h6 class="text-dark font-weight-bold mb-3">Rp <?= number_format($data['product']['price'], 0, ',', '.'); ?></h6>
                <div class="p-3 bg-gray-100 rounded-3 text-start border">
                    <span class="text-xxs text-secondary text-uppercase font-weight-bold d-block mb-1">Deskripsi</span>
                    <p class="text-xs text-muted mb-0"><?= nl2br(htmlspecialchars($data['product']['description'])); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Stock Form Card -->
    <div class="col-lg-7">
        <div class="card shadow-xs border h-100">
            <div class="card-header border-bottom">
                <h6 class="font-weight-semibold text-dark mb-0">Update Inventaris Fisik</h6>
                <p class="text-xs text-secondary mb-0">Kewenangan Operator khusus perbaruan kuantitas stok</p>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info border-0 p-3 rounded-3 mb-4 text-xs text-secondary">
                    <i class="bi bi-info-circle me-1 text-primary"></i>
                    Sebagai Operator, Anda hanya diizinkan memperbarui jumlah <b>Stok</b> fisik gudang dan <b>Status</b> ketersediaan produk di toko.
                </div>

                <form action="<?= BASEURL; ?>/operatorproduct/update/<?= $data['product']['id']; ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                    
                    <div class="mb-3">
                        <label for="stock" class="form-label text-xs font-weight-semibold text-dark">Jumlah Stok Fisik <span class="text-danger">*</span></label>
                        <input type="number" class="form-control corp-search-input" id="stock" name="stock" min="0" value="<?= $data['product']['stock']; ?>" required>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="form-label text-xs font-weight-semibold text-dark">Status Etalase Toko <span class="text-danger">*</span></label>
                        <select class="form-select corp-search-input" id="status" name="status" required>
                            <option value="Available" <?= ($data['product']['status'] == 'Available') ? 'selected' : ''; ?>>Tersedia (Tampil di Toko)</option>
                            <option value="Unavailable" <?= ($data['product']['status'] == 'Unavailable') ? 'selected' : ''; ?>>Tidak Tersedia (Disembunyikan)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-dark-corp w-100 py-2 font-weight-bold" onclick="return confirm('Simpan perubahan jumlah stok ini?');">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan Stok
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
