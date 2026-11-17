<!-- Corporate UI Dashboard: Owner View -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-md-flex align-items-center justify-content-between">
            <div class="mb-3 mb-md-0">
                <h3 class="font-weight-bold text-dark mb-1">Halo, <?= htmlspecialchars($_SESSION['user_name']); ?>! 👋</h3>
                <p class="text-secondary text-sm mb-0">Executive Dashboard &bull; Ringkasan performa finansial dan operasional <b>NNACO SA</b>.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASEURL; ?>/ownerreport" class="btn btn-sm btn-dark-corp mb-0 d-inline-flex align-items-center">
                    <i class="bi bi-bar-chart-line me-1"></i> Laporan Keuangan Lengkap
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Primary Financial Metrics (Corporate UI Style) -->
<div class="row g-3 mb-4">
    <!-- Total Revenue -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-corporate">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-title">Total Pendapatan</p>
                    <h3 class="stat-value" style="font-size: 1.45rem;">Rp <?= number_format($data['stats']['total_revenue'] ?? 0, 0, ',', '.'); ?></h3>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge badge-corp badge-corp-success">Total Lunas</span>
                        <span class="text-xs text-muted">Seluruh waktu</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper bg-slate-900 text-white shadow-sm">
                    <i class="bi bi-wallet2 fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulan Ini -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-corporate">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-title">Penjualan Bulan Ini</p>
                    <h3 class="stat-value" style="font-size: 1.45rem;">Rp <?= number_format($data['stats']['month_sales'] ?? 0, 0, ',', '.'); ?></h3>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge badge-corp badge-corp-info"><?= date('F Y'); ?></span>
                        <span class="text-xs text-muted">Bulan berjalan</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper bg-primary text-white shadow-sm">
                    <i class="bi bi-graph-up-arrow fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Hari Ini -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-corporate">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-title">Penjualan Hari Ini</p>
                    <h3 class="stat-value" style="font-size: 1.45rem;">Rp <?= number_format($data['stats']['today_sales'] ?? 0, 0, ',', '.'); ?></h3>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge badge-corp badge-corp-warning"><?= date('d M'); ?></span>
                        <span class="text-xs text-muted">Transaksi hari ini</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper bg-warning text-dark shadow-sm">
                    <i class="bi bi-cash-stack fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Orders Selesai -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-corporate">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-title">Order Sukses</p>
                    <h3 class="stat-value"><?= number_format($data['stats']['total_orders'] ?? 0); ?></h3>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge badge-corp badge-corp-success">Selesai</span>
                        <span class="text-xs text-muted">Transaksi tuntas</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper bg-success text-white shadow-sm">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Secondary Metrics -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card-corporate">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="stat-title">Total Customers</p>
                    <h4 class="stat-value mb-0"><?= number_format($data['stats']['total_customers'] ?? 0); ?></h4>
                    <span class="text-xs text-muted">Pelanggan terdaftar</span>
                </div>
                <div class="stat-icon-wrapper bg-gray-100 text-dark">
                    <i class="bi bi-people fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card-corporate">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="stat-title">Total Produk Aktif</p>
                    <h4 class="stat-value mb-0"><?= number_format($data['stats']['total_products'] ?? 0); ?></h4>
                    <span class="text-xs text-muted">Katalog siap jual</span>
                </div>
                <div class="stat-icon-wrapper bg-gray-100 text-dark">
                    <i class="bi bi-box-seam fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card-corporate">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="stat-title">Stok Rendah (&le; 5)</p>
                    <h4 class="stat-value mb-0 <?= !empty($data['low_stock']) ? 'text-danger' : ''; ?>"><?= count($data['low_stock'] ?? []); ?></h4>
                    <span class="text-xs text-muted">Perlu restock segera</span>
                </div>
                <div class="stat-icon-wrapper <?= !empty($data['low_stock']) ? 'bg-danger bg-opacity-10 text-danger' : 'bg-gray-100 text-muted'; ?>">
                    <i class="bi bi-exclamation-triangle fs-5"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Gateway Stats & AI Assistant -->
