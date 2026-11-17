<div class="container-fluid py-3 px-4">
    <!-- Header Banner -->
    <div class="card shadow-xs border rounded-4 mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #774dd3 100%);">
        <div class="card-body p-4 text-white">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-10 py-1 px-3 text-xs font-weight-bold rounded-pill mb-2">
                        <i class="bi bi-clock-history me-1"></i> Transaksi Belanja
                    </span>
                    <h3 class="text-white font-weight-bold mb-1">Riwayat Pesanan Saya</h3>
                    <p class="text-white-50 text-sm mb-0">Pantau status konfirmasi, pengemasan, dan pengiriman paket belanjaan Anda secara real-time.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="<?= BASEURL; ?>/product" class="btn btn-sm btn-white-corp mb-0 me-2">
                        <i class="bi bi-bag-plus me-1 text-primary"></i> Belanja Lagi
                    </a>
                    <a href="<?= BASEURL; ?>/customer" class="btn btn-sm btn-dark-corp mb-0">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Message -->
    <div class="row">
        <div class="col-12">
            <?php Flasher::flash(); ?>
        </div>
    </div>

    <div class="ajax-table-wrapper">
        <!-- Search & Filter Card -->
        <div class="card shadow-xs border rounded-4 mb-4 bg-white">
            <div class="card-body p-3">
                <form action="<?= BASEURL; ?>/customer/history" method="GET" class="ajax-filter-form row g-2 align-items-center">
                    <div class="col-md-6 col-lg-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" name="q" class="form-control border-start-0 ps-0 corp-search-input" placeholder="Cari No. Pesanan (#ORD-00001)..." value="<?= htmlspecialchars($data['keyword'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="col-md-4 col-lg-4">
                        <select name="status" class="form-select corp-search-input text-sm">
                            <option value="">Semua Status Pesanan</option>
                            <option value="Menunggu Pembayaran" <?= (($data['statusFilter'] ?? '') == 'Menunggu Pembayaran') ? 'selected' : ''; ?>>Menunggu Pembayaran</option>
                            <option value="Diproses" <?= (($data['statusFilter'] ?? '') == 'Diproses') ? 'selected' : ''; ?>>Diproses</option>
                            <option value="Dikemas" <?= (($data['statusFilter'] ?? '') == 'Dikemas') ? 'selected' : ''; ?>>Dikemas</option>
                            <option value="Dikirim" <?= (($data['statusFilter'] ?? '') == 'Dikirim') ? 'selected' : ''; ?>>Dikirim</option>
                            <option value="Selesai" <?= (($data['statusFilter'] ?? '') == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                            <option value="Dibatalkan" <?= (($data['statusFilter'] ?? '') == 'Dibatalkan') ? 'selected' : ''; ?>>Dibatalkan</option>
                        </select>
                    </div>
                    <div class="col-auto d-flex align-items-center gap-1">
                        <button type="submit" class="btn btn-sm btn-dark-corp mb-0">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <a href="<?= BASEURL; ?>/customer/history" class="btn btn-sm btn-white-corp mb-0 btn-reset-filter" data-ajax-reset>
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Orders Table Card -->
        <div class="card shadow-xs border rounded-4 overflow-hidden bg-white">
            <div class="card-header border-bottom py-3 px-4 d-flex justify-content-between align-items-center bg-white">
                <div>
                    <h6 class="font-weight-bold text-slate-900 mb-0">Daftar Transaksi</h6>
                    <small class="text-muted text-xs">Riwayat pemesanan yang tercatat di akun Anda</small>
                </div>
                <div>
                    <span class="badge badge-corp badge-corp-dark ajax-count-badge">Total <?= count($data['orders'] ?? []); ?> Pesanan</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive ajax-table-content position-relative">
                    <table class="table table-corporate align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">No. Pesanan</th>
                                <th>Tanggal &amp; Waktu</th>
                                <th>Total Pembayaran</th>
                                <th>Status Pesanan</th>
                                <th>Status Bayar</th>
                                <th class="text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($data['orders'])) : ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-receipt fs-1 d-block mb-2 text-secondary"></i>
                                        Tidak ada data pesanan yang sesuai dengan filter pencarian.
                                    </td>
                                </tr>
                            <?php else : ?>
                                <?php foreach ($data['orders'] as $order) : ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="icon icon-shape bg-gray-100 text-dark rounded-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-receipt text-xs"></i>
                                                </div>
                                                <span class="font-weight-bold text-dark text-sm">#ORD-<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="d-block text-sm font-weight-semibold text-slate-800"><?= date('d M Y', strtotime($order['created_at'])); ?></span>
                                            <small class="text-muted text-xs"><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($order['created_at'])); ?> WIB</small>
                                        </td>
                                        <td>
                                            <span class="font-weight-bold text-sm text-slate-900">Rp <?= number_format($order['total'], 0, ',', '.'); ?></span>
                                        </td>
                                        <td>
                                            <?php 
                                                $badgeClass = 'badge-corp-dark';
                                                $icon = 'bi-circle';
                                                $status = strtoupper($order['status']);
                                                if ($status == 'MENUNGGU PEMBAYARAN') { $badgeClass = 'badge-corp-warning'; $icon = 'bi-clock-history'; }
                                                elseif ($status == 'DIPROSES' || $status == 'DIKEMAS') { $badgeClass = 'badge-corp-info'; $icon = 'bi-box-seam'; }
                                                elseif ($status == 'DIKIRIM') { $badgeClass = 'badge-corp-info'; $icon = 'bi-truck'; }
                                                elseif ($status == 'SELESAI') { $badgeClass = 'badge-corp-success'; $icon = 'bi-check-circle'; }
                                                elseif ($status == 'DIBATALKAN') { $badgeClass = 'badge-corp-danger'; $icon = 'bi-x-circle'; }
                                            ?>
                                            <span class="badge badge-corp <?= $badgeClass; ?>">
                                                <i class="bi <?= $icon; ?> me-1"></i> <?= $status; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (isset($order['payment']) && $order['payment']): ?>
                                                <?php 
                                                    $pStatus = $order['payment']['status'];
                                                    $pBadge = 'badge-corp-dark';
                                                    if ($pStatus == 'PAID') $pBadge = 'badge-corp-success';
                                                    elseif ($pStatus == 'PENDING') $pBadge = 'badge-corp-warning';
                                                    elseif (in_array($pStatus, ['FAILED', 'EXPIRED', 'CANCELLED'])) $pBadge = 'badge-corp-danger';
                                                ?>
                                                <span class="badge badge-corp <?= $pBadge; ?>"><?= $pStatus; ?></span>
                                            <?php else: ?>
                                                <span class="badge badge-corp badge-corp-dark">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center pe-4">
                                            <a href="<?= BASEURL; ?>/customer/orderDetail/<?= $order['id']; ?>" class="btn btn-sm btn-white-corp mb-0">
                                                <i class="bi bi-eye me-1 text-primary"></i> Detail
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
    </div>
</div>
