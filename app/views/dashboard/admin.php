<!-- Corporate UI Dashboard: Admin View -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-md-flex align-items-center justify-content-between">
            <div class="mb-3 mb-md-0">
                <h3 class="font-weight-bold text-dark mb-1">Halo, <?= htmlspecialchars($_SESSION['user_name']); ?>! 👋</h3>
                <p class="text-secondary text-sm mb-0">Selamat datang di Panel Kendali Utama <b>NNACO SA Corporate UI</b>.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASEURL; ?>/adminproduct/create" class="btn btn-sm btn-dark-corp mb-0 d-inline-flex align-items-center">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Produk
                </a>
                <a href="<?= BASEURL; ?>/adminorder" class="btn btn-sm btn-white-corp mb-0 d-inline-flex align-items-center">
                    <i class="bi bi-receipt me-1"></i> Kelola Pesanan
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Stat Metric Cards (Corporate UI Style) -->
<div class="row g-3 mb-4">
    <!-- Total Produk -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-corporate">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-title">Katalog Produk</p>
                    <h3 class="stat-value"><?= number_format($data['total_products'] ?? 0); ?></h3>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge badge-corp badge-corp-success">Aktif</span>
                        <span class="text-xs text-muted">Koleksi aksesoris</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper bg-slate-900 text-white shadow-sm">
                    <i class="bi bi-box-seam fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Kategori -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-corporate">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-title">Kategori Aksesoris</p>
                    <h3 class="stat-value"><?= number_format($data['total_categories'] ?? 0); ?></h3>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge badge-corp badge-corp-info">Master</span>
                        <span class="text-xs text-muted">Kategori aktif</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper bg-primary text-white shadow-sm">
                    <i class="bi bi-tags fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Pengguna -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-corporate">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-title">Total Pengguna</p>
                    <h3 class="stat-value"><?= number_format($data['total_users'] ?? 0); ?></h3>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge badge-corp badge-corp-dark">Terdaftar</span>
                        <span class="text-xs text-muted">Semua hak akses</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper bg-info text-white shadow-sm">
                    <i class="bi bi-people fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Pesanan -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-corporate">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-title">Total Pesanan</p>
                    <h3 class="stat-value"><?= number_format($data['total_orders'] ?? 0); ?></h3>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge badge-corp badge-corp-warning">Semua Status</span>
                        <span class="text-xs text-muted">Seluruh transaksi</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper bg-success text-white shadow-sm">
                    <i class="bi bi-receipt-cutoff fs-5"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Section: Recent Orders & Quick Management -->
