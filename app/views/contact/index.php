<!-- Contact Us Hero Header (Corporate UI Style) -->
<div class="py-5 mb-5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #774dd3 100%);">
    <div class="container py-4 text-center text-white">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb justify-content-center mb-3 p-0 bg-transparent">
                <li class="breadcrumb-item"><a href="<?= BASEURL; ?>" class="text-xs text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-xs font-weight-bold text-white" aria-current="page">Kontak Kami</li>
            </ol>
        </nav>
        <span class="badge badge-corp badge-corp-warning mb-2">LAYANAN PELANGGAN</span>
        <h1 class="display-5 font-weight-bolder text-white mb-2" style="font-family: 'Noto Sans', sans-serif;">
            Hubungi Tim NNACO SA
        </h1>
        <p class="text-white-50 text-sm mx-auto" style="max-width: 600px;">
            Ada pertanyaan mengenai produk, kustomisasi ukuran, atau status pesanan Anda? Tim NNACO SA siap membantu Anda sepenuh hati.
        </p>
    </div>
</div>

<div class="container mb-5">
    <!-- Flash message feedback if any -->
    <div class="row justify-content-center mb-4">
        <div class="col-lg-10">
            <?php Flasher::flash(); ?>
        </div>
    </div>

    <!-- Contact Info Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-6 col-lg-3">
            <div class="card shadow-xs border rounded-4 p-4 text-center bg-white h-100">
                <div class="icon icon-shape bg-slate-900 text-white rounded-3 mx-auto mb-3 d-flex align-items-center justify-content-center shadow-xs" style="width: 50px; height: 50px;">
                    <i class="bi bi-telephone fs-5"></i>
                </div>
                <h6 class="font-weight-bold text-slate-900 mb-1">Telepon &amp; WhatsApp</h6>
                <p class="text-muted text-xs mb-1">+62 812-3456-7890</p>
                <small class="text-muted text-xxs">Senin - Sabtu: 08.00 - 20.00</small>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-xs border rounded-4 p-4 text-center bg-white h-100">
                <div class="icon icon-shape bg-primary text-white rounded-3 mx-auto mb-3 d-flex align-items-center justify-content-center shadow-xs" style="width: 50px; height: 50px;">
                    <i class="bi bi-envelope fs-5"></i>
                </div>
                <h6 class="font-weight-bold text-slate-900 mb-1">Email Dukungan</h6>
                <p class="text-muted text-xs mb-1">support@nnacosa.com</p>
                <small class="text-muted text-xxs">Balasan maksimal 1x24 jam</small>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-xs border rounded-4 p-4 text-center bg-white h-100">
                <div class="icon icon-shape bg-info text-white rounded-3 mx-auto mb-3 d-flex align-items-center justify-content-center shadow-xs" style="width: 50px; height: 50px;">
                    <i class="bi bi-geo-alt fs-5"></i>
                </div>
                <h6 class="font-weight-bold text-slate-900 mb-1">Kantor &amp; Store</h6>
                <p class="text-muted text-xs mb-1">Jakarta, Indonesia</p>
                <small class="text-muted text-xxs">Buka setiap hari kerja</small>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card shadow-xs border rounded-4 p-4 text-center bg-white h-100">
                <div class="icon icon-shape bg-success text-white rounded-3 mx-auto mb-3 d-flex align-items-center justify-content-center shadow-xs" style="width: 50px; height: 50px;">
                    <i class="bi bi-robot fs-5"></i>
                </div>
                <h6 class="font-weight-bold text-slate-900 mb-1">AI Assistant 24/7</h6>
                <p class="text-muted text-xs mb-1">Chat Respons Instan</p>
                <small class="text-muted text-xxs">Klik tombol di pojok kanan</small>
            </div>
        </div>
    </div>

    <!-- Contact Form & FAQ -->
    <div class="row g-4">
        <!-- Form -->
        <div class="col-lg-6">
            <div class="card shadow-xs border rounded-4 p-4 bg-white h-100">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon icon-shape bg-slate-900 text-white rounded-2 me-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="bi bi-send text-xs"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold text-slate-900 mb-0">Kirim Pesan Dukungan</h6>
                        <small class="text-muted text-xs">Formulir pertanyaan dan masukan pelanggan</small>
                    </div>
                </div>

                <form action="<?= BASEURL; ?>/contact/send" method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label text-xs font-weight-bold text-slate-700">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control corp-search-input" id="name" name="name" placeholder="Masukkan nama Anda" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label text-xs font-weight-bold text-slate-700">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control corp-search-input" id="email" name="email" placeholder="nama@email.com" required>
                    </div>

                    <div class="mb-3">
                        <label for="subject" class="form-label text-xs font-weight-bold text-slate-700">Topik Pertanyaan</label>
                        <select class="form-select corp-search-input" id="subject" name="subject">
                            <option value="Informasi Produk">Informasi Produk &amp; Stok</option>
                            <option value="Status Pengiriman">Status Pesanan &amp; Pengiriman</option>
                            <option value="Pengembalian Produk">Kebijakan Retur / Garansi</option>
                            <option value="Kerjasama Bisnis">Kerjasama / Kemitraan</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label text-xs font-weight-bold text-slate-700">Isi Pesan <span class="text-danger">*</span></label>
                        <textarea class="form-control corp-search-input" id="message" name="message" rows="4" placeholder="Tuliskan pertanyaan Anda secara detail..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-dark-corp w-100 py-2 mb-0 shadow-xs">
                        <i class="bi bi-send-fill me-1"></i> Kirim Pesan Sekarang
                    </button>
                </form>
            </div>
        </div>

        <!-- FAQ Cards -->
        <div class="col-lg-6">
            <div class="card shadow-xs border rounded-4 p-4 bg-white h-100">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon icon-shape bg-primary text-white rounded-2 me-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="bi bi-question-circle text-xs"></i>
                    </div>
                    <div>
                        <h6 class="font-weight-bold text-slate-900 mb-0">Pertanyaan Populer (FAQ)</h6>
                        <small class="text-muted text-xs">Jawaban cepat untuk pertanyaan yang sering diajukan</small>
                    </div>
                </div>

                <div class="accordion accordion-flush" id="faqAccordion">
                    <div class="accordion-item border-bottom py-2">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button collapsed px-0 fw-semibold text-slate-800 text-sm bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                Berapa lama proses pengiriman barang?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body px-0 text-xs text-muted">
                                Pesanan diproses dalam 1x24 jam kerja setelah pembayaran terkonfirmasi. Estimasi pengiriman pulau Jawa 1-3 hari, luar Jawa 3-5 hari kerja.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-bottom py-2">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed px-0 fw-semibold text-slate-800 text-sm bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                Apakah perhiasan NNACO SA bisa berkarat atau luntur?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body px-0 text-xs text-muted">
                                Produk kami menggunakan material titanium premium dan perak anti karat (hypoallergenic) yang sangat tahan terhadap air dan keringat sehari-hari.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item py-2">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed px-0 fw-semibold text-slate-800 text-sm bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                Bagaimana cara login menggunakan WhatsApp OTP?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body px-0 text-xs text-muted">
                                Cukup pilih menu Login via WhatsApp OTP, masukkan nomor WhatsApp Anda, dan sistem Fonnte kami akan mengirimkan 6 digit kode verifikasi instan.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
