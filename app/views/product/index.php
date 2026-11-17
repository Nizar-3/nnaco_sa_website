<div class="container py-4 mb-5">
    <!-- Flash Messages -->
    <?php Flasher::flash(); ?>

    <!-- Breadcrumb & Header Title -->
    <div class="card shadow-xs border rounded-4 mb-4 bg-white p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent mb-1 p-0">
                        <li class="breadcrumb-item"><a href="<?= BASEURL; ?>" class="text-xs text-muted text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item active text-xs font-weight-bold text-slate-800" aria-current="page">Katalog Belanja</li>
                    </ol>
                </nav>
                <h4 class="font-weight-bold text-slate-900 mb-0">Katalog Aksesoris NNACO SA</h4>
            </div>
            <div>
                <span class="badge badge-corp badge-corp-dark">
                    <?= $data['total_pages'] > 0 ? "Halaman " . $data['current_page'] . " dari " . $data['total_pages'] : "0 Produk" ?>
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar Filter Card -->
        <div class="col-lg-3">
            <div class="card shadow-xs border rounded-4 bg-white sticky-top" style="top: 90px;">
                <div class="card-header border-bottom py-3 px-4 bg-white">
                    <h6 class="font-weight-bold text-slate-900 mb-0 d-flex align-items-center">
                        <i class="bi bi-funnel me-2 text-primary"></i> Filter Pencarian
                    </h6>
                </div>
                <div class="card-body p-4">
                    <form action="<?= BASEURL; ?>/product" method="get">
                        <!-- Search Name -->
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-slate-700">Kata Kunci</label>
                            <input type="text" class="form-control corp-search-input" name="q" placeholder="Cari aksesoris..." value="<?= htmlspecialchars($data['filters']['q'] ?? ''); ?>">
                        </div>
                        
                        <!-- Category -->
                        <div class="mb-3">
                            <label class="form-label text-xs font-weight-bold text-slate-700">Kategori</label>
                            <select class="form-select corp-search-input" name="category">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($data['categories'] as $cat) : ?>
                                    <option value="<?= $cat['id']; ?>" <?= ($data['filters']['category'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Price Range -->
                        <div class="mb-4">
                            <label class="form-label text-xs font-weight-bold text-slate-700">Rentang Harga (Rp)</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control corp-search-input form-control-sm" name="min_price" placeholder="Min" value="<?= htmlspecialchars($data['filters']['min_price'] ?? ''); ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control corp-search-input form-control-sm" name="max_price" placeholder="Max" value="<?= htmlspecialchars($data['filters']['max_price'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-sm btn-dark-corp">
                                <i class="bi bi-search me-1"></i> Terapkan Filter
                            </button>
                            <a href="<?= BASEURL; ?>/product" class="btn btn-sm btn-white-corp text-center">Reset Filter</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="col-lg-9">
            <?php if (empty($data['products'])) : ?>
                <div class="card shadow-xs border rounded-4 text-center py-5 bg-white">
                    <div class="card-body py-5">
                        <div class="icon icon-shape bg-gray-100 text-muted rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                            <i class="bi bi-search fs-3"></i>
                        </div>
                        <h6 class="font-weight-bold text-slate-900 mb-1">Produk Tidak Ditemukan</h6>
                        <p class="text-muted text-xs mb-3">Tidak ada aksesoris yang sesuai dengan filter atau kata kunci Anda.</p>
                        <a href="<?= BASEURL; ?>/product" class="btn btn-sm btn-white-corp">Lihat Semua Koleksi</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($data['products'] as $product) : ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="card card-product border-0 h-100 rounded-4 bg-white p-3 shadow-xs position-relative d-flex flex-column">
                                <div class="position-relative bg-gray-100 rounded-3 overflow-hidden mb-3 text-center" style="height: 220px;">
                                    <?php if ($product['stock'] <= 0) : ?>
                                        <span class="badge badge-corp badge-corp-danger position-absolute top-0 end-0 m-2">HABIS</span>
                                    <?php else: ?>
                                        <span class="badge badge-corp badge-corp-success position-absolute top-0 end-0 m-2">TERSEDIA</span>
                                    <?php endif; ?>
                                    
                                    <?php $img = !empty($product['image']) ? BASEURL . '/img/products/' . $product['image'] : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?q=80&w=400&auto=format&fit=crop'; ?>
                                    <img src="<?= $img ?>" class="img-fluid w-100 h-100" style="object-fit: cover;" alt="<?= htmlspecialchars($product['name']); ?>">
                                </div>
                                
                                <div class="d-flex flex-column flex-grow-1 text-start">
                                    <span class="text-xxs text-uppercase font-weight-bold text-muted mb-1"><?= htmlspecialchars($product['category_name'] ?? 'Aksesoris'); ?></span>
                                    <h6 class="font-weight-bold text-slate-900 text-truncate mb-2" title="<?= htmlspecialchars($product['name']); ?>">
                                        <?= htmlspecialchars($product['name']); ?>
                                    </h6>
                                    
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="font-weight-bold text-slate-900 fs-6 me-2">Rp <?= number_format($product['price'], 0, ',', '.'); ?></span>
                                        <small class="text-muted text-decoration-line-through text-xs">Rp <?= number_format($product['price'] * 1.15, 0, ',', '.'); ?></small>
                                    </div>
                                    
                                    <div class="mt-auto pt-2 border-top">
                                        <?php if ($product['stock'] > 0) : ?>
                                            <a href="<?= BASEURL; ?>/product/detail/<?= $product['id']; ?>" class="btn btn-sm btn-white-corp w-100 mb-0 d-flex align-items-center justify-content-center">
                                                <i class="bi bi-eye me-1 text-primary"></i> Detail Produk
                                            </a>
                                        <?php else : ?>
                                            <button class="btn btn-sm btn-white-corp w-100 mb-0 text-muted" disabled>Stok Habis</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination (Corporate UI) -->
                <?php if ($data['total_pages'] > 1) : ?>
                    <nav class="mt-5">
                        <ul class="pagination justify-content-center gap-1">
                            <?php $queryParams = $_GET; ?>
                            <?php for($i=1; $i<=$data['total_pages']; $i++) : ?>
                                <?php $queryParams['page'] = $i; ?>
                                <li class="page-item <?= ($data['current_page'] == $i) ? 'active' : '' ?>">
                                    <a class="page-link rounded-3 border-0 shadow-xs font-weight-bold text-xs <?= ($data['current_page'] == $i) ? 'bg-slate-900 text-white' : 'bg-white text-slate-700' ?>" href="<?= BASEURL; ?>/product?<?= http_build_query($queryParams); ?>"><?= $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
