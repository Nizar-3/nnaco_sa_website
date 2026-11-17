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
                <h4 class="font-weight-bold text-slate-900 mb-1">Selamat Datang Kembali</h4>
                <p class="text-muted text-xs">Masukkan kredensial akun Anda untuk melanjutkan ke sistem</p>
            </div>
            
            <?php Flasher::flash(); ?>
            
            <div class="card shadow-xs border rounded-4 bg-white overflow-hidden p-4">
                <form action="<?= BASEURL; ?>/auth/login" method="post">
                    <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label text-xs font-weight-bold text-slate-700">Alamat Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-gray-100 border-end-0 text-muted" style="border-color: #e2e8f0;"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control corp-search-input border-start-0 ps-0" id="email" name="email" required placeholder="nama@email.com">
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label text-xs font-weight-bold text-slate-700 mb-0">Kata Sandi</label>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-gray-100 border-end-0 text-muted" style="border-color: #e2e8f0;"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control corp-search-input border-start-0 ps-0" id="password" name="password" required placeholder="Masukkan password Anda">
                        </div>
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-sm btn-dark-corp py-2 shadow-xs font-weight-bold">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Sekarang
                        </button>
                    </div>
                    
                    <div class="d-flex align-items-center gap-2 my-3">
                        <hr class="flex-grow-1 opacity-25 m-0">
                        <span class="text-muted text-xxs font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Atau</span>
                        <hr class="flex-grow-1 opacity-25 m-0">
                    </div>

                    <div class="d-grid mb-2">
                        <a href="<?= BASEURL; ?>/auth/loginOtp" class="btn btn-sm btn-white-corp py-2 d-flex align-items-center justify-content-center gap-2 font-weight-bold text-success border">
                            <i class="bi bi-whatsapp fs-6 text-success"></i>
                            Login via WhatsApp OTP
                        </a>
                    </div>
                </form>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-muted text-xs">Belum memiliki akun? <a href="<?= BASEURL; ?>/auth/register" class="font-weight-bold text-primary text-decoration-none">Daftar sekarang</a></p>
            </div>
        </div>
    </div>
</div>
