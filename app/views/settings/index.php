<?php
$settings = $data['settings'] ?? [];
$auditLogs = $data['audit_logs'] ?? [];
$activeTab = $data['active_tab'] ?? 'ai';

// Helper helper untuk membaca value aman
$s = function($key, $default = '') use ($settings) {
    return htmlspecialchars($settings[$key]['value'] ?? $default);
};
$isConfigured = function($key) use ($settings) {
    return !empty($settings[$key]['configured']);
};

$aiEnabled = ($settings['ai.enabled']['value'] ?? '1') === '1';
$otpEnabled = ($settings['otp.enabled']['value'] ?? '1') === '1';
$orderWaEnabled = ($settings['order_wa.enabled']['value'] ?? '1') === '1';
$orderWaTestMode = ($settings['order_wa.test_mode']['value'] ?? '0') === '1';
$orderWaLogs = $data['order_wa_logs'] ?? [];
$totalOrderWaLogs = $data['total_order_wa_logs'] ?? 0;

$rawSavedAiKey = SettingService::getSecret('ai.api_key') ?? '';
$isGeminiKey = (str_starts_with($rawSavedAiKey, 'AIzaSy') || str_starts_with($rawSavedAiKey, 'AQ.'));
$isGroqKey = str_starts_with($rawSavedAiKey, 'gsk_');
$savedKeyType = !empty($rawSavedAiKey)
    ? ($isGroqKey ? 'groq' : ($isGeminiKey ? 'gemini' : 'other'))
    : 'none';
?>

