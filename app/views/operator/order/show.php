<!-- Corporate UI Dashboard: Operator Order Detail -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-md-flex align-items-center justify-content-between">
            <div class="mb-3 mb-md-0">
                <h3 class="font-weight-bold text-dark mb-1">Proses Pesanan #<?= str_pad($data['order']['id'], 5, '0', STR_PAD_LEFT); ?></h3>
                <p class="text-secondary text-sm mb-0">Rincian produk yang harus disiapkan dan diperbarui status alur pengiriman.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASEURL; ?>/operatororder" class="btn btn-sm btn-white-corp mb-0 d-inline-flex align-items-center">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left: Customer & Products -->
    <div class="col-lg-8">
        <!-- Customer Info Card -->
        <div class="card shadow-xs border mb-4">
            <div class="card-header border-bottom">
                <h6 class="font-weight-semibold text-dark mb-0">Informasi Pemesan</h6>
                <p class="text-xs text-secondary mb-0">Rincian data pelanggan</p>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm rounded-circle bg-slate-900 text-white me-3 d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                                <?= strtoupper(substr($data['order']['customer_name'] ?? 'P', 0, 1)); ?>
                            </div>
                            <div>
                                <p class="text-xs text-secondary font-weight-semibold mb-0">Nama Pelanggan</p>
                                <h6 class="text-sm font-weight-bold text-dark mb-0"><?= htmlspecialchars($data['order']['customer_name']); ?></h6>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="icon icon-shape bg-gray-100 text-dark rounded-2 me-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <div>
                                <p class="text-xs text-secondary font-weight-semibold mb-0">Email</p>
                                <h6 class="text-sm font-weight-bold text-dark mb-0"><?= htmlspecialchars($data['order']['customer_email']); ?></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 border-start ps-md-4">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-shape bg-gray-100 text-dark rounded-2 me-3 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="bi bi-calendar-event"></i>
                            </div>
                            <div>
                                <p class="text-xs text-secondary font-weight-semibold mb-0">Waktu Transaksi</p>
                                <h6 class="text-sm font-weight-bold text-dark mb-0"><?= date('d F Y', strtotime($data['order']['created_at'])); ?></h6>
                                <span class="text-xxs text-muted"><i class="bi bi-clock me-1"></i><?= date('H:i:s', strtotime($data['order']['created_at'])); ?> WIB</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Packing List Table Card -->
        <div class="card shadow-xs border">
            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="font-weight-semibold text-dark mb-0">Daftar Barang untuk Dikemas (Packing List)</h6>
                    <p class="text-xs text-secondary mb-0">Pastikan kuantitas fisik sesuai sebelum dikirim</p>
                </div>
            </div>
            <div class="card-body px-0 py-0">
                <div class="table-responsive">
                    <table class="table table-corporate align-items-center mb-0">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-center pe-4" width="140">Kuantitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['order_details'] as $item) : ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon icon-shape bg-gray-100 text-dark rounded-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                <i class="bi bi-box-seam"></i>
                                            </div>
                                            <span class="fw-bold text-dark text-sm"><?= htmlspecialchars($item['product_name'] ?? 'Produk'); ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center pe-4">
                                        <span class="badge badge-corp badge-corp-dark fs-6 px-3 py-1"><?= $item['quantity']; ?> pcs</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Update Status Workflow -->
    <div class="col-lg-4">
        <div class="card shadow-xs border sticky-top" style="top: 20px;">
            <div class="card-header border-bottom">
                <h6 class="font-weight-semibold text-dark mb-0">Alur Status Pesanan</h6>
                <p class="text-xs text-secondary mb-0">Perbarui tahapan pemrosesan paket</p>
            </div>
            <div class="card-body p-4">
                <?php 
                $statusNow = strtoupper($data['order']['status']);
                $isFinal = ($statusNow === 'SELESAI' || $statusNow === 'DIBATALKAN');
                ?>

                <!-- Current Status Pill -->
                <div class="mb-4 text-center p-3 bg-gray-100 rounded-3 border">
                    <p class="text-xs text-secondary font-weight-semibold mb-1 text-uppercase">Status Saat Ini</p>
                    <?php
                    $badge = 'badge-corp-dark';
                    if ($statusNow === 'SELESAI') $badge = 'badge-corp-success';
                    elseif ($statusNow === 'MENUNGGU PEMBAYARAN') $badge = 'badge-corp-warning';
                    elseif (in_array($statusNow, ['DIPROSES', 'DIKEMAS', 'DIKIRIM'])) $badge = 'badge-corp-info';
                    elseif ($statusNow === 'DIBATALKAN') $badge = 'badge-corp-danger';
                    ?>
                    <span class="badge badge-corp <?= $badge; ?> fs-6 py-2 px-3"><?= htmlspecialchars($statusNow); ?></span>
                </div>

                <?php if (!$isFinal): ?>
                    <form action="<?= BASEURL; ?>/operatororder/updateStatus/<?= $data['order']['id']; ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                        
                        <div class="mb-3">
                            <label for="status" class="form-label text-xs font-weight-semibold text-dark">Ubah Status Menjadi:</label>
                            <select class="form-select corp-search-input" id="status" name="status" required>
                                <option value="MENUNGGU PEMBAYARAN" <?= ($statusNow == 'MENUNGGU PEMBAYARAN') ? 'selected' : ''; ?>>MENUNGGU PEMBAYARAN</option>
                                <option value="DIPROSES" <?= ($statusNow == 'DIPROSES') ? 'selected' : ''; ?>>DIPROSES</option>
                                <option value="DIKEMAS" <?= ($statusNow == 'DIKEMAS') ? 'selected' : ''; ?>>DIKEMAS</option>
                                <option value="DIKIRIM" <?= ($statusNow == 'DIKIRIM') ? 'selected' : ''; ?>>DIKIRIM</option>
                                <option value="SELESAI" <?= ($statusNow == 'SELESAI') ? 'selected' : ''; ?>>SELESAI</option>
                                <option value="DIBATALKAN" <?= ($statusNow == 'DIBATALKAN') ? 'selected' : ''; ?>>DIBATALKAN</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-dark-corp w-100 py-2 font-weight-bold" onclick="return confirm('Simpan status baru untuk pesanan ini?');">
                            <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan Status
                        </button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning border-0 p-3 rounded-3 mb-0 text-xs text-secondary">
                        <i class="bi bi-shield-lock me-1 text-warning"></i> Pesanan ini telah berada di status final (Selesai/Dibatalkan). Hubungi Administrator jika perlu melakukan revisi.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
