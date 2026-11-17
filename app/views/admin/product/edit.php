<!-- Corporate UI Dashboard: Edit Produk -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-md-flex align-items-center justify-content-between">
            <div class="mb-3 mb-md-0">
                <h3 class="font-weight-bold text-dark mb-1">Edit Produk #<?= $data['product']['id']; ?></h3>
                <p class="text-secondary text-sm mb-0">Perbarui spesifikasi harga, stok, kategori, atau media produk aksesoris.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASEURL; ?>/adminproduct" class="btn btn-sm btn-white-corp mb-0 d-inline-flex align-items-center">
                    <i class="bi bi-arrow-left me-1"></i> Batal & Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-xs border">
    <div class="card-body p-4 p-md-5">
        <form action="<?= BASEURL; ?>/adminproduct/update/<?= $data['product']['id']; ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
            
            <div class="row g-4">
                <!-- Left: Informasi Utama -->
                <div class="col-lg-8 pe-lg-4">
                    <h6 class="font-weight-semibold text-dark mb-3 pb-2 border-bottom">Informasi Dasar Produk</h6>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label text-xs font-weight-semibold text-dark">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" class="form-control corp-search-input" id="name" name="name" value="<?= htmlspecialchars($data['product']['name']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label text-xs font-weight-semibold text-dark">Deskripsi Produk</label>
                        <textarea class="form-control corp-search-input" id="description" name="description" rows="6"><?= htmlspecialchars($data['product']['description']); ?></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label text-xs font-weight-semibold text-dark">Harga Jual (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-dark font-weight-bold text-xs">Rp</span>
                                <input type="number" class="form-control border-start-0 ps-0 corp-search-input" id="price" name="price" min="0" value="<?= $data['product']['price']; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="stock" class="form-label text-xs font-weight-semibold text-dark">Jumlah Stok <span class="text-danger">*</span></label>
                            <input type="number" class="form-control corp-search-input" id="stock" name="stock" min="0" value="<?= $data['product']['stock']; ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Right: Kategori, Status & Gambar -->
                <div class="col-lg-4 border-start ps-lg-4">
                    <h6 class="font-weight-semibold text-dark mb-3 pb-2 border-bottom">Pengaturan & Media</h6>

                    <div class="mb-3">
                        <label for="category_id" class="form-label text-xs font-weight-semibold text-dark">Kategori Aksesoris</label>
                        <select class="form-select corp-search-input" id="category_id" name="category_id">
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($data['categories'] as $cat) : ?>
                                <option value="<?= $cat['id']; ?>" <?= ($data['product']['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label text-xs font-weight-semibold text-dark">Status Ketersediaan <span class="text-danger">*</span></label>
                        <select class="form-select corp-search-input" id="status" name="status" required>
                            <option value="Available" <?= ($data['product']['status'] == 'Available') ? 'selected' : ''; ?>>Tersedia (Aktif di Toko)</option>
                            <option value="Unavailable" <?= ($data['product']['status'] == 'Unavailable') ? 'selected' : ''; ?>>Tidak Tersedia (Disembunyikan)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-xs font-weight-semibold text-dark d-block">Foto Produk Saat Ini</label>
                        <?php if ($data['product']['image']) : ?>
                            <div class="mb-2">
                                <img src="<?= BASEURL; ?>/img/products/<?= htmlspecialchars($data['product']['image']); ?>" alt="Gambar Produk" class="avatar avatar-xxl rounded-3 border shadow-xs object-fit-cover" style="width: 120px; height: 120px;">
                            </div>
                        <?php else : ?>
                            <div class="bg-gray-100 border rounded-3 d-flex align-items-center justify-content-center mb-2" style="width: 120px; height: 120px;">
                                <span class="text-xs text-muted">Belum ada foto</span>
                            </div>
                        <?php endif; ?>

                        <label for="image" class="form-label text-xs font-weight-semibold text-dark mt-2">Ganti Foto Produk</label>
                        <input class="form-control corp-search-input" type="file" id="image" name="image" accept="image/jpeg, image/png, image/jpg, image/webp">
                        <div class="text-xxs text-muted mt-1">Format: JPG, PNG, WEBP. Maks: 2MB. Kosongkan jika tidak diubah.</div>
                    </div>

                    <div class="pt-3 border-top">
                        <button type="submit" class="btn btn-dark-corp w-100 py-2 font-weight-bold">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan Produk
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
