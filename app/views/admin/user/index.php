<!-- Corporate UI Dashboard: Manajemen User -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-md-flex align-items-center justify-content-between">
            <div class="mb-3 mb-md-0">
                <h3 class="font-weight-bold text-dark mb-1">Manajemen Pengguna</h3>
                <p class="text-secondary text-sm mb-0">Kelola master akun pengguna, hak akses peran (role), dan status pengguna toko.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASEURL; ?>/admin" class="btn btn-sm btn-white-corp mb-0 d-inline-flex align-items-center">
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
            <form action="<?= BASEURL; ?>/adminuser" method="GET" class="ajax-filter-form row g-2 align-items-center">
                <div class="col-md-5 col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0 corp-search-input" placeholder="Cari nama, email, atau HP..." value="<?= htmlspecialchars($data['keyword'] ?? ''); ?>">
                    </div>
                </div>

                <div class="col-md-3 col-lg-3">
                    <select name="role" class="form-select text-sm">
                        <option value="">Semua Peran (Role)</option>
                        <option value="Customer" <?= (($data['selected_role'] ?? '') === 'Customer') ? 'selected' : ''; ?>>Customer</option>
                        <option value="Operator" <?= (($data['selected_role'] ?? '') === 'Operator') ? 'selected' : ''; ?>>Operator</option>
                        <option value="Admin" <?= (($data['selected_role'] ?? '') === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="Owner" <?= (($data['selected_role'] ?? '') === 'Owner') ? 'selected' : ''; ?>>Owner</option>
                    </select>
                </div>

                <div class="col-md-3 col-lg-3">
                    <select name="status" class="form-select text-sm">
                        <option value="">Semua Status</option>
                        <option value="Active" <?= (($data['selected_status'] ?? '') === 'Active') ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?= (($data['selected_status'] ?? '') === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <div class="col-auto d-flex align-items-center gap-1">
                    <button type="submit" class="btn btn-sm btn-dark-corp mb-0">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="<?= BASEURL; ?>/adminuser" class="btn btn-sm btn-white-corp mb-0 btn-reset-filter" data-ajax-reset>
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="card shadow-xs border">
        <div class="card-header border-bottom d-flex align-items-center justify-content-between">
            <div>
                <h6 class="font-weight-semibold text-dark mb-0">Daftar Pengguna</h6>
                <p class="text-xs text-secondary mb-0">Master data pengguna sistem & customer</p>
            </div>
            <div>
                <span class="badge badge-corp badge-corp-dark ajax-count-badge">Total <?= $data['total_users'] ?? count($data['users'] ?? []); ?> Pengguna</span>
            </div>
        </div>
        <div class="card-body px-0 py-0">
            <div class="table-responsive ajax-table-content position-relative">
                <table class="table table-corporate align-items-center mb-0">
                    <thead>
                        <tr>
                            <th width="80" class="ps-4">ID</th>
                            <th>Pengguna</th>
                            <th>Email</th>
                            <th>No. HP / WA</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Tgl Terdaftar</th>
                            <th class="text-center pe-4" width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['users'])) : ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-person-x fs-1 d-block mb-2 text-secondary"></i>
                                    Tidak ada data pengguna yang sesuai dengan filter.
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($data['users'] as $user) : ?>
                                <tr>
                                    <td class="ps-4 text-muted fw-bold">#<?= $user['id']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm rounded-circle bg-slate-900 text-white me-2 d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 0.8rem;">
                                                <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
                                            </div>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold text-dark text-sm"><?= htmlspecialchars($user['name']); ?></span>
                                                <span class="text-xxs text-muted">ID: #<?= $user['id']; ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-sm text-secondary"><?= htmlspecialchars($user['email']); ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($user['phone'])) : ?>
                                            <span class="d-inline-flex align-items-center gap-1 text-sm text-dark font-weight-semibold">
                                                <i class="bi bi-whatsapp text-success"></i>
                                                <?= htmlspecialchars($user['phone']); ?>
                                            </span>
                                        <?php else : ?>
                                            <span class="text-muted text-xs fst-italic">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $roleBadge = 'badge-corp-dark';
                                        if ($user['role'] === 'Admin') $roleBadge = 'badge-corp-danger';
                                        elseif ($user['role'] === 'Owner') $roleBadge = 'badge-corp-info';
                                        elseif ($user['role'] === 'Operator') $roleBadge = 'badge-corp-warning';
                                        ?>
                                        <span class="badge badge-corp <?= $roleBadge; ?>"><?= htmlspecialchars($user['role']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($user['status'] === 'Active') : ?>
                                            <span class="badge badge-corp badge-corp-success"><i class="bi bi-check-circle-fill"></i> Active</span>
                                        <?php else : ?>
                                            <span class="badge badge-corp badge-corp-danger"><i class="bi bi-x-circle-fill"></i> Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-xs text-secondary"><?= date('d M Y, H:i', strtotime($user['created_at'])); ?></span>
                                    </td>
                                    <td class="text-center pe-4 text-nowrap">
                                        <div class="d-inline-flex gap-1 align-items-center">
                                            <!-- Detail User -->
                                            <a href="<?= BASEURL; ?>/adminuser/show/<?= $user['id']; ?>" class="btn btn-sm btn-white-corp py-1 px-2 mb-0 text-xs" title="Detail Pengguna">
                                                <i class="bi bi-eye text-primary"></i>
                                            </a>

                                            <!-- Edit User Modal Trigger -->
                                            <button type="button" class="btn btn-sm btn-white-corp py-1 px-2 mb-0 text-xs" data-bs-toggle="modal" data-bs-target="#modalEditUser<?= $user['id']; ?>" title="Edit Pengguna">
                                                <i class="bi bi-pencil-square text-warning"></i>
                                            </button>

                                            <!-- Delete User Modal Trigger -->
                                            <?php if ($user['id'] == $_SESSION['user_id']) : ?>
                                                <button type="button" class="btn btn-sm btn-white-corp py-1 px-2 mb-0 text-xs opacity-50" title="Akun Anda Sendiri (Tidak dapat dihapus)" disabled>
                                                    <i class="bi bi-trash-fill text-muted"></i>
                                                </button>
                                            <?php else : ?>
                                                <button type="button" class="btn btn-sm btn-white-corp py-1 px-2 mb-0 text-xs text-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteUser<?= $user['id']; ?>" title="Hapus Pengguna">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            <?php endif; ?>
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

    <!-- Modals Container (Rendered outside table & card to prevent backdrop stacking context issues) -->
    <div class="ajax-modals-container">
        <?php if (!empty($data['users'])) : ?>
            <?php foreach ($data['users'] as $user) : ?>
                <!-- Modal Edit Pengguna -->
                <div class="modal fade" id="modalEditUser<?= $user['id']; ?>" tabindex="-1" aria-labelledby="modalLabelEditUser<?= $user['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content shadow-lg border-0 rounded-4">
                            <div class="modal-header border-bottom px-4 py-3 bg-light rounded-top-4">
                                <h6 class="modal-title font-weight-bold text-dark mb-0" id="modalLabelEditUser<?= $user['id']; ?>">
                                    <i class="bi bi-pencil-square text-warning me-2"></i>Edit Pengguna: <?= htmlspecialchars($user['name']); ?>
                                </h6>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="<?= BASEURL; ?>/adminuser/update/<?= $user['id']; ?>" method="POST">
                                <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                                <input type="hidden" name="redirect_to" value="<?= BASEURL; ?>/adminuser">
                                <div class="modal-body p-4 text-start">
                                    <div class="mb-3">
                                        <label class="form-label text-xs font-weight-bold text-dark">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-xs font-weight-bold text-dark">Alamat Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-xs font-weight-bold text-dark">Nomor WhatsApp / HP</label>
                                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Contoh: 08123456789 / 628123456789">
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <label class="form-label text-xs font-weight-bold text-dark">Peran (Role) <span class="text-danger">*</span></label>
                                            <select name="role" class="form-select" required <?= ($user['id'] == $_SESSION['user_id']) ? 'disabled' : ''; ?>>
                                                <option value="Customer" <?= ($user['role'] === 'Customer') ? 'selected' : ''; ?>>Customer</option>
                                                <option value="Operator" <?= ($user['role'] === 'Operator') ? 'selected' : ''; ?>>Operator</option>
                                                <option value="Admin" <?= ($user['role'] === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                                                <option value="Owner" <?= ($user['role'] === 'Owner') ? 'selected' : ''; ?>>Owner</option>
                                            </select>
                                            <?php if ($user['id'] == $_SESSION['user_id']) : ?>
                                                <input type="hidden" name="role" value="<?= $user['role']; ?>">
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label text-xs font-weight-bold text-dark">Status Akun <span class="text-danger">*</span></label>
                                            <select name="status" class="form-select" required <?= ($user['id'] == $_SESSION['user_id']) ? 'disabled' : ''; ?>>
                                                <option value="Active" <?= ($user['status'] === 'Active') ? 'selected' : ''; ?>>Active</option>
                                                <option value="Inactive" <?= ($user['status'] === 'Inactive') ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                            <?php if ($user['id'] == $_SESSION['user_id']) : ?>
                                                <input type="hidden" name="status" value="<?= $user['status']; ?>">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label text-xs font-weight-bold text-dark">Reset Password (Opsional)</label>
                                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika password tidak diubah">
                                        <span class="text-xxs text-muted">Isi hanya jika ingin mengganti password akun pengguna ini.</span>
                                    </div>
                                </div>
                                <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
                                    <button type="button" class="btn btn-sm btn-white-corp mb-0" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-sm btn-dark-corp mb-0"><i class="bi bi-check-lg me-1"></i> Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Konfirmasi Hapus Pengguna -->
                <?php if ($user['id'] != $_SESSION['user_id']) : ?>
                    <div class="modal fade" id="modalDeleteUser<?= $user['id']; ?>" tabindex="-1" aria-labelledby="modalLabelDelUser<?= $user['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content shadow-lg border-0 rounded-4">
                                <div class="modal-header border-bottom px-4 py-3 bg-light rounded-top-4">
                                    <h6 class="modal-title font-weight-bold text-danger mb-0" id="modalLabelDelUser<?= $user['id']; ?>">
                                        <i class="bi bi-trash-fill me-2"></i>Konfirmasi Hapus Pengguna
                                    </h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="<?= BASEURL; ?>/adminuser/delete/<?= $user['id']; ?>" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                                    <input type="hidden" name="redirect_to" value="<?= BASEURL; ?>/adminuser">
                                    <div class="modal-body p-4 text-start">
                                        <p class="text-sm text-secondary mb-3">
                                            Yakin ingin menghapus akun pengguna <strong><?= htmlspecialchars($user['name']); ?></strong> (#<?= $user['id']; ?>) dengan email <strong><?= htmlspecialchars($user['email']); ?></strong> dan peran <strong><?= $user['role']; ?></strong>?
                                        </p>
                                        <div class="alert alert-danger text-xs mb-0 text-white border-0 py-2 rounded-3">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Tindakan ini tidak dapat dibatalkan. Riwayat pesanan, review, dan seluruh data akun ini akan terhapus.
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
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination Container -->
    <div class="ajax-pagination-container">
        <?php if (!empty($data['total_pages']) && $data['total_pages'] > 1) : 
            $filterParams = [];
            if (!empty($data['keyword'])) $filterParams['q'] = $data['keyword'];
            if (!empty($data['selected_role'])) $filterParams['role'] = $data['selected_role'];
            if (!empty($data['selected_status'])) $filterParams['status'] = $data['selected_status'];
            $queryString = !empty($filterParams) ? '&' . http_build_query($filterParams) : '';
        ?>
            <div class="d-flex justify-content-center mt-4">
                <ul class="pagination gap-1">
                    <?php for ($i = 1; $i <= $data['total_pages']; $i++) : ?>
                        <li class="page-item <?= ($i == $data['current_page']) ? 'active' : ''; ?>">
                            <a class="btn btn-sm <?= ($i == $data['current_page']) ? 'btn-dark-corp' : 'btn-white-corp'; ?> mb-0 px-3" href="<?= BASEURL; ?>/adminuser?page=<?= $i . $queryString; ?>"><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>
