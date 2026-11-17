<div class="container py-5 my-4">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5 col-xl-4">
            
            <!-- Brand Logo & Header -->
            <div class="text-center mb-4">
                <a href="<?= BASEURL; ?>" class="d-inline-flex align-items-center text-decoration-none mb-3">
                    <div class="icon icon-shape rounded-3 me-2 shadow-xs d-flex align-items-center justify-content-center overflow-hidden p-0" style="width: 44px; height: 44px; background-color: #f2efe8 !important;">
                        <img src="<?= BASEURL; ?>/img/logo-square.png" alt="Logo NNACO SA" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div class="text-start lh-1">
                        <span class="fs-5 font-weight-bold text-slate-900 d-block" style="font-family: 'Noto Sans', sans-serif;">NNACO SA</span>
                        <span class="text-xxs text-muted font-weight-semibold" style="letter-spacing: 1.2px;">CORPORATE PORTAL</span>
                    </div>
                </a>
                <h4 class="font-weight-bold text-slate-900 mb-1">Buat Akun Baru</h4>
                <p class="text-muted text-xs">Daftarkan diri Anda untuk kemudahan berbelanja dan melacak pesanan</p>
            </div>
            
            <?php Flasher::flash(); ?>
            
            <div class="card shadow-xs border rounded-4 bg-white overflow-hidden p-4">
                <form action="<?= BASEURL; ?>/auth/register" method="post">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label text-xs font-weight-bold text-slate-700">Nama Lengkap <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-gray-100 border-end-0 text-muted" style="border-color: #e2e8f0;"><i class="bi bi-person"></i></span>
                            <input type="text" class="form-control corp-search-input border-start-0 ps-0" id="name" name="name" required placeholder="Nama Lengkap Anda">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label text-xs font-weight-bold text-slate-700">Alamat Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-gray-100 border-end-0 text-muted" style="border-color: #e2e8f0;"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control corp-search-input border-start-0 ps-0" id="email" name="email" required placeholder="nama@email.com">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label text-xs font-weight-bold text-slate-700">Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-gray-100 border-end-0 text-muted" style="border-color: #e2e8f0;"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control corp-search-input border-start-0 ps-0" id="password" name="password" required placeholder="Minimal 6 karakter" minlength="6">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label text-xs font-weight-bold text-slate-700">Konfirmasi Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-gray-100 border-end-0 text-muted" style="border-color: #e2e8f0;"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control corp-search-input border-start-0 ps-0" id="confirm_password" name="confirm_password" required placeholder="Ulangi password" minlength="6">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="reg-phone" class="form-label text-xs font-weight-bold text-slate-700">
                            Nomor WhatsApp <span class="text-muted fw-normal text-xxs">(opsional — untuk login via OTP)</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-gray-100 border-end-0 text-success fw-bold" style="border-color: #e2e8f0;"><i class="bi bi-whatsapp"></i></span>
                            <input type="tel" class="form-control corp-search-input border-start-0 ps-0" id="reg-phone" name="phone" placeholder="Contoh: 08123456789" maxlength="15">
                        </div>
                        <div class="text-muted text-xxs mt-1">Format: 08xx, 628xx, atau 8xx</div>
                    </div>
                    
                    <div class="d-grid mb-2">
                        <button type="submit" class="btn btn-sm btn-dark-corp py-2 shadow-xs font-weight-bold">
                            <i class="bi bi-person-plus me-1"></i> Daftar Sekarang
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-muted text-xs">Sudah memiliki akun? <a href="<?= BASEURL; ?>/auth/login" class="font-weight-bold text-primary text-decoration-none">Masuk di sini</a></p>
            </div>
        </div>
    </div>
</div>
