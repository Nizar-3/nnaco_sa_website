<!-- Corporate UI Dashboard: Operator Order View -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-md-flex align-items-center justify-content-between">
            <div class="mb-3 mb-md-0">
                <h3 class="font-weight-bold text-dark mb-1">Manajemen Pesanan (Operator)</h3>
                <p class="text-secondary text-sm mb-0">Verifikasi pesanan, siapkan pengemasan, dan input resi pengiriman.</p>
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
            <form action="<?= BASEURL; ?>/operatororder" method="GET" class="ajax-filter-form row g-2 align-items-center">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0 corp-search-input" placeholder="Cari nama atau Order ID..." value="<?= htmlspecialchars($data['keyword'] ?? ''); ?>">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <select name="status" class="form-select corp-search-input text-sm">
                        <option value="">Semua Status Pesanan</option>
                        <option value="Menunggu Pembayaran" <?= ($data['statusFilter'] == 'Menunggu Pembayaran') ? 'selected' : ''; ?>>Menunggu Pembayaran</option>
                        <option value="Diproses" <?= ($data['statusFilter'] == 'Diproses') ? 'selected' : ''; ?>>Diproses</option>
                        <option value="Dikemas" <?= ($data['statusFilter'] == 'Dikemas') ? 'selected' : ''; ?>>Dikemas</option>
                        <option value="Dikirim" <?= ($data['statusFilter'] == 'Dikirim') ? 'selected' : ''; ?>>Dikirim</option>
                        <option value="Selesai" <?= ($data['statusFilter'] == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                        <option value="Dibatalkan" <?= ($data['statusFilter'] == 'Dibatalkan') ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                </div>
                <div class="col-auto d-flex align-items-center gap-1">
                    <button type="submit" class="btn btn-sm btn-dark-corp mb-0">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="<?= BASEURL; ?>/operatororder" class="btn btn-sm btn-white-corp mb-0 btn-reset-filter" data-ajax-reset>
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table Card -->
    <div class="card shadow-xs border">
        <div class="card-header border-bottom d-flex align-items-center justify-content-between">
            <div>
                <h6 class="font-weight-semibold text-dark mb-0">Daftar Tugas Pesanan</h6>
                <p class="text-xs text-secondary mb-0">Pesanan aktif yang memerlukan tindakan operasional</p>
            </div>
            <div>
                <span class="badge badge-corp badge-corp-dark ajax-count-badge">Total <?= $data['total_orders'] ?? count($data['orders'] ?? []); ?> Pesanan</span>
            </div>
        </div>
        <div class="card-body px-0 py-0">
            <div class="table-responsive ajax-table-content position-relative">
                <table class="table table-corporate align-items-center mb-0">
                    <thead>
                        <tr>
                            <th width="110" class="ps-4">No. Order</th>
                            <th>Waktu Transaksi</th>
                            <th>Pelanggan</th>
                            <th>Total Tagihan</th>
                            <th>Status Pesanan</th>
                            <th class="text-center pe-4" width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['orders'])) : ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-receipt-cutoff fs-1 d-block mb-2 text-secondary"></i>
                                    Tidak ada data pesanan yang perlu diproses.
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($data['orders'] as $order) : ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold text-dark text-sm">#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></span>
                                    </td>
                                    <td>
                                        <span class="text-xs text-secondary d-block"><?= date('d M Y', strtotime($order['created_at'])); ?></span>
                                        <span class="text-xxs text-muted"><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($order['created_at'])); ?> WIB</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm rounded-circle bg-slate-900 text-white me-2 d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                <?= strtoupper(substr($order['customer_name'] ?? 'P', 0, 1)); ?>
                                            </div>
                                            <span class="fw-semibold text-dark text-sm"><?= htmlspecialchars($order['customer_name'] ?? 'Pelanggan'); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark text-sm">Rp <?= number_format($order['total'], 0, ',', '.'); ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $st = $order['status'];
                                        $badge = 'badge-corp-dark';
                                        if ($st === 'Selesai') $badge = 'badge-corp-success';
                                        elseif ($st === 'Menunggu Pembayaran') $badge = 'badge-corp-warning';
                                        elseif (in_array($st, ['Diproses', 'Dikemas', 'Dikirim'])) $badge = 'badge-corp-info';
                                        elseif ($st === 'Dibatalkan') $badge = 'badge-corp-danger';
                                        ?>
                                        <span class="badge badge-corp <?= $badge; ?>"><?= htmlspecialchars($st); ?></span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <a href="<?= BASEURL; ?>/operatororder/show/<?= $order['id']; ?>" class="btn btn-sm btn-white-corp py-1 px-3 mb-0 text-xs">
                                            <i class="bi bi-pencil me-1"></i> Proses
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
            if (!empty($data['statusFilter'])) $filterParams['status'] = $data['statusFilter'];
            $queryString = !empty($filterParams) ? '&' . http_build_query($filterParams) : '';
        ?>
            <div class="d-flex justify-content-center mt-4">
                <ul class="pagination gap-1">
                    <?php for ($i = 1; $i <= $data['total_pages']; $i++) : ?>
                        <li class="page-item <?= ($i == $data['current_page']) ? 'active' : ''; ?>">
                            <a class="btn btn-sm <?= ($i == $data['current_page']) ? 'btn-dark-corp' : 'btn-white-corp'; ?> mb-0 px-3" href="<?= BASEURL; ?>/operatororder?page=<?= $i . $queryString; ?>"><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>
