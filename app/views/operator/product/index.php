<!-- Corporate UI Dashboard: Operator Product View -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-md-flex align-items-center justify-content-between">
            <div class="mb-3 mb-md-0">
                <h3 class="font-weight-bold text-dark mb-1">Manajemen Stok Gudang (Operator)</h3>
                <p class="text-secondary text-sm mb-0">Pantau ketersediaan fisik stok aksesoris dan perbarui kuantitas produk.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASEURL; ?>/operator" class="btn btn-sm btn-white-corp mb-0 d-inline-flex align-items-center">
                    <i class="bi bi-arrow-left me-1"></i> Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<div class="ajax-table-wrapper">
    <!-- Search & Filter Card -->
    <div class="card shadow-xs border mb-4">
        <div class="card-body p-3">
            <form action="<?= BASEURL; ?>/operatorproduct" method="GET" class="ajax-filter-form row g-2 align-items-center">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0 corp-search-input" placeholder="Cari nama produk aksesoris..." value="<?= htmlspecialchars($data['keyword'] ?? ''); ?>">
                    </div>
                </div>

                <div class="col-md-3 col-lg-3">
                    <select name="category_id" class="form-select text-sm">
                        <option value="">Semua Kategori</option>
                        <?php if (!empty($data['categories'])) : ?>
                            <?php foreach ($data['categories'] as $cat) : ?>
                                <option value="<?= $cat['id']; ?>" <?= (($data['selected_category'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-md-3 col-lg-3">
                    <select name="status" class="form-select text-sm">
                        <option value="">Semua Kondisi Stok</option>
                        <option value="low_stock" <?= (($data['selected_status'] ?? '') === 'low_stock') ? 'selected' : ''; ?>>⚠️ Stok Menipis (&le; 5 unit)</option>
                        <option value="out_of_stock" <?= (($data['selected_status'] ?? '') === 'out_of_stock') ? 'selected' : ''; ?>>❌ Stok Habis (0 unit)</option>
                        <option value="Available" <?= (($data['selected_status'] ?? '') === 'Available') ? 'selected' : ''; ?>>Status: Tersedia (Available)</option>
                        <option value="Out of Stock" <?= (($data['selected_status'] ?? '') === 'Out of Stock') ? 'selected' : ''; ?>>Status: Habis (Out of Stock)</option>
                    </select>
                </div>

                <div class="col-auto d-flex align-items-center gap-1">
                    <button type="submit" class="btn btn-sm btn-dark-corp mb-0">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="<?= BASEURL; ?>/operatorproduct" class="btn btn-sm btn-white-corp mb-0 btn-reset-filter" data-ajax-reset>
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table Card -->
    <div class="card shadow-xs border">
        <div class="card-header border-bottom d-flex align-items-center justify-content-between">
            <div>
                <h6 class="font-weight-semibold text-dark mb-0">Daftar Stok Produk</h6>
                <p class="text-xs text-secondary mb-0">Monitoring stok gudang dan perbarui status fisik</p>
            </div>
            <div>
                <span class="badge badge-corp badge-corp-dark ajax-count-badge">Total <?= $data['total_products'] ?? count($data['products'] ?? []); ?> Produk</span>
            </div>
        </div>
        <div class="card-body px-0 py-0">
            <div class="table-responsive ajax-table-content position-relative">
                <table class="table table-corporate align-items-center mb-0">
                    <thead>
                        <tr>
                            <th width="80" class="ps-4">Gambar</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok Fisik</th>
                            <th>Status</th>
                            <th class="text-center pe-4" width="140">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['products'])) : ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-box-seam fs-1 d-block mb-2 text-secondary"></i>
                                    Tidak ada data produk yang sesuai filter.
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($data['products'] as $prod) : ?>
                                <?php $isLow = ($prod['stock'] <= 5); ?>
                                <tr>
                                    <td class="ps-4">
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
                                        <?php if ($prod['stock'] == 0): ?>
                                            <span class="badge badge-corp badge-corp-danger text-xxs py-0 px-2 mt-1">
                                                <i class="bi bi-x-circle-fill"></i> Stok Habis!
                                            </span>
                                        <?php elseif ($isLow): ?>
                                            <span class="badge badge-corp badge-corp-warning text-xxs py-0 px-2 mt-1">
                                                <i class="bi bi-exclamation-triangle-fill"></i> Stok Menipis!
                                            </span>
                                        <?php endif; ?>
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
                                        <span class="badge badge-corp <?= $isLow ? 'badge-corp-danger' : 'badge-corp-success'; ?> fs-6 px-3 py-1">
                                            <?= $prod['stock']; ?> unit
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($prod['status'] === 'Available') : ?>
                                            <span class="badge badge-corp badge-corp-success"><i class="bi bi-check-circle-fill"></i> Tersedia</span>
                                        <?php else : ?>
                                            <span class="badge badge-corp badge-corp-danger"><i class="bi bi-x-circle-fill"></i> Kosong</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center pe-4">
                                        <a href="<?= BASEURL; ?>/operatorproduct/edit/<?= $prod['id']; ?>" class="btn btn-sm btn-white-corp py-1 px-3 mb-0 text-xs">
                                            <i class="bi bi-pencil-square me-1"></i> Update Stok
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination Container -->
    <div class="ajax-pagination-container">
        <?php if (!empty($data['total_pages']) && $data['total_pages'] > 1) : 
            $filterParams = [];
            if (!empty($data['keyword'])) $filterParams['q'] = $data['keyword'];
            if (!empty($data['selected_category'])) $filterParams['category_id'] = $data['selected_category'];
            if (!empty($data['selected_status'])) $filterParams['status'] = $data['selected_status'];
            $queryString = !empty($filterParams) ? '&' . http_build_query($filterParams) : '';
        ?>
            <div class="d-flex justify-content-center mt-4">
                <ul class="pagination gap-1">
                    <?php for ($i = 1; $i <= $data['total_pages']; $i++) : ?>
                        <li class="page-item <?= ($i == $data['current_page']) ? 'active' : ''; ?>">
                            <a class="btn btn-sm <?= ($i == $data['current_page']) ? 'btn-dark-corp' : 'btn-white-corp'; ?> mb-0 px-3" href="<?= BASEURL; ?>/operatorproduct?page=<?= $i . $queryString; ?>"><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>
