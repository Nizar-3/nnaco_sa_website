<!-- Blog Hero Header (Corporate UI Style) -->
<div class="py-5 mb-5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #774dd3 100%);">
    <div class="container py-4 text-center text-white">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mb-3 p-0 bg-transparent">
                <li class="breadcrumb-item"><a href="<?= BASEURL; ?>" class="text-xs text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-xs font-weight-bold text-white" aria-current="page">Blog &amp; Inspirasi</li>
            </ol>
        </nav>
        <span class="badge badge-corp badge-corp-warning mb-2">ARTIKEL &amp; TREN</span>
        <h1 class="display-5 font-weight-bolder text-white mb-2" style="font-family: 'Noto Sans', sans-serif;">
            Inspirasi &amp; Tips Aksesoris NNACO
        </h1>
        <p class="text-white-50 text-sm mx-auto" style="max-width: 600px;">
            Temukan panduan gaya, tips merawat perhiasan agar selalu berkilau, hingga tren aksesoris terkini yang dirangkum khusus untuk Anda.
        </p>
    </div>
</div>

<!-- Blog Articles Content -->
<div class="container mb-5">
    <!-- Featured Article (First article) -->
    <?php if (!empty($data['articles'])): $featured = $data['articles'][0]; ?>
        <div class="card shadow-xs border rounded-4 overflow-hidden mb-5 bg-white">
            <div class="row g-0 align-items-center">
                <div class="col-lg-6">
                    <img src="<?= $featured['image']; ?>" class="img-fluid w-100" style="height: 360px; object-fit: cover;" alt="<?= htmlspecialchars($featured['title']); ?>">
                </div>
                <div class="col-lg-6 p-4 p-md-5">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge badge-corp badge-corp-dark"><?= htmlspecialchars($featured['category']); ?></span>
                        <span class="text-muted text-xs">&bull;</span>
                        <span class="text-muted text-xs"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($featured['read_time']); ?></span>
                        <span class="text-muted text-xs">&bull;</span>
                        <span class="text-muted text-xs"><?= htmlspecialchars($featured['date']); ?></span>
                    </div>
                    <h3 class="font-weight-bold mb-3 text-slate-900" style="font-family: 'Noto Sans', sans-serif;">
                        <?= htmlspecialchars($featured['title']); ?>
                    </h3>
                    <p class="text-muted text-sm mb-4" style="line-height: 1.7;">
                        <?= htmlspecialchars($featured['excerpt']); ?>
                    </p>
                    <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                        <div class="d-flex align-items-center">
                            <div class="icon icon-shape bg-gray-100 text-dark rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                <i class="bi bi-person text-xs"></i>
                            </div>
                            <span class="text-xs font-weight-semibold text-slate-800"><?= htmlspecialchars($featured['author']); ?></span>
                        </div>
                        <a href="<?= BASEURL; ?>/product" class="btn btn-sm btn-white-corp mb-0">
                            Lihat Produk Terkait <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Articles Grid -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="font-weight-bold text-slate-900 mb-0">Semua Artikel &amp; Panduan</h5>
            <small class="text-muted text-xs"><?= count($data['articles']); ?> artikel informatif tersedia</small>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach ($data['articles'] as $index => $article): ?>
            <?php if ($index === 0 && count($data['articles']) > 1) continue; // skip featured if already shown ?>
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-xs border rounded-4 bg-white h-100 overflow-hidden d-flex flex-column">
                    <div class="position-relative">
                        <img src="<?= $article['image']; ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?= htmlspecialchars($article['title']); ?>">
                        <span class="badge badge-corp badge-corp-dark position-absolute top-0 start-0 m-3"><?= htmlspecialchars($article['category']); ?></span>
                    </div>
                    <div class="card-body p-4 d-flex flex-column flex-grow-1 text-start">
                        <div class="text-muted text-xxs mb-2 d-flex align-items-center gap-2">
                            <span><i class="bi bi-calendar3 me-1"></i><?= htmlspecialchars($article['date']); ?></span>
                            <span>&bull;</span>
                            <span><i class="bi bi-clock me-1"></i><?= htmlspecialchars($article['read_time']); ?></span>
                        </div>
                        <h6 class="font-weight-bold text-slate-900 mb-2">
                            <?= htmlspecialchars($article['title']); ?>
                        </h6>
                        <p class="text-muted text-xs mb-3 flex-grow-1" style="line-height: 1.6;">
                            <?= htmlspecialchars($article['excerpt']); ?>
                        </p>
                        <div class="pt-3 border-top mt-auto d-flex justify-content-between align-items-center">
                            <small class="text-muted text-xxs"><?= htmlspecialchars($article['author']); ?></small>
                            <a href="<?= BASEURL; ?>/product" class="text-xs font-weight-bold text-primary text-decoration-none">
                                Baca Tips <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
