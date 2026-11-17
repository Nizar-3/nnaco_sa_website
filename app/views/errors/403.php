<div class="container py-5 my-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5 text-center">
            <?php if (class_exists('Flasher')) Flasher::flash(); ?>
            <div class="card shadow-xs border rounded-4 p-4 p-md-5">
                <div class="icon-shape bg-light text-danger shadow-xs rounded-circle mx-auto mb-4" style="width: 72px; height: 72px; display: inline-flex; align-items: center; justify-content: center;">
                    <i class="bi bi-shield-lock-fill fs-2 text-danger"></i>
                </div>
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-1 rounded-pill text-xs fw-bold mb-2 mx-auto">Akses Ditolak</span>
                <h3 class="fw-bold text-dark mb-2">403 Forbidden</h3>
                <p class="text-secondary text-sm mb-4">Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.</p>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <?php if (isset($_SESSION['user_id'])) : ?>
                        <a href="<?= BASEURL; ?>/<?= strtolower($_SESSION['user_role'] ?? 'customer'); ?>" class="btn btn-dark-corp py-2 px-3 font-weight-bold">
                            <i class="bi bi-speedometer2 me-1"></i> Ke Dashboard
                        </a>
                    <?php endif; ?>
                    <a href="<?= BASEURL; ?>" class="btn btn-white-corp py-2 px-3 font-weight-bold">
                        <i class="bi bi-house-door me-1"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
