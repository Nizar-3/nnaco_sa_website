<div class="container-fluid py-3 px-4">
    <!-- Header Page Banner -->
    <div class="card shadow-xs border rounded-4 mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #774dd3 100%);">
        <div class="card-body p-4 text-white">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge bg-white bg-opacity-10 text-white border border-white border-opacity-10 py-1 px-3 text-xs font-weight-bold rounded-pill mb-2">
                        <i class="bi bi-person-badge me-1"></i> Pengaturan Akun
                    </span>
                    <h3 class="text-white font-weight-bold mb-1">Profil &amp; Keamanan Akun</h3>
                    <p class="text-white-50 text-sm mb-0">Kelola informasi pribadi, nomor kontak WhatsApp, dan perlindungan keamanan kata sandi Anda.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="<?= BASEURL; ?>/<?= strtolower($data['user']['role']); ?>" class="btn btn-sm btn-white-corp mb-0">
                        <i class="bi bi-speedometer2 me-1"></i> Dashboard <?= htmlspecialchars($data['user']['role']); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Notification -->
    <div class="row">
        <div class="col-12">
            <?php Flasher::flash(); ?>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: User Summary Card -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow-xs border rounded-4 bg-white text-center p-4">
                <div class="mx-auto mb-3 avatar rounded-circle bg-slate-900 text-white font-weight-bold shadow-sm d-flex align-items-center justify-content-center" style="width: 84px; height: 84px; font-size: 1.85rem; border: 3px solid #e2e8f0;">
                    <?php 
                        $names = explode(' ', trim($data['user']['name']));
                        $initials = strtoupper(substr($names[0], 0, 1) . (isset($names[1]) ? substr($names[1], 0, 1) : ''));
                        echo htmlspecialchars($initials);
                    ?>
                </div>

                <h5 class="font-weight-bold text-slate-900 mb-1"><?= htmlspecialchars($data['user']['name']); ?></h5>
                <p class="text-muted text-sm mb-2"><?= htmlspecialchars($data['user']['email']); ?></p>

                <div class="d-flex justify-content-center gap-2 mb-3">
                    <?php
                        $roleBadgeClass = 'badge-corp-dark';
                        if ($data['user']['role'] === 'Customer') $roleBadgeClass = 'badge-corp-info';
                        elseif ($data['user']['role'] === 'Admin') $roleBadgeClass = 'badge-corp-dark';
                        elseif ($data['user']['role'] === 'Operator') $roleBadgeClass = 'badge-corp-warning';
                        elseif ($data['user']['role'] === 'Owner') $roleBadgeClass = 'badge-corp-success';
                    ?>
                    <span class="badge badge-corp <?= $roleBadgeClass; ?>">
                        <i class="bi bi-shield-lock me-1"></i> <?= htmlspecialchars($data['user']['role']); ?>
                    </span>
                    <span class="badge badge-corp badge-corp-success">
                        <i class="bi bi-check-circle me-1"></i> <?= htmlspecialchars($data['user']['status'] ?? 'Active'); ?>
                    </span>
                </div>

                <hr class="my-3 opacity-25">

                <div class="text-start text-sm">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted"><i class="bi bi-hash me-1"></i> ID Pengguna</span>
                        <span class="font-weight-bold text-dark">#<?= $data['user']['id']; ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted"><i class="bi bi-calendar3 me-1"></i> Terdaftar Sejak</span>
                        <span class="font-weight-semibold text-dark"><?= date('d M Y', strtotime($data['user']['created_at'])); ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted"><i class="bi bi-whatsapp me-1 text-success"></i> No. WhatsApp</span>
                        <?php if (!empty($data['user']['phone'])) : ?>
                            <span class="font-weight-semibold text-dark"><?= htmlspecialchars($data['user']['phone']); ?></span>
                        <?php else : ?>
                            <span class="text-muted text-xs fst-italic">Belum diisi</span>
                        <?php endif; ?>
                    </div>
                    <?php if (isset($data['total_orders'])): ?>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted"><i class="bi bi-bag-check me-1"></i> Total Belanja</span>
                        <span class="font-weight-bold text-primary"><?= $data['total_orders']; ?> Pesanan</span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="mt-4 pt-2">
                    <a href="<?= BASEURL; ?>/product" class="btn btn-sm btn-white-corp w-100 mb-0">
                        <i class="bi bi-shop me-1 text-primary"></i> Kunjungi Katalog Belanja
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Column: Edit Forms -->
        <div class="col-xl-8 col-lg-7">
            <!-- Form 1: Biodata / Informasi Akun -->
            <div class="card shadow-xs border rounded-4 bg-white p-4 mb-4">
                <div class="d-flex align-items-center mb-4">
                    <div class="icon icon-shape bg-slate-900 text-white rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-person-vcard fs-5"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold text-slate-900 mb-0">Informasi Pribadi</h6>
                        <small class="text-muted text-xs">Perbarui nama lengkap dan alamat email yang digunakan untuk login.</small>
                    </div>
                </div>

                <form action="<?= BASEURL; ?>/profile/update" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label text-xs font-weight-bold text-slate-700">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control corp-search-input" id="name" name="name" value="<?= htmlspecialchars($data['user']['name']); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label text-xs font-weight-bold text-slate-700">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control corp-search-input" id="email" name="email" value="<?= htmlspecialchars($data['user']['email']); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-xs font-weight-bold text-slate-700">Peran Akun (Role)</label>
                            <input type="text" class="form-control corp-search-input bg-gray-100 text-muted" value="<?= htmlspecialchars($data['user']['role']); ?>" readonly>
                            <small class="text-muted text-xxs">*Hak akses role ditentukan oleh administrator sistem.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-xs font-weight-bold text-slate-700">Status Akun</label>
                            <input type="text" class="form-control corp-search-input bg-gray-100 text-muted" value="<?= htmlspecialchars($data['user']['status'] ?? 'Active'); ?>" readonly>
                        </div>
                    </div>

                    <div class="mt-4 pt-2 text-end">
                        <button type="submit" class="btn btn-sm btn-dark-corp mb-0">
                            <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>

            <!-- Form: Preferensi Tema & Tampilan -->
            <div class="card shadow-xs border rounded-4 bg-white p-4 mb-4" id="themePreferencesCard">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center">
                        <div class="icon icon-shape bg-primary text-white rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                            <i class="bi bi-palette-fill fs-5"></i>
                        </div>
                        <div>
                            <h6 class="font-weight-bold text-slate-900 mb-0">Preferensi Tampilan &amp; Tema Website</h6>
                            <small class="text-muted text-xs">Pilih mode tampilan website yang paling nyaman untuk mata Anda.</small>
                        </div>
                    </div>
                    <span class="badge bg-primary-light text-primary border border-primary border-opacity-20 px-3 py-1 rounded-pill text-xs fw-bold d-none d-sm-inline-block">
                        Aktif: <span class="theme-current-label">Ikuti Sistem</span>
                    </span>
                </div>

                <?php 
                    $userTheme = $data['user']['theme_preference'] ?? 'system';
                ?>
                <div class="row g-3">
                    <!-- Light Mode Card -->
                    <div class="col-md-4">
                        <div class="theme-card-option <?= $userTheme === 'light' ? 'active' : ''; ?>" data-set-theme="light" data-theme-value="light">
                            <div class="theme-preview-box preview-light">
                                <div class="mock-sidebar"></div>
                                <div class="mock-header"></div>
                                <div class="mock-card"></div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-bold text-sm text-dark d-flex align-items-center gap-1">
                                    <i class="bi bi-sun-fill text-warning"></i> Mode Terang
                                </span>
                                <input type="radio" name="profile_theme_radio" value="light" <?= $userTheme === 'light' ? 'checked' : ''; ?> class="form-check-input mt-0 pointer-events-none">
                            </div>
                            <p class="text-muted text-xs mb-0">Latar cerah bersih dengan kontras optimal untuk penggunaan di siang hari.</p>
                        </div>
                    </div>

                    <!-- Dark Mode Card -->
                    <div class="col-md-4">
                        <div class="theme-card-option <?= $userTheme === 'dark' ? 'active' : ''; ?>" data-set-theme="dark" data-theme-value="dark">
                            <div class="theme-preview-box preview-dark">
                                <div class="mock-sidebar"></div>
                                <div class="mock-header"></div>
                                <div class="mock-card"></div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-bold text-sm text-dark d-flex align-items-center gap-1">
                                    <i class="bi bi-moon-stars-fill text-primary"></i> Mode Gelap
                                </span>
                                <input type="radio" name="profile_theme_radio" value="dark" <?= $userTheme === 'dark' ? 'checked' : ''; ?> class="form-check-input mt-0 pointer-events-none">
                            </div>
                            <p class="text-muted text-xs mb-0">Nuansa charcoal slate lembut, elegan dan nyaman di mata saat minim cahaya.</p>
                        </div>
                    </div>

                    <!-- System Mode Card -->
                    <div class="col-md-4">
                        <div class="theme-card-option <?= $userTheme === 'system' ? 'active' : ''; ?>" data-set-theme="system" data-theme-value="system">
                            <div class="theme-preview-box preview-system">
                                <span class="mock-center-badge"><i class="bi bi-display me-1"></i> Auto</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-bold text-sm text-dark d-flex align-items-center gap-1">
                                    <i class="bi bi-display text-info"></i> Ikuti Sistem
                                </span>
                                <input type="radio" name="profile_theme_radio" value="system" <?= $userTheme === 'system' ? 'checked' : ''; ?> class="form-check-input mt-0 pointer-events-none">
                            </div>
                            <p class="text-muted text-xs mb-0">Otomatis beralih mengikuti pengaturan tampilan perangkat (OS) Anda.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-3 pt-2 text-muted text-xxs d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle text-primary"></i>
                    <span>Pilihan tema diterapkan secara instan dan otomatis disinkronkan ke akun Anda di database.</span>
                </div>
            </div>

            <!-- Form 2: Keamanan & Ganti Password -->
            <div class="card shadow-xs border rounded-4 bg-white p-4">
                <div class="d-flex align-items-center mb-4">
                    <div class="icon icon-shape bg-primary text-white rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-shield-lock-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold text-slate-900 mb-0">Keamanan &amp; Ganti Password</h6>
                        <small class="text-muted text-xs">Pastikan Anda menggunakan kata sandi unik yang kuat untuk melindungi akun.</small>
                    </div>
                </div>

                <form action="<?= BASEURL; ?>/profile/updatePassword" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">

                    <div class="mb-3">
                        <label for="current_password" class="form-label text-xs font-weight-bold text-slate-700">Password Saat Ini <span class="text-danger">*</span></label>
                        <input type="password" class="form-control corp-search-input" id="current_password" name="current_password" placeholder="Masukkan password saat ini" required>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="new_password" class="form-label text-xs font-weight-bold text-slate-700">Password Baru <span class="text-danger">*</span></label>
                            <input type="password" class="form-control corp-search-input" id="new_password" name="new_password" placeholder="Minimal 6 karakter" required minlength="6">
                        </div>

                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label text-xs font-weight-bold text-slate-700">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <input type="password" class="form-control corp-search-input" id="confirm_password" name="confirm_password" placeholder="Ulangi password baru" required minlength="6">
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-sm btn-primary-corp mb-0">
                            <i class="bi bi-key me-1"></i> Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