<div class="row g-4 mb-4">
    <!-- Payment Gateway Statistics -->
    <div class="col-lg-5">
        <div class="card shadow-xs border h-100">
            <div class="card-header border-bottom">
                <h6 class="font-weight-semibold text-dark mb-0">Statistik Metode Pembayaran</h6>
                <p class="text-xs text-secondary mb-0">Distribusi transaksi payment gateway</p>
            </div>
            <div class="card-body p-3">
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="p-3 border rounded-3 bg-gray-100 text-center">
                            <span class="text-xs text-muted d-block font-weight-bold">PENDING</span>
                            <h4 class="fw-bold text-warning mb-0"><?= number_format($data['pending_payments'] ?? 0); ?></h4>
                            <span class="text-xxs text-muted">Menunggu</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border rounded-3 bg-gray-100 text-center">
                            <span class="text-xs text-muted d-block font-weight-bold">FAILED</span>
                            <h4 class="fw-bold text-danger mb-0"><?= number_format($data['failed_payments'] ?? 0); ?></h4>
                            <span class="text-xxs text-muted">Gagal/Batal</span>
                        </div>
                    </div>
                </div>

                <h6 class="text-xs text-uppercase font-weight-bold text-muted mb-2">Metode Terpopuler (LUNAS)</h6>
                <?php if (empty($data['payment_stats'])): ?>
                    <p class="text-xs text-muted">Belum ada data pembayaran lunas.</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($data['payment_stats'] as $stat): ?>
                            <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-white">
                                <span class="text-sm font-weight-bold text-dark text-uppercase">
                                    <i class="bi bi-credit-card-2-front text-primary me-2"></i> <?= htmlspecialchars($stat['payment_method']); ?>
                                </span>
                                <span class="badge badge-corp badge-corp-dark"><?= $stat['total_count']; ?> transaksi</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- AI Business Assistant Console -->
    <div class="col-lg-7">
        <div class="card shadow-xs border h-100 bg-slate-900 text-white overflow-hidden position-relative">
            <div class="card-header border-bottom border-secondary border-opacity-25 bg-transparent d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="icon icon-shape bg-primary text-white rounded-2 d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                        <i class="bi bi-robot fs-5"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-semibold text-white mb-0">AI Business Intelligence</h6>
                        <p class="text-xs text-white-50 mb-0">Analisis instan performa toko berbasis data riil</p>
                    </div>
                </div>
                <span class="badge badge-corp badge-corp-info text-white bg-opacity-20 border-0">Gemini Powered</span>
            </div>
            
            <div class="card-body p-4 d-flex flex-column justify-content-between">
                <!-- Response Box -->
                <div id="ai-response-box" class="mb-3 p-3 rounded-3 bg-slate-800 text-light border border-secondary border-opacity-25 d-none" style="min-height: 90px; max-height: 220px; overflow-y: auto;">
                    <!-- Response injected via JS -->
                </div>

                <div class="mb-3 text-white-50 text-xs">
                    Tanyakan hal seperti: <i>"Berapa rata-rata transaksi hari ini?"</i> atau <i>"Produk apa yang memberikan kontribusi omset tertinggi?"</i>
                </div>

                <!-- Input form -->
                <form id="owner-ai-form">
                    <div class="input-group">
                        <input type="text" id="owner-ai-input" class="form-control bg-slate-800 text-white border-secondary border-opacity-50" placeholder="Ketik pertanyaan bisnis di sini..." required autocomplete="off" style="font-size: 0.875rem;">
                        <button type="submit" id="owner-ai-btn" class="btn btn-primary-corp mb-0 px-4">
                            <i class="bi bi-send-fill me-1"></i> Analisis
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Section: Top Products & Low Stock Alerts -->
<div class="row g-4">
    <!-- Top Selling Products Table -->
    <div class="col-lg-8">
        <div class="card shadow-xs border h-100">
            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="font-weight-semibold text-dark mb-0">Top 5 Produk Terlaris</h6>
                    <p class="text-xs text-secondary mb-0">Produk dengan volume penjualan tertinggi</p>
                </div>
                <a href="<?= BASEURL; ?>/ownerreport" class="btn btn-sm btn-white-corp mb-0">
                    Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body px-0 py-0">
                <div class="table-responsive">
                    <table class="table table-corporate align-items-center mb-0">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-center">Total Terjual</th>
                                <th class="text-end pe-4">Total Omset</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['top_products'])): ?>
                                <tr><td colspan="4" class="text-center py-5 text-muted">Belum ada data penjualan tercatat.</td></tr>
                            <?php else: ?>
                                <?php foreach ($data['top_products'] as $tp): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="icon icon-shape bg-gray-100 text-dark rounded-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-gem"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-bold text-dark d-block"><?= htmlspecialchars($tp['name']); ?></span>
                                                    <span class="text-xxs text-muted">ID: #<?= $tp['id']; ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-corp badge-corp-info"><?= $tp['total_sold']; ?> unit</span>
                                        </td>
                                        <td class="text-end pe-4 fw-bold text-dark">
                                            Rp <?= number_format($tp['total_revenue'], 0, ',', '.'); ?>
                                        </td>
                                        <td class="text-center text-nowrap">
                                            <div class="d-inline-flex gap-1 align-items-center">
                                                <a href="<?= BASEURL; ?>/adminproduct/edit/<?= $tp['id']; ?>" class="btn btn-sm btn-white-corp py-1 px-2 mb-0" title="Edit Produk">
                                                    <i class="bi bi-pencil-square text-warning"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-white-corp py-1 px-2 mb-0" data-bs-toggle="modal" data-bs-target="#modalDeleteProductOwner<?= $tp['id']; ?>" title="Hapus Produk">
                                                    <i class="bi bi-trash-fill text-danger"></i>
                                                </button>
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

        <!-- Modals for Owner Top Selling Products (Rendered outside table & card) -->
        <?php if (!empty($data['top_products'])) : ?>
            <?php foreach ($data['top_products'] as $tp) : ?>
                <div class="modal fade" id="modalDeleteProductOwner<?= $tp['id']; ?>" tabindex="-1" aria-labelledby="modalLabelDelOwnerProd<?= $tp['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow-lg border-0 rounded-4">
                            <div class="modal-header border-bottom px-4 py-3 bg-light rounded-top-4">
                                <h6 class="modal-title font-weight-bold text-danger mb-0" id="modalLabelDelOwnerProd<?= $tp['id']; ?>">
                                    <i class="bi bi-trash-fill me-2"></i>Konfirmasi Hapus Produk
                                </h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="<?= BASEURL; ?>/adminproduct/delete/<?= $tp['id']; ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                                <input type="hidden" name="redirect_to" value="<?= BASEURL; ?>/owner">
                                <div class="modal-body p-4 text-start">
                                    <p class="text-sm text-secondary mb-3">
                                        Yakin ingin menghapus produk terlaris <strong><?= htmlspecialchars($tp['name']); ?></strong>?
                                    </p>
                                    <div class="alert alert-danger text-xs mb-0 text-white border-0 py-2 rounded-3">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Tindakan Owner ini permanen. Data katalog dan file foto produk akan terhapus.
                                    </div>
                                </div>
                                <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
                                    <button type="button" class="btn btn-sm btn-white-corp mb-0" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-sm btn-danger mb-0"><i class="bi bi-trash-fill me-1"></i> Ya, Hapus Produk</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Low Stock Alert Card -->
    <div class="col-lg-4">
        <div class="card shadow-xs border h-100">
            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="font-weight-semibold text-dark mb-0">Peringatan Restock</h6>
                    <p class="text-xs text-secondary mb-0">Produk dengan stok &le; 5 unit</p>
                </div>
                <span class="badge badge-corp badge-corp-danger"><?= count($data['low_stock'] ?? []); ?> item</span>
            </div>
            <div class="card-body p-3">
                <?php if (empty($data['low_stock'])): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle-fill text-success fs-2 mb-2 d-block"></i>
                        <h6 class="text-sm font-weight-bold text-dark mb-0">Kondisi Stok Aman</h6>
                        <p class="text-xs text-muted mb-0">Tidak ada produk yang perlu di-restock saat ini.</p>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($data['low_stock'] as $ls): ?>
                            <div class="d-flex justify-content-between align-items-center p-2 border rounded-3 bg-white">
                                <div class="d-flex align-items-center me-2 overflow-hidden">
                                    <span class="text-sm fw-medium text-dark text-truncate" style="max-width: 140px;"><?= htmlspecialchars($ls['name']); ?></span>
                                    <span class="badge badge-corp badge-corp-danger ms-2"><?= $ls['stock']; ?> unit</span>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <a href="<?= BASEURL; ?>/adminproduct/edit/<?= $ls['id']; ?>" class="btn btn-sm btn-white-corp py-1 px-2 mb-0 text-xs" title="Edit Produk">
                                        <i class="bi bi-pencil-square text-warning"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-white-corp py-1 px-2 mb-0 text-xs text-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteLowStockOwner<?= $ls['id']; ?>" title="Hapus Produk">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modals for Owner Low Stock Products (Rendered outside card) -->
        <?php if (!empty($data['low_stock'])) : ?>
            <?php foreach ($data['low_stock'] as $ls) : ?>
                <div class="modal fade" id="modalDeleteLowStockOwner<?= $ls['id']; ?>" tabindex="-1" aria-labelledby="modalLabelDelLsOwner<?= $ls['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow-lg border-0 rounded-4">
                            <div class="modal-header border-bottom px-4 py-3 bg-light rounded-top-4">
                                <h6 class="modal-title font-weight-bold text-danger mb-0" id="modalLabelDelLsOwner<?= $ls['id']; ?>">
                                    <i class="bi bi-trash-fill me-2"></i>Konfirmasi Hapus Produk
                                </h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="<?= BASEURL; ?>/adminproduct/delete/<?= $ls['id']; ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                                <input type="hidden" name="redirect_to" value="<?= BASEURL; ?>/owner">
                                <div class="modal-body p-4 text-start">
                                    <p class="text-sm text-secondary mb-3">
                                        Yakin ingin menghapus produk <strong><?= htmlspecialchars($ls['name']); ?></strong> (Sisa stok: <?= $ls['stock']; ?>)?
                                    </p>
                                    <div class="alert alert-danger text-xs mb-0 text-white border-0 py-2 rounded-3">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Data produk akan dihapus permanen oleh Owner.
                                    </div>
                                </div>
                                <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
                                    <button type="button" class="btn btn-sm btn-white-corp mb-0" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-sm btn-danger mb-0"><i class="bi bi-trash-fill me-1"></i> Ya, Hapus Produk</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
    </div>
