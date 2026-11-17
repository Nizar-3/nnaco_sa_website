<!-- Corporate UI Dashboard: Detail User -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-md-flex align-items-center justify-content-between">
            <div class="mb-3 mb-md-0">
                <h3 class="font-weight-bold text-dark mb-1">Detail Pengguna</h3>
                <p class="text-secondary text-sm mb-0">Informasi detail profil dan manajemen hak akses akun pengguna.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASEURL; ?>/adminuser" class="btn btn-sm btn-white-corp mb-0 d-inline-flex align-items-center">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- User Profile Card -->
    <div class="col-lg-6">
        <div class="card shadow-xs border h-100">
            <div class="card-header border-bottom">
                <h6 class="font-weight-semibold text-dark mb-0">Informasi Profil</h6>
                <p class="text-xs text-secondary mb-0">Rincian identitas akun pengguna</p>
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="avatar avatar-xl rounded-circle bg-slate-900 text-white d-inline-flex justify-content-center align-items-center shadow-xs mx-auto fw-bold" style="width: 80px; height: 80px; font-size: 2rem;">
                        <?= strtoupper(substr($data['user']['name'] ?? 'U', 0, 1)); ?>
                    </div>
                    <h5 class="font-weight-bold text-dark mt-3 mb-1"><?= htmlspecialchars($data['user']['name']); ?></h5>
                    <?php
                    $roleBadge = 'badge-corp-dark';
                    if ($data['user']['role'] === 'Admin') $roleBadge = 'badge-corp-danger';
                    elseif ($data['user']['role'] === 'Owner') $roleBadge = 'badge-corp-info';
                    elseif ($data['user']['role'] === 'Operator') $roleBadge = 'badge-corp-warning';
                    ?>
                    <span class="badge badge-corp <?= $roleBadge; ?>"><?= htmlspecialchars($data['user']['role']); ?></span>
                </div>

                <div class="bg-gray-100 p-3 rounded-3 border">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon icon-shape bg-white text-dark rounded-2 me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-hash"></i>
                        </div>
                        <div>
                            <p class="mb-0 text-xs text-secondary font-weight-semibold">ID Pengguna</p>
                            <p class="mb-0 text-sm font-weight-bold text-dark">#<?= $data['user']['id']; ?></p>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3">
                        <div class="icon icon-shape bg-white text-dark rounded-2 me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <div>
                            <p class="mb-0 text-xs text-secondary font-weight-semibold">Alamat Email</p>
                            <p class="mb-0 text-sm font-weight-bold text-dark"><?= htmlspecialchars($data['user']['email']); ?></p>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3">
                        <div class="icon icon-shape bg-white text-success rounded-2 me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-whatsapp"></i>
                        </div>
                        <div>
                            <p class="mb-0 text-xs text-secondary font-weight-semibold">No. HP / WhatsApp</p>
                            <?php if (!empty($data['user']['phone'])) : ?>
                                <p class="mb-0 text-sm font-weight-bold text-dark"><?= htmlspecialchars($data['user']['phone']); ?></p>
                            <?php else : ?>
                                <p class="mb-0 text-xs text-muted fst-italic">Belum didaftarkan</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <div class="icon icon-shape bg-white text-dark rounded-2 me-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <div>
                            <p class="mb-0 text-xs text-secondary font-weight-semibold">Tanggal Terdaftar</p>
                            <p class="mb-0 text-sm font-weight-bold text-dark"><?= date('d F Y, H:i', strtotime($data['user']['created_at'])); ?> WIB</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Role & Status Card -->
    <div class="col-lg-6">
        <div class="card shadow-xs border h-100">
            <div class="card-header border-bottom">
                <h6 class="font-weight-semibold text-dark mb-0">Ubah Role & Status Akun</h6>
                <p class="text-xs text-secondary mb-0">Pengaturan kewenangan dan aktivasi akun</p>
            </div>
            <div class="card-body p-4">
                <?php if ($data['user']['id'] == $_SESSION['user_id']) : ?>
                    <div class="alert alert-warning border-0 p-3 rounded-3 d-flex align-items-start mb-0">
                        <i class="bi bi-shield-exclamation fs-4 me-3 text-warning"></i>
                        <div>
                            <h6 class="font-weight-bold mb-1 text-dark">Aksi Dibatasi</h6>
                            <p class="text-xs mb-0 text-secondary">Anda sedang melihat akun Anda sendiri. Anda tidak dapat mengubah peran atau status akun Anda sendiri untuk mencegah hilangnya akses sistem.</p>
                        </div>
                    </div>
                <?php else : ?>
                    <form action="<?= BASEURL; ?>/adminuser/update/<?= $data['user']['id']; ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                        <input type="hidden" name="redirect_to" value="<?= BASEURL; ?>/adminuser/show/<?= $data['user']['id']; ?>">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label text-xs font-weight-semibold text-dark">Nama Lengkap</label>
                            <input type="text" class="form-control corp-search-input" id="name" name="name" value="<?= htmlspecialchars($data['user']['name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label text-xs font-weight-semibold text-dark">Email</label>
                            <input type="email" class="form-control corp-search-input" id="email" name="email" value="<?= htmlspecialchars($data['user']['email']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label text-xs font-weight-semibold text-dark">Nomor Telepon / WhatsApp</label>
                            <input type="text" class="form-control corp-search-input" id="phone" name="phone" value="<?= htmlspecialchars($data['user']['phone'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label text-xs font-weight-semibold text-dark">Peran (Role) Pengguna</label>
                            <select class="form-select corp-search-input" id="role" name="role" required>
                                <option value="Customer" <?= ($data['user']['role'] == 'Customer') ? 'selected' : ''; ?>>Customer (Pelanggan)</option>
                                <option value="Operator" <?= ($data['user']['role'] == 'Operator') ? 'selected' : ''; ?>>Operator (Gudang & Pesanan)</option>
                                <option value="Admin" <?= ($data['user']['role'] == 'Admin') ? 'selected' : ''; ?>>Admin (Administrator)</option>
                                <option value="Owner" <?= ($data['user']['role'] == 'Owner') ? 'selected' : ''; ?>>Owner (Pemilik Toko)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="status" class="form-label text-xs font-weight-semibold text-dark">Status Akun</label>
                            <select class="form-select corp-search-input" id="status" name="status" required>
                                <option value="Active" <?= ($data['user']['status'] == 'Active') ? 'selected' : ''; ?>>Aktif (Active)</option>
                                <option value="Inactive" <?= ($data['user']['status'] == 'Inactive') ? 'selected' : ''; ?>>Nonaktif (Inactive)</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-dark-corp w-100 py-2 font-weight-bold" onclick="return confirm('Apakah Anda yakin ingin menyimpan perubahan akses untuk user ini?');">
                            <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan Akses
                        </button>
                    </form>

                    <!-- Danger Zone: Delete User -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-sm font-weight-bold text-danger mb-0">Hapus Akun Pengguna</h6>
                                <p class="text-xxs text-secondary mb-0">Tindakan ini menghapus pengguna dan data terkait secara permanen.</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger mb-0" data-bs-toggle="modal" data-bs-target="#modalDeleteUserShow">
                                <i class="bi bi-trash-fill me-1"></i> Hapus Akun
                            </button>
                        </div>
                    </div>

                    <!-- Modal Konfirmasi Hapus Pengguna Show -->
                    <div class="modal fade" id="modalDeleteUserShow" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow-lg border-0 rounded-4">
                                <div class="modal-header border-bottom px-4 py-3 bg-light rounded-top-4">
                                    <h6 class="modal-title font-weight-bold text-danger mb-0">
                                        <i class="bi bi-trash-fill me-2"></i>Konfirmasi Hapus Pengguna
                                    </h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="<?= BASEURL; ?>/adminuser/delete/<?= $data['user']['id']; ?>" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                                    <input type="hidden" name="redirect_to" value="<?= BASEURL; ?>/adminuser">
                                    <div class="modal-body p-4 text-start">
                                        <p class="text-sm text-secondary mb-3">
                                            Yakin ingin menghapus akun pengguna <strong><?= htmlspecialchars($data['user']['name']); ?></strong> (#<?= $data['user']['id']; ?>)?
                                        </p>
                                        <div class="alert alert-danger text-xs mb-0 text-white border-0 py-2 rounded-3">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Seluruh data terkait akun ini akan dihapus secara permanen.
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
                                        <button type="button" class="btn btn-sm btn-white-corp mb-0" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-sm btn-danger mb-0"><i class="bi bi-trash-fill me-1"></i> Ya, Hapus Pengguna</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
