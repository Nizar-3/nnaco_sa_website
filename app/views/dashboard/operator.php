<!-- Corporate UI Dashboard: Operator View -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-md-flex align-items-center justify-content-between">
            <div class="mb-3 mb-md-0">
                <h3 class="font-weight-bold text-dark mb-1">Halo, <?= htmlspecialchars($_SESSION['user_name']); ?>! 👋</h3>
                <p class="text-secondary text-sm mb-0">Panel Operasional Toko <b>NNACO SA Corporate UI</b>.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASEURL; ?>/operatororder" class="btn btn-sm btn-dark-corp mb-0 d-inline-flex align-items-center">
                    <i class="bi bi-receipt me-1"></i> Proses Pesanan
                </a>
                <a href="<?= BASEURL; ?>/operatorproduct" class="btn btn-sm btn-white-corp mb-0 d-inline-flex align-items-center">
                    <i class="bi bi-boxes me-1"></i> Cek Stok Produk
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Stat Metric Cards (Corporate UI Style) -->
<div class="row g-3 mb-4">
    <!-- Order Baru -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-corporate">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-title">Order Baru</p>
                    <h3 class="stat-value"><?= number_format($data['new_orders_count'] ?? 0); ?></h3>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge badge-corp badge-corp-warning">Menunggu</span>
                        <span class="text-xs text-muted">Perlu konfirmasi</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper bg-warning bg-opacity-20 text-warning shadow-sm">
                    <i class="bi bi-bell-fill fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Diproses & Dikemas -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-corporate">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-title">Sedang Diproses</p>
                    <h3 class="stat-value"><?= number_format($data['processing_orders_count'] ?? 0); ?></h3>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge badge-corp badge-corp-info">Packing</span>
                        <span class="text-xs text-muted">Diproses & dikemas</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper bg-info bg-opacity-20 text-info shadow-sm">
                    <i class="bi bi-box-seam fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Selesai -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-corporate">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-title">Pesanan Selesai</p>
                    <h3 class="stat-value"><?= number_format($data['completed_orders_count'] ?? 0); ?></h3>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge badge-corp badge-corp-success">Sukses</span>
                        <span class="text-xs text-muted">Telah diterima</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper bg-success bg-opacity-20 text-success shadow-sm">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Stok Rendah -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-corporate">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-title">Stok Rendah</p>
                    <h3 class="stat-value <?= (($data['low_stock_count'] ?? 0) > 0) ? 'text-danger' : ''; ?>"><?= number_format($data['low_stock_count'] ?? 0); ?></h3>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge badge-corp <?= (($data['low_stock_count'] ?? 0) > 0) ? 'badge-corp-danger' : 'badge-corp-success'; ?>">
                            <?= (($data['low_stock_count'] ?? 0) > 0) ? 'Peringatan' : 'Aman'; ?>
                        </span>
                        <span class="text-xs text-muted">Stok &le; 5</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper <?= (($data['low_stock_count'] ?? 0) > 0) ? 'bg-danger bg-opacity-20 text-danger' : 'bg-gray-100 text-muted'; ?> shadow-sm">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Section: Tables Antrean Pesanan & Pemantauan Stok -->