</div>

<!-- Executive Section: Tabel Transaksi Pesanan Terbaru -->
<div class="row g-4 mt-1 mb-4">
    <div class="col-12">
        <div class="card shadow-xs border">
            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="font-weight-semibold text-dark mb-0">Monitoring Transaksi Pesanan</h6>
                    <p class="text-xs text-secondary mb-0">Kelola dan tinjau seluruh status transaksi pesanan toko</p>
                </div>
                <a href="<?= BASEURL; ?>/ownerreport" class="btn btn-sm btn-white-corp mb-0">
                    Laporan Lengkap <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body px-0 py-0">
                <div class="table-responsive">
                    <table class="table table-corporate align-items-center mb-0">
                        <thead>
                            <tr>
                                <th>No. Order</th>
                                <th>Pelanggan</th>
                                <th>Total Transaksi</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['recent_orders'])) : ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Belum ada transaksi tercatat.</td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($data['recent_orders'] as $ord) : ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-dark">#<?= str_pad($ord['id'], 5, '0', STR_PAD_LEFT); ?></span>
                                            <div class="text-xs text-muted"><?= date('d M Y, H:i', strtotime($ord['created_at'])); ?></div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs rounded-circle bg-gray-200 text-dark me-2 d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                                    <?= strtoupper(substr($ord['customer_name'] ?? 'U', 0, 1)); ?>
                                                </div>
                                                <span class="fw-semibold text-dark"><?= htmlspecialchars($ord['customer_name'] ?? 'Pelanggan'); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark">Rp <?= number_format($ord['total'], 0, ',', '.'); ?></span>
                                        </td>
                                        <td>
                                            <?php
                                            $st = $ord['status'];
                                            $badgeClass = 'badge-corp-dark';
                                            if ($st === 'Selesai') $badgeClass = 'badge-corp-success';
                                            elseif ($st === 'Menunggu Pembayaran') $badgeClass = 'badge-corp-warning';
                                            elseif ($st === 'Diproses' || $st === 'Dikemas' || $st === 'Dikirim') $badgeClass = 'badge-corp-info';
                                            elseif ($st === 'Dibatalkan') $badgeClass = 'badge-corp-danger';
                                            ?>
                                            <span class="badge badge-corp <?= $badgeClass; ?>"><?= htmlspecialchars($st); ?></span>
                                        </td>
                                        <td class="text-center text-nowrap">
                                            <div class="d-inline-flex gap-1 align-items-center">
                                                <a href="<?= BASEURL; ?>/adminorder/show/<?= $ord['id']; ?>" class="btn btn-sm btn-white-corp py-1 px-2 mb-0" title="Detail Pesanan">
                                                    <i class="bi bi-eye text-primary"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-white-corp py-1 px-2 mb-0" data-bs-toggle="modal" data-bs-target="#modalEditOrderStatusOwner<?= $ord['id']; ?>" title="Edit Status Pesanan">
                                                    <i class="bi bi-pencil-square text-warning"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-white-corp py-1 px-2 mb-0" data-bs-toggle="modal" data-bs-target="#modalDeleteOrderOwner<?= $ord['id']; ?>" title="Hapus Pesanan">
                                                    <i class="bi bi-trash-fill text-danger"></i>
                                                </button>
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

        <!-- Modals for Owner Recent Orders (Rendered outside table & card) -->
        <?php if (!empty($data['recent_orders'])) : ?>
            <?php foreach ($data['recent_orders'] as $ord) : ?>
                <!-- Modal Edit Status Pesanan Owner -->
                <div class="modal fade" id="modalEditOrderStatusOwner<?= $ord['id']; ?>" tabindex="-1" aria-labelledby="modalLabelEditOwnerOrd<?= $ord['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow-lg border-0 rounded-4">
                            <div class="modal-header border-bottom px-4 py-3 bg-light rounded-top-4">
                                <h6 class="modal-title font-weight-bold text-dark mb-0" id="modalLabelEditOwnerOrd<?= $ord['id']; ?>">
                                    <i class="bi bi-pencil-square text-warning me-2"></i>Edit Status Pesanan #<?= str_pad($ord['id'], 5, '0', STR_PAD_LEFT); ?>
                                </h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="<?= BASEURL; ?>/adminorder/updateStatus/<?= $ord['id']; ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                                <input type="hidden" name="redirect_to" value="<?= BASEURL; ?>/owner">
                                <div class="modal-body p-4 text-start">
                                    <div class="mb-3">
                                        <label class="form-label text-xs font-weight-bold text-dark">Pelanggan</label>
                                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($ord['customer_name'] ?? 'Pelanggan'); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-xs font-weight-bold text-dark">Total Transaksi</label>
                                        <input type="text" class="form-control bg-light font-weight-bold text-dark" value="Rp <?= number_format($ord['total'], 0, ',', '.'); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-xs font-weight-bold text-dark">Status Pesanan <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select" required>
                                            <?php
                                            $statOptions = ['MENUNGGU PEMBAYARAN', 'DIPROSES', 'DIKEMAS', 'DIKIRIM', 'SELESAI', 'DIBATALKAN'];
                                            foreach ($statOptions as $so) :
                                                $sel = (strcasecmp($ord['status'], $so) === 0) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $so; ?>" <?= $sel; ?>><?= $so; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label text-xs font-weight-bold text-dark">Nomor Resi / Catatan (Opsional)</label>
                                        <input type="text" name="tracking_number" class="form-control" placeholder="Contoh: JNE / POS - 12873491">
                                    </div>
                                </div>
                                <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
                                    <button type="button" class="btn btn-sm btn-white-corp mb-0" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-sm btn-dark-corp mb-0"><i class="bi bi-check-lg me-1"></i> Simpan Status</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Konfirmasi Hapus Pesanan Owner -->
                <div class="modal fade" id="modalDeleteOrderOwner<?= $ord['id']; ?>" tabindex="-1" aria-labelledby="modalLabelDelOwnerOrd<?= $ord['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow-lg border-0 rounded-4">
                            <div class="modal-header border-bottom px-4 py-3 bg-light rounded-top-4">
                                <h6 class="modal-title font-weight-bold text-danger mb-0" id="modalLabelDelOwnerOrd<?= $ord['id']; ?>">
                                    <i class="bi bi-trash-fill me-2"></i>Konfirmasi Hapus Pesanan
                                </h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="<?= BASEURL; ?>/adminorder/delete/<?= $ord['id']; ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                                <input type="hidden" name="redirect_to" value="<?= BASEURL; ?>/owner">
                                <div class="modal-body p-4 text-start">
                                    <p class="text-sm text-secondary mb-3">
                                        Yakin ingin menghapus pesanan <strong>#<?= str_pad($ord['id'], 5, '0', STR_PAD_LEFT); ?></strong> milik <strong><?= htmlspecialchars($ord['customer_name'] ?? 'Pelanggan'); ?></strong> senilai <strong>Rp <?= number_format($ord['total'], 0, ',', '.'); ?></strong>?
                                    </p>
                                    <div class="alert alert-danger text-xs mb-0 text-white border-0 py-2 rounded-3">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Tindakan Owner ini permanen menghapus data transaksi dan rincian item pesanan.
                                    </div>
                                </div>
                                <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
                                    <button type="button" class="btn btn-sm btn-white-corp mb-0" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-sm btn-danger mb-0"><i class="bi bi-trash-fill me-1"></i> Ya, Hapus Pesanan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const aiForm = document.getElementById('owner-ai-form');
    const aiInput = document.getElementById('owner-ai-input');
    const aiBtn = document.getElementById('owner-ai-btn');
    const responseBox = document.getElementById('ai-response-box');

    if (aiForm) {
        aiForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const question = aiInput.value.trim();
            if (!question) return;

            aiInput.disabled = true;
            aiBtn.disabled = true;
            aiBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menganalisis...';
            
            responseBox.classList.remove('d-none');
            responseBox.innerHTML = `
                <div class="d-flex align-items-center text-info text-xs">
                    <div class="spinner-grow spinner-grow-sm me-2" role="status"></div> 
                    <span>Mengolah data penjualan dan parameter bisnis...</span>
                </div>
            `;

            fetch('<?= BASEURL; ?>/owner/askAI', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ question: question })
            })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    responseBox.innerHTML = `
                        <div class="d-flex align-items-start text-danger text-xs">
                            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                            <div>
                                <strong class="d-block">Gagal Memproses:</strong>
                                <span>${data.error}</span>
                            </div>
                        </div>
                    `;
                } else {
                    responseBox.innerHTML = `
                        <div class="text-white-50 text-xxs mb-1">
                            <i class="bi bi-person-fill me-1"></i> Pertanyaan: "<i>${question}</i>"
                        </div>
                        <div class="text-white text-xs lh-base">
                            ${data.answer.replace(/\n/g, '<br>')}
                        </div>
                    `;
                }
            })
            .catch(err => {
                responseBox.innerHTML = `
                    <div class="d-flex align-items-start text-danger text-xs">
                        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                        <div>
                            <strong class="d-block">Koneksi Gagal:</strong>
                            <span>Tidak dapat menghubungi server AI. Pastikan server AI aktif.</span>
                        </div>
                    </div>
                `;
            })
            .finally(() => {
                aiInput.value = '';
                aiInput.disabled = false;
                aiBtn.disabled = false;
                aiBtn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Analisis';
                aiInput.focus();
            });
        });
    }
});
</script>
