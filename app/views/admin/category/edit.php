<!-- Corporate UI Dashboard: Edit Kategori -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-md-flex align-items-center justify-content-between">
            <div class="mb-3 mb-md-0">
                <h3 class="font-weight-bold text-dark mb-1">Edit Kategori #<?= $data['category']['id']; ?></h3>
                <p class="text-secondary text-sm mb-0">Perbarui rincian nama atau deskripsi kategori aksesoris.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASEURL; ?>/admincategory" class="btn btn-sm btn-white-corp mb-0 d-inline-flex align-items-center">
                    <i class="bi bi-arrow-left me-1"></i> Batal & Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-xs border">
            <div class="card-header border-bottom">
                <h6 class="font-weight-semibold text-dark mb-0">Formulir Edit Kategori</h6>
                <p class="text-xs text-secondary mb-0">Rincian data kategori produk</p>
            </div>
            <div class="card-body p-4">
                <form action="<?= BASEURL; ?>/admincategory/update/<?= $data['category']['id']; ?>" method="post">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                    
                    <div class="mb-3">
                        <label class="form-label text-xs font-weight-semibold text-dark">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control corp-search-input" name="name" value="<?= htmlspecialchars($data['category']['name']); ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-xs font-weight-semibold text-dark">Deskripsi Kategori</label>
                        <textarea class="form-control corp-search-input" name="description" rows="4"><?= htmlspecialchars($data['category']['description']); ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark-corp py-2 px-4 font-weight-bold">
                            <i class="bi bi-save me-1"></i> Simpan Perubahan
                        </button>
                        <a href="<?= BASEURL; ?>/admincategory" class="btn btn-white-corp py-2 px-3">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
