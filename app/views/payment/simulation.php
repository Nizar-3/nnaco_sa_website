<div class="container py-5 my-3">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-xs border rounded-4 overflow-hidden">
                <div class="p-4 text-center border-bottom" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                    <div class="icon-shape bg-white text-dark shadow-xs rounded-circle mx-auto mb-3" style="width: 52px; height: 52px; display: inline-flex; align-items: center; justify-content: center;">
                        <i class="bi bi-credit-card-2-front-fill fs-4 text-primary"></i>
                    </div>
                    <h4 class="mb-1 fw-bold text-white">Payment Gateway Simulator</h4>
                    <span class="badge bg-white bg-opacity-25 text-white fw-medium px-3 py-1 rounded-pill text-xs">Sandbox Environment / Development Mode</span>
                </div>
                <div class="card-body p-4 p-md-5">
                    <?php Flasher::flash(); ?>

                    <div class="text-center mb-4 p-3 rounded-3" style="background-color: #f8fafc; border: 1px dashed #cbd5e1;">
                        <span class="text-xs text-uppercase font-weight-semibold text-secondary d-block mb-1">Nomor Pesanan</span>
                        <h3 class="fw-bold text-dark mb-0 font-monospace">#NNACO-<?= str_pad($data['order']['id'], 5, '0', STR_PAD_LEFT); ?></h3>
                    </div>

                    <div class="card shadow-none border rounded-3 mb-4">
                        <ul class="list-group list-group-flush rounded-3">
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-3">
                                <span class="text-secondary text-sm">Metode Pembayaran</span>
                                <span class="badge bg-light text-dark border px-2 py-1 text-uppercase fw-semibold"><?= htmlspecialchars($data['payment']['payment_method']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-3">
                                <span class="text-secondary text-sm">Status Pembayaran</span>
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-2 py-1 fw-semibold"><?= htmlspecialchars($data['payment']['status']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-3 bg-light bg-opacity-50">
                                <span class="text-dark font-weight-semibold text-sm">Total Tagihan</span>
                                <span class="fw-bold fs-5 text-dark">Rp <?= number_format($data['payment']['amount'], 0, ',', '.'); ?></span>
                            </li>
                        </ul>
                    </div>

                    <?php if ($data['payment']['status'] === 'PENDING'): ?>
                        <div class="d-grid gap-2">
                            <button onclick="simulateCallback('PAID')" class="btn btn-dark-corp py-2 font-weight-bold">
                                <i class="bi bi-check-circle-fill text-success me-2"></i> Konfirmasi Pembayaran Berhasil (PAID)
                            </button>
                            <button onclick="simulateCallback('FAILED')" class="btn btn-outline-danger py-2 font-weight-semibold">
                                <i class="bi bi-x-circle-fill me-2"></i> Simulasi Transaksi Gagal (FAILED)
                            </button>
                            <button onclick="simulateCallback('EXPIRED')" class="btn btn-white-corp py-2 font-weight-semibold">
                                <i class="bi bi-clock-history me-2"></i> Simulasi Kadaluarsa (EXPIRED)
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info border-0 shadow-xs text-center py-3">
                            <i class="bi bi-info-circle me-1"></i> Transaksi ini sudah disimulasikan: <strong><?= $data['payment']['status']; ?></strong>
                        </div>
                        <div class="d-grid mt-3">
                            <a href="<?= BASEURL; ?>/customer/history" class="btn btn-dark-corp py-2 font-weight-bold">
                                <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat Pesanan
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function simulateCallback(status) {
    if (!confirm('Kirim webhook status ' + status + ' ke sistem?')) return;

    const payload = {
        transaction_id: '<?= $data['payment']['transaction_id']; ?>',
        order_id: <?= $data['order']['id']; ?>,
        amount: <?= $data['payment']['amount']; ?>,
        status: status
    };

    fetch('<?= BASEURL; ?>/payment/callback', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'OK') {
            alert('Simulasi Callback Berhasil: ' + status);
            window.location.href = '<?= BASEURL; ?>/customer/history';
        } else {
            alert('Simulasi Gagal: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(err => {
        alert('Terjadi kesalahan jaringan.');
        console.error(err);
    });
}
</script>
