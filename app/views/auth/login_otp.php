<!-- Halaman Login via OTP WhatsApp — Corporate UI Style -->
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
                <h4 class="font-weight-bold text-slate-900 mb-1">Login via WhatsApp OTP</h4>
                <p class="text-muted text-xs">Kami akan mengirim kode verifikasi OTP langsung ke nomor WhatsApp Anda</p>
            </div>

            <?php Flasher::flash(); ?>

            <div class="card shadow-xs border rounded-4 bg-white overflow-hidden p-4">
                <div class="card-body p-0">

                    <!-- ========================================== -->
                    <!-- STEP 1: Input Nomor WhatsApp               -->
                    <!-- ========================================== -->
                    <div id="otp-step-phone">
                        <div class="mb-3">
                            <label for="otp-phone" class="form-label text-xs font-weight-bold text-slate-700">
                                Nomor WhatsApp Terdaftar
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-gray-100 border-end-0 text-success fw-bold" style="border-color: #e2e8f0;">
                                    <i class="bi bi-whatsapp"></i>
                                </span>
                                <input
                                    type="tel"
                                    class="form-control corp-search-input border-start-0 ps-0"
                                    id="otp-phone"
                                    name="phone"
                                    placeholder="Contoh: 08123456789"
                                    maxlength="15"
                                    autocomplete="tel"
                                >
                            </div>
                            <div class="text-muted text-xxs mt-1">
                                Format: 08xx, 628xx, atau 8xx
                            </div>
                        </div>

                        <div id="otp-phone-error" class="alert alert-danger py-2 text-xs d-none rounded-3" role="alert"></div>

                        <div class="d-grid mt-4">
                            <button
                                type="button"
                                id="btn-send-otp"
                                class="btn btn-sm btn-dark-corp py-2 shadow-xs font-weight-bold"
                                onclick="otpSendRequest()"
                            >
                                <i class="bi bi-send-fill me-1"></i>
                                Kirim Kode OTP
                            </button>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- STEP 2: Input OTP (tersembunyi dulu)       -->
                    <!-- ========================================== -->
                    <div id="otp-step-verify" class="d-none">

                        <!-- Info nomor yang dipakai + tombol ubah -->
                        <div class="d-flex align-items-center justify-content-between mb-3 p-2 bg-gray-100 rounded-3">
                            <div class="text-xs text-muted">
                                OTP dikirim ke: <strong id="otp-phone-display" class="text-slate-900"></strong>
                            </div>
                            <button
                                type="button"
                                class="btn btn-link btn-sm text-primary p-0 text-decoration-none text-xs font-weight-bold"
                                onclick="otpChangePhone()"
                            >
                                <i class="bi bi-pencil-square me-1"></i>Ubah
                            </button>
                        </div>

                        <!-- Countdown timer -->
                        <div class="text-center mb-3">
                            <div id="otp-timer-wrapper" class="badge badge-corp badge-corp-warning py-1 px-3">
                                <i class="bi bi-clock me-1"></i>
                                Berlaku: <span id="otp-countdown" class="font-weight-bold">05:00</span>
                            </div>
                            <div id="otp-expired-notice" class="alert alert-warning py-2 text-xs d-none mt-2 rounded-3" role="alert">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Kode OTP sudah kadaluarsa. Silakan minta kode baru.
                            </div>
                        </div>

                        <!-- Input 6 digit OTP -->
                        <div class="mb-4 text-center">
                            <label for="otp-code" class="form-label text-xs font-weight-bold text-slate-700 d-block">
                                Masukkan 6 Digit Kode OTP
                            </label>
                            <input
                                type="text"
                                class="form-control corp-search-input text-center font-weight-bold letter-spacing-2 mx-auto"
                                style="max-width: 220px; font-size: 1.4rem !important;"
                                id="otp-code"
                                name="otp"
                                placeholder="· · · · · ·"
                                maxlength="6"
                                inputmode="numeric"
                                pattern="[0-9]{6}"
                                autocomplete="one-time-code"
                            >
                        </div>

                        <div id="otp-verify-error" class="alert alert-danger py-2 text-xs d-none rounded-3" role="alert"></div>
                        <div id="otp-verify-success" class="alert alert-success py-2 text-xs d-none rounded-3" role="alert"></div>

                        <!-- Tombol Verifikasi -->
                        <div class="d-grid mb-3">
                            <button
                                type="button"
                                id="btn-verify-otp"
                                class="btn btn-sm btn-dark-corp py-2 shadow-xs font-weight-bold"
                                onclick="otpVerify()"
                            >
                                <i class="bi bi-check-circle-fill me-1"></i>
                                Verifikasi &amp; Masuk
                            </button>
                        </div>

                        <!-- Tombol Kirim Ulang -->
                        <div class="text-center">
                            <button
                                type="button"
                                id="btn-resend-otp"
                                class="btn btn-link btn-sm text-muted text-decoration-none text-xs"
                                onclick="otpResend()"
                                disabled
                            >
                                <i class="bi bi-arrow-clockwise me-1"></i>
                                Kirim Ulang OTP
                                <span id="resend-countdown" class="text-muted"></span>
                            </button>
                        </div>

                    </div>

                    <!-- Loading spinner (tersembunyi) -->
                    <div id="otp-loading" class="text-center mt-3 d-none">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="text-muted text-xs">Memproses data...</span>
                    </div>

                </div>
            </div>

            <!-- Link kembali ke login biasa -->
            <div class="text-center mt-4">
                <p class="text-muted text-xs mb-1">
                    Lebih suka login dengan email?
                    <a href="<?= BASEURL; ?>/auth/login" class="font-weight-bold text-primary text-decoration-none">
                        Login dengan Email
                    </a>
                </p>
                <p class="text-muted text-xs">
                    Belum punya akun?
                    <a href="<?= BASEURL; ?>/auth/register" class="font-weight-bold text-primary text-decoration-none">
                        Daftar di sini
                    </a>
                </p>
            </div>

        </div>
    </div>