<div class="row g-4 mb-4">
    <!-- Recent Orders Table (Corporate UI) -->
    <div class="col-lg-8">
        <div class="card shadow-xs border h-100">
            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="font-weight-semibold text-dark mb-0">Pesanan Terbaru</h6>
                    <p class="text-xs text-secondary mb-0">Transaksi yang baru masuk ke sistem</p>
                </div>
                <a href="<?= BASEURL; ?>/adminorder" class="btn btn-sm btn-white-corp mb-0">
                    Lihat Semua Pesanan <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body px-0 py-0">
                <div class="table-responsive">
                    <table class="table table-corporate align-items-center mb-0">
                        <thead>
                            <tr>
                                <th>No. Order</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['recent_orders'])) : ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Belum ada pesanan terbaru.</td>
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
                                                <button type="button" class="btn btn-sm btn-white-corp py-1 px-2 mb-0" data-bs-toggle="modal" data-bs-target="#modalEditOrderStatusAdmin<?= $ord['id']; ?>" title="Edit Status Pesanan">
                                                    <i class="bi bi-pencil-square text-warning"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-white-corp py-1 px-2 mb-0" data-bs-toggle="modal" data-bs-target="#modalDeleteOrderAdmin<?= $ord['id']; ?>" title="Hapus Pesanan">
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

        <!-- Modals for Orders (Rendered outside table & card) -->
        <?php if (!empty($data['recent_orders'])) : ?>
            <?php foreach ($data['recent_orders'] as $ord) : ?>
                <!-- Modal Edit Status Pesanan Admin -->
                <div class="modal fade" id="modalEditOrderStatusAdmin<?= $ord['id']; ?>" tabindex="-1" aria-labelledby="modalLabelEditOrd<?= $ord['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow-lg border-0 rounded-4">
                            <div class="modal-header border-bottom px-4 py-3 bg-light rounded-top-4">
                                <h6 class="modal-title font-weight-bold text-dark mb-0" id="modalLabelEditOrd<?= $ord['id']; ?>">
                                    <i class="bi bi-pencil-square text-warning me-2"></i>Edit Status Pesanan #<?= str_pad($ord['id'], 5, '0', STR_PAD_LEFT); ?>
                                </h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="<?= BASEURL; ?>/adminorder/updateStatus/<?= $ord['id']; ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                                <input type="hidden" name="redirect_to" value="<?= BASEURL; ?>/admin">
                                <div class="modal-body p-4 text-start">
                                    <div class="mb-3">
                                        <label class="form-label text-xs font-weight-bold text-dark">Pelanggan</label>
                                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($ord['customer_name'] ?? 'Pelanggan'); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-xs font-weight-bold text-dark">Total Tagihan</label>
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
                                        <label class="form-label text-xs font-weight-bold text-dark">Nomor Resi / Ekspedisi (Opsional)</label>
                                        <input type="text" name="tracking_number" class="form-control" placeholder="Contoh: JNE-0012398471">
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

                <!-- Modal Konfirmasi Hapus Pesanan Admin -->
                <div class="modal fade" id="modalDeleteOrderAdmin<?= $ord['id']; ?>" tabindex="-1" aria-labelledby="modalLabelDelOrd<?= $ord['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow-lg border-0 rounded-4">
                            <div class="modal-header border-bottom px-4 py-3 bg-light rounded-top-4">
                                <h6 class="modal-title font-weight-bold text-danger mb-0" id="modalLabelDelOrd<?= $ord['id']; ?>">
                                    <i class="bi bi-trash-fill me-2"></i>Konfirmasi Hapus Pesanan
                                </h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="<?= BASEURL; ?>/adminorder/delete/<?= $ord['id']; ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                                <input type="hidden" name="redirect_to" value="<?= BASEURL; ?>/admin">
                                <div class="modal-body p-4 text-start">
                                    <p class="text-sm text-secondary mb-3">
                                        Apakah Anda yakin ingin menghapus pesanan <strong>#<?= str_pad($ord['id'], 5, '0', STR_PAD_LEFT); ?></strong> atas nama <strong><?= htmlspecialchars($ord['customer_name'] ?? 'Pelanggan'); ?></strong> senilai <strong>Rp <?= number_format($ord['total'], 0, ',', '.'); ?></strong>?
                                    </p>
                                    <div class="alert alert-danger text-xs mb-0 text-white border-0 py-2 rounded-3">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Tindakan ini tidak dapat dibatalkan. Riwayat transaksi dan rincian barang terkait akan terhapus.
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

    <!-- Right Sidebar Widgets -->
    <div class="col-lg-4">
        <div class="d-flex flex-column gap-4">
            
            <!-- Management Quick Links Card -->
            <div class="card shadow-xs border">
                <div class="card-header border-bottom">
                    <h6 class="font-weight-semibold text-dark mb-0">Menu Cepat Manajemen</h6>
                    <p class="text-xs text-secondary mb-0">Akses cepat modul operasional</p>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex flex-column gap-2">
                        <a href="<?= BASEURL; ?>/adminproduct" class="d-flex align-items-center justify-content-between p-2 rounded-3 text-decoration-none text-dark bg-gray-100 hover-bg-gray-200 transition">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-shape bg-slate-900 text-white rounded-2 me-3 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <div>
                                    <h6 class="text-sm font-weight-bold mb-0">Katalog Produk</h6>
                                    <p class="text-xs text-muted mb-0">Kelola master data produk</p>
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-muted text-xs"></i>
                        </a>

                        <a href="<?= BASEURL; ?>/admincategory" class="d-flex align-items-center justify-content-between p-2 rounded-3 text-decoration-none text-dark bg-gray-100 hover-bg-gray-200 transition">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-shape bg-primary text-white rounded-2 me-3 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                    <i class="bi bi-tags"></i>
                                </div>
                                <div>
                                    <h6 class="text-sm font-weight-bold mb-0">Kategori Produk</h6>
                                    <p class="text-xs text-muted mb-0">Kelola kategori aksesoris</p>
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-muted text-xs"></i>
                        </a>

                        <a href="<?= BASEURL; ?>/adminuser" class="d-flex align-items-center justify-content-between p-2 rounded-3 text-decoration-none text-dark bg-gray-100 hover-bg-gray-200 transition">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-shape bg-info text-white rounded-2 me-3 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                    <i class="bi bi-people"></i>
                                </div>
                                <div>
                                    <h6 class="text-sm font-weight-bold mb-0">Kelola Pengguna</h6>
                                    <p class="text-xs text-muted mb-0">Hak akses, role, & staf</p>
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-muted text-xs"></i>
                        </a>

                        <a href="<?= BASEURL; ?>/adminchatbot" class="d-flex align-items-center justify-content-between p-2 rounded-3 text-decoration-none text-dark bg-gray-100 hover-bg-gray-200 transition">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-shape bg-indigo text-white rounded-2 me-3 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background-color: #6366f1;">
                                    <i class="bi bi-robot"></i>
                                </div>
                                <div>
                                    <h6 class="text-sm font-weight-bold mb-0">Riwayat Chatbot AI</h6>
                                    <p class="text-xs text-muted mb-0">Log percakapan asisten</p>
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-muted text-xs"></i>
                        </a>

                        <a href="<?= BASEURL; ?>/settings?tab=ai" class="d-flex align-items-center justify-content-between p-2 rounded-3 text-decoration-none text-dark bg-gray-100 hover-bg-gray-200 transition">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-shape bg-dark text-white rounded-2 me-3 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                    <i class="bi bi-cpu-fill text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="text-sm font-weight-bold mb-0">Konfigurasi AI Model</h6>
                                    <p class="text-xs text-muted mb-0">Model Groq/Gemini, API Key & Guardrail</p>
                                </div>
                            </div>
                            <i class="bi bi-chevron-right text-muted text-xs"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Low Stock Alert Card -->
            <div class="card shadow-xs border">
                <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="font-weight-semibold text-dark mb-0">Peringatan Stok Rendah</h6>
                        <p class="text-xs text-secondary mb-0">Stok &le; 5 unit</p>
                    </div>
                    <span class="badge badge-corp badge-corp-danger"><?= count($data['low_stock_products'] ?? []); ?> Produk</span>
                </div>
                <div class="card-body p-3">
                    <?php if (empty($data['low_stock_products'])) : ?>
                        <div class="text-center py-3">
                            <i class="bi bi-check-circle text-success fs-3 mb-2 d-block"></i>
                            <p class="text-xs text-muted mb-0">Semua produk memiliki stok yang aman.</p>
                        </div>
                    <?php else : ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($data['low_stock_products'] as $lsp) : ?>
                                <div class="d-flex align-items-center justify-content-between p-2 border rounded-3 bg-white">
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($lsp['image'])) : ?>
                                            <img src="<?= BASEURL; ?>/img/products/<?= htmlspecialchars($lsp['image']); ?>" class="avatar avatar-sm rounded-2 me-2 object-fit-cover" alt="<?= htmlspecialchars($lsp['name']); ?>" style="width: 34px; height: 34px;">
                                        <?php else : ?>
                                            <div class="avatar avatar-sm rounded-2 bg-gray-100 me-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <h6 class="text-xs font-weight-bold mb-0 text-truncate" style="max-width: 150px;"><?= htmlspecialchars($lsp['name']); ?></h6>
                                            <span class="text-xxs text-muted">Sisa: <b class="text-danger"><?= $lsp['stock']; ?></b> unit</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <a href="<?= BASEURL; ?>/adminproduct/edit/<?= $lsp['id']; ?>" class="btn btn-sm btn-white-corp py-1 px-2 mb-0 text-xs" title="Edit Produk">
                                            <i class="bi bi-pencil-square text-warning me-1"></i>Edit
                                        </a>
                                        <button type="button" class="btn btn-sm btn-white-corp py-1 px-2 mb-0 text-xs text-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteProductAdmin<?= $lsp['id']; ?>" title="Hapus Produk">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Modals for Low Stock Products (Rendered outside card) -->
            <?php if (!empty($data['low_stock_products'])) : ?>
                <?php foreach ($data['low_stock_products'] as $lsp) : ?>
                    <div class="modal fade" id="modalDeleteProductAdmin<?= $lsp['id']; ?>" tabindex="-1" aria-labelledby="modalLabelDelProd<?= $lsp['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow-lg border-0 rounded-4">
                                <div class="modal-header border-bottom px-4 py-3 bg-light rounded-top-4">
                                    <h6 class="modal-title font-weight-bold text-danger mb-0" id="modalLabelDelProd<?= $lsp['id']; ?>">
                                        <i class="bi bi-trash-fill me-2"></i>Konfirmasi Hapus Produk
                                    </h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="<?= BASEURL; ?>/adminproduct/delete/<?= $lsp['id']; ?>" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                                    <input type="hidden" name="redirect_to" value="<?= BASEURL; ?>/admin">
                                    <div class="modal-body p-4 text-start">
                                        <p class="text-sm text-secondary mb-3">
                                            Yakin ingin menghapus produk <strong><?= htmlspecialchars($lsp['name']); ?></strong> (Sisa stok: <?= $lsp['stock']; ?>)?
                                        </p>
                                        <div class="alert alert-danger text-xs mb-0 text-white border-0 py-2 rounded-3">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Data produk dan foto fisik pada server akan dihapus secara permanen.
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
