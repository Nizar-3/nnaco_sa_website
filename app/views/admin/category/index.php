<!-- Corporate UI Dashboard: Kategori Produk -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-md-flex align-items-center justify-content-between">
            <div class="mb-3 mb-md-0">
                <h3 class="font-weight-bold text-dark mb-1">Kategori Produk</h3>
                <p class="text-secondary text-sm mb-0">Kelola klasifikasi dan pengelompokan koleksi aksesoris toko.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASEURL; ?>/admincategory/create" class="btn btn-sm btn-dark-corp mb-0 d-inline-flex align-items-center">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Kategori Baru
                </a>
                <a href="<?= BASEURL; ?>/admin" class="btn btn-sm btn-white-corp mb-0 d-inline-flex align-items-center">
                    <i class="bi bi-arrow-left me-1"></i> Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- AJAX Table Wrapper -->
<div class="ajax-table-wrapper" id="adminCategoryTableWrapper">

<!-- Search & Filter Card -->
<div class="card shadow-xs border mb-4">
    <div class="card-body p-3">
        <form action="<?= BASEURL; ?>/admincategory" method="GET" class="ajax-filter-form row g-2 align-items-center">
            <div class="col-md-6 col-lg-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="q" class="form-control border-start-0 ps-0 corp-search-input" placeholder="Cari nama atau deskripsi kategori..." value="<?= htmlspecialchars($data['keyword'] ?? ''); ?>" autocomplete="off">
                </div>
            </div>
            <div class="col-auto d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-dark-corp mb-0">
                    <i class="bi bi-search me-1"></i> Cari
                </button>
                <button type="button" class="btn btn-sm btn-white-corp mb-0 btn-reset-filter" data-ajax-reset>
                    <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-xs border">
    <div class="card-header border-bottom d-flex align-items-center justify-content-between">
        <div>
            <h6 class="font-weight-semibold text-dark mb-0">Daftar Kategori Aksesoris</h6>
            <p class="text-xs text-secondary mb-0 ajax-count-badge">Total <?= count($data['categories'] ?? []); ?> kategori aktif</p>
        </div>
    </div>
    <div class="card-body px-0 py-0 ajax-table-content">
        <div class="table-responsive">
            <table class="table table-corporate align-items-center mb-0">
                <thead>
                    <tr>
                        <th width="80" class="ps-4">ID</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th class="text-center pe-4" width="140">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['categories'])) : ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-tags fs-1 d-block mb-2 text-secondary"></i>
                                Tidak ada kategori yang sesuai dengan pencarian.
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($data['categories'] as $cat) : ?>
                            <tr>
                                <td class="ps-4 text-muted fw-bold">#<?= $cat['id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="icon icon-shape bg-gray-100 text-dark rounded-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="bi bi-tag-fill text-primary"></i>
                                        </div>
                                        <span class="fw-bold text-dark text-sm"><?= htmlspecialchars($cat['name']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm text-secondary"><?= htmlspecialchars($cat['description']) ?: '<span class="text-muted fst-italic">Tidak ada deskripsi</span>'; ?></span>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="btn-group" role="group">
                                        <a href="<?= BASEURL; ?>/admincategory/edit/<?= $cat['id']; ?>" class="btn btn-sm btn-white-corp py-1 px-2 mb-0" title="Edit Kategori">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </a>
                                        <a href="<?= BASEURL; ?>/admincategory/delete/<?= $cat['id']; ?>" class="btn btn-sm btn-white-corp py-1 px-2 mb-0" title="Hapus Kategori" onclick="return confirm('Peringatan: Menghapus kategori dapat memengaruhi produk terkait. Lanjutkan?');">
                                            <i class="bi bi-trash-fill text-danger"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>
<!-- /AJAX Table Wrapper -->