</div>

<style>
.letter-spacing-2 { letter-spacing: 0.4em; }
#otp-code { font-size: 1.5rem !important; }
</style>

<script>
(function() {
    'use strict';

    const BASE = '<?= BASEURL; ?>';
    const CSRF_TOKEN = '<?= CSRF::generateToken(); ?>';

    let currentPhone = '';
    let countdownInterval = null;
    let resendInterval = null;
    let otpExpired = false;

    // =============================================
    // STEP 1: Kirim OTP
    // =============================================
    window.otpSendRequest = async function() {
        const phone = document.getElementById('otp-phone').value.trim();
        const errorBox = document.getElementById('otp-phone-error');

        if (!phone) {
            showError(errorBox, 'Nomor WhatsApp wajib diisi.');
            return;
        }

        const btn = document.getElementById('btn-send-otp');
        setLoading(true, btn, 'Mengirim...');

        try {
            const res = await apiFetch(BASE + '/auth/requestOtp', { phone });
            const data = await res.json();

            if (data.success) {
                currentPhone = phone;
                showVerifyStep(data.cooldown || 60, data.expires || 300);
            } else {
                showError(errorBox, data.message || 'Gagal mengirim OTP.');
                if (data.cooldown) startResendCountdown(data.cooldown);
            }
        } catch (e) {
            showError(errorBox, 'Koneksi bermasalah. Coba lagi.');
        } finally {
            setLoading(false, btn, '<i class="bi bi-send-fill me-2"></i>Kirim Kode OTP');
        }
    };

    // =============================================
    // STEP 2: Verifikasi OTP
    // =============================================
    window.otpVerify = async function() {
        const otp = document.getElementById('otp-code').value.trim();
        const errorBox = document.getElementById('otp-verify-error');
        const successBox = document.getElementById('otp-verify-success');

        hideAlert(errorBox);
        hideAlert(successBox);

        if (!/^\d{6}$/.test(otp)) {
            showError(errorBox, 'OTP harus tepat 6 digit angka.');
            return;
        }

        if (otpExpired) {
            showError(errorBox, 'Kode OTP sudah kadaluarsa. Minta kode baru.');
            return;
        }

        const btn = document.getElementById('btn-verify-otp');
        setLoading(true, btn, 'Memverifikasi...');

        try {
            const res = await apiFetch(BASE + '/auth/verifyOtp', { phone: currentPhone, otp });
            const data = await res.json();

            if (data.success) {
                showSuccess(successBox, 'Login berhasil! Mengalihkan...');
                clearInterval(countdownInterval);
                setTimeout(() => { window.location.href = data.redirect; }, 1200);
            } else {
                showError(errorBox, data.message || 'OTP tidak valid.');
                document.getElementById('otp-code').value = '';
            }
        } catch (e) {
            showError(errorBox, 'Koneksi bermasalah. Coba lagi.');
        } finally {
            setLoading(false, btn, '<i class="bi bi-check-circle-fill me-2"></i>Verifikasi OTP');
        }
    };

    // =============================================
    // RESEND OTP
    // =============================================
    window.otpResend = async function() {
        const errorBox = document.getElementById('otp-verify-error');
        const btn = document.getElementById('btn-resend-otp');

        hideAlert(errorBox);
        setLoading(true, btn, 'Mengirim ulang...');

        try {
            const res = await apiFetch(BASE + '/auth/resendOtp', { phone: currentPhone });
            const data = await res.json();

            if (data.success) {
                otpExpired = false;
                document.getElementById('otp-expired-notice').classList.add('d-none');
                document.getElementById('otp-timer-wrapper').classList.remove('d-none');
                document.getElementById('otp-code').value = '';
                hideAlert(document.getElementById('otp-verify-error'));

                startOtpCountdown(data.expires || 300);
                startResendCountdown(data.cooldown || 60);
            } else {
                showError(errorBox, data.message || 'Gagal mengirim ulang OTP.');
                if (data.cooldown) startResendCountdown(data.cooldown);
            }
        } catch (e) {
            showError(errorBox, 'Koneksi bermasalah. Coba lagi.');
        } finally {
            setLoading(false, btn, '<i class="bi bi-arrow-clockwise me-1"></i>Kirim Ulang OTP');
        }
    };

    // =============================================
    // Ubah nomor — kembali ke Step 1
    // =============================================
    window.otpChangePhone = function() {
        clearInterval(countdownInterval);
        clearInterval(resendInterval);
        document.getElementById('otp-step-verify').classList.add('d-none');
        document.getElementById('otp-step-phone').classList.remove('d-none');
        document.getElementById('otp-phone').value = '';
        document.getElementById('otp-code').value = '';
        hideAlert(document.getElementById('otp-phone-error'));
        hideAlert(document.getElementById('otp-verify-error'));
        hideAlert(document.getElementById('otp-verify-success'));
        otpExpired = false;
    };

    // =============================================
    // Tampilkan step verifikasi
    // =============================================
    function showVerifyStep(cooldownSec, expiresSec) {
        document.getElementById('otp-step-phone').classList.add('d-none');
        document.getElementById('otp-step-verify').classList.remove('d-none');

        // Tampilkan nomor yang digunakan
        document.getElementById('otp-phone-display').textContent = currentPhone;

        // Sembunyikan pesan expired
        document.getElementById('otp-expired-notice').classList.add('d-none');
        document.getElementById('otp-timer-wrapper').classList.remove('d-none');
        otpExpired = false;

        startOtpCountdown(expiresSec);
        startResendCountdown(cooldownSec);
    }

    // =============================================
    // Countdown masa berlaku OTP
    // =============================================
    function startOtpCountdown(seconds) {
        clearInterval(countdownInterval);
        let remaining = seconds;

        updateCountdownDisplay(remaining);

        countdownInterval = setInterval(() => {
            remaining--;
            updateCountdownDisplay(remaining);

            if (remaining <= 0) {
                clearInterval(countdownInterval);
                otpExpired = true;
                document.getElementById('otp-timer-wrapper').classList.add('d-none');
                document.getElementById('otp-expired-notice').classList.remove('d-none');
            }
        }, 1000);
    }

    function updateCountdownDisplay(remaining) {
        const m = String(Math.floor(remaining / 60)).padStart(2, '0');
        const s = String(remaining % 60).padStart(2, '0');
        document.getElementById('otp-countdown').textContent = m + ':' + s;
    }

    // =============================================
    // Cooldown tombol Kirim Ulang
    // =============================================
    function startResendCountdown(seconds) {
        clearInterval(resendInterval);
        const btn = document.getElementById('btn-resend-otp');
        const cdSpan = document.getElementById('resend-countdown');
        let remaining = seconds;

        btn.disabled = true;
        cdSpan.textContent = ' (' + remaining + 'd)';

        resendInterval = setInterval(() => {
            remaining--;
            cdSpan.textContent = remaining > 0 ? ' (' + remaining + 'd)' : '';
            if (remaining <= 0) {
                clearInterval(resendInterval);
                btn.disabled = false;
            }
        }, 1000);
    }

    // =============================================
    // Helper: fetch dengan CSRF
    // =============================================
    async function apiFetch(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
            },
            body: JSON.stringify(body),
        });
    }

    function setLoading(isLoading, btn, label) {
        const loader = document.getElementById('otp-loading');
        if (isLoading) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Memproses...';
            loader.classList.remove('d-none');
        } else {
            btn.disabled = false;
            btn.innerHTML = label;
            loader.classList.add('d-none');
        }
    }

    function showError(el, msg) {
        el.textContent = msg;
        el.classList.remove('d-none');
    }

    function showSuccess(el, msg) {
        el.textContent = msg;
        el.classList.remove('d-none');
    }

    function hideAlert(el) {
        el.classList.add('d-none');
        el.textContent = '';
    }

    // Enter key di input nomor dan OTP
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('otp-phone')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') otpSendRequest();
        });
        document.getElementById('otp-code')?.addEventListener('keydown', e => {
            if (e.key === 'Enter') otpVerify();
        });
        // Hanya izinkan digit pada input OTP
        document.getElementById('otp-code')?.addEventListener('input', e => {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 6);
        });
    });
}());
</script>
