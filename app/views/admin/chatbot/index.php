<!-- Corporate UI Dashboard: Riwayat Chatbot AI -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-md-flex align-items-center justify-content-between">
            <div class="mb-3 mb-md-0">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge badge-corp badge-corp-dark">
                        <i class="bi bi-shield-lock-fill me-1 text-warning"></i> Khusus Admin
                    </span>
                    <span class="text-xs text-muted">Akses Terproteksi</span>
                </div>
                <h3 class="font-weight-bold text-dark mb-1">Riwayat Chatbot AI</h3>
                <p class="text-secondary text-sm mb-0">Monitoring interaksi percakapan pelanggan dan pengunjung dengan Asisten AI NNACO SA.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASEURL; ?>/settings?tab=ai" class="btn btn-sm btn-dark-corp mb-0 d-inline-flex align-items-center">
                    <i class="bi bi-gear-fill me-1"></i> Konfigurasi AI
                </a>
                <a href="<?= BASEURL; ?>/admin" class="btn btn-sm btn-white-corp mb-0 d-inline-flex align-items-center">
                    <i class="bi bi-arrow-left me-1"></i> Dashboard
                </a>
                <a href="<?= BASEURL; ?>/adminchatbot" class="btn btn-sm btn-white-corp mb-0 d-inline-flex align-items-center" title="Refresh Data">
                    <i class="bi bi-arrow-clockwise me-1"></i> Segarkan
                </a>
                <button type="button" class="btn btn-sm btn-outline-danger mb-0 d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#clearHistoryModal">
                    <i class="bi bi-trash3 me-1"></i> Bersihkan Log
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Stat Metric Cards (Corporate UI Style) -->
<div class="row g-3 mb-4">
    <!-- Total Percakapan -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-corporate">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-title">Total Interaksi</p>
                    <h3 class="stat-value"><?= number_format($data['stats']['total_all'] ?? 0); ?></h3>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge badge-corp badge-corp-primary">Sepanjang Waktu</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper bg-slate-900 text-white shadow-sm">
                    <i class="bi bi-chat-square-dots fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Percakapan Hari Ini -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-corporate">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-title">Percakapan Hari Ini</p>
                    <h3 class="stat-value"><?= number_format($data['stats']['total_today'] ?? 0); ?></h3>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge badge-corp badge-corp-success">Hari Ini</span>
                        <span class="text-xs text-muted"><?= date('d M Y'); ?></span>
                    </div>
                </div>
                <div class="stat-icon-wrapper bg-success text-white shadow-sm">
                    <i class="bi bi-calendar-check fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Pengguna Terdaftar -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-corporate">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-title">Member Terdaftar</p>
                    <h3 class="stat-value"><?= number_format($data['stats']['total_unique_users'] ?? 0); ?></h3>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge badge-corp badge-corp-info">Akun Aktif</span>
                        <span class="text-xs text-muted">User unik</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper bg-info text-white shadow-sm">
                    <i class="bi bi-person-check fs-5"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Tamu / Guest -->
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card-corporate">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="stat-title">Pengunjung Tamu</p>
                    <h3 class="stat-value"><?= number_format($data['stats']['total_guest'] ?? 0); ?></h3>
                    <div class="d-flex align-items-center gap-1">
                        <span class="badge badge-corp badge-corp-warning">Belum Login</span>
                        <span class="text-xs text-muted">Publik</span>
                    </div>
                </div>
                <div class="stat-icon-wrapper bg-warning text-white shadow-sm">
                    <i class="bi bi-incognito fs-5"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="ajax-table-wrapper">
    <!-- Search & Filter Card -->
    <div class="card shadow-xs border mb-4">
        <div class="card-body p-3">
            <form action="<?= BASEURL; ?>/adminchatbot" method="GET" class="ajax-filter-form row g-2 align-items-center">
                <div class="col-md-4 col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-start-0 ps-0 corp-search-input" placeholder="Cari pertanyaan, jawaban, nama user, email..." value="<?= htmlspecialchars($data['keyword'] ?? ''); ?>">
                    </div>
                </div>

                <div class="col-md-3 col-lg-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-calendar3"></i>
                        </span>
                        <input type="date" name="date" class="form-control border-start-0 ps-0 text-sm" value="<?= htmlspecialchars($data['date'] ?? ''); ?>" title="Filter Tanggal Percakapan">
                    </div>
                </div>

                <div class="col-md-3 col-lg-2">
                    <select name="user_type" class="form-select text-sm">
                        <option value="">Semua Penanya</option>
                        <option value="registered" <?= (($data['user_type'] ?? '') === 'registered') ? 'selected' : ''; ?>>Member Terdaftar</option>
                        <option value="guest" <?= (($data['user_type'] ?? '') === 'guest') ? 'selected' : ''; ?>>Tamu / Guest</option>
                    </select>
                </div>

                <div class="col-auto d-flex align-items-center gap-1">
                    <button type="submit" class="btn btn-sm btn-dark-corp mb-0">
                        <i class="bi bi-funnel me-1"></i> Filter
                    </button>
                    <a href="<?= BASEURL; ?>/adminchatbot" class="btn btn-sm btn-white-corp mb-0 btn-reset-filter" data-ajax-reset>
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Conversation History Table Card -->
    <div class="card shadow-xs border">
        <div class="card-header border-bottom d-flex align-items-center justify-content-between py-3">
            <div>
                <h6 class="font-weight-semibold text-dark mb-0">Daftar Percakapan Chatbot</h6>
                <p class="text-xs text-secondary mb-0">Menampilkan total <b><?= number_format($data['total_records']); ?></b> riwayat interaksi AI</p>
            </div>
            <div>
                <span class="badge badge-corp badge-corp-dark">
                    Halaman <?= $data['current_page']; ?> dari <?= $data['total_pages']; ?>
                </span>
            </div>
        </div>

        <div class="card-body px-0 py-0">
            <div class="table-responsive">
                <table class="table table-corporate align-items-center mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th style="width: 170px;">Waktu & Tanggal</th>
                            <th style="width: 220px;">Penanya / Pengguna</th>
                            <th>Pertanyaan User</th>
                            <th>Jawaban AI Assistant</th>
                            <th class="text-center" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data['conversations'])) : ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                        <div class="icon-shape bg-gray-100 text-muted rounded-circle p-4 mb-3">
                                            <i class="bi bi-chat-left-dots fs-1"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">Belum Ada Riwayat Percakapan</h6>
                                        <p class="text-xs text-muted mb-0">
                                            <?= (!empty($data['keyword']) || !empty($data['date']) || !empty($data['user_type'])) 
                                                ? 'Tidak ada percakapan yang cocok dengan kriteria pencarian Anda.' 
                                                : 'Percakapan pelanggan dan asisten AI akan otomatis tercatat di sini.'; ?>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($data['conversations'] as $conv) : ?>
                                <tr>
                                    <td>
                                        <span class="text-xs fw-bold text-slate-500">#<?= $conv['id']; ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark text-xs">
                                                <i class="bi bi-clock me-1 text-muted"></i><?= date('H:i:s', strtotime($conv['created_at'])); ?> WIB
                                            </span>
                                            <span class="text-xxs text-muted">
                                                <?= date('d M Y', strtotime($conv['created_at'])); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($conv['user_id'])) : ?>
                                                <div class="avatar avatar-xs rounded-circle bg-slate-900 text-white me-2 d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                    <?= strtoupper(substr($conv['user_name'] ?? 'U', 0, 1)); ?>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-dark text-xs text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($conv['user_name'] ?? ''); ?>">
                                                        <?= htmlspecialchars($conv['user_name'] ?? 'User'); ?>
                                                    </span>
                                                    <span class="text-xxs text-muted text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($conv['user_email'] ?? ''); ?>">
                                                        <?= htmlspecialchars($conv['user_email'] ?? '-'); ?>
                                                    </span>
                                                    <div>
                                                        <span class="badge badge-corp badge-corp-info text-xxs px-1 py-0" style="font-size: 0.65rem;">
                                                            <?= htmlspecialchars($conv['user_role'] ?? 'Member'); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php else : ?>
                                                <div class="avatar avatar-xs rounded-circle bg-gray-200 text-muted me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    <i class="bi bi-person text-secondary"></i>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold text-secondary text-xs">Tamu / Guest</span>
                                                    <span class="text-xxs text-muted">Belum login</span>
                                                    <div>
                                                        <span class="badge badge-corp badge-corp-warning text-xxs px-1 py-0" style="font-size: 0.65rem;">
                                                            Publik
                                                        </span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-xs text-dark fw-medium text-break" style="max-width: 280px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4;">
                                            <?= htmlspecialchars($conv['question']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-xs text-secondary text-break" style="max-width: 320px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4;">
                                            <?= htmlspecialchars(strip_tags(str_replace('[PROD_CARDS]', '', $conv['answer']))); ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <!-- Tombol Buka Modal Detail -->
                                            <button type="button" 
                                                    class="btn btn-sm btn-white-corp py-1 px-2 mb-0 btn-view-chat" 
                                                    data-id="<?= $conv['id']; ?>"
                                                    title="Lihat Dialog Lengkap">
                                                <i class="bi bi-eye text-primary"></i>
                                            </button>

                                            <!-- Tombol Hapus (CSRF Protected) -->
                                            <form action="<?= BASEURL; ?>/adminchatbot/delete/<?= $conv['id']; ?>" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan percakapan ini?');">
                                                <input type="hidden" name="csrf_token" value="<?= CSRF::getToken(); ?>">
                                                <button type="submit" class="btn btn-sm btn-white-corp py-1 px-2 mb-0 text-danger hover-danger" title="Hapus Riwayat">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls (Corporate UI) -->
            <?php if ($data['total_pages'] > 1) : ?>
                <div class="card-footer border-top d-flex align-items-center justify-content-between py-3">
                    <span class="text-xs text-secondary">
                        Menampilkan <?= min($data['total_records'], ($data['current_page'] - 1) * $data['limit'] + 1); ?> - <?= min($data['total_records'], $data['current_page'] * $data['limit']); ?> dari <?= $data['total_records']; ?> data
                    </span>
                    <div class="d-flex gap-1">
                        <?php 
                        $queryParams = [];
                        if (!empty($data['keyword'])) $queryParams['q'] = $data['keyword'];
                        if (!empty($data['date'])) $queryParams['date'] = $data['date'];
                        if (!empty($data['user_type'])) $queryParams['user_type'] = $data['user_type'];
                        $queryPrefix = !empty($queryParams) ? '&' . http_build_query($queryParams) : '';
                        ?>

                        <!-- Previous Button -->
                        <?php if ($data['current_page'] > 1) : ?>
                            <a class="btn btn-sm btn-white-corp mb-0 px-3" href="<?= BASEURL; ?>/adminchatbot?page=<?= ($data['current_page'] - 1) . $queryPrefix; ?>">
                                <i class="bi bi-chevron-left me-1"></i> Prev
                            </a>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php 
                        $startPage = max(1, $data['current_page'] - 2);
                        $endPage = min($data['total_pages'], $data['current_page'] + 2);
                        for ($i = $startPage; $i <= $endPage; $i++) : 
                        ?>
                            <a class="btn btn-sm <?= ($i == $data['current_page']) ? 'btn-dark-corp' : 'btn-white-corp'; ?> mb-0 px-3" href="<?= BASEURL; ?>/adminchatbot?page=<?= $i . $queryPrefix; ?>">
                                <?= $i; ?>
                            </a>
                        <?php endfor; ?>

                        <!-- Next Button -->
                        <?php if ($data['current_page'] < $data['total_pages']) : ?>
                            <a class="btn btn-sm btn-white-corp mb-0 px-3" href="<?= BASEURL; ?>/adminchatbot?page=<?= ($data['current_page'] + 1) . $queryPrefix; ?>">
                                Next <i class="bi bi-chevron-right ms-1"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Modal: Detail Percakapan Lengkap (Corporate UI Dialog) -->
<div class="modal fade" id="chatDetailModal" tabindex="-1" aria-labelledby="chatDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <div class="modal-header border-bottom py-3 px-4">
                <div class="d-flex align-items-center">
                    <div class="icon-shape bg-slate-900 text-white rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-robot fs-5"></i>
                    </div>
                    <div>
                        <h6 class="modal-title font-weight-bold text-dark mb-0" id="chatDetailModalLabel">Detail Percakapan AI</h6>
                        <span class="text-xs text-muted" id="modalConvTime">-</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <!-- User Info Bar -->
                <div class="card border shadow-none mb-3 bg-white">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm rounded-circle bg-gray-200 text-dark fw-bold me-2 d-flex align-items-center justify-content-center" id="modalUserAvatar" style="width: 36px; height: 36px;">
                                    U
                                </div>
                                <div>
                                    <h6 class="text-xs font-weight-bold mb-0 text-dark" id="modalUserName">-</h6>
                                    <span class="text-xxs text-muted" id="modalUserEmail">-</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge badge-corp badge-corp-dark" id="modalUserRole">-</span>
                                <span class="text-xxs text-muted" id="modalUserPhone">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chat Dialog Container -->
                <div class="d-flex flex-column gap-3">
                    <!-- User Message Bubble (Right) -->
                    <div class="d-flex justify-content-end">
                        <div class="d-flex align-items-start gap-2" style="max-width: 85%;">
                            <div class="card border-0 shadow-xs text-white" style="background: #0f172a; border-radius: 14px 14px 2px 14px;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1 gap-3">
                                        <span class="text-xxs text-slate-400 fw-bold text-uppercase">
                                            <i class="bi bi-person me-1"></i> Pertanyaan Pelanggan
                                        </span>
                                    </div>
                                    <div class="text-sm fw-normal text-white" id="modalQuestionText" style="white-space: pre-wrap; word-break: break-word;">
                                        Loading...
                                    </div>
                                </div>
                            </div>
                            <div class="avatar avatar-xs rounded-circle bg-slate-900 text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                        </div>
                    </div>

                    <!-- AI Assistant Reply Bubble (Left) -->
                    <div class="d-flex justify-content-start">
                        <div class="d-flex align-items-start gap-2" style="max-width: 85%;">
                            <div class="avatar avatar-xs rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                <i class="bi bi-robot"></i>
                            </div>
                            <div class="card border shadow-xs bg-white" style="border-radius: 14px 14px 14px 2px;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center justify-content-between mb-1 gap-3">
                                        <span class="text-xxs text-primary fw-bold text-uppercase">
                                            <i class="bi bi-stars me-1"></i> Asisten AI NNACO SA
                                        </span>
                                    </div>
                                    <div class="text-sm text-dark" id="modalAnswerText" style="white-space: pre-wrap; word-break: break-word; line-height: 1.6;">
                                        Loading...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer border-top py-2 px-4 justify-content-between">
                <span class="text-xxs text-muted">ID Catatan: <b id="modalConvId">#0</b></span>
                <button type="button" class="btn btn-sm btn-dark-corp mb-0" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Bersihkan Log Riwayat Percakapan -->
<div class="modal fade" id="clearHistoryModal" tabindex="-1" aria-labelledby="clearHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px;">
            <form action="<?= BASEURL; ?>/adminchatbot/clear" method="POST">
                <input type="hidden" name="csrf_token" value="<?= CSRF::getToken(); ?>">
                
                <div class="modal-header border-bottom py-3 px-4">
                    <h6 class="modal-title font-weight-bold text-dark mb-0" id="clearHistoryModalLabel">
                        <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Bersihkan Log Percakapan
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <p class="text-sm text-secondary mb-3">
                        Tindakan ini akan menghapus log riwayat chatbot secara permanen dari database. Pilih opsi pembersihan:
                    </p>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="days" id="clear30" value="30" checked>
                        <label class="form-check-label text-sm text-dark" for="clear30">
                            Hapus riwayat yang lebih lama dari <b>30 hari</b> (Disarankan)
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="days" id="clear90" value="90">
                        <label class="form-check-label text-sm text-dark" for="clear90">
                            Hapus riwayat yang lebih lama dari <b>90 hari</b>
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="days" id="clearAll" value="0">
                        <label class="form-check-label text-sm text-danger fw-semibold" for="clearAll">
                            Hapus <b>SEMUA</b> riwayat percakapan chatbot
                        </label>
                    </div>
                </div>

                <div class="modal-footer border-top py-2 px-4 justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-white-corp mb-0" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-danger mb-0" onclick="return confirm('Konfirmasi: Anda yakin ingin melanjutkan pembersihan log?');">
                        <i class="bi bi-trash3 me-1"></i> Bersihkan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const detailModalEl = document.getElementById('chatDetailModal');
    let detailModal = null;
    if (detailModalEl && typeof bootstrap !== 'undefined') {
        detailModal = new bootstrap.Modal(detailModalEl);
    }

    // Event listener untuk tombol lihat dialog lengkap
    document.querySelectorAll('.btn-view-chat').forEach(button => {
        button.addEventListener('click', function () {
            const convId = this.getAttribute('data-id');
            if (!convId) return;

            // Tampilkan modal dan state loading
            document.getElementById('modalConvId').textContent = '#' + convId;
            document.getElementById('modalQuestionText').textContent = 'Memuat pertanyaan...';
            document.getElementById('modalAnswerText').textContent = 'Memuat jawaban...';
            document.getElementById('modalConvTime').textContent = 'Mengambil data...';
            
            if (detailModal) {
                detailModal.show();
            }

            fetch('<?= BASEURL; ?>/adminchatbot/detail/' + convId, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(result => {
                if (result.success && result.data) {
                    const d = result.data;
                    document.getElementById('modalConvId').textContent = '#' + d.id;
                    document.getElementById('modalConvTime').textContent = d.created_at + ' WIB';
                    document.getElementById('modalUserName').textContent = d.user_name;
                    document.getElementById('modalUserEmail').textContent = d.user_email;
                    document.getElementById('modalUserRole').textContent = d.user_role;
                    document.getElementById('modalUserPhone').textContent = d.user_phone ? 'HP: ' + d.user_phone : '';
                    document.getElementById('modalUserAvatar').textContent = d.user_name.charAt(0).toUpperCase();

                    document.getElementById('modalQuestionText').textContent = d.question;
                    
                    // Bersihkan tag placeholder internal jika ada
                    let cleanAnswer = d.answer.replace(/\[PROD_CARDS\]/g, '').trim();
                    document.getElementById('modalAnswerText').textContent = cleanAnswer;
                } else {
                    document.getElementById('modalQuestionText').textContent = 'Gagal memuat detail percakapan.';
                    document.getElementById('modalAnswerText').textContent = result.error || 'Terjadi kesalahan.';
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('modalQuestionText').textContent = 'Terjadi kesalahan koneksi.';
                document.getElementById('modalAnswerText').textContent = 'Tidak dapat memuat data.';
            });
        });
    });
});
</script>
