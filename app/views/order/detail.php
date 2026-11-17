<div class="container-fluid py-3 px-4">
    <!-- Header Banner -->
    <div class="card shadow-xs border rounded-4 mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #774dd3 100%);">
        <div class="card-body p-4 text-white">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-10 py-1 px-3 text-xs font-weight-bold rounded-pill mb-2">
                        <i class="bi bi-receipt me-1"></i> Detail Transaksi
                    </span>
                    <h3 class="text-white font-weight-bold mb-1">Pesanan #ORD-<?= str_pad($data['order']['id'], 5, '0', STR_PAD_LEFT); ?></h3>
                    <p class="text-white-50 text-sm mb-0">Rincian produk yang dipesan, informasi pembayaran, dan status pemrosesan paket.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="<?= BASEURL; ?>/customer/history" class="btn btn-sm btn-white-corp mb-0">
                        <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Products Table -->
        <div class="col-lg-8">
            <div class="card shadow-xs border rounded-4 overflow-hidden bg-white mb-4">
                <div class="card-header border-bottom py-3 px-4 bg-white d-flex align-items-center">
                    <div class="icon icon-shape bg-slate-900 text-white rounded-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="bi bi-box-seam text-xs"></i>
                    </div>
                    <h6 class="font-weight-bold text-slate-900 mb-0">Daftar Produk Dipesan</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-corporate align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Item Produk</th>
                                    <th>Harga Satuan</th>
                                    <th class="text-center">Kuantitas</th>
                                    <th class="text-end pe-4">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['order_details'] as $item) : ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="icon icon-shape bg-gray-100 text-dark rounded-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-bag text-sm"></i>
                                                </div>
                                                <span class="font-weight-bold text-slate-800 text-sm"><?= htmlspecialchars($item['product_name'] ?? 'Aksesoris'); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-sm text-slate-600 font-weight-semibold">Rp <?= number_format($item['price'], 0, ',', '.'); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-corp badge-corp-dark"><?= $item['quantity']; ?>x</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="font-weight-bold text-sm text-slate-900">Rp <?= number_format($item['subtotal'], 0, ',', '.'); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order Summary Card -->
        <div class="col-lg-4">
            <div class="card shadow-xs border rounded-4 bg-white p-4 sticky-top" style="top: 20px;">
                <div class="d-flex align-items-center mb-4">
                    <div class="icon icon-shape bg-primary text-white rounded-2 me-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="bi bi-receipt-cutoff text-sm"></i>
                    </div>
                    <h6 class="font-weight-bold text-slate-900 mb-0">Ringkasan Pesanan</h6>
                </div>
                
                <div class="d-flex justify-content-between mb-3 text-sm">
                    <span class="text-muted"><i class="bi bi-calendar-event me-2"></i>Waktu Pemesanan</span>
                    <span class="font-weight-semibold text-slate-800 text-end"><?= date('d M Y, H:i', strtotime($data['order']['order_date'])); ?> WIB</span>
                </div>
                
                <div class="d-flex justify-content-between mb-3 text-sm align-items-center">
                    <span class="text-muted"><i class="bi bi-info-circle me-2"></i>Status Pesanan</span>
                    <?php 
                        $badgeClass = 'badge-corp-dark';
                        $icon = 'bi-circle';
                        $status = strtoupper($data['order']['status']);
                        if ($status == 'MENUNGGU PEMBAYARAN') { $badgeClass = 'badge-corp-warning'; $icon = 'bi-clock-history'; }
                        elseif ($status == 'DIPROSES' || $status == 'DIKEMAS') { $badgeClass = 'badge-corp-info'; $icon = 'bi-box-seam'; }
                        elseif ($status == 'DIKIRIM') { $badgeClass = 'badge-corp-info'; $icon = 'bi-truck'; }
                        elseif ($status == 'SELESAI') { $badgeClass = 'badge-corp-success'; $icon = 'bi-check-circle'; }
                        elseif ($status == 'DIBATALKAN') { $badgeClass = 'badge-corp-danger'; $icon = 'bi-x-circle'; }
                    ?>
                    <span class="badge badge-corp <?= $badgeClass; ?>">
                        <i class="bi <?= $icon; ?> me-1"></i> <?= $status; ?>
                    </span>
                </div>

                <?php if (isset($data['payment']) && $data['payment']): ?>
                    <div class="d-flex justify-content-between mb-3 text-sm align-items-center">
                        <span class="text-muted"><i class="bi bi-credit-card me-2"></i>Status Bayar</span>
                        <?php 
                            $pStatus = $data['payment']['status'];
                            $pBadge = 'badge-corp-dark';
                            if ($pStatus == 'PAID') $pBadge = 'badge-corp-success';
                            elseif ($pStatus == 'PENDING') $pBadge = 'badge-corp-warning';
                            elseif (in_array($pStatus, ['FAILED', 'EXPIRED', 'CANCELLED'])) $pBadge = 'badge-corp-danger';
                        ?>
                        <span class="badge badge-corp <?= $pBadge; ?>"><?= $pStatus; ?></span>
                    </div>
                <?php endif; ?>
                
                <hr class="my-3 opacity-25">
                
                <div class="d-flex justify-content-between align-items-center bg-gray-100 p-3 rounded-3 mb-4">
                    <span class="font-weight-bold text-slate-700 text-sm">Total Bayar</span>
                    <h5 class="font-weight-bold text-slate-900 mb-0">Rp <?= number_format($data['order']['total'], 0, ',', '.'); ?></h5>
                </div>

                <?php if (isset($data['payment']) && $data['payment']): ?>
                    <?php if ($data['payment']['status'] === 'PENDING'): ?>
                        <a href="<?= $data['payment']['payment_url']; ?>" class="btn btn-sm btn-dark-corp w-100 py-2 mb-2">
                            <i class="bi bi-credit-card me-1"></i> Lanjutkan Pembayaran
                        </a>
                    <?php elseif (in_array($data['payment']['status'], ['FAILED', 'EXPIRED'])): ?>
                        <div class="alert alert-danger text-center text-xs p-2 rounded-3 mb-0">
                            Pembayaran online kadaluarsa atau gagal. Silakan bayar tunai (Cash) saat barang sampai.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <a href="<?= BASEURL; ?>/customer/history" class="btn btn-sm btn-white-corp w-100 mb-0">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
                </a>
            </div>
        </div>
    </div>
</div>