<div class="row g-4 mb-4">
    <!-- Tabel Antrean Pesanan Operasional -->
    <div class="col-lg-8">
        <div class="card shadow-xs border h-100">
            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="font-weight-semibold text-dark mb-0">Antrean Pesanan Operasional</h6>
                    <p class="text-xs text-secondary mb-0">Pesanan terbaru yang memerlukan tindak lanjut pemrosesan</p>
                </div>
                <a href="<?= BASEURL; ?>/operatororder" class="btn btn-sm btn-white-corp mb-0">
                    Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
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
                                    <td colspan="5" class="text-center py-5 text-muted">Belum ada antrean pesanan.</td>
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
                                                <a href="<?= BASEURL; ?>/operatororder/show/<?= $ord['id']; ?>" class="btn btn-sm btn-white-corp py-1 px-2 mb-0" title="Detail Pesanan">
                                                    <i class="bi bi-eye text-primary"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-white-corp py-1 px-2 mb-0" data-bs-toggle="modal" data-bs-target="#modalEditOrderStatusOperator<?= $ord['id']; ?>" title="Edit Status Pesanan">
                                                    <i class="bi bi-pencil-square text-warning"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-white-corp py-1 px-2 mb-0" data-bs-toggle="modal" data-bs-target="#modalDeleteOrderOperator<?= $ord['id']; ?>" title="Hapus / Batalkan Pesanan">
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

        <!-- Modals for Operator Orders (Rendered outside table & card) -->
        <?php if (!empty($data['recent_orders'])) : ?>
            <?php foreach ($data['recent_orders'] as $ord) : ?>
                <!-- Modal Edit Status Pesanan Operator -->
                <div class="modal fade" id="modalEditOrderStatusOperator<?= $ord['id']; ?>" tabindex="-1" aria-labelledby="modalLabelEditOpOrd<?= $ord['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow-lg border-0 rounded-4">
                            <div class="modal-header border-bottom px-4 py-3 bg-light rounded-top-4">
                                <h6 class="modal-title font-weight-bold text-dark mb-0" id="modalLabelEditOpOrd<?= $ord['id']; ?>">
                                    <i class="bi bi-pencil-square text-warning me-2"></i>Perbarui Status Pesanan #<?= str_pad($ord['id'], 5, '0', STR_PAD_LEFT); ?>
                                </h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="<?= BASEURL; ?>/operatororder/updateStatus/<?= $ord['id']; ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                                <input type="hidden" name="redirect_to" value="<?= BASEURL; ?>/operator">
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
                                        <label class="form-label text-xs font-weight-bold text-dark">Tahap Status Pesanan <span class="text-danger">*</span></label>
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
                                        <label class="form-label text-xs font-weight-bold text-dark">Nomor Resi / Kurir Ekspedisi</label>
                                        <input type="text" name="tracking_number" class="form-control" placeholder="Contoh: JNE / SiCepat - 9821736412">
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

                <!-- Modal Konfirmasi Hapus Pesanan Operator -->
                <div class="modal fade" id="modalDeleteOrderOperator<?= $ord['id']; ?>" tabindex="-1" aria-labelledby="modalLabelDelOpOrd<?= $ord['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow-lg border-0 rounded-4">
                            <div class="modal-header border-bottom px-4 py-3 bg-light rounded-top-4">
                                <h6 class="modal-title font-weight-bold text-danger mb-0" id="modalLabelDelOpOrd<?= $ord['id']; ?>">
                                    <i class="bi bi-trash-fill me-2"></i>Konfirmasi Hapus Pesanan
                                </h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="<?= BASEURL; ?>/operatororder/delete/<?= $ord['id']; ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                                <input type="hidden" name="redirect_to" value="<?= BASEURL; ?>/operator">
                                <div class="modal-body p-4 text-start">
                                    <p class="text-sm text-secondary mb-3">
                                        Apakah Anda yakin ingin menghapus pesanan operasional <strong>#<?= str_pad($ord['id'], 5, '0', STR_PAD_LEFT); ?></strong> milik <strong><?= htmlspecialchars($ord['customer_name'] ?? 'Pelanggan'); ?></strong> senilai <strong>Rp <?= number_format($ord['total'], 0, ',', '.'); ?></strong>?
                                    </p>
                                    <div class="alert alert-danger text-xs mb-0 text-white border-0 py-2 rounded-3">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Tindakan ini permanen menghapus data pesanan dari database.
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

    <!-- Tabel Pemantauan Stok Produk Rendah Gudang -->
    <div class="col-lg-4">
        <div class="card shadow-xs border h-100">
            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="font-weight-semibold text-dark mb-0">Peringatan Stok Gudang</h6>
                    <p class="text-xs text-secondary mb-0">Produk dengan stok &le; 5 unit</p>
                </div>
                <a href="<?= BASEURL; ?>/operatorproduct" class="btn btn-sm btn-white-corp mb-0 text-xs">
                    Semua Stok <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-3">
                <?php if (empty($data['low_stock_products'])) : ?>
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle-fill text-success fs-2 mb-2 d-block"></i>
                        <h6 class="text-sm font-weight-bold text-dark mb-0">Kondisi Stok Aman</h6>
                        <p class="text-xs text-muted mb-0">Tidak ada stok yang menipis saat ini.</p>
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
                                        <h6 class="text-xs font-weight-bold mb-0 text-truncate" style="max-width: 140px;"><?= htmlspecialchars($lsp['name']); ?></h6>
                                        <span class="text-xxs text-muted">Sisa: <b class="text-danger"><?= $lsp['stock']; ?></b> unit</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-sm btn-white-corp py-1 px-2 mb-0 text-xs" data-bs-toggle="modal" data-bs-target="#modalEditStockOperator<?= $lsp['id']; ?>" title="Edit Stok">
                                        <i class="bi bi-pencil-square text-warning"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-white-corp py-1 px-2 mb-0 text-xs text-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteProductOperator<?= $lsp['id']; ?>" title="Hapus Produk">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modals for Operator Low Stock Products (Rendered outside card) -->
        <?php if (!empty($data['low_stock_products'])) : ?>
            <?php foreach ($data['low_stock_products'] as $lsp) : ?>
                <!-- Modal Edit Stok Operator -->
                <div class="modal fade" id="modalEditStockOperator<?= $lsp['id']; ?>" tabindex="-1" aria-labelledby="modalLabelStockOp<?= $lsp['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow-lg border-0 rounded-4">
                            <div class="modal-header border-bottom px-4 py-3 bg-light rounded-top-4">
                                <h6 class="modal-title font-weight-bold text-dark mb-0" id="modalLabelStockOp<?= $lsp['id']; ?>">
                                    <i class="bi bi-boxes text-primary me-2"></i>Update Stok: <?= htmlspecialchars($lsp['name']); ?>
                                </h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="<?= BASEURL; ?>/operatorproduct/updateStock/<?= $lsp['id']; ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                                <input type="hidden" name="redirect_to" value="<?= BASEURL; ?>/operator">
                                <div class="modal-body p-4 text-start">
                                    <div class="mb-3">
                                        <label class="form-label text-xs font-weight-bold text-dark">Nama Produk</label>
                                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($lsp['name']); ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-xs font-weight-bold text-dark">Jumlah Stok Fisik <span class="text-danger">*</span></label>
                                        <input type="number" name="stock" class="form-control" value="<?= $lsp['stock']; ?>" min="0" required>
                                        <span class="text-xxs text-muted">Masukkan jumlah unit perhiasan yang tersedia di gudang.</span>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label text-xs font-weight-bold text-dark">Status Ketersediaan <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select" required>
                                            <option value="Available" <?= ($lsp['status'] === 'Available') ? 'selected' : ''; ?>>Available (Tersedia untuk dijual)</option>
                                            <option value="Out_of_Stock" <?= ($lsp['status'] === 'Out_of_Stock') ? 'selected' : ''; ?>>Out of Stock (Habis)</option>
                                            <option value="Hidden" <?= ($lsp['status'] === 'Hidden') ? 'selected' : ''; ?>>Hidden (Disembunyikan)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
                                    <button type="button" class="btn btn-sm btn-white-corp mb-0" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-sm btn-dark-corp mb-0"><i class="bi bi-check-lg me-1"></i> Simpan Stok</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Konfirmasi Hapus Produk Operator -->
                <div class="modal fade" id="modalDeleteProductOperator<?= $lsp['id']; ?>" tabindex="-1" aria-labelledby="modalLabelDelOpProd<?= $lsp['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow-lg border-0 rounded-4">
                            <div class="modal-header border-bottom px-4 py-3 bg-light rounded-top-4">
                                <h6 class="modal-title font-weight-bold text-danger mb-0" id="modalLabelDelOpProd<?= $lsp['id']; ?>">
                                    <i class="bi bi-trash-fill me-2"></i>Konfirmasi Hapus Produk
                                </h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="<?= BASEURL; ?>/operatorproduct/delete/<?= $lsp['id']; ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                                <input type="hidden" name="redirect_to" value="<?= BASEURL; ?>/operator">
                                <div class="modal-body p-4 text-start">
                                    <p class="text-sm text-secondary mb-3">
                                        Yakin ingin menghapus produk <strong><?= htmlspecialchars($lsp['name']); ?></strong> (Sisa stok: <?= $lsp['stock']; ?>)?
                                    </p>
                                    <div class="alert alert-danger text-xs mb-0 text-white border-0 py-2 rounded-3">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Data produk akan dihapus dari sistem inventaris toko.
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

