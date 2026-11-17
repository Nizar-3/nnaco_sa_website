<!-- Corporate UI Dashboard: Katalog Produk -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-md-flex align-items-center justify-content-between">
            <div class="mb-3 mb-md-0">
                <h3 class="font-weight-bold text-dark mb-1">Katalog Produk</h3>
                <p class="text-secondary text-sm mb-0">Kelola master data produk aksesoris, harga, gambar, stok, dan ketersediaan.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASEURL; ?>/adminproduct/create" class="btn btn-sm btn-dark-corp mb-0 d-inline-flex align-items-center">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Produk Baru
                </a>
                <a href="<?= BASEURL; ?>/admin" class="btn btn-sm btn-white-corp mb-0 d-inline-flex align-items-center">
                    <i class="bi bi-arrow-left me-1"></i> Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- AJAX Table Wrapper -->
<div class="ajax-table-wrapper" id="adminProductTableWrapper">

<!-- Search & Filter Card -->
<div class="card shadow-xs border mb-4">
    <div class="card-body p-3">
        <form action="<?= BASEURL; ?>/adminproduct" method="GET" class="ajax-filter-form row g-2 align-items-center">
            <div class="col-md-5 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="q" class="form-control border-start-0 ps-0 corp-search-input" placeholder="Cari nama produk aksesoris..." value="<?= htmlspecialchars($data['keyword'] ?? ''); ?>" autocomplete="off">
                </div>
            </div>
            <div class="col-md-3 col-lg-3">
                <select name="category" class="form-select corp-search-input">
                    <option value="">-- Semua Kategori --</option>
                    <?php if (!empty($data['categories'])) : ?>
                        <?php foreach ($data['categories'] as $cat) : ?>
                            <option value="<?= $cat['id']; ?>" <?= ($data['categoryFilter'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-2 col-lg-2">
                <select name="status" class="form-select corp-search-input">
                    <option value="">-- Semua Status --</option>
                    <option value="Available" <?= ($data['statusFilter'] === 'Available') ? 'selected' : ''; ?>>Tersedia</option>
                    <option value="Out of Stock" <?= ($data['statusFilter'] === 'Out of Stock') ? 'selected' : ''; ?>>Kosong / Habis</option>
                </select>
            </div>
            <div class="col-auto d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-dark-corp mb-0">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <button type="button" class="btn btn-sm btn-white-corp mb-0 btn-reset-filter" data-ajax-reset>
                    <i class="bi bi-arrow-clockwise me-1"></i> Reset
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Products Table Card -->
<div class="card shadow-xs border">
    <div class="card-header border-bottom d-flex align-items-center justify-content-between">
        <div>
            <h6 class="font-weight-semibold text-dark mb-0">Daftar Produk</h6>
            <p class="text-xs text-secondary mb-0 ajax-count-badge">Total <?= count($data['products'] ?? []); ?> produk ditampilkan (dari <?= $data['total_products'] ?? count($data['products'] ?? []); ?> total)</p>
        </div>
        <a href="<?= BASEURL; ?>/product" target="_blank" class="btn btn-sm btn-white-corp mb-0">
            <i class="bi bi-eye me-1"></i> Preview di Toko
        </a>
    </div>
    <div class="card-body px-0 py-0 ajax-table-content">
        <div class="table-responsive">
            <table class="table table-corporate align-items-center mb-0">
                <thead>
                    <tr>
                        <th width="60" class="ps-4">ID</th>
                        <th width="80">Gambar</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th class="text-center pe-4" width="140">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['products'])) : ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-box-seam fs-1 d-block mb-2 text-secondary"></i>
                                Tidak ada data produk yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($data['products'] as $prod) : ?>
                            <tr>
                                <td class="ps-4 text-muted fw-bold">#<?= $prod['id']; ?></td>
                                <td>
                                    <?php if (!empty($prod['image'])) : ?>
                                        <img src="<?= BASEURL; ?>/img/products/<?= htmlspecialchars($prod['image']); ?>" alt="<?= htmlspecialchars($prod['name']); ?>" class="avatar avatar-sm rounded-3 border object-fit-cover shadow-xs" style="width: 44px; height: 44px;">
                                    <?php else : ?>
                                        <div class="avatar avatar-sm rounded-3 bg-gray-100 border text-muted d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                            <i class="bi bi-image fs-6"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark text-sm d-block"><?= htmlspecialchars($prod['name']); ?></span>
                                    <span class="text-xxs text-muted">ID: #<?= $prod['id']; ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-corp badge-corp-dark">
                                        <?= htmlspecialchars($prod['category_name'] ?: 'Umum'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark text-sm">Rp <?= number_format($prod['price'], 0, ',', '.'); ?></span>
                                </td>
                                <td>
                                    <?php if ($prod['stock'] > 5) : ?>
                                        <span class="badge badge-corp badge-corp-success"><?= $prod['stock']; ?> unit</span>
                                    <?php elseif ($prod['stock'] > 0) : ?>
                                        <span class="badge badge-corp badge-corp-danger"><?= $prod['stock']; ?> unit (Menipis)</span>
                                    <?php else : ?>
                                        <span class="badge badge-corp badge-corp-danger">Habis</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($prod['status'] === 'Available') : ?>
                                        <span class="badge badge-corp badge-corp-success"><i class="bi bi-check-circle-fill"></i> Tersedia</span>
                                    <?php else : ?>
                                        <span class="badge badge-corp badge-corp-danger"><i class="bi bi-x-circle-fill"></i> Kosong</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="btn-group" role="group">
                                        <a href="<?= BASEURL; ?>/adminproduct/edit/<?= $prod['id']; ?>" class="btn btn-sm btn-white-corp py-1 px-2 mb-0" title="Edit Produk">
                                            <i class="bi bi-pencil-square text-primary"></i>
                                        </a>
                                        <a href="<?= BASEURL; ?>/adminproduct/delete/<?= $prod['id']; ?>" class="btn btn-sm btn-white-corp py-1 px-2 mb-0" title="Hapus Produk" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
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

<!-- AJAX Pagination Container -->
<div class="ajax-pagination-container <?= ($data['total_pages'] <= 1) ? 'd-none' : ''; ?>">
    <?php if ($data['total_pages'] > 1) : ?>
        <div class="d-flex justify-content-center mt-4">
            <ul class="pagination gap-1">
                <?php for ($i = 1; $i <= $data['total_pages']; $i++) : ?>
                    <li class="page-item <?= ($i == $data['current_page']) ? 'active' : ''; ?>">
                        <a class="btn btn-sm <?= ($i == $data['current_page']) ? 'btn-dark-corp' : 'btn-white-corp'; ?> mb-0 px-3" href="<?= BASEURL; ?>/adminproduct?page=<?= $i; ?><?= !empty($data['keyword']) ? '&q=' . urlencode($data['keyword']) : ''; ?><?= !empty($data['categoryFilter']) ? '&category=' . urlencode($data['categoryFilter']) : ''; ?><?= !empty($data['statusFilter']) ? '&status=' . urlencode($data['statusFilter']) : ''; ?>"><?= $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

</div>
<!-- /AJAX Table Wrapper -->