<div class="container-fluid py-2">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-xs border-radius-xl p-3 bg-white">
                <div class="d-md-flex align-items-center justify-content-between">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-slate-900 text-white rounded-pill px-3 py-1 text-xs">
                                <i class="bi bi-shield-check me-1"></i> Core System Configuration
                            </span>
                            <span class="text-xs text-muted">Role Akses: <b><?= htmlspecialchars($_SESSION['user_role'] ?? 'Admin'); ?></b></span>
                        </div>
                        <h3 class="font-weight-bold text-dark mb-1">Pengaturan Sistem</h3>
                        <p class="text-secondary text-sm mb-0">
                            Kelola model AI, perilaku chatbot toko, autentikasi WhatsApp OTP, dan notifikasi pesanan customer terintegrasi Fonnte.
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-3 mt-md-0 flex-wrap">
                        <!-- Indikator Status AI -->
                        <div class="d-flex align-items-center bg-gray-100 rounded-pill px-3 py-2 border">
                            <span class="badge rounded-circle p-1 me-2 <?= $aiEnabled ? 'bg-success' : 'bg-danger'; ?>" style="width: 10px; height: 10px;"></span>
                            <div>
                                <span class="d-block text-xxs text-muted text-uppercase fw-bold lh-1">Chatbot AI</span>
                                <span class="text-xs fw-semibold text-dark"><?= $aiEnabled ? 'Aktif (' . $s('ai.provider', 'groq') . ')' : 'Nonaktif'; ?></span>
                            </div>
                        </div>

                        <!-- Indikator Status OTP -->
                        <div class="d-flex align-items-center bg-gray-100 rounded-pill px-3 py-2 border">
                            <span class="badge rounded-circle p-1 me-2 <?= $otpEnabled ? 'bg-success' : 'bg-secondary'; ?>" style="width: 10px; height: 10px;"></span>
                            <div>
                                <span class="d-block text-xxs text-muted text-uppercase fw-bold lh-1">OTP WhatsApp</span>
                                <span class="text-xs fw-semibold text-dark"><?= $otpEnabled ? 'Aktif' : 'Nonaktif'; ?></span>
                            </div>
                        </div>

                        <!-- Indikator Status WA Pesanan -->
                        <div class="d-flex align-items-center bg-gray-100 rounded-pill px-3 py-2 border">
                            <span class="badge rounded-circle p-1 me-2 <?= $orderWaEnabled ? 'bg-success' : 'bg-secondary'; ?>" style="width: 10px; height: 10px;"></span>
                            <div>
                                <span class="d-block text-xxs text-muted text-uppercase fw-bold lh-1">WA Pesanan</span>
                                <span class="text-xs fw-semibold text-dark"><?= $orderWaEnabled ? ($orderWaTestMode ? 'Aktif (Simulasi)' : 'Aktif (Fonnte)') : 'Nonaktif'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Notifikasi Real-time -->
    <div id="settingsAlertPlaceholder"></div>

    <!-- Tab Navigation & Content -->
    <div class="row">
        <div class="col-12">
            <!-- Navigation Tabs -->
            <ul class="nav nav-pills bg-white p-2 rounded-xl border shadow-xs mb-4 flex-wrap" id="settingsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeTab === 'ai' ? 'active' : ''; ?> d-flex align-items-center gap-2 fw-semibold px-4 py-2" id="tab-ai-btn" data-bs-toggle="pill" data-bs-target="#tab-ai" type="button" role="tab">
                        <i class="bi bi-cpu-fill fs-6"></i>
                        <span>Konfigurasi AI</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeTab === 'otp' ? 'active' : ''; ?> d-flex align-items-center gap-2 fw-semibold px-4 py-2" id="tab-otp-btn" data-bs-toggle="pill" data-bs-target="#tab-otp" type="button" role="tab">
                        <i class="bi bi-whatsapp fs-6"></i>
                        <span>Authentication OTP</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeTab === 'order_wa' ? 'active' : ''; ?> d-flex align-items-center gap-2 fw-semibold px-4 py-2" id="tab-order-wa-btn" data-bs-toggle="pill" data-bs-target="#tab-order-wa" type="button" role="tab" onclick="loadOrderWaLogs()">
                        <i class="bi bi-bell-fill fs-6"></i>
                        <span>Notifikasi Pesanan</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeTab === 'security' ? 'active' : ''; ?> d-flex align-items-center gap-2 fw-semibold px-4 py-2" id="tab-security-btn" data-bs-toggle="pill" data-bs-target="#tab-security" type="button" role="tab">
                        <i class="bi bi-shield-lock-fill fs-6"></i>
                        <span>Keamanan & Kebijakan</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeTab === 'theme' ? 'active' : ''; ?> d-flex align-items-center gap-2 fw-semibold px-4 py-2" id="tab-theme-btn" data-bs-toggle="pill" data-bs-target="#tab-theme" type="button" role="tab">
                        <i class="bi bi-palette-fill fs-6"></i>
                        <span>Tema & Tampilan</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeTab === 'audit' ? 'active' : ''; ?> d-flex align-items-center gap-2 fw-semibold px-4 py-2" id="tab-audit-btn" data-bs-toggle="pill" data-bs-target="#tab-audit" type="button" role="tab" onclick="loadAuditLogs()">
                        <i class="bi bi-clock-history fs-6"></i>
                        <span>Riwayat Perubahan</span>
                    </button>
                </li>
            </ul>

            <!-- Tab Content Panes -->
            <div class="tab-content" id="settingsTabContent">
                
                <!-- ============================================================== -->
                <!-- TAB 1: KONFIGURASI AI                                          -->
                <!-- ============================================================== -->
                <div class="tab-pane fade <?= $activeTab === 'ai' ? 'show active' : ''; ?>" id="tab-ai" role="tabpanel">
                    <form id="formAiSettings" onsubmit="handleSaveAi(event)">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">
                        
                        <div class="card border-0 shadow-xs border-radius-xl mb-4">
                            <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="fw-bold text-dark mb-0">Model & Provider AI</h5>
                                    <small class="text-muted">Tentukan provider LLM, model kecerdasan buatan, dan parameter komputasi.</small>
                                </div>
                                <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="ai_enabled" name="ai_enabled" value="1" <?= $aiEnabled ? 'checked' : ''; ?> onchange="updateAiSwitchLabel(this.checked)" style="cursor: pointer; width: 42px; height: 22px;">
                                    <label class="form-check-label fw-bold text-sm text-dark cursor-pointer mb-0 d-flex align-items-center" for="ai_enabled">
                                        <span id="ai_enabled_label">Chatbot <?= $aiEnabled ? 'Aktif' : 'Nonaktif'; ?></span>
                                        <span id="ai_enabled_badge" class="badge <?= $aiEnabled ? 'bg-success' : 'bg-secondary'; ?> ms-2"><?= $aiEnabled ? 'Aktif' : 'Nonaktif'; ?></span>
                                    </label>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <!-- Provider AI -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Provider AI <span class="text-danger">*</span></label>
                                        <select class="form-select shadow-none border" id="ai_provider" name="ai_provider" required onchange="handleProviderChange()">
                                            <option value="groq" <?= ($settings['ai.provider']['value'] ?? '') === 'groq' ? 'selected' : ''; ?>>Groq Cloud AI (Rekomendasi Utama)</option>
                                            <option value="gemini" <?= ($settings['ai.provider']['value'] ?? '') === 'gemini' ? 'selected' : ''; ?>>Google Gemini AI</option>
                                            <option value="mock" <?= ($settings['ai.provider']['value'] ?? '') === 'mock' ? 'selected' : ''; ?>>Mock AI (Mode Simulasi Offline)</option>
                                        </select>
                                        <div class="form-text text-xxs" id="ai_provider_helper">Groq memberikan inferensi ultra-cepat untuk chatbot e-commerce.</div>
                                    </div>

                                    <!-- Model AI -->
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary d-flex justify-content-between">
                                            <span>Model AI <span class="text-danger">*</span></span>
                                            <a href="javascript:void(0)" class="text-decoration-none text-xxs fw-bold text-primary" id="btnFetchModels" onclick="fetchProviderModels()">
                                                <i class="bi bi-arrow-repeat me-1"></i>Ambil dari Provider
                                            </a>
                                        </label>
                                        <div class="input-group">
                                            <input type="text" class="form-control shadow-none border" id="ai_model" name="ai_model" value="<?= $s('ai.model', 'openai/gpt-oss-20b'); ?>" required placeholder="Contoh: openai/gpt-oss-20b">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Pilih Model Cepat">
                                                Pilih
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" id="aiModelDropdownMenu" style="max-height: 250px; overflow-y: auto;">
                                                <!-- Dynamic via handleProviderChange -->
                                            </ul>
                                        </div>
                                        <div class="form-text text-xxs">Daftar model dapat dipilih dari provider atau diketik manual.</div>
                                    </div>

                                    <!-- Base URL -->
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">API Base URL</label>
                                        <input type="url" class="form-control shadow-none border" id="ai_api_base_url" name="ai_api_base_url" value="<?= $s('ai.api_base_url', 'https://api.groq.com/openai/v1'); ?>" placeholder="https://api.groq.com/openai/v1">
                                        <div class="form-text text-xxs">Endpoint standar OpenAI-compatible.</div>
                                    </div>

                                    <!-- API Key -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary d-flex justify-content-between align-items-center">
                                            <span id="ai_api_key_label">API Key Provider <span class="text-danger">*</span></span>
                                            <span id="aiApiKeyBadge" class="badge <?= $isConfigured('ai.api_key') ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'bg-warning bg-opacity-10 text-warning'; ?> text-xxs py-0 px-2 rounded-pill">
                                                <?= $isConfigured('ai.api_key') ? '<i class="bi bi-shield-lock-fill me-1"></i>Key Tersimpan & Terenkripsi' : 'Belum Dikonfigurasi'; ?>
                                            </span>
                                        </label>
                                        <div class="input-group">
                                            <input type="password" class="form-control shadow-none border font-monospace" id="ai_api_key" name="ai_api_key" value="<?= $s('ai.api_key'); ?>" data-saved-type="<?= $savedKeyType; ?>" placeholder="<?= $isConfigured('ai.api_key') ? '•••••••••••• (Biarkan kosong jika tidak diubah)' : 'Masukkan API Key gsk_...'; ?>" autocomplete="off">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('ai_api_key', this)" title="Lihat/Sembunyikan">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <div class="form-text text-xxs text-muted" id="ai_api_key_helper">
                                            Rahasia backend: Biarkan field ini tetap kosong untuk mempertahankan API Key lama tanpa mengirim ulangnya.
                                        </div>
                                    </div>

                                    <!-- Temperature -->
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary d-flex justify-content-between">
                                            <span>Temperature</span>
                                            <span class="badge bg-slate-900 text-white text-xxs" id="tempDisplay"><?= $s('ai.temperature', '0.3'); ?></span>
                                        </label>
                                        <input type="range" class="form-range" id="ai_temperature" name="ai_temperature" min="0.0" max="2.0" step="0.05" value="<?= $s('ai.temperature', '0.3'); ?>" oninput="document.getElementById('tempDisplay').textContent = this.value">
                                        <div class="d-flex justify-content-between text-xxs text-muted">
                                            <span>Faktual (0.0)</span>
                                            <span>Kreatif (1.0+)</span>
                                        </div>
                                    </div>

                                    <!-- Max Tokens -->
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Max Output Tokens</label>
                                        <input type="number" class="form-control shadow-none border" id="ai_max_output_tokens" name="ai_max_output_tokens" value="<?= $s('ai.max_output_tokens', '1024'); ?>" min="50" max="8192" required>
                                        <div class="form-text text-xxs">Batas token jawaban per request (50 - 8192).</div>
                                    </div>

                                    <!-- Timeout -->
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Timeout Request (detik)</label>
                                        <input type="number" class="form-control shadow-none border" id="ai_timeout_seconds" name="ai_timeout_seconds" value="<?= $s('ai.timeout_seconds', '30'); ?>" min="5" max="120" required>
                                        <div class="form-text text-xxs">Batas waktu tunggu koneksi ke API (5 - 120s).</div>
                                    </div>

                                    <!-- Max User Message Length -->
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Batas Karakter User</label>
                                        <input type="number" class="form-control shadow-none border" id="ai_max_user_message_length" name="ai_max_user_message_length" value="<?= $s('ai.max_user_message_length', '2000'); ?>" min="100" max="10000" required>
                                        <div class="form-text text-xxs">Maksimum panjang input pesan customer.</div>
                                    </div>

                                    <!-- Max History Messages -->
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Batas Riwayat Percakapan</label>
                                        <input type="number" class="form-control shadow-none border" id="ai_max_history_messages" name="ai_max_history_messages" value="<?= $s('ai.max_history_messages', '10'); ?>" min="2" max="50" required>
                                        <div class="form-text text-xxs">Pesan lampau yang disertakan ke LLM.</div>
                                    </div>

                                    <!-- Rate Limit per Minute -->
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Rate Limit (Req/Menit)</label>
                                        <input type="number" class="form-control shadow-none border" id="ai_rate_limit_per_minute" name="ai_rate_limit_per_minute" value="<?= $s('ai.rate_limit_per_minute', '20'); ?>" min="1" max="300" required>
                                        <div class="form-text text-xxs">Mencegah abuse/spam request AI.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card: Perilaku & Guardrail Chatbot -->
                        <div class="card border-0 shadow-xs border-radius-xl mb-4">
                            <div class="card-header bg-transparent border-bottom py-3">
                                <h5 class="fw-bold text-dark mb-0">Perilaku & Guardrail Keamanan Chatbot</h5>
                                <small class="text-muted">Chatbot hanya melayani seputar katalog, stok, pesanan, dan kebijakan toko nnaco_sa.</small>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Nama Asisten AI</label>
                                        <input type="text" class="form-control shadow-none border" id="ai_assistant_name" name="ai_assistant_name" value="<?= $s('ai.assistant_name', 'nnaco AI Assistant'); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Bahasa Utama</label>
                                        <input type="text" class="form-control shadow-none border" id="ai_primary_language" name="ai_primary_language" value="<?= $s('ai.primary_language', 'Bahasa Indonesia'); ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-bold text-xs text-uppercase text-secondary mb-0">System Prompt Inti</label>
                                            <button type="button" class="btn btn-link text-xxs text-decoration-none p-0 fw-semibold text-primary" onclick="resetSystemPromptToDefault()">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>Kembalikan ke Prompt Default
                                            </button>
                                        </div>
                                        <textarea class="form-control shadow-none border font-monospace text-xs" id="ai_system_prompt" name="ai_system_prompt" rows="5" required><?= $s('ai.system_prompt'); ?></textarea>
                                        <div class="form-text text-xxs text-muted">
                                            Aturan keamanan sistem (larangan coding, perlindungan prompt, dan pembatasan e-commerce) akan selalu diinject secara otomatis di backend demi integritas keamanan.
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Pesan Penolakan (Guardrail Rejection Message)</label>
                                        <textarea class="form-control shadow-none border text-xs" id="ai_rejection_message" name="ai_rejection_message" rows="2" required><?= $s('ai.rejection_message'); ?></textarea>
                                        <div class="form-text text-xxs">Ditampilkan ketika user menanyakan topik umum di luar konteks toko, meminta pembuatan kode, atau jailbreak.</div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-top py-3 d-flex flex-wrap gap-2 align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-outline-primary mb-0 d-inline-flex align-items-center gap-2 shadow-xs" onclick="handleTestAiConnection()">
                                        <i class="bi bi-broadcast fs-6" id="testAiIcon"></i>
                                        <span>Uji Koneksi AI</span>
                                    </button>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="reset" class="btn btn-white-corp mb-0 shadow-xs" onclick="resetFormConfirmation()">
                                        Batalkan Perubahan
                                    </button>
                                    <button type="submit" class="btn btn-dark-corp mb-0 d-inline-flex align-items-center gap-2 shadow-xs" id="btnSaveAi">
                                        <i class="bi bi-check2-circle fs-6"></i>
                                        <span>Simpan Konfigurasi AI</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- ============================================================== -->
                <!-- TAB 2: AUTHENTICATION OTP (FONNTE WHATSAPP)                    -->
                <!-- ============================================================== -->
                <div class="tab-pane fade <?= $activeTab === 'otp' ? 'show active' : ''; ?>" id="tab-otp" role="tabpanel">
                    <form id="formOtpSettings" onsubmit="handleSaveOtp(event)">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">

                        <div class="card border-0 shadow-xs border-radius-xl mb-4">
                            <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="fw-bold text-dark mb-0">Integrasi Fonnte WhatsApp Gateway</h5>
                                    <small class="text-muted">Kredensial API Fonnte untuk mengirimkan kode OTP verifikasi login ke nomor customer.</small>
                                </div>
                                <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                    <input class="form-check-input" type="checkbox" role="switch" id="otp_enabled" name="otp_enabled" value="1" <?= $otpEnabled ? 'checked' : ''; ?> style="cursor: pointer; width: 42px; height: 22px;">
                                    <label class="form-check-label fw-bold text-sm text-dark cursor-pointer mb-0" for="otp_enabled">
                                        OTP WhatsApp Aktif
                                    </label>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <!-- Provider Name -->
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Provider OTP</label>
                                        <input type="text" class="form-control shadow-none border bg-light" id="otp_provider" name="otp_provider" value="<?= $s('otp.provider', 'fonnte'); ?>" readonly>
                                        <div class="form-text text-xxs">Official WhatsApp Gateway Fonnte.</div>
                                    </div>

                                    <!-- Fonnte API URL -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Fonnte Send API URL <span class="text-danger">*</span></label>
                                        <input type="url" class="form-control shadow-none border" id="otp_api_url" name="otp_api_url" value="<?= $s('otp.api_url', 'https://api.fonnte.com/send'); ?>" required>
                                        <div class="form-text text-xxs">Endpoint pengiriman pesan WhatsApp.</div>
                                    </div>

                                    <!-- Fonnte Device Check URL -->
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Fonnte Device Check URL</label>
                                        <input type="url" class="form-control shadow-none border" id="otp_device_api_url" name="otp_device_api_url" value="<?= $s('otp.device_api_url', 'https://api.fonnte.com/device'); ?>" required>
                                        <div class="form-text text-xxs">Endpoint pemeriksaan koneksi perangkat tanpa kirim pesan.</div>
                                    </div>

                                    <!-- Fonnte Token -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary d-flex justify-content-between align-items-center">
                                            <span>Fonnte API Token <span class="text-danger">*</span></span>
                                            <?php if ($isConfigured('otp.api_token')): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 text-xxs py-0 px-2 rounded-pill">
                                                    <i class="bi bi-shield-lock-fill me-1"></i>Token Tersimpan & Terenkripsi
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning bg-opacity-10 text-warning text-xxs py-0 px-2 rounded-pill">Belum Dikonfigurasi</span>
                                            <?php endif; ?>
                                        </label>
                                        <div class="input-group">
                                            <input type="password" class="form-control shadow-none border font-monospace" id="otp_api_token" name="otp_api_token" value="<?= $s('otp.api_token'); ?>" placeholder="<?= $isConfigured('otp.api_token') ? '•••••••••••• (Biarkan kosong jika tidak diubah)' : 'Masukkan token akun Fonnte Anda'; ?>" autocomplete="off">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('otp_api_token', this)" title="Lihat/Sembunyikan">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <div class="form-text text-xxs text-muted">
                                            Disimpan sebagai secret backend. Tidak pernah diekspos ke HTML source, sessionStorage, atau localStorage.
                                        </div>
                                    </div>

                                    <!-- Sender Name / Device Info -->
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Identitas / Nama Pengirim</label>
                                        <input type="text" class="form-control shadow-none border" id="otp_sender_name" name="otp_sender_name" value="<?= $s('otp.sender_name', 'nnaco_sa'); ?>">
                                        <div class="form-text text-xxs">Label pengirim toko untuk referensi internal.</div>
                                    </div>

                                    <!-- Mode Pengujian / Simulasi -->
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Mode Simulasi Pengujian</label>
                                        <div class="form-check form-switch pt-2">
                                            <input class="form-check-input" type="checkbox" role="switch" id="otp_test_mode" name="otp_test_mode" value="1" <?= ($settings['otp.test_mode']['value'] ?? '0') === '1' ? 'checked' : ''; ?> style="cursor: pointer; width: 42px; height: 22px;">
                                            <label class="form-check-label text-xs fw-semibold" for="otp_test_mode">
                                                Simulasi Tanpa Kuota
                                            </label>
                                        </div>
                                        <div class="form-text text-xxs">Jika aktif, verifikasi diuji tanpa mengirim WhatsApp nyata.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card: Kebijakan Keamanan OTP -->
                        <div class="card border-0 shadow-xs border-radius-xl mb-4">
                            <div class="card-header bg-transparent border-bottom py-3">
                                <h5 class="fw-bold text-dark mb-0">Kebijakan Keamanan & Masa Berlaku OTP</h5>
                                <small class="text-muted">Parameter proteksi brute-force, kedaluwarsa kode, dan rate limiting.</small>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Panjang Digit OTP</label>
                                        <select class="form-select shadow-none border" id="otp_length" name="otp_length">
                                            <option value="4" <?= ($settings['otp.otp_length']['value'] ?? '') === '4' ? 'selected' : ''; ?>>4 Digit</option>
                                            <option value="6" <?= ($settings['otp.otp_length']['value'] ?? '') === '6' ? 'selected' : ''; ?>>6 Digit (Standar Keamanan)</option>
                                            <option value="8" <?= ($settings['otp.otp_length']['value'] ?? '') === '8' ? 'selected' : ''; ?>>8 Digit</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Masa Berlaku (Menit)</label>
                                        <input type="number" class="form-control shadow-none border" id="otp_expiry_minutes" name="otp_expiry_minutes" value="<?= $s('otp.expiry_minutes', '5'); ?>" min="1" max="60" required>
                                        <div class="form-text text-xxs">Default: 5 menit.</div>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Maksimum Percobaan</label>
                                        <input type="number" class="form-control shadow-none border" id="otp_max_attempts" name="otp_max_attempts" value="<?= $s('otp.max_attempts', '5'); ?>" min="1" max="10" required>
                                        <div class="form-text text-xxs">OTP dibatalkan setelah batas ini terlampaui.</div>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Cooldown Kirim Ulang (Detik)</label>
                                        <input type="number" class="form-control shadow-none border" id="otp_resend_cooldown_seconds" name="otp_resend_cooldown_seconds" value="<?= $s('otp.resend_cooldown_seconds', '60'); ?>" min="10" max="300" required>
                                        <div class="form-text text-xxs">Waktu tunggu sebelum kirim ulang (10 - 300s).</div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Batas Request per Nomor / Jam</label>
                                        <input type="number" class="form-control shadow-none border" id="otp_rate_limit_phone_per_hour" name="otp_rate_limit_phone_per_hour" value="<?= $s('otp.rate_limit_phone_per_hour', '5'); ?>" min="1" max="50" required>
                                        <div class="form-text text-xxs">Mencegah spam ke nomor WhatsApp yang sama.</div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Batas Request per IP / Jam</label>
                                        <input type="number" class="form-control shadow-none border" id="otp_rate_limit_ip_per_hour" name="otp_rate_limit_ip_per_hour" value="<?= $s('otp.rate_limit_ip_per_hour', '5'); ?>" min="1" max="100" required>
                                        <div class="form-text text-xxs">Mencegah penyerangan bot per alamat IP.</div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Masa Sesi Login OTP (Menit)</label>
                                        <input type="number" class="form-control shadow-none border" id="otp_session_expiry_minutes" name="otp_session_expiry_minutes" value="<?= $s('otp.session_expiry_minutes', '120'); ?>" min="15" max="1440" required>
                                        <div class="form-text text-xxs">Durasi login aktif pengguna setelah verifikasi.</div>
                                    </div>

                                    <!-- Template Pesan WhatsApp -->
                                    <div class="col-md-8">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary d-flex justify-content-between">
                                            <span>Template Pesan WhatsApp <span class="text-danger">*</span></span>
                                            <span class="text-xxs text-muted">Variabel: <code>{OTP}</code>, <code>{EXPIRY}</code>, <code>{APP_NAME}</code></span>
                                        </label>
                                        <textarea class="form-control shadow-none border text-xs" id="otp_message_template" name="otp_message_template" rows="4" required oninput="updateOtpPreview()"><?= $s('otp.message_template'); ?></textarea>
                                        <div class="form-text text-xxs">Wajib menyertakan placeholder <code>{OTP}</code>.</div>
                                    </div>

                                    <!-- Real-time Preview Chat WhatsApp -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Preview Tampilan di WhatsApp</label>
                                        <div class="p-3 rounded-3 border" style="background-color: #efeae2; min-height: 110px;">
                                            <div class="bg-white p-2 rounded-2 shadow-xs text-xs" style="border-radius: 7.5px 7.5px 7.5px 0px;">
                                                <div id="otpLivePreview" class="text-dark lh-sm" style="white-space: pre-wrap; font-size: 0.8rem;"></div>
                                                <div class="text-end text-xxs text-muted mt-1">
                                                    <span><?= date('H:i'); ?></span> <i class="bi bi-check2-all text-primary"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-top py-3 d-flex flex-wrap gap-2 align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-outline-dark mb-0 d-inline-flex align-items-center gap-2 shadow-xs" onclick="handleTestFonnteConnection()">
                                        <i class="bi bi-link-45deg fs-6" id="testFonnteIcon"></i>
                                        <span>Uji Koneksi Fonnte</span>
                                    </button>
                                    <button type="button" class="btn btn-outline-success mb-0 d-inline-flex align-items-center gap-2 shadow-xs" onclick="openTestMessageModal()">
                                        <i class="bi bi-send-check fs-6"></i>
                                        <span>Kirim Pesan Uji</span>
                                    </button>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="reset" class="btn btn-white-corp mb-0 shadow-xs" onclick="resetFormConfirmation()">
                                        Batalkan Perubahan
                                    </button>
                                    <button type="submit" class="btn btn-dark-corp mb-0 d-inline-flex align-items-center gap-2 shadow-xs" id="btnSaveOtp">
                                        <i class="bi bi-check2-circle fs-6"></i>
                                        <span>Simpan Konfigurasi OTP</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- ============================================================== -->
                <!-- TAB 3: KEAMANAN & KEBIJAKAN SISTEM                            -->
                <!-- ============================================================== -->
                <div class="tab-pane fade <?= $activeTab === 'security' ? 'show active' : ''; ?>" id="tab-security" role="tabpanel">
                    <form id="formSecuritySettings" onsubmit="handleSaveSecurity(event)">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">

                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-xs border-radius-xl">
                                    <div class="card-header bg-transparent border-bottom py-3">
                                        <h5 class="fw-bold text-dark mb-0">Kebijakan Keamanan & Secret Management</h5>
                                        <small class="text-muted">Proteksi data rahasia, enkripsi AES-256-CBC, dan manajemen sesi.</small>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row g-3">
                                            <!-- Enkripsi Secret -->
                                            <div class="col-12">
                                                <div class="d-flex align-items-center justify-content-between p-3 bg-gray-100 rounded-3 border">
                                                    <div>
                                                        <h6 class="fw-bold text-dark mb-1">
                                                            <i class="bi bi-key-fill text-primary me-2"></i>Enkripsi Rahasia Database (AES-256-CBC)
                                                        </h6>
                                                        <p class="text-xs text-muted mb-0">
                                                            Semua token API Fonnte dan Groq Key dienkripsi secara otomatis menggunakan IV unik sebelum disimpan di MySQL.
                                                        </p>
                                                    </div>
                                                    <span class="badge badge-corp badge-corp-success">Aktif Otomatis</span>
                                                </div>
                                            </div>

                                            <!-- Sesi Inaktif -->
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-xs text-uppercase text-secondary">Batas Sesi Tidak Aktif (Menit)</label>
                                                <input type="number" class="form-control shadow-none border" id="security_session_lifetime_minutes" name="security_session_lifetime_minutes" value="<?= $s('security.session_lifetime_minutes', '120'); ?>" min="15" max="1440" required>
                                                <div class="form-text text-xxs">Pengguna akan logout otomatis jika tidak ada aktivitas.</div>
                                            </div>

                                            <!-- Force HTTPS -->
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-xs text-uppercase text-secondary">Paksa Koneksi HTTPS</label>
                                                <div class="form-check form-switch pt-2">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="security_force_https" name="security_force_https" value="1" <?= ($settings['security.force_https']['value'] ?? '0') === '1' ? 'checked' : ''; ?> style="cursor: pointer; width: 42px; height: 22px;">
                                                    <label class="form-check-label text-xs fw-semibold" for="security_force_https">
                                                        Aktifkan HTTPS Redirect
                                                    </label>
                                                </div>
                                                <div class="form-text text-xxs">Disarankan untuk server produksi dengan sertifikat SSL aktif.</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent border-top py-3 text-end">
                                        <button type="submit" class="btn btn-dark-corp mb-0 shadow-xs" id="btnSaveSecurity">
                                            Simpan Kebijakan Keamanan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Matriks Izin Role -->
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-xs border-radius-xl h-100">
                                    <div class="card-header bg-transparent border-bottom py-3">
                                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-shield-lock me-2"></i>Matriks Hak Akses Role</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <ul class="list-group list-group-flush text-xs">
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                                <div>
                                                    <span class="fw-bold text-dark d-block">Owner</span>
                                                    <span class="text-muted text-xxs">Master Admin</span>
                                                </div>
                                                <span class="badge bg-success text-xxs">Akses Penuh (Full)</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                                <div>
                                                    <span class="fw-bold text-dark d-block">Admin</span>
                                                    <span class="text-muted text-xxs">Manajer Sistem</span>
                                                </div>
                                                <span class="badge bg-primary text-xxs">Kelola & Uji</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                                <div>
                                                    <span class="fw-bold text-dark d-block">Operator</span>
                                                    <span class="text-muted text-xxs">Staf Operasional</span>
                                                </div>
                                                <span class="badge bg-danger text-xxs">Ditolak (403)</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                                <div>
                                                    <span class="fw-bold text-dark d-block">Customer</span>
                                                    <span class="text-muted text-xxs">Pengguna Belanja</span>
                                                </div>
                                                <span class="badge bg-danger text-xxs">Ditolak (403)</span>
                                            </li>
                                        </ul>
                                        <div class="alert alert-secondary bg-gray-100 border text-xxs mt-3 mb-0 p-2 text-dark">
                                            <i class="bi bi-info-circle me-1"></i> Seluruh rute dilindungi oleh <code>Middleware::adminOrOwner()</code> dan validasi CSRF token.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- ============================================================== -->
                <!-- TAB 4: TEMA & TAMPILAN                                         -->
                <!-- ============================================================== -->
                <div class="tab-pane fade <?= $activeTab === 'theme' ? 'show active' : ''; ?>" id="tab-theme" role="tabpanel">
                    <form id="formThemeSettings" onsubmit="handleSaveTheme(event)">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">

                        <div class="card border-0 shadow-xs border-radius-xl mb-4">
                            <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <h5 class="fw-bold text-dark mb-0">Konfigurasi Tema &amp; Tampilan Website</h5>
                                    <small class="text-muted">Tentukan tema default untuk pengunjung baru dan preferensi visual toko.</small>
                                </div>
                                <span class="badge bg-primary-light text-primary border border-primary border-opacity-20 px-3 py-1 rounded-pill text-xs fw-bold">
                                    Global Theme Control
                                </span>
                            </div>
                            <div class="card-body p-4">
                                <?php
                                    $defaultTheme = $settings['theme.default']['value'] ?? 'system';
                                ?>
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-xs text-uppercase text-secondary">
                                        Tema Default Website (Untuk Pengunjung Baru) <span class="text-danger">*</span>
                                    </label>
                                    <p class="text-xs text-muted mb-3">
                                        Pengaturan ini menentukan tampilan awal ketika pengunjung pertama kali membuka website sebelum mereka memilih tema sendiri secara manual.
                                    </p>

                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="theme-card-option <?= $defaultTheme === 'light' ? 'active' : ''; ?>" onclick="selectSettingTheme('light')">
                                                <div class="theme-preview-box preview-light">
                                                    <div class="mock-sidebar"></div>
                                                    <div class="mock-header"></div>
                                                    <div class="mock-card"></div>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="fw-bold text-sm text-dark d-flex align-items-center gap-1">
                                                        <i class="bi bi-sun-fill text-warning"></i> Mode Terang
                                                    </span>
                                                    <input type="radio" name="theme_default" id="theme_default_light" value="light" <?= $defaultTheme === 'light' ? 'checked' : ''; ?> class="form-check-input mt-0">
                                                </div>
                                                <p class="text-muted text-xs mb-0">Nuansa putih cerah bersih, ideal untuk toko retail dan penggunaan siang hari.</p>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="theme-card-option <?= $defaultTheme === 'dark' ? 'active' : ''; ?>" onclick="selectSettingTheme('dark')">
                                                <div class="theme-preview-box preview-dark">
                                                    <div class="mock-sidebar"></div>
                                                    <div class="mock-header"></div>
                                                    <div class="mock-card"></div>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="fw-bold text-sm text-dark d-flex align-items-center gap-1">
                                                        <i class="bi bi-moon-stars-fill text-primary"></i> Mode Gelap
                                                    </span>
                                                    <input type="radio" name="theme_default" id="theme_default_dark" value="dark" <?= $defaultTheme === 'dark' ? 'checked' : ''; ?> class="form-check-input mt-0">
                                                </div>
                                                <p class="text-muted text-xs mb-0">Nuansa charcoal slate gelap yang nyaman di mata pada malam hari dan hemat energi.</p>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="theme-card-option <?= $defaultTheme === 'system' ? 'active' : ''; ?>" onclick="selectSettingTheme('system')">
                                                <div class="theme-preview-box preview-system">
                                                    <span class="mock-center-badge"><i class="bi bi-display me-1"></i> Auto</span>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="fw-bold text-sm text-dark d-flex align-items-center gap-1">
                                                        <i class="bi bi-display text-info"></i> Ikuti Sistem (OS)
                                                    </span>
                                                    <input type="radio" name="theme_default" id="theme_default_system" value="system" <?= $defaultTheme === 'system' ? 'checked' : ''; ?> class="form-check-input mt-0">
                                                </div>
                                                <p class="text-muted text-xs mb-0">Menyesuaikan secara dinamis dengan pengaturan Light/Dark pada OS perangkat pengguna.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info border d-flex align-items-start gap-2 text-xs mb-0">
                                    <i class="bi bi-info-circle-fill fs-6 text-info flex-shrink-0 mt-1"></i>
                                    <div>
                                        <b>Prinsip Desain &amp; Aksesibilitas:</b>
                                        Sistem tema nnaco_sa menggunakan <i>CSS Custom Properties</i> terpusat tanpa reload penuh halaman. Setiap pengguna (Customer, Operator, Admin, Owner) juga memiliki kontrol personal untuk memilih tema yang disukai melalui dropdown di navbar atau halaman profil masing-masing.
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-top py-3 d-flex justify-content-between align-items-center">
                                <span class="text-xs text-muted">Perubahan langsung berlaku untuk sesi baru dan dicatat ke audit log.</span>
                                <button type="submit" class="btn btn-sm btn-dark-corp mb-0 shadow-xs" id="btnSaveTheme">
                                    <i class="bi bi-check2-circle me-1"></i> Simpan Pengaturan Tema
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- ============================================================== -->
                <!-- TAB 5: RIWAYAT PERUBAHAN (AUDIT LOG)                          -->
                <!-- ============================================================== -->
                <div class="tab-pane fade <?= $activeTab === 'audit' ? 'show active' : ''; ?>" id="tab-audit" role="tabpanel">
                    <div class="card border-0 shadow-xs border-radius-xl">
                        <div class="card-header bg-transparent border-bottom py-3 d-flex flex-wrap gap-2 align-items-center justify-content-between">
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Riwayat Perubahan Sistem (Audit Log)</h5>
                                <small class="text-muted">Mencatat seluruh pembaruan konfigurasi dan uji koneksi tanpa memaparkan kunci rahasia.</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <select class="form-select form-select-sm shadow-none border text-xs" id="auditCategoryFilter" onchange="loadAuditLogs()" style="width: auto;">
                                    <option value="">Semua Kategori</option>
                                    <option value="ai">Konfigurasi AI</option>
                                    <option value="otp">Authentication OTP</option>
                                    <option value="security">Keamanan</option>
                                </select>
                                <button type="button" class="btn btn-sm btn-white-corp mb-0 shadow-xs" onclick="loadAuditLogs()" title="Muat Ulang Log">
                                    <i class="bi bi-arrow-repeat"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive p-0" style="max-height: 500px; overflow-y: auto;">
                            <table class="table align-items-center justify-content-center mb-0 text-xs" id="auditLogsTable">
                                <thead class="bg-gray-100 sticky-top">
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-4">Waktu</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Pengguna</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kategori</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Aksi / Event</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Keterangan</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center pe-4">IP Address</th>
                                    </tr>
                                </thead>
                                <tbody id="auditLogsBody">
                                    <?php if (empty($auditLogs)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat aktivitas yang tercatat.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($auditLogs as $log): ?>
                                            <tr>
                                                <td class="ps-4 text-nowrap text-muted text-xxs">
                                                    <i class="bi bi-calendar3 me-1"></i><?= date('d M Y H:i:s', strtotime($log['created_at'])); ?>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-dark d-block"><?= htmlspecialchars($log['user_name'] ?? 'System'); ?></span>
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary text-xxs py-0 px-2 rounded-pill"><?= htmlspecialchars($log['user_role'] ?? 'System'); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-corp badge-corp-<?= $log['category'] === 'ai' ? 'info' : ($log['category'] === 'otp' ? 'success' : 'dark'); ?>">
                                                        <?= strtoupper($log['category']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="font-monospace text-xxs fw-semibold text-dark"><?= htmlspecialchars($log['action']); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge rounded-pill text-xxs py-1 px-2 <?= $log['status'] === 'SUCCESS' ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger'; ?>">
                                                        <i class="bi bi-<?= $log['status'] === 'SUCCESS' ? 'check-circle-fill' : 'x-circle-fill'; ?> me-1"></i><?= $log['status']; ?>
                                                    </span>
                                                </td>
                                                <td style="max-width: 280px;" class="text-truncate" title="<?= htmlspecialchars($log['details'] ?? '-'); ?>">
                                                    <span class="text-secondary"><?= htmlspecialchars($log['details'] ?? '-'); ?></span>
                                                </td>
                                                <td class="text-center pe-4 text-xxs text-muted font-monospace">
                                                    <?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1'); ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- TAB 3: NOTIFIKASI WHATSAPP PESANAN (FONNTE GATEWAY)             -->
                <!-- ============================================================== -->
                <div class="tab-pane fade <?= $activeTab === 'order_wa' ? 'show active' : ''; ?>" id="tab-order-wa" role="tabpanel">
                    <form id="formOrderWaSettings" onsubmit="handleSaveOrderWa(event)">
                        <input type="hidden" name="csrf_token" value="<?= CSRF::generateToken(); ?>">

                        <!-- Card 1: Master Switch & Gateway Connection -->
                        <div class="card border-0 shadow-xs border-radius-xl mb-4">
                            <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <h5 class="fw-bold text-dark mb-0">Integrasi WhatsApp Gateway untuk Pesanan Customer</h5>
                                    <small class="text-muted">Kirim notifikasi otomatis secara real-time ke WhatsApp Customer saat status pesanan berubah.</small>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="order_wa_enabled" name="order_wa_enabled" value="1" <?= $orderWaEnabled ? 'checked' : ''; ?> style="cursor: pointer; width: 42px; height: 22px;">
                                        <label class="form-check-label fw-bold text-sm text-dark cursor-pointer mb-0" for="order_wa_enabled">
                                            Notifikasi Pesanan Aktif
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <!-- Mode Simulasi (Test Mode) -->
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 bg-light d-flex align-items-center justify-content-between">
                                            <div>
                                                <h6 class="fw-bold text-dark mb-0 text-sm">Mode Simulasi (Test Mode)</h6>
                                                <small class="text-muted text-xs">Simulasikan pengiriman pesan tanpa mengurangi kuota Fonnte nyata.</small>
                                            </div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input" type="checkbox" role="switch" id="order_wa_test_mode" name="order_wa_test_mode" value="1" <?= $orderWaTestMode ? 'checked' : ''; ?> style="cursor: pointer; width: 38px; height: 20px;">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Fonnte Token (Shared) -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary d-flex justify-content-between">
                                            <span>API Token Fonnte (Shared)</span>
                                            <span class="badge bg-info-subtle text-info border text-xxs px-2 py-0">Terhubung ke WhatsApp OTP</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border"><i class="bi bi-key-fill text-muted"></i></span>
                                            <input type="password" class="form-control shadow-none border" id="order_wa_api_token" name="otp_api_token" value="<?= $s('otp.api_token'); ?>" placeholder="Masukkan token Fonnte baru jika ingin memperbarui">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('order_wa_api_token')" title="Lihat/Sembunyikan Token">
                                                <i class="bi bi-eye" id="order_wa_api_token_icon"></i>
                                            </button>
                                        </div>
                                        <div class="form-text text-xxs">Token disimpan terenkripsi di database. Kosongkan jika tidak ingin mengubah token saat ini.</div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2 mt-4 pt-3 border-top flex-wrap">
                                    <button type="button" class="btn btn-sm btn-outline-dark d-inline-flex align-items-center gap-2 mb-0" onclick="testFonnteConnection()">
                                        <i class="bi bi-broadcast"></i>
                                        <span>Uji Koneksi Fonnte</span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-2 mb-0" onclick="openTestOrderWaModal()">
                                        <i class="bi bi-whatsapp"></i>
                                        <span>Kirim Pesan Uji Notifikasi Pesanan</span>
                                    </button>
                                    <small class="text-muted text-xs ms-auto">
                                        <i class="bi bi-shield-check text-success me-1"></i>Endpoint Resmi Fonnte API
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: Pemicu Notifikasi (Toggle per Status Pesanan) -->
                        <div class="card border-0 shadow-xs border-radius-xl mb-4">
                            <div class="card-header bg-transparent border-bottom py-3">
                                <h5 class="fw-bold text-dark mb-0">Event Pemicu Notifikasi WhatsApp</h5>
                                <small class="text-muted">Tentukan tahapan pesanan mana saja yang akan otomatis mengirimkan pesan WhatsApp ke nomor customer.</small>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <!-- 1. Pesanan Dibuat -->
                                    <div class="col-md-6 col-lg-3">
                                        <div class="p-3 border rounded-3 h-100 bg-white shadow-none d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="badge bg-primary-subtle text-primary border rounded-pill px-2 py-1 text-xxs">
                                                        <i class="bi bi-bag-plus me-1"></i> Baru
                                                    </span>
                                                    <div class="form-check form-switch m-0">
                                                        <input class="form-check-input" type="checkbox" role="switch" name="order_wa_notify_created" value="1" <?= ($settings['order_wa.notify_created']['value'] ?? '1') === '1' ? 'checked' : ''; ?> style="cursor: pointer;">
                                                    </div>
                                                </div>
                                                <h6 class="fw-bold text-dark text-sm mb-1">Pesanan Dibuat</h6>
                                                <p class="text-muted text-xxs mb-0">Dikirim saat customer berhasil checkout dan data tersimpan di DB.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 2. Menunggu Pembayaran -->
                                    <div class="col-md-6 col-lg-3">
                                        <div class="p-3 border rounded-3 h-100 bg-white shadow-none d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="badge bg-warning-subtle text-warning border rounded-pill px-2 py-1 text-xxs">
                                                        <i class="bi bi-clock-history me-1"></i> Pending
                                                    </span>
                                                    <div class="form-check form-switch m-0">
                                                        <input class="form-check-input" type="checkbox" role="switch" name="order_wa_notify_pending_payment" value="1" <?= ($settings['order_wa.notify_pending_payment']['value'] ?? '1') === '1' ? 'checked' : ''; ?> style="cursor: pointer;">
                                                    </div>
                                                </div>
                                                <h6 class="fw-bold text-dark text-sm mb-1">Menunggu Bayar</h6>
                                                <p class="text-muted text-xxs mb-0">Instruksi pembayaran, jumlah tagihan, dan tautan pembayaran.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 3. Pembayaran Berhasil -->
                                    <div class="col-md-6 col-lg-3">
                                        <div class="p-3 border rounded-3 h-100 bg-white shadow-none d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="badge bg-success-subtle text-success border rounded-pill px-2 py-1 text-xxs">
                                                        <i class="bi bi-check-circle me-1"></i> Lunas
                                                    </span>
                                                    <div class="form-check form-switch m-0">
                                                        <input class="form-check-input" type="checkbox" role="switch" name="order_wa_notify_payment_success" value="1" <?= ($settings['order_wa.notify_payment_success']['value'] ?? '1') === '1' ? 'checked' : ''; ?> style="cursor: pointer;">
                                                    </div>
                                                </div>
                                                <h6 class="fw-bold text-dark text-sm mb-1">Pembayaran Berhasil</h6>
                                                <p class="text-muted text-xxs mb-0">Hanya dikirim saat gateway atau admin memvalidasi status PAID.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 4. Pesanan Diproses -->
                                    <div class="col-md-6 col-lg-3">
                                        <div class="p-3 border rounded-3 h-100 bg-white shadow-none d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="badge bg-info-subtle text-info border rounded-pill px-2 py-1 text-xxs">
                                                        <i class="bi bi-gear-wide-connected me-1"></i> Proses
                                                    </span>
                                                    <div class="form-check form-switch m-0">
                                                        <input class="form-check-input" type="checkbox" role="switch" name="order_wa_notify_processing" value="1" <?= ($settings['order_wa.notify_processing']['value'] ?? '1') === '1' ? 'checked' : ''; ?> style="cursor: pointer;">
                                                    </div>
                                                </div>
                                                <h6 class="fw-bold text-dark text-sm mb-1">Pesanan Diproses</h6>
                                                <p class="text-muted text-xxs mb-0">Pemberitahuan bahwa pesanan disiapkan oleh tim toko.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 5. Pesanan Dikemas -->
                                    <div class="col-md-6 col-lg-3">
                                        <div class="p-3 border rounded-3 h-100 bg-white shadow-none d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-1 text-xxs">
                                                        <i class="bi bi-box-seam me-1"></i> Packing
                                                    </span>
                                                    <div class="form-check form-switch m-0">
                                                        <input class="form-check-input" type="checkbox" role="switch" name="order_wa_notify_packed" value="1" <?= ($settings['order_wa.notify_packed']['value'] ?? '1') === '1' ? 'checked' : ''; ?> style="cursor: pointer;">
                                                    </div>
                                                </div>
                                                <h6 class="fw-bold text-dark text-sm mb-1">Pesanan Dikemas</h6>
                                                <p class="text-muted text-xxs mb-0">Pesanan selesai dipacking dan siap diserahkan ke kurir.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 6. Pesanan Dikirim -->
                                    <div class="col-md-6 col-lg-3">
                                        <div class="p-3 border rounded-3 h-100 bg-white shadow-none d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="badge bg-primary-subtle text-primary border rounded-pill px-2 py-1 text-xxs">
                                                        <i class="bi bi-truck me-1"></i> Ekspedisi
                                                    </span>
                                                    <div class="form-check form-switch m-0">
                                                        <input class="form-check-input" type="checkbox" role="switch" name="order_wa_notify_shipped" value="1" <?= ($settings['order_wa.notify_shipped']['value'] ?? '1') === '1' ? 'checked' : ''; ?> style="cursor: pointer;">
                                                    </div>
                                                </div>
                                                <h6 class="fw-bold text-dark text-sm mb-1">Pesanan Dikirim</h6>
                                                <p class="text-muted text-xxs mb-0">Memuat nomor resi dan nama jasa pengiriman aktual.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 7. Pesanan Selesai -->
                                    <div class="col-md-6 col-lg-3">
                                        <div class="p-3 border rounded-3 h-100 bg-white shadow-none d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="badge bg-success-subtle text-success border rounded-pill px-2 py-1 text-xxs">
                                                        <i class="bi bi-patch-check me-1"></i> Selesai
                                                    </span>
                                                    <div class="form-check form-switch m-0">
                                                        <input class="form-check-input" type="checkbox" role="switch" name="order_wa_notify_completed" value="1" <?= ($settings['order_wa.notify_completed']['value'] ?? '1') === '1' ? 'checked' : ''; ?> style="cursor: pointer;">
                                                    </div>
                                                </div>
                                                <h6 class="fw-bold text-dark text-sm mb-1">Pesanan Selesai</h6>
                                                <p class="text-muted text-xxs mb-0">Ucapan terima kasih dan tautan ulasan produk untuk customer.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 8. Pesanan Dibatalkan -->
                                    <div class="col-md-6 col-lg-3">
                                        <div class="p-3 border rounded-3 h-100 bg-white shadow-none d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="badge bg-danger-subtle text-danger border rounded-pill px-2 py-1 text-xxs">
                                                        <i class="bi bi-x-circle me-1"></i> Batal
                                                    </span>
                                                    <div class="form-check form-switch m-0">
                                                        <input class="form-check-input" type="checkbox" role="switch" name="order_wa_notify_cancelled" value="1" <?= ($settings['order_wa.notify_cancelled']['value'] ?? '1') === '1' ? 'checked' : ''; ?> style="cursor: pointer;">
                                                    </div>
                                                </div>
                                                <h6 class="fw-bold text-dark text-sm mb-1">Pesanan Dibatalkan</h6>
                                                <p class="text-muted text-xxs mb-0">Informasi pembatalan beserta alasan yang tercatat.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: Template Editor Pesan WhatsApp -->
                        <div class="card border-0 shadow-xs border-radius-xl mb-4">
                            <div class="card-header bg-transparent border-bottom py-3">
                                <h5 class="fw-bold text-dark mb-1">Pengelolaan Template Pesan WhatsApp</h5>
                                <small class="text-muted">Sesuaikan narasi pesan untuk setiap status. Klik tombol variabel di bawah untuk menyisipkan placeholder data dinamis.</small>

                                <!-- Helper Variable Pills -->
                                <div class="mt-3 p-2 bg-light rounded-3 border">
                                    <span class="d-block text-xxs text-uppercase fw-bold text-secondary mb-1">Klik untuk menyisipkan variabel ke template aktif:</span>
                                    <div class="d-flex flex-wrap gap-1">
                                        <button type="button" class="btn btn-xs btn-white border shadow-none mb-0 text-xxs font-monospace" onclick="insertActivePlaceholder('{{nama_customer}}')">+ {{nama_customer}}</button>
                                        <button type="button" class="btn btn-xs btn-white border shadow-none mb-0 text-xxs font-monospace" onclick="insertActivePlaceholder('{{nomor_pesanan}}')">+ {{nomor_pesanan}}</button>
                                        <button type="button" class="btn btn-xs btn-white border shadow-none mb-0 text-xxs font-monospace" onclick="insertActivePlaceholder('{{tanggal_pesanan}}')">+ {{tanggal_pesanan}}</button>
                                        <button type="button" class="btn btn-xs btn-white border shadow-none mb-0 text-xxs font-monospace" onclick="insertActivePlaceholder('{{daftar_produk}}')">+ {{daftar_produk}}</button>
                                        <button type="button" class="btn btn-xs btn-white border shadow-none mb-0 text-xxs font-monospace" onclick="insertActivePlaceholder('{{total_pesanan}}')">+ {{total_pesanan}}</button>
                                        <button type="button" class="btn btn-xs btn-white border shadow-none mb-0 text-xxs font-monospace" onclick="insertActivePlaceholder('{{metode_pembayaran}}')">+ {{metode_pembayaran}}</button>
                                        <button type="button" class="btn btn-xs btn-white border shadow-none mb-0 text-xxs font-monospace" onclick="insertActivePlaceholder('{{status_pembayaran}}')">+ {{status_pembayaran}}</button>
                                        <button type="button" class="btn btn-xs btn-white border shadow-none mb-0 text-xxs font-monospace" onclick="insertActivePlaceholder('{{status_pesanan}}')">+ {{status_pesanan}}</button>
                                        <button type="button" class="btn btn-xs btn-white border shadow-none mb-0 text-xxs font-monospace" onclick="insertActivePlaceholder('{{nomor_resi}}')">+ {{nomor_resi}}</button>
                                        <button type="button" class="btn btn-xs btn-white border shadow-none mb-0 text-xxs font-monospace" onclick="insertActivePlaceholder('{{jasa_pengiriman}}')">+ {{jasa_pengiriman}}</button>
                                        <button type="button" class="btn btn-xs btn-white border shadow-none mb-0 text-xxs font-monospace" onclick="insertActivePlaceholder('{{alasan_pembatalan}}')">+ {{alasan_pembatalan}}</button>
                                        <button type="button" class="btn btn-xs btn-white border shadow-none mb-0 text-xxs font-monospace" onclick="insertActivePlaceholder('{{url_pesanan}}')">+ {{url_pesanan}}</button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body p-4">
                                <!-- Nav tabs template -->
                                <ul class="nav nav-pills bg-light p-1 rounded-3 mb-3 flex-wrap text-xs" id="tplSubTabs" role="tablist">
                                    <li class="nav-item">
                                        <button class="nav-link active py-1 px-3" data-bs-toggle="pill" data-bs-target="#tpl-created" type="button" onclick="setActiveTemplateTextarea('order_wa_template_created')">Dibuat</button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link py-1 px-3" data-bs-toggle="pill" data-bs-target="#tpl-pending" type="button" onclick="setActiveTemplateTextarea('order_wa_template_pending_payment')">Menunggu Bayar</button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link py-1 px-3" data-bs-toggle="pill" data-bs-target="#tpl-payment-success" type="button" onclick="setActiveTemplateTextarea('order_wa_template_payment_success')">Lunas</button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link py-1 px-3" data-bs-toggle="pill" data-bs-target="#tpl-processing" type="button" onclick="setActiveTemplateTextarea('order_wa_template_processing')">Diproses</button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link py-1 px-3" data-bs-toggle="pill" data-bs-target="#tpl-packed" type="button" onclick="setActiveTemplateTextarea('order_wa_template_packed')">Dikemas</button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link py-1 px-3" data-bs-toggle="pill" data-bs-target="#tpl-shipped" type="button" onclick="setActiveTemplateTextarea('order_wa_template_shipped')">Dikirim</button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link py-1 px-3" data-bs-toggle="pill" data-bs-target="#tpl-completed" type="button" onclick="setActiveTemplateTextarea('order_wa_template_completed')">Selesai</button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link py-1 px-3" data-bs-toggle="pill" data-bs-target="#tpl-cancelled" type="button" onclick="setActiveTemplateTextarea('order_wa_template_cancelled')">Dibatalkan</button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="tplSubTabContent">
                                    <!-- 1. Dibuat -->
                                    <div class="tab-pane fade show active" id="tpl-created" role="tabpanel">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Template: Pesanan Berhasil Dibuat</label>
                                        <textarea class="form-control shadow-none border font-monospace text-sm" id="order_wa_template_created" name="order_wa_template_created" rows="8"><?= htmlspecialchars($settings['order_wa.template_created']['value'] ?? ''); ?></textarea>
                                    </div>

                                    <!-- 2. Menunggu Bayar -->
                                    <div class="tab-pane fade" id="tpl-pending" role="tabpanel">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Template: Menunggu Pembayaran</label>
                                        <textarea class="form-control shadow-none border font-monospace text-sm" id="order_wa_template_pending_payment" name="order_wa_template_pending_payment" rows="8"><?= htmlspecialchars($settings['order_wa.template_pending_payment']['value'] ?? ''); ?></textarea>
                                    </div>

                                    <!-- 3. Pembayaran Berhasil -->
                                    <div class="tab-pane fade" id="tpl-payment-success" role="tabpanel">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Template: Pembayaran Berhasil Diverifikasi</label>
                                        <textarea class="form-control shadow-none border font-monospace text-sm" id="order_wa_template_payment_success" name="order_wa_template_payment_success" rows="8"><?= htmlspecialchars($settings['order_wa.template_payment_success']['value'] ?? ''); ?></textarea>
                                    </div>

                                    <!-- 4. Diproses -->
                                    <div class="tab-pane fade" id="tpl-processing" role="tabpanel">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Template: Pesanan Sedang Diproses</label>
                                        <textarea class="form-control shadow-none border font-monospace text-sm" id="order_wa_template_processing" name="order_wa_template_processing" rows="8"><?= htmlspecialchars($settings['order_wa.template_processing']['value'] ?? ''); ?></textarea>
                                    </div>

                                    <!-- 5. Dikemas -->
                                    <div class="tab-pane fade" id="tpl-packed" role="tabpanel">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Template: Pesanan Selesai Dikemas</label>
                                        <textarea class="form-control shadow-none border font-monospace text-sm" id="order_wa_template_packed" name="order_wa_template_packed" rows="8"><?= htmlspecialchars($settings['order_wa.template_packed']['value'] ?? ''); ?></textarea>
                                    </div>

                                    <!-- 6. Dikirim -->
                                    <div class="tab-pane fade" id="tpl-shipped" role="tabpanel">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Template: Pesanan Sedang Dikirim (Resi & Ekspedisi)</label>
                                        <textarea class="form-control shadow-none border font-monospace text-sm" id="order_wa_template_shipped" name="order_wa_template_shipped" rows="8"><?= htmlspecialchars($settings['order_wa.template_shipped']['value'] ?? ''); ?></textarea>
                                    </div>

                                    <!-- 7. Selesai -->
                                    <div class="tab-pane fade" id="tpl-completed" role="tabpanel">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Template: Pesanan Selesai & Ulasan Produk</label>
                                        <textarea class="form-control shadow-none border font-monospace text-sm" id="order_wa_template_completed" name="order_wa_template_completed" rows="8"><?= htmlspecialchars($settings['order_wa.template_completed']['value'] ?? ''); ?></textarea>
                                    </div>

                                    <!-- 8. Dibatalkan -->
                                    <div class="tab-pane fade" id="tpl-cancelled" role="tabpanel">
                                        <label class="form-label fw-bold text-xs text-uppercase text-secondary">Template: Pesanan Dibatalkan</label>
                                        <textarea class="form-control shadow-none border font-monospace text-sm" id="order_wa_template_cancelled" name="order_wa_template_cancelled" rows="8"><?= htmlspecialchars($settings['order_wa.template_cancelled']['value'] ?? ''); ?></textarea>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                                    <button type="submit" class="btn btn-dark-corp px-4 py-2 d-inline-flex align-items-center gap-2 mb-0 shadow-xs" id="btnSaveOrderWa">
                                        <i class="bi bi-save2"></i>
                                        <span>Simpan Seluruh Pengaturan Notifikasi Pesanan</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Card 4: Riwayat Log Notifikasi WhatsApp Pesanan -->
                    <div class="card border-0 shadow-xs border-radius-xl mb-4">
                        <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Riwayat Pengiriman Notifikasi WhatsApp Pesanan</h5>
                                <small class="text-muted">Pencatatan status, nomor terproteksi, jumlah percobaan (attempt), dan log kegagalan.</small>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <select class="form-select form-select-sm shadow-none border" id="filterOrderWaStatus" onchange="loadOrderWaLogs()" style="width: 140px;">
                                    <option value="">Semua Status</option>
                                    <option value="SENT">Terkirim (SENT)</option>
                                    <option value="FAILED">Gagal (FAILED)</option>
                                    <option value="PENDING">Pending</option>
                                </select>
                                <input type="text" class="form-control form-control-sm shadow-none border" id="searchOrderWaQuery" placeholder="Cari No Pesanan / HP..." onkeyup="debounceOrderWaSearch()" style="width: 180px;">
                                <button type="button" class="btn btn-sm btn-white border mb-0 d-inline-flex align-items-center gap-1" onclick="loadOrderWaLogs()">
                                    <i class="bi bi-arrow-clockwise"></i>
                                    <span>Segarkan</span>
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0" id="tableOrderWaLogs">
                                    <thead class="bg-light text-xxs text-uppercase text-secondary font-weight-bolder">
                                        <tr>
                                            <th class="ps-4 py-3">Waktu</th>
                                            <th>Pesanan</th>
                                            <th>Event</th>
                                            <th>Nomor WA Customer</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Percobaan</th>
                                            <th>Keterangan / Pesan Error</th>
                                            <th class="text-end pe-4">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="orderWaLogsBody" class="text-xs">
                                        <?php if (empty($orderWaLogs)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-4 text-muted">
                                                    <i class="bi bi-inbox fs-4 d-block mb-1 text-secondary opacity-50"></i>
                                                    Belum ada riwayat pengiriman notifikasi WhatsApp pesanan.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($orderWaLogs as $log): ?>
                                                <tr>
                                                    <td class="ps-4 text-nowrap text-muted font-monospace">
                                                        <?= date('d M Y, H:i', strtotime($log['created_at'])); ?>
                                                    </td>
                                                    <td>
                                                        <span class="fw-bold text-dark">#ORD-<?= str_pad($log['order_id'] ?? 0, 5, '0', STR_PAD_LEFT); ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-light text-dark border text-xxs">
                                                            <?= htmlspecialchars($log['event_type']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="font-monospace text-muted">
                                                        <?= OrderNotificationService::maskPhone($log['recipient_phone']); ?>
                                                        <small class="d-block text-xxs text-secondary"><?= htmlspecialchars($log['customer_name'] ?? '-'); ?></small>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php
                                                        $statusClass = match ($log['status']) {
                                                            'SENT'    => 'bg-success text-white',
                                                            'FAILED'  => 'bg-danger text-white',
                                                            default   => 'bg-warning text-dark',
                                                        };
                                                        ?>
                                                        <span class="badge <?= $statusClass; ?> text-xxs px-2 py-1">
                                                            <?= $log['status']; ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-light text-secondary border text-xxs"><?= (int)$log['attempt_count']; ?>x</span>
                                                    </td>
                                                    <td class="text-truncate" style="max-width: 250px;" title="<?= htmlspecialchars($log['error_message'] ?? $log['provider_message_id'] ?? '-'); ?>">
                                                        <?php if ($log['status'] === 'FAILED'): ?>
                                                            <span class="text-danger"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($log['error_message'] ?? 'Gagal'); ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted"><?= htmlspecialchars($log['provider_message_id'] ?? 'Terkirim via Fonnte'); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-end pe-4">
                                                        <?php if ($log['status'] !== 'SENT'): ?>
                                                            <button type="button" class="btn btn-xs btn-outline-primary mb-0 d-inline-flex align-items-center gap-1" onclick="handleRetryWa(<?= (int)$log['id']; ?>)" id="btnRetry_<?= (int)$log['id']; ?>">
                                                                <i class="bi bi-arrow-repeat"></i>
                                                                <span>Retry</span>
                                                            </button>
                                                        <?php else: ?>
                                                            <span class="text-success text-xxs"><i class="bi bi-check-all fs-6"></i> Sukses</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: HASIL UJI KONEKSI AI                                    -->
<!-- ============================================================== -->
<div class="modal fade" id="modalAiTest" tabindex="-1" aria-labelledby="modalAiTestLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg border-radius-xl">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title fw-bold text-dark" id="modalAiTestLabel">
                    <i class="bi bi-broadcast me-2 text-primary"></i>Hasil Uji Koneksi Provider AI
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4 text-center" id="modalAiTestBody">
                <div class="spinner-border text-primary my-4" role="status">
                    <span class="visually-hidden">Menguji koneksi ke Groq API...</span>
                </div>
                <p class="text-sm text-muted">Sedang mengirim request ping minimal ke provider AI...</p>
            </div>
            <div class="modal-footer border-top py-2">
                <button type="button" class="btn btn-sm btn-white-corp mb-0" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: HASIL UJI KONEKSI FONNTE                                -->
<!-- ============================================================== -->
<div class="modal fade" id="modalFonnteTest" tabindex="-1" aria-labelledby="modalFonnteTestLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg border-radius-xl">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title fw-bold text-dark" id="modalFonnteTestLabel">
                    <i class="bi bi-whatsapp me-2 text-success"></i>Hasil Uji Gateway Fonnte
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4 text-center" id="modalFonnteTestBody">
                <div class="spinner-border text-success my-4" role="status">
                    <span class="visually-hidden">Memeriksa status perangkat Fonnte...</span>
                </div>
                <p class="text-sm text-muted">Menghubungi endpoint perangkat Fonnte tanpa mengirim pesan...</p>
            </div>
            <div class="modal-footer border-top py-2">
                <button type="button" class="btn btn-sm btn-white-corp mb-0" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: KONFIRMASI KIRIM PESAN UJI WHATSAPP                     -->
<!-- ============================================================== -->
<div class="modal fade" id="modalTestMessage" tabindex="-1" aria-labelledby="modalTestMessageLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg border-radius-xl">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title fw-bold text-dark" id="modalTestMessageLabel">
                    <i class="bi bi-send-check me-2 text-success"></i>Kirim Pesan Uji WhatsApp
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning border text-xs mb-3">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    <b>Perhatian:</b> Tindakan ini akan mengirimkan pesan WhatsApp nyata melalui gateway Fonnte dan memotong 1 kuota pesan. Ini <u>bukan kode OTP login</u>.
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-xs text-uppercase text-secondary">Nomor WhatsApp Tujuan <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control shadow-none border" id="testMsgPhone" placeholder="Contoh: 08123456789 atau 628123456789" required autocomplete="off">
                    <div class="form-text text-xxs">Pastikan nomor aktif dan terhubung dengan aplikasi WhatsApp.</div>
                </div>

                <div class="form-check mb-2">
                    <input class="form-check-input cursor-pointer" type="checkbox" id="testMsgConfirmCheck" required>
                    <label class="form-check-label text-xs cursor-pointer text-dark" for="testMsgConfirmCheck">
                        Saya mengonfirmasi untuk mengirimkan pesan pengujian ini sekarang.
                    </label>
                </div>

                <div id="testMsgFeedback" class="mt-2"></div>
            </div>
            <div class="modal-footer border-top py-2 d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-white-corp mb-0" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sm btn-success mb-0 d-inline-flex align-items-center gap-1" id="btnSubmitTestMsg" onclick="submitSendTestMessage()">
                    <i class="bi bi-send-fill text-xs"></i>
                    <span>Kirim Sekarang</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: UJI KIRIM PESAN NOTIFIKASI WHATSAPP PESANAN             -->
<!-- ============================================================== -->
<div class="modal fade" id="modalTestOrderWa" tabindex="-1" aria-labelledby="modalTestOrderWaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg border-radius-xl">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title fw-bold text-dark" id="modalTestOrderWaLabel">
                    <i class="bi bi-whatsapp me-2 text-success"></i>Kirim Pesan Uji Notifikasi Pesanan
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-light border text-xs mb-3">
                    <i class="bi bi-info-circle text-primary me-1"></i>
                    Fitur ini akan mengirimkan pesan pengujian menggunakan template status aktual ke nomor WhatsApp tujuan di bawah ini.
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-xs text-uppercase text-secondary">Pilih Jenis Event Status <span class="text-danger">*</span></label>
                    <select class="form-select shadow-none border" id="testOrderWaEventType">
                        <option value="order_created">Pesanan Berhasil Dibuat (Baru)</option>
                        <option value="payment_pending">Menunggu Pembayaran</option>
                        <option value="payment_confirmed">Pembayaran Berhasil Diverifikasi</option>
                        <option value="order_processing">Pesanan Sedang Diproses</option>
                        <option value="order_packed">Pesanan Selesai Dikemas</option>
                        <option value="order_shipped">Pesanan Sedang Dikirim</option>
                        <option value="order_completed">Pesanan Selesai</option>
                        <option value="order_cancelled">Pesanan Dibatalkan</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-xs text-uppercase text-secondary">Nomor WhatsApp Tujuan <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control shadow-none border" id="testOrderWaPhone" placeholder="Contoh: 08123456789 atau 628123456789" required autocomplete="off">
                    <div class="form-text text-xxs">Pastikan nomor aktif dan terhubung ke WhatsApp.</div>
                </div>

                <div id="testOrderWaFeedback" class="mt-2"></div>
            </div>
            <div class="modal-footer border-top py-2 d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-white-corp mb-0" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-sm btn-success mb-0 d-inline-flex align-items-center gap-1" id="btnSubmitTestOrderWa" onclick="submitTestOrderWa()">
                    <i class="bi bi-send-fill text-xs"></i>
                    <span>Kirim Pesan Uji</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- JAVASCRIPT LOGIC & HANDLERS                                    -->
<!-- ============================================================== -->
<script>
const BASE_URL = '<?= BASEURL; ?>';
const CSRF_TOKEN = '<?= CSRF::generateToken(); ?>';

// Live Preview WhatsApp Template
function updateOtpPreview() {
    const template = document.getElementById('otp_message_template')?.value || '';
    const expiry = document.getElementById('otp_expiry_minutes')?.value || '5';
    const otpSample = '123456';
    const preview = template
        .replace(/\{OTP\}/g, `*${otpSample}*`)
        .replace(/\{EXPIRY\}/g, expiry)
        .replace(/\{APP_NAME\}/g, 'nnaco_sa');
    const el = document.getElementById('otpLivePreview');
    if (el) el.textContent = preview;
}
// Switch Label & Badge Dynamic Update
function updateAiSwitchLabel(checked) {
    const lbl = document.getElementById('ai_enabled_label');
    const badge = document.getElementById('ai_enabled_badge');
    if (lbl) lbl.textContent = checked ? 'Chatbot Aktif' : 'Chatbot Nonaktif';
    if (badge) {
        badge.textContent = checked ? 'Aktif' : 'Nonaktif';
        badge.className = 'badge ms-2 ' + (checked ? 'bg-success' : 'bg-secondary');
    }
}

// Provider Configurations & Preset Models
const PROVIDER_CONFIG = {
    groq: {
        name: 'Groq Cloud AI',
        helper: 'Groq memberikan inferensi ultra-cepat (LPU) untuk chatbot e-commerce.',
        baseUrl: 'https://api.groq.com/openai/v1',
        defaultModel: 'openai/gpt-oss-20b',
        keyLabel: 'Groq Cloud API Key',
        keyPlaceholder: 'Masukkan API Key Groq gsk_...',
        keyHelper: 'Dapatkan API Key gratis di console.groq.com. Biarkan kosong jika tidak diubah.',
        fetchText: '<i class="bi bi-arrow-repeat me-1"></i>Ambil dari Provider',
        models: [
            { id: 'openai/gpt-oss-20b', label: 'openai/gpt-oss-20b', badge: 'Default Toko' },
            { id: 'openai/gpt-oss-120b', label: 'openai/gpt-oss-120b', badge: 'High-Capacity' },
            { id: 'llama-3.3-70b-versatile', label: 'llama-3.3-70b-versatile', badge: 'Rekomendasi' },
            { id: 'llama-3.1-8b-instant', label: 'llama-3.1-8b-instant', badge: 'Ultra Cepat' },
            { id: 'qwen/qwen3.8-27b', label: 'qwen/qwen3.8-27b' },
            { id: 'mixtral-8x7b-32768', label: 'mixtral-8x7b-32768' }
        ]
    },
    gemini: {
        name: 'Google Gemini AI',
        helper: 'Google Gemini AI menyediakan kapabilitas penalaran cerdas dan pemahaman konteks luas.',
        baseUrl: 'https://generativelanguage.googleapis.com/v1beta/openai',
        defaultModel: 'gemini-3.5-flash-lite',
        keyLabel: 'Google Gemini API Key',
        keyPlaceholder: 'Masukkan API Key Gemini (AQ... atau AIzaSy...)',
        keyHelper: 'Dapatkan API Key di Google AI Studio (aistudio.google.com). Biarkan kosong jika tidak diubah.',
        fetchText: '<i class="bi bi-arrow-repeat me-1"></i>Pilihan Model Gemini',
        models: [
            { id: 'gemini-3.5-flash-lite', label: 'gemini-3.5-flash-lite', badge: 'Rekomendasi Cepat & Stabil' },
            { id: 'gemini-3.8-flash', label: 'gemini-3.8-flash', badge: 'Generasi Terbaru' },
            { id: 'gemini-3.5-flash', label: 'gemini-3.5-flash', badge: 'Performa Tinggi' },
            { id: 'gemini-flash-latest', label: 'gemini-flash-latest', badge: 'Auto Update' },
            { id: 'gemini-2.5-pro', label: 'gemini-2.5-pro', badge: 'Akurasi Tinggi' }
        ]
    },
    mock: {
        name: 'Mock AI (Mode Simulasi Offline)',
        helper: 'Mode Simulasi Offline berjalan lokal membaca database toko tanpa memanggil API eksternal.',
        baseUrl: 'local://mock',
        defaultModel: 'mock-ecommerce-fast',
        keyLabel: 'API Key (Opsional untuk Mock AI)',
        keyPlaceholder: 'Mode simulasi offline (tanpa API key eksternal)',
        keyHelper: 'Mock AI tidak memerlukan API Key eksternal dan dapat dijalankan tanpa kuota internet.',
        fetchText: '<i class="bi bi-arrow-repeat me-1"></i>Preset Model Mock',
        models: [
            { id: 'mock-ecommerce-fast', label: 'mock-ecommerce-fast', badge: 'Simulasi Cepat' },
            { id: 'mock-analytics-smart', label: 'mock-analytics-smart', badge: 'Simulasi Analitik' }
        ]
    }
};

function handleProviderChange(isInit = false) {
    const providerSelect = document.getElementById('ai_provider');
    if (!providerSelect) return;
    const provider = providerSelect.value || 'groq';
    const conf = PROVIDER_CONFIG[provider] || PROVIDER_CONFIG.groq;

    // Helper text
    const helperEl = document.getElementById('ai_provider_helper');
    if (helperEl) helperEl.textContent = conf.helper;

    // Key label and helper
    const keyLabelEl = document.getElementById('ai_api_key_label');
    if (keyLabelEl) keyLabelEl.innerHTML = `${conf.keyLabel} <span class="text-danger">*</span>`;

    const keyHelperEl = document.getElementById('ai_api_key_helper');
    if (keyHelperEl) keyHelperEl.textContent = conf.keyHelper;

    const apiKeyInput = document.getElementById('ai_api_key');
    if (apiKeyInput && !apiKeyInput.value) {
        apiKeyInput.placeholder = conf.keyPlaceholder;
    }

    // Dynamic Badge Update based on key compatibility
    const badgeEl = document.getElementById('aiApiKeyBadge');
    if (badgeEl && apiKeyInput) {
        const savedType = apiKeyInput.getAttribute('data-saved-type') || 'none';
        if (provider === 'mock') {
            badgeEl.className = 'badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 text-xxs py-0 px-2 rounded-pill';
            badgeEl.innerHTML = '<i class="bi bi-info-circle me-1"></i>Offline / Tanpa Kunci';
        } else if (savedType === provider) {
            badgeEl.className = 'badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 text-xxs py-0 px-2 rounded-pill';
            badgeEl.innerHTML = `<i class="bi bi-shield-lock-fill me-1"></i>Key ${conf.name} Tersimpan`;
        } else if (savedType !== 'none') {
            badgeEl.className = 'badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 text-xxs py-0 px-2 rounded-pill';
            badgeEl.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i>Kunci Tersimpan Adalah Kunci ${savedType.toUpperCase()}`;
            if (!isInit) {
                showAlert(`Perhatian: Kunci yang tersimpan saat ini berformat <b>${savedType.toUpperCase()}</b>. Untuk menggunakan <b>${conf.name}</b>, masukkan API Key baru di kolom API Key.`, 'warning');
            }
        } else {
            badgeEl.className = 'badge bg-warning bg-opacity-10 text-warning text-xxs py-0 px-2 rounded-pill';
            badgeEl.innerHTML = 'Belum Dikonfigurasi';
        }
    }

    // Fetch button
    const btnFetch = document.getElementById('btnFetchModels');
    if (btnFetch) btnFetch.innerHTML = conf.fetchText;

    // Base URL & Model updates (if changed actively by user)
    const baseUrlInput = document.getElementById('ai_api_base_url');
    const modelInput = document.getElementById('ai_model');

    if (!isInit) {
        if (baseUrlInput) {
            baseUrlInput.value = conf.baseUrl;
        }
        if (modelInput) {
            modelInput.value = conf.defaultModel;
            showAlert(`Provider dialihkan ke ${conf.name}. Model disesuaikan ke "${conf.defaultModel}".`, 'info');
        }
    }

    // Populate Preset Dropdown Menu
    const dropdown = document.getElementById('aiModelDropdownMenu');
    if (dropdown) {
        let html = `<li><h6 class="dropdown-header text-xxs">Model ${conf.name}</h6></li>`;
        conf.models.forEach(m => {
            const badgeHtml = m.badge ? `<span class="badge bg-light text-dark ms-1">${m.badge}</span>` : '';
            html += `<li><a class="dropdown-item text-xs" href="javascript:void(0)" onclick="selectModel('${m.id}')">${m.label} ${badgeHtml}</a></li>`;
        });
        dropdown.innerHTML = html;
    }
}
window.handleProviderChange = handleProviderChange;

document.addEventListener('DOMContentLoaded', function() {
    updateOtpPreview();
    handleProviderChange(true);
    const aiSw = document.getElementById('ai_enabled');
    if (aiSw) updateAiSwitchLabel(aiSw.checked);
});

// Toggle Visibility Password
function togglePasswordVisibility(fieldId, btn) {
    const input = document.getElementById(fieldId);
    if (!input) return;
    const isPass = input.type === 'password';
    input.type = isPass ? 'text' : 'password';
    btn.innerHTML = isPass ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
}

// Reset System Prompt to Default
function resetSystemPromptToDefault() {
    if (!confirm('Kembalikan system prompt chatbot ke pengaturan baku sistem?')) return;
    const defaultPrompt = "Kamu adalah nnaco AI Assistant, asisten virtual resmi untuk toko aksesoris nnaco_sa. Kamu ramah, sopan, dan berwawasan luas seputar aksesoris wanita/pria, perhiasan, gelang, kalung, dan cincin. Kamu hanya boleh membantu pertanyaan seputar produk, katalog, stok, harga, pesanan, pembayaran, pengiriman, dan kebijakan toko nnaco_sa. Tolak dengan sopan setiap pertanyaan di luar konteks toko, pembuatan kode pemrograman, tugas umum, atau instruksi yang melanggar keamanan.";
    document.getElementById('ai_system_prompt').value = defaultPrompt;
    showAlert('System prompt telah dikembalikan ke standar bawaan. Klik Simpan untuk menerapkan.', 'info');
}

// Reset Form Confirmation
function resetFormConfirmation() {
    showAlert('Perubahan form telah dibatalkan.', 'secondary');
    setTimeout(updateOtpPreview, 50);
}

// Select Model Quick Action
function selectModel(modelName) {
    const input = document.getElementById('ai_model');
    if (input) {
        input.value = modelName;
        showAlert(`Model "${modelName}" dipilih. Klik Simpan untuk menerapkan.`, 'info');
    }
}

// Fetch Models dynamically via backend for active provider
async function fetchProviderModels() {
    const provider = document.getElementById('ai_provider')?.value || 'groq';
    const key = document.getElementById('ai_api_key')?.value || '';
    const dropdown = document.getElementById('aiModelDropdownMenu');
    if (!dropdown) return;

    showAlert(`Sedang memuat daftar model dari provider ${provider.toUpperCase()}...`, 'info');

    try {
        const resp = await fetch(`${BASE_URL}/settings/getAiModels`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({ ai_provider: provider, ai_api_key: key })
        });
        const res = await resp.json();

        if (res.models && res.models.length > 0) {
            let html = `<li><h6 class="dropdown-header text-xxs">Model Resmi dari ${res.provider?.toUpperCase() || provider.toUpperCase()}</h6></li>`;
            res.models.forEach(m => {
                html += `<li><a class="dropdown-item text-xs" href="javascript:void(0)" onclick="selectModel('${m}')">${m}</a></li>`;
            });
            dropdown.innerHTML = html;
            showAlert(`Berhasil memuat ${res.models.length} model untuk ${provider.toUpperCase()}.`, 'success');
        } else {
            showAlert(res.message || 'Gagal memuat model dari provider.', 'warning');
        }
    } catch (e) {
        showAlert('Gagal menghubungi backend untuk mengambil model.', 'danger');
    }
}
const fetchGroqModels = fetchProviderModels;
window.fetchProviderModels = fetchProviderModels;
window.fetchGroqModels = fetchProviderModels;

// Handle Save AI Settings
async function handleSaveAi(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveAi');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

    const form = document.getElementById('formAiSettings');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    data.ai_enabled = form.ai_enabled.checked ? '1' : '0';

    try {
        const resp = await fetch(`${BASE_URL}/settings/saveAi`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify(data)
        });
        const res = await resp.json();

        if (res.success) {
            showAlert(res.message, 'success');
        } else {
            let errText = res.message || 'Gagal menyimpan konfigurasi AI.';
            if (res.errors) {
                errText += '<br>' + Object.values(res.errors).join('<br>');
            }
            showAlert(errText, 'danger');
        }
    } catch (e) {
        showAlert('Terjadi kesalahan jaringan saat menyimpan konfigurasi AI.', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
}

// Handle Test AI Connection
async function handleTestAiConnection() {
    const modalEl = document.getElementById('modalAiTest');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    const provider = document.getElementById('ai_provider')?.value || 'groq';
    const model = document.getElementById('ai_model')?.value || '';
    const key = document.getElementById('ai_api_key')?.value || '';
    const baseUrl = document.getElementById('ai_api_base_url')?.value || '';

    const body = document.getElementById('modalAiTestBody');
    body.innerHTML = `
        <div class="spinner-border text-primary my-4" role="status">
            <span class="visually-hidden">Menguji koneksi ke ${escapeHtml(provider.toUpperCase())} AI...</span>
        </div>
        <p class="text-sm text-muted">Sedang mengirim ping minimal ke provider ${escapeHtml(provider.toUpperCase())} AI...</p>
    `;

    try {
        const resp = await fetch(`${BASE_URL}/settings/testAi`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                ai_provider: provider,
                ai_model: model,
                ai_api_key: key,
                ai_api_base_url: baseUrl
            })
        });
        const res = await resp.json();

        if (res.success) {
            body.innerHTML = `
                <div class="mb-3">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3.5rem;"></i>
                </div>
                <h5 class="fw-bold text-success mb-1">Koneksi AI Berhasil!</h5>
                <p class="text-sm text-dark mb-3">${escapeHtml(res.message)}</p>
                <div class="bg-gray-100 rounded-3 p-3 text-start border text-xs">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Provider:</span>
                        <span class="fw-bold text-dark">${escapeHtml(res.provider || provider.toUpperCase())}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Model:</span>
                        <span class="fw-bold text-dark font-monospace">${escapeHtml(res.model || model)}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Response Time:</span>
                        <span class="badge bg-success">${res.latency_ms} ms</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Waktu Uji:</span>
                        <span class="text-dark">${escapeHtml(res.tested_at || '')}</span>
                    </div>
                </div>
            `;
        } else {
            body.innerHTML = `
                <div class="mb-3">
                    <i class="bi bi-x-circle-fill text-danger" style="font-size: 3.5rem;"></i>
                </div>
                <h5 class="fw-bold text-danger mb-1">Koneksi AI Gagal</h5>
                <p class="text-sm text-dark mb-3">${escapeHtml(res.message || 'Provider tidak merespons.')}</p>
                <div class="alert alert-danger bg-danger bg-opacity-10 border border-danger border-opacity-25 text-start text-xs p-3">
                    <i class="bi bi-shield-exclamation me-1"></i>
                    Pastikan API Key benar dan akun provider memiliki akses ke model <b>${escapeHtml(model)}</b>.
                </div>
            `;
        }
    } catch (e) {
        body.innerHTML = `
            <div class="mb-3">
                <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 3.5rem;"></i>
            </div>
            <h5 class="fw-bold text-danger mb-1">Kesalahan Jaringan</h5>
            <p class="text-sm text-muted">Tidak dapat menghubungi server backend.</p>
        `;
    }
}

// Handle Save OTP Settings
async function handleSaveOtp(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveOtp');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

    const form = document.getElementById('formOtpSettings');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    if (!form.otp_enabled.checked) data.otp_enabled = '0';
    if (!form.otp_test_mode.checked) data.otp_test_mode = '0';

    try {
        const resp = await fetch(`${BASE_URL}/settings/saveOtp`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify(data)
        });
        const res = await resp.json();

        if (res.success) {
            showAlert(res.message, 'success');
        } else {
            let errText = res.message || 'Gagal menyimpan konfigurasi OTP.';
            if (res.errors) {
                errText += '<br>' + Object.values(res.errors).join('<br>');
            }
            showAlert(errText, 'danger');
        }
    } catch (e) {
        showAlert('Terjadi kesalahan jaringan saat menyimpan konfigurasi OTP.', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
}

// Handle Test Fonnte Connection (Device / Account Check)
async function handleTestFonnteConnection() {
    const modalEl = document.getElementById('modalFonnteTest');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    const body = document.getElementById('modalFonnteTestBody');
    body.innerHTML = `
        <div class="spinner-border text-success my-4" role="status">
            <span class="visually-hidden">Memeriksa status perangkat Fonnte...</span>
        </div>
        <p class="text-sm text-muted">Menghubungi endpoint /device Fonnte tanpa mengirim pesan...</p>
    `;

    const token = document.getElementById('otp_api_token')?.value || '';
    const deviceUrl = document.getElementById('otp_device_api_url')?.value || '';

    try {
        const resp = await fetch(`${BASE_URL}/settings/testOtp`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                otp_api_token: token,
                otp_device_api_url: deviceUrl
            })
        });
        const res = await resp.json();

        if (res.success) {
            body.innerHTML = `
                <div class="mb-3">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3.5rem;"></i>
                </div>
                <h5 class="fw-bold text-success mb-1">Gateway Fonnte Terhubung!</h5>
                <p class="text-sm text-dark mb-3">${escapeHtml(res.message)}</p>
                <div class="bg-gray-100 rounded-3 p-3 text-start border text-xs">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Nomor WhatsApp Device:</span>
                        <span class="fw-bold text-dark font-monospace">${escapeHtml(res.device || '-')}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Status Koneksi:</span>
                        <span class="badge bg-success">${escapeHtml(res.device_status || 'connect')}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Paket Akun:</span>
                        <span class="badge bg-slate-900 text-white">${escapeHtml(res.package || 'Free')}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Sisa Kuota Pesan:</span>
                        <span class="fw-bold text-dark">${res.quota} pesan</span>
                    </div>
                </div>
            `;
        } else {
            body.innerHTML = `
                <div class="mb-3">
                    <i class="bi bi-x-circle-fill text-danger" style="font-size: 3.5rem;"></i>
                </div>
                <h5 class="fw-bold text-danger mb-1">Koneksi Fonnte Gagal</h5>
                <p class="text-sm text-dark mb-3">${escapeHtml(res.message || 'Gagal menghubungi akun Fonnte.')}</p>
                <div class="alert alert-danger bg-danger bg-opacity-10 border border-danger border-opacity-25 text-start text-xs p-3">
                    <i class="bi bi-shield-exclamation me-1"></i>
                    Pastikan Token Fonnte valid dan status perangkat WhatsApp di dashboard Fonnte sudah berstatus <b>Connect</b>.
                </div>
            `;
        }
    } catch (e) {
        body.innerHTML = `
            <div class="mb-3">
                <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 3.5rem;"></i>
            </div>
            <h5 class="fw-bold text-danger mb-1">Kesalahan Sistem</h5>
            <p class="text-sm text-muted">Tidak dapat menghubungi server backend.</p>
        `;
    }
}

// Modal Test Message
function openTestMessageModal() {
    document.getElementById('testMsgConfirmCheck').checked = false;
    document.getElementById('testMsgFeedback').innerHTML = '';
    const modal = new bootstrap.Modal(document.getElementById('modalTestMessage'));
    modal.show();
}

async function submitSendTestMessage() {
    const phoneInput = document.getElementById('testMsgPhone');
    const check = document.getElementById('testMsgConfirmCheck');
    const feedback = document.getElementById('testMsgFeedback');
    const btn = document.getElementById('btnSubmitTestMsg');

    const phone = phoneInput?.value?.trim() || '';
    if (!phone) {
        feedback.innerHTML = '<div class="alert alert-danger text-xxs p-2 mb-0">Nomor WhatsApp tujuan wajib diisi.</div>';
        return;
    }

    if (!check.checked) {
        feedback.innerHTML = '<div class="alert alert-danger text-xxs p-2 mb-0">Silakan centang persetujuan konfirmasi di atas.</div>';
        return;
    }

    const token = document.getElementById('otp_api_token')?.value || '';
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...';
    feedback.innerHTML = '<div class="alert alert-info text-xxs p-2 mb-0">Mengirimkan pesan uji melalui Fonnte...</div>';

    try {
        const resp = await fetch(`${BASE_URL}/settings/sendTestOtpMessage`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                target_phone: phone,
                otp_api_token: token,
                confirmed: 1
            })
        });
        const res = await resp.json();

        if (res.success) {
            feedback.innerHTML = `<div class="alert alert-success text-xxs p-2 mb-0"><i class="bi bi-check-circle-fill me-1"></i>${escapeHtml(res.message)}</div>`;
            showAlert('Pesan pengujian WhatsApp berhasil terkirim ke ' + phone, 'success');
        } else {
            feedback.innerHTML = `<div class="alert alert-danger text-xxs p-2 mb-0"><i class="bi bi-x-circle-fill me-1"></i>${escapeHtml(res.message)}</div>`;
        }
    } catch (e) {
        feedback.innerHTML = '<div class="alert alert-danger text-xxs p-2 mb-0">Gagal mengirim pesan pengujian.</div>';
    } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
}

// Handle Save Security
async function handleSaveSecurity(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveSecurity');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

    const form = document.getElementById('formSecuritySettings');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    if (!form.security_force_https.checked) data.security_force_https = '0';

    try {
        const resp = await fetch(`${BASE_URL}/settings/saveSecurity`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify(data)
        });
        const res = await resp.json();

        if (res.success) {
            showAlert(res.message, 'success');
        } else {
            showAlert(res.message || 'Gagal menyimpan pengaturan keamanan.', 'danger');
        }
    } catch (e) {
        showAlert('Kesalahan jaringan saat menyimpan keamanan.', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
}

// Load Audit Logs dynamically via AJAX
async function loadAuditLogs() {
    const category = document.getElementById('auditCategoryFilter')?.value || '';
    const tbody = document.getElementById('auditLogsBody');
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Memuat riwayat perubahan...</td></tr>';

    try {
        const url = `${BASE_URL}/settings/auditLogs?category=${encodeURIComponent(category)}&limit=50`;
        const resp = await fetch(url);
        const res = await resp.json();

        if (res.logs && res.logs.length > 0) {
            let html = '';
            res.logs.forEach(log => {
                const dateStr = new Date(log.created_at).toLocaleString('id-ID', {
                    day: '2-digit', month: 'short', year: 'numeric',
                    hour: '2-digit', minute: '2-digit', second: '2-digit'
                });
                const catBadge = log.category === 'ai' ? 'info' : (log.category === 'otp' ? 'success' : 'dark');
                const isSuccess = log.status === 'SUCCESS';
                const statusBadge = isSuccess ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger';
                const statusIcon = isSuccess ? 'check-circle-fill' : 'x-circle-fill';

                html += `
                    <tr>
                        <td class="ps-4 text-nowrap text-muted text-xxs">
                            <i class="bi bi-calendar3 me-1"></i>${dateStr}
                        </td>
                        <td>
                            <span class="fw-bold text-dark d-block">${escapeHtml(log.user_name || 'System')}</span>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary text-xxs py-0 px-2 rounded-pill">${escapeHtml(log.user_role || 'System')}</span>
                        </td>
                        <td>
                            <span class="badge badge-corp badge-corp-${catBadge}">${log.category.toUpperCase()}</span>
                        </td>
                        <td>
                            <span class="font-monospace text-xxs fw-semibold text-dark">${escapeHtml(log.action)}</span>
                        </td>
                        <td>
                            <span class="badge rounded-pill text-xxs py-1 px-2 ${statusBadge}">
                                <i class="bi bi-${statusIcon} me-1"></i>${log.status}
                            </span>
                        </td>
                        <td style="max-width: 280px;" class="text-truncate" title="${escapeHtml(log.details || '-')}">
                            <span class="text-secondary">${escapeHtml(log.details || '-')}</span>
                        </td>
                        <td class="text-center pe-4 text-xxs text-muted font-monospace">
                            ${escapeHtml(log.ip_address || '127.0.0.1')}
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-muted">Belum ada riwayat perubahan yang sesuai kriteria.</td></tr>';
        }
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-danger">Gagal memuat log audit.</td></tr>';
    }
}

// Theme Settings Handlers
function selectSettingTheme(val) {
    document.querySelectorAll('#tab-theme .theme-card-option').forEach(card => card.classList.remove('active'));
    const radio = document.getElementById('theme_default_' + val);
    if (radio) {
        radio.checked = true;
        radio.closest('.theme-card-option').classList.add('active');
    }
}

async function handleSaveTheme(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveTheme');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';

    const form = document.getElementById('formThemeSettings');
    const formData = new FormData(form);

    try {
        const res = await fetch('<?= BASEURL; ?>/settings/saveTheme', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            showAlert(data.message || 'Pengaturan tema default berhasil disimpan!', 'success');
        } else {
            showAlert(data.message || 'Gagal menyimpan pengaturan tema.', 'danger');
        }
    } catch (err) {
        showAlert('Terjadi kesalahan jaringan: ' + err.message, 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle me-1"></i> Simpan Pengaturan Tema';
    }
}

// Global Alert Notification Helper
function showAlert(message, type = 'success') {
    const container = document.getElementById('settingsAlertPlaceholder');
    if (!container) return;
    const alertId = 'alert_' + Date.now();
    const icon = type === 'success' ? 'check-circle-fill' : (type === 'danger' ? 'exclamation-octagon-fill' : 'info-circle-fill');
    
    container.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show border shadow-xs d-flex align-items-center mb-4 text-sm" role="alert" id="${alertId}">
            <i class="bi bi-${icon} fs-5 me-2 flex-shrink-0"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close shadow-none p-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    setTimeout(() => {
        const el = document.getElementById(alertId);
        if (el) {
            bootstrap.Alert.getOrCreateInstance(el).close();
        }
    }, 6000);
}

// Utility Escape HTML
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// ==============================================================
// LOGIKA NOTIFIKASI WHATSAPP PESANAN (FONNTE GATEWAY)
// ==============================================================
let activeOrderWaTextareaId = 'order_wa_template_created';

function setActiveTemplateTextarea(id) {
    activeOrderWaTextareaId = id;
}

function insertActivePlaceholder(placeholder) {
    const textarea = document.getElementById(activeOrderWaTextareaId);
    if (!textarea) return;

    const startPos = textarea.selectionStart;
    const endPos = textarea.selectionEnd;
    const value = textarea.value;

    textarea.value = value.substring(0, startPos) + placeholder + value.substring(endPos, value.length);
    textarea.focus();
    textarea.selectionStart = startPos + placeholder.length;
    textarea.selectionEnd = startPos + placeholder.length;
}

// Simpan Pengaturan Notifikasi WA Pesanan
async function handleSaveOrderWa(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveOrderWa');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan Pengaturan...';

    const form = document.getElementById('formOrderWaSettings');
    const formData = new FormData(form);

    try {
        const res = await fetch(`${BASE_URL}/settings/saveOrderWa`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            showAlert(data.message || 'Pengaturan Notifikasi WhatsApp Pesanan berhasil disimpan.', 'success');
        } else {
            let errorText = data.message || 'Gagal menyimpan pengaturan.';
            if (data.errors) {
                const list = Object.values(data.errors).map(err => `• ${err}`).join('<br>');
                errorText += `<br><small class="mt-1 d-block">${list}</small>`;
            }
            showAlert(errorText, 'danger');
        }
    } catch (err) {
        console.error(err);
        showAlert('Terjadi kesalahan koneksi saat menyimpan pengaturan Notifikasi Pesanan.', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save2 me-1"></i> Simpan Seluruh Pengaturan Notifikasi Pesanan';
    }
}

// Modal Uji Pesan WA Pesanan
function openTestOrderWaModal() {
    const modalEl = document.getElementById('modalTestOrderWa');
    const feedback = document.getElementById('testOrderWaFeedback');
    if (feedback) feedback.innerHTML = '';
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}

async function submitTestOrderWa() {
    const phoneInput = document.getElementById('testOrderWaPhone');
    const eventTypeSelect = document.getElementById('testOrderWaEventType');
    const feedback = document.getElementById('testOrderWaFeedback');
    const btn = document.getElementById('btnSubmitTestOrderWa');

    const phone = phoneInput?.value.trim() || '';
    const eventType = eventTypeSelect?.value || 'order_created';

    if (!phone) {
        if (feedback) feedback.innerHTML = '<div class="alert alert-danger text-xs p-2 mb-0">Nomor WhatsApp tujuan wajib diisi.</div>';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mengirim...';
    if (feedback) feedback.innerHTML = '<div class="text-xs text-muted"><span class="spinner-border spinner-border-sm me-1"></span>Menghubungi Fonnte WhatsApp API...</div>';

    const formData = new FormData();
    formData.append('target_phone', phone);
    formData.append('event_type', eventType);
    formData.append('csrf_token', CSRF_TOKEN);

    const overrideToken = document.getElementById('order_wa_api_token')?.value.trim();
    if (overrideToken) {
        formData.append('otp_api_token', overrideToken);
    }

    try {
        const res = await fetch(`${BASE_URL}/settings/testOrderWa`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            feedback.innerHTML = `
                <div class="alert alert-success text-xs p-2 mb-2">
                    <i class="bi bi-check-circle-fill me-1"></i> ${data.message}
                </div>
                <div class="p-2 bg-light border rounded text-xxs font-monospace text-start" style="max-height: 120px; overflow-y: auto; white-space: pre-wrap;">${escapeHtml(data.rendered_preview || '')}</div>
            `;
            showAlert('Pesan uji notifikasi pesanan berhasil dikirim ke WhatsApp tujuan!', 'success');
            loadOrderWaLogs();
        } else {
            feedback.innerHTML = `
                <div class="alert alert-danger text-xs p-2 mb-0">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> ${data.message}
                </div>
            `;
        }
    } catch (err) {
        console.error(err);
        feedback.innerHTML = '<div class="alert alert-danger text-xs p-2 mb-0">Terjadi kesalahan jaringan saat mengirim pesan uji.</div>';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send-fill text-xs me-1"></i> Kirim Pesan Uji';
    }
}

// Live Filter & Reload Logs
let orderWaSearchTimer = null;
function debounceOrderWaSearch() {
    clearTimeout(orderWaSearchTimer);
    orderWaSearchTimer = setTimeout(() => {
        loadOrderWaLogs();
    }, 400);
}

async function loadOrderWaLogs() {
    const tbody = document.getElementById('orderWaLogsBody');
    if (!tbody) return;

    const status = document.getElementById('filterOrderWaStatus')?.value || '';
    const search = document.getElementById('searchOrderWaQuery')?.value.trim() || '';

    try {
        const url = new URL(`${BASE_URL}/settings/orderWaLogs`);
        if (status) url.searchParams.set('status', status);
        if (search) url.searchParams.set('search', search);

        const res = await fetch(url.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();

        if (data.success && data.logs) {
            if (data.logs.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-4 d-block mb-1 text-secondary opacity-50"></i>
                            Tidak ditemukan log notifikasi yang sesuai.
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            for (const log of data.logs) {
                const statusBadge = log.status === 'SENT' 
                    ? '<span class="badge bg-success text-white text-xxs px-2 py-1">SENT</span>' 
                    : (log.status === 'FAILED' 
                        ? '<span class="badge bg-danger text-white text-xxs px-2 py-1">FAILED</span>' 
                        : '<span class="badge bg-warning text-dark text-xxs px-2 py-1">PENDING</span>');

                const actionHtml = log.status !== 'SENT'
                    ? `<button type="button" class="btn btn-xs btn-outline-primary mb-0 d-inline-flex align-items-center gap-1" onclick="handleRetryWa(${log.id})" id="btnRetry_${log.id}">
                            <i class="bi bi-arrow-repeat"></i>
                            <span>Retry</span>
                       </button>`
                    : '<span class="text-success text-xxs"><i class="bi bi-check-all fs-6"></i> Sukses</span>';

                const detailText = log.status === 'FAILED'
                    ? `<span class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>${escapeHtml(log.error_message || 'Gagal')}</span>`
                    : `<span class="text-muted">${escapeHtml(log.provider_message_id || 'Terkirim via Fonnte')}</span>`;

                const orderNum = log.order_id ? `#ORD-${String(log.order_id).padStart(5, '0')}` : '-';

                html += `
                    <tr>
                        <td class="ps-4 text-nowrap text-muted font-monospace">${escapeHtml(log.created_at)}</td>
                        <td><span class="fw-bold text-dark">${orderNum}</span></td>
                        <td><span class="badge bg-light text-dark border text-xxs">${escapeHtml(log.event_type)}</span></td>
                        <td class="font-monospace text-muted">
                            ${escapeHtml(log.recipient_phone_masked || log.recipient_phone)}
                            <small class="d-block text-xxs text-secondary">${escapeHtml(log.customer_name || '-')}</small>
                        </td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="text-center"><span class="badge bg-light text-secondary border text-xxs">${log.attempt_count}x</span></td>
                        <td class="text-truncate" style="max-width: 250px;" title="${escapeHtml(log.error_message || log.provider_message_id || '-')}">${detailText}</td>
                        <td class="text-end pe-4">${actionHtml}</td>
                    </tr>
                `;
            }
            tbody.innerHTML = html;
        }
    } catch (err) {
        console.error('Gagal memuat log notifikasi WA:', err);
    }
}

// Handle Retry WA Notification
async function handleRetryWa(logId) {
    const btn = document.getElementById(`btnRetry_${logId}`);
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }

    const formData = new FormData();
    formData.append('log_id', logId);
    formData.append('csrf_token', CSRF_TOKEN);

    try {
        const res = await fetch(`${BASE_URL}/settings/retryWaNotification`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            showAlert(data.message || 'Pengiriman ulang berhasil!', 'success');
        } else {
            showAlert(data.message || 'Pengiriman ulang gagal.', 'danger');
        }
        loadOrderWaLogs();
    } catch (err) {
        console.error(err);
        showAlert('Terjadi kesalahan koneksi saat mencoba mengirim ulang.', 'danger');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Retry';
        }
    }
}

</script>