<!-- Operational Quick Action Cards -->
<div class="row g-4">
    <div class="col-md-6">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-4 d-flex flex-column text-center">
                <div class="stat-icon-wrapper bg-slate-900 text-white rounded-3 mx-auto mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-receipt fs-3"></i>
                </div>
                <h5 class="font-weight-bold text-dark mb-2">Kelola Alur Pesanan</h5>
                <p class="text-secondary text-sm mb-4 flex-grow-1">
                    Pantau pesanan yang masuk, verifikasi bukti pembayaran, perbarui status pengemasan, dan input nomor resi pengiriman pelanggan.
                </p>
                <div>
                    <a href="<?= BASEURL; ?>/operatororder" class="btn btn-dark-corp w-75 py-2">
                        Buka Manajemen Pesanan <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-xs border h-100">
            <div class="card-body p-4 d-flex flex-column text-center">
                <div class="stat-icon-wrapper bg-primary text-white rounded-3 mx-auto mb-3" style="width: 60px; height: 60px;">
                    <i class="bi bi-boxes fs-3"></i>
                </div>
                <h5 class="font-weight-bold text-dark mb-2">Manajemen Stok Gudang</h5>
                <p class="text-secondary text-sm mb-4 flex-grow-1">
                    Pantau jumlah unit fisik produk di gudang, update ketersediaan stok, dan tandai produk yang stoknya habis agar tidak dipesan berlebih.
                </p>
                <div>
                    <a href="<?= BASEURL; ?>/operatorproduct" class="btn btn-primary-corp w-75 py-2">
                        Buka Katalog Stok <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
