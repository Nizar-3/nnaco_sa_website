/**
 * aiGuardrailService.js
 * Multi-layer security and relevance validation for nnaco AI Assistant
 */

const STANDARD_REJECTION = "Maaf, saya adalah nnaco AI Assistant yang khusus membantu Anda seputar website dan database toko nnaco_sa. Silakan tanyakan sesuatu yang berkaitan dengan produk, pesanan, pengguna, laporan, atau layanan kami.";

// Out-of-context and unauthorized patterns
const CODING_PATTERNS = [
    /(?:buatkan|tuliskan|bikin|generate|write|create)\s+(?:kode|koding|coding|script|program|fungsi|function|class)\s*(?:python|javascript|js|typescript|ts|php|java|c\+\+|c#|ruby|golang|rust|swift|kotlin|bash|powershell|r\b)/i,
    /(?:coding|koding|script|program)\s+(?:python|javascript|js|php|java|c\+\+|golang)/i,
    /(?:def\s+[a-zA-Z_]\w*\s*\(|function\s+[a-zA-Z_]\w*\s*\(|console\.log\(|print\(|import\s+[a-zA-Z_]|<\?php)/i
];

const SQL_MUTATION_PATTERNS = [
    /(?:jalankan|eksekusi|run|lakukan)?\s*(?:sql|query)?\s*(?:insert\s+into|update\s+\w+|delete\s+from|drop\s+table|drop\s+database|truncate\s+table|alter\s+table|grant\s+all|revoke\s+all)/i,
    /(?:delete\s+from\s+orders|hapus\s+(?:seluruh|semua)?\s*(?:data\s+)?(?:pesanan|produk|user|tabel|database))/i,
    /(?:drop\s+database|drop\s+table|truncate\s+table)/i
];

const SECRET_CREDENTIAL_PATTERNS = [
    /(?:token\s+fonnte|fonnte\s+token|api\s*key\s*groq|groq\s*api\s*key|kredensial\s+database|db_password|password\s+hash|otp_hash|secret_key|private_key|kunci\s+rahasia)/i,
    /(?:bocorkan|tampilkan|sebutkan|minta)\s+(?:semua\s+)?(?:password|kata\s+sandi|otp|token\s+api|api\s*key)/i,
    /(?:tampilkan|berikan|bocorkan)\s+(?:token\s+fonnte|api\s*key\s*groq|kredensial)/i
];

const PROMPT_INJECTION_PATTERNS = [
    /(?:abaikan|ignore|lupakan|hapus)\s+(?:semua\s+)?(?:aturan|instruksi|perintah|rules|instructions|system\s+prompt)/i,
    /(?:tampilkan|reveal|show|print|bocorkan|sebutkan|apa\s+isi)\s+(?:system\s+prompt|instruksi\s+sistem|prompt\s+kamu|api\s*key|kredensial|secret|password|db_pass|source\s*code|kode\s+sumber)/i,
    /(?:kamu\s+adalah|act\s+as|pretend\s+to\s+be|jailbreak|mode\s+pengembang|developer\s+mode)\s+(?:dan|tanpa\s+batasan|unrestricted|god\s+mode)/i,
    /(?:ubah|ganti)\s+role\s+saya\s+(?:menjadi|ke)\s+(?:admin|owner|operator)/i
];

const PURE_MATH_PATTERNS = [
    /^(?:berapa\s+(?:hasil|jumlah|total|nilai)\s+(?:dari\s+)?)?\s*\d+\s*[\+\-\*\/x×÷]\s*\d+\s*(?:=|\?)?$/i,
    /^(?:hitung|hitunglah)\s+\d+\s*[\+\-\*\/x×÷]\s*\d+$/i
];

const GENERAL_KNOWLEDGE_PATTERNS = [
    /(?:buatkan|tuliskan|bikin)\s+(?:puisi|pantun|cerpen|cerita|lirik\s+lagu|dongeng|esai|artikel\s+tentang|lelucon|joke)/i,
    /(?:siapa\s+presiden|ibu\s+kota|jarak\s+bumi|teori\s+relativitas|rumus\s+kimia|rumus\s+fisika|siapa\s+penemu|sejarah\s+perang|kapan\s+kemerdekaan|siapa\s+menteri|siapa\s+gubernur)/i,
    /(?:siapa\s+(?:messi|ronaldo|jokowi|prabowo|elon\s*musk|artis|aktor|penyanyi))/i,
    /(?:resep\s+(?:masakan|makanan|kue|rendang|nasi\s+goreng|ayam|mie|kopi)|cara\s+masak|cara\s+bikin\s+kue)/i,
    /(?:tugas\s+sekolah|pr\s+sekolah|soal\s+ujian|pelajaran\s+sekolah|tugas\s+kuliah)/i,
    /(?:rekomendasi\s+film|film\s+apa|serial\s+tv|anime\s+apa|drakor|berita\s+hari\s+ini|cuaca\s+hari\s+ini|ramalan\s+cuaca|zodiak|horoskop|skor\s+bola|jadwal\s+bola)/i
];

const UNAUTHORIZED_DATA_PATTERNS = [
    /(?:bocorkan|hack)\s+(?:password|kata\s+sandi|rahasia|token|kredensial)/i
];

// Expanded keywords for all nnaco_sa website and database topics
const ECOMMERCE_KEYWORDS = [
    // Products & catalog
    'produk', 'aksesoris', 'kategori', 'katalog', 'harga', 'stok', 'beli', 'order',
    'gelang', 'kalung', 'cincin', 'anting', 'jepit', 'bando', 'nametag', 'gantungan',
    'murah', 'tersedia', 'rekomendasi', 'koleksi', 'terbaru', 'terlaris', 'diskon', 'promo',
    'available', 'out of stock', 'habis',

    // Orders & transactions
    'pesanan', 'keranjang', 'cart', 'checkout', 'bayar', 'pembayaran', 'ongkir',
    'shipping', 'pengiriman', 'retur', 'kembalikan', 'transaksi', 'invoice',
    'gopay', 'ovo', 'transfer', 'bank', 'cicilan', 'lunas', 'paid', 'pending',
    'selesai', 'diproses', 'dikemas', 'dikirim', 'dibatalkan',

    // Users & accounts
    'user', 'pengguna', 'pelanggan', 'customer', 'operator', 'admin', 'owner',
    'akun', 'login', 'daftar', 'register', 'profil', 'profile', 'sesi', 'session',
    'role', 'peran', 'status', 'aktif', 'nonaktif', 'banned',

    // Database & system
    'database', 'tabel', 'table', 'skema', 'schema', 'data', 'rekap', 'laporan',
    'ringkasan', 'statistik', 'grafik', 'omzet', 'omset', 'finansial', 'revenue',
    'penjualan', 'perbandingan', 'keuangan', 'perkembangan', 'performa', 'antrean',
    'antrian', 'operasional', 'restock', 'terjual',
    'order_details', 'payments', 'categories', 'products', 'users', 'reviews',
    'baca database', 'setiap menu', 'setiap tabel', 'sistem',

    // Reviews & ratings
    'ulasan', 'review', 'rating', 'bintang', 'kepuasan', 'testimoni', 'komentar',

    // Website & menu
    'toko', 'website', 'nnaco', 'menu', 'fitur', 'halaman', 'dashboard',
    'beranda', 'home', 'about', 'kontak', 'bantuan', 'faq',

    // General store questions
    'berapa', 'daftar', 'tampilkan', 'lihat', 'cek', 'info', 'informasi',
    'total', 'jumlah', 'semua', 'seluruh', 'semua data', 'isi'
];

const IDENTITY_PHRASES = [
    'siapa kamu', 'kamu siapa',
    'siapa anda', 'anda siapa',
    'siapa namamu', 'siapa nama kamu',
    'siapa nama anda', 'nama kamu siapa', 'nama anda siapa',
    'siapa ini', 'ini siapa',
    'siapa ai ini', 'kamu bot apa', 'kamu ai apa',
    'apakah kamu ai', 'apakah anda ai',
    'identitas kamu', 'identitas anda'
];

const USER_IDENTITY_PHRASES = [
    'siapa saya', 'saya siapa',
    'siapa aku', 'aku siapa',
    'siapa akun saya', 'akun saya siapa',
    'siapa yang sedang login', 'siapa yang login',
    'saya login sebagai siapa', 'saya login sebagai apa',
    'saya login pakai akun apa', 'saya login pake akun apa',
    'login sebagai siapa', 'login sebagai apa',
    'akun apa yang sedang login', 'akun apa yang login',
    'nama saya siapa', 'siapa nama saya',
    'nama ku siapa', 'siapa nama ku',
    'profil saya', 'profil akun saya', 'data akun saya', 'informasi akun saya',
    'cek akun saya', 'info akun saya', 'status akun saya',
    'status login saya', 'status login akun saya',
    'apakah saya sudah login', 'apakah aku sudah login',
    'siapa user saya', 'user saya siapa'
];

const GREETINGS = [
    'halo', 'hai', 'hi', 'hello', 'hey',
    'selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam',
    'pagi', 'siang', 'sore', 'malam',
    'assalamualaikum', 'assalamu alaikum', 'permisi',
    'apa kabar'
];

// Patterns that are clearly about nnaco_sa / store website context
const NNACO_CONTEXT_PATTERNS = [
    /nnaco/i,
    /website\s+(?:ini|toko|nnaco)/i,
    /toko\s+(?:ini|online|nnaco)/i,
    /(?:fitur|menu|halaman|fungsi|cara)\s+(?:website|aplikasi|toko|sistem)/i,
    /(?:cara|bagaimana)\s+(?:memesan|pesan|order|beli|bayar|checkout|daftar|register|login)/i,
    /(?:alur|proses)\s+(?:pemesanan|pesanan|order|pembayaran|pengiriman)/i,
    /(?:kebijakan|policy)\s+(?:retur|pengembalian|pengiriman|privasi)/i,
    /(?:database|tabel|data)\s+(?:apa|mana|yang|semua|seluruh)/i,
    /(?:tampilkan|lihat|cek|baca|ambil|rekap)\s+(?:data|tabel|semua|seluruh)/i,
    /(?:ringkasan|rekap|laporan|statistik)\s+(?:database|toko|penjualan|pesanan|pengguna|produk)/i,
];

class AiGuardrailService {
    getStandardRejection() {
        return STANDARD_REJECTION;
    }

    isIdentityQuery(text) {
        if (!text || typeof text !== 'string') return false;
        const clean = text.toLowerCase().replace(/[^a-z0-9\s]/g, ' ').replace(/\s+/g, ' ').trim();
        // Disambiguate: queries about "saya" / "aku" belong to user identity, not AI identity
        if (clean.includes('saya') || clean.includes('aku') || clean.includes('akun saya') || clean.includes('nama saya')) {
            return false;
        }
        return IDENTITY_PHRASES.some(p => clean === p || clean.startsWith(p) || clean.endsWith(p));
    }

    isUserIdentityQuery(text) {
        if (!text || typeof text !== 'string') return false;
        const clean = text.toLowerCase().replace(/[^a-z0-9\s]/g, ' ').replace(/\s+/g, ' ').trim();

        if (USER_IDENTITY_PHRASES.some(p => clean === p || clean.startsWith(p + ' ') || clean.endsWith(' ' + p) || clean.includes(p))) {
            return true;
        }

        const patterns = [
            /^(?:apakah\s+)?(?:kamu|anda)?\s*(?:tahu|kenal|mengetahui)?\s*(?:siapa\s+)?(?:saya|aku)\s*(?:saat\s+ini|sekarang|ya)?$/i,
            /^(?:siapa|kenal)\s+(?:nama\s+)?(?:saya|aku)\s*(?:ya|nih)?$/i,
            /^(?:nama\s+)(?:saya|aku)\s*(?:siapa|apa)\s*(?:ya|nih)?$/i,
            /^(?:saya|aku)\s+(?:ini\s+)?(?:siapa|sebagai\s+siapa)\s*(?:ya|nih)?$/i,
            /^(?:saya|aku)\s+login\s+(?:sebagai|pakai|menggunakan)\s+(?:siapa|apa)/i,
            /^(?:siapa\s+yang\s+(?:sedang\s+)?login)/i,
            /^(?:cek|lihat|tampilkan|info|informasi|status)\s+(?:profil|akun|login)\s*(?:saya|aku)?$/i,
            /^(?:akun\s+)(?:saya|aku)\s*(?:siapa|apa|statusnya)?$/i,
            /^(?:apakah\s+)?(?:saya|aku)\s+(?:sudah\s+)?(?:berhasil\s+)?login/i
        ];

        return patterns.some(rgx => rgx.test(clean));
    }

    isGreetingQuery(text) {
        if (!text || typeof text !== 'string') return false;
        const clean = text.toLowerCase().replace(/[^a-z0-9\s]/g, ' ').replace(/\s+/g, ' ').trim();
        const nonGreetingTerms = ['cara', 'memesan', 'order', 'pesan', 'beli', 'menu', 'tabel', 'database', 'produk', 'harga', 'stok', 'berapa', 'apa saja', 'fitur', 'daftar', 'kenapa', 'bagaimana', 'dimana'];
        if (nonGreetingTerms.some(term => clean.includes(term))) {
            return false;
        }
        return GREETINGS.some(g => clean === g || clean.startsWith(g + ' ') || clean.endsWith(' ' + g));
    }

    /**
     * Check if the message is clearly about the nnaco_sa website/store context
     */
    isNnacoContextQuery(text) {
        if (!text) return false;
        const lower = text.toLowerCase();
        // Direct nnaco mentions
        if (lower.includes('nnaco')) return true;
        // Pattern matches for store context
        return NNACO_CONTEXT_PATTERNS.some(p => p.test(lower));
    }

    /**
     * Validates whether a user message is relevant to the nnaco_sa store.
     * Returns: { isRelevant, isMixed, directRejection, relevantPart }
     */
    validateRelevance(message, user = {}) {
        if (!message || typeof message !== 'string') {
            return {
                isRelevant: false,
                isMixed: false,
                directRejection: STANDARD_REJECTION
            };
        }

        const trimmed = message.trim();
        const lower = trimmed.toLowerCase();

        // 1a. SQL Mutation Check (Strict Read-Only Enforcement)
        for (const pattern of SQL_MUTATION_PATTERNS) {
            if (pattern.test(lower)) {
                console.warn(`[SECURITY GUARDRAIL] SQL mutation attempt blocked: "${trimmed}"`);
                return {
                    isRelevant: false,
                    isMixed: false,
                    directRejection: "Maaf, AI Chatbot nnaco_sa hanya beroperasi dalam mode baca (read-only) dan menolak segala instruksi perubahan atau penghapusan data database."
                };
            }
        }

        // 1b. Secret Credential & API Key Protection
        for (const pattern of SECRET_CREDENTIAL_PATTERNS) {
            if (pattern.test(lower)) {
                console.warn(`[SECURITY GUARDRAIL] Secret credential request blocked: "${trimmed}"`);
                return {
                    isRelevant: false,
                    isMixed: false,
                    directRejection: "Maaf, kata sandi, kode OTP, token Fonnte, API key Groq, dan kredensial database merupakan informasi rahasia sistem yang tidak dapat diakses atau ditampilkan."
                };
            }
        }

        // 1c. Prompt Injection & Role Hijacking Check
        for (const pattern of PROMPT_INJECTION_PATTERNS) {
            if (pattern.test(lower)) {
                console.warn(`[SECURITY GUARDRAIL] Prompt injection detected: "${trimmed}"`);
                return {
                    isRelevant: false,
                    isMixed: false,
                    directRejection: "Maaf, permintaan pengabaian instruksi atau perubahan role sistem tidak diizinkan. Sistem kami beroperasi dengan hak akses ketat yang diverifikasi backend."
                };
            }
        }

        // 1d. Role-Based Permission Check (Enforced directly at backend)
        const userRole = user.role || 'Customer';
        if (userRole === 'Customer') {
            const isOmzetRequest = /(?:omzet|omset|laporan\s+keuangan|total\s+penjualan\s+toko|keuntungan\s+toko|laba\s+toko|pendapatan\s+toko)/i.test(lower);
            if (isOmzetRequest) {
                console.warn(`[ROLE GUARDRAIL] Customer attempted to access store omzet: "${trimmed}"`);
                return {
                    isRelevant: false,
                    isMixed: false,
                    directRejection: "Maaf, data laporan omzet dan keuangan toko hanya dapat diakses oleh Admin atau Owner nnaco_sa."
                };
            }

            const isOtherCustomerRequest = /(?:semua\s+nomor\s+(?:whatsapp|telepon|hp)|nomor\s+(?:wa|telepon|hp)\s+customer|daftar\s+semua\s+customer|seluruh\s+customer|customer\s+terbanyak|pesanan\s+pengguna\s+lain|customer\s+lain|daftar\s+pesanan\s+pengguna\s+lain)/i.test(lower);
            if (isOtherCustomerRequest) {
                console.warn(`[ROLE GUARDRAIL] Customer attempted to access other customer data: "${trimmed}"`);
                return {
                    isRelevant: false,
                    isMixed: false,
                    directRejection: "Maaf, untuk menjaga privasi dan keamanan data pelanggan, Anda hanya memiliki izin untuk mengakses data akun dan pesanan milik Anda sendiri."
                };
            }
        }

        if (userRole === 'Operator') {
            const isOmzetRequest = /(?:omzet|omset|laporan\s+keuangan|keuntungan\s+toko|laba\s+toko|pendapatan\s+toko)/i.test(lower);
            if (isOmzetRequest) {
                console.warn(`[ROLE GUARDRAIL] Operator attempted to access omzet: "${trimmed}"`);
                return {
                    isRelevant: false,
                    isMixed: false,
                    directRejection: "Maaf, data finansial dan laporan omzet toko nnaco_sa merupakan kewenangan akun Owner/Admin."
                };
            }
        }

        // 2. Identity, User Profile, & Greeting fast pass (always relevant)
        if (this.isIdentityQuery(message) || this.isUserIdentityQuery(message) || this.isGreetingQuery(message)) {
            return {
                isRelevant: true,
                isMixed: false,
                directRejection: null
            };
        }

        // 3. nnaco-specific context
        if (this.isNnacoContextQuery(message)) {
            return {
                isRelevant: true,
                isMixed: false,
                directRejection: null
            };
        }

        // 5. Check ecommerce keywords
        const hasEcommerceIntent = ECOMMERCE_KEYWORDS.some(k => lower.includes(k));

        // 6. Coding Requests Check
        const isCodingRequest = CODING_PATTERNS.some(p => p.test(lower));
        if (isCodingRequest && !hasEcommerceIntent) {
            console.warn(`[SECURITY GUARDRAIL] Pure coding request rejected: "${trimmed}"`);
            return {
                isRelevant: false,
                isMixed: false,
                directRejection: STANDARD_REJECTION
            };
        }

        // 7. Pure Isolated Math Check
        const isPureMath = PURE_MATH_PATTERNS.some(p => p.test(lower));
        if (isPureMath && !hasEcommerceIntent) {
            console.warn(`[SECURITY GUARDRAIL] Pure math question rejected: "${trimmed}"`);
            return {
                isRelevant: false,
                isMixed: false,
                directRejection: STANDARD_REJECTION
            };
        }

        // 8. General Knowledge, Essays, Poems Check
        const isGeneralKnowledge = GENERAL_KNOWLEDGE_PATTERNS.some(p => p.test(lower));
        if (isGeneralKnowledge && !hasEcommerceIntent) {
            console.warn(`[SECURITY GUARDRAIL] General knowledge / creative request rejected: "${trimmed}"`);
            return {
                isRelevant: false,
                isMixed: false,
                directRejection: STANDARD_REJECTION
            };
        }

        // 9. Mixed Request Handling (contains ecommerce intent + coding/general request)
        if (isCodingRequest || isGeneralKnowledge) {
            return {
                isRelevant: true,
                isMixed: true,
                directRejection: null,
                instruction: "Pengguna mengajukan pertanyaan campuran. Jawab HANYA bagian yang berkaitan dengan produk, pesanan, atau layanan toko nnaco_sa. TOLAK bagian yang meminta kode pemrograman atau topik umum dengan sopan."
            };
        }

        // 10. Ecommerce intent is present -> always relevant
        if (hasEcommerceIntent) {
            return {
                isRelevant: true,
                isMixed: false,
                directRejection: null
            };
        }

        // 11. Allow short queries only if they match identity or store context
        if (this.isIdentityQuery(trimmed) || this.isUserIdentityQuery(trimmed) || this.isGreetingQuery(trimmed)) {
            return {
                isRelevant: true,
                isMixed: false,
                directRejection: null
            };
        }

        const isStoreContext = NNACO_CONTEXT_PATTERNS.some(p => p.test(lower));
        if (isStoreContext) {
            return {
                isRelevant: true,
                isMixed: false,
                directRejection: null
            };
        }

        // 12. Queries with no store, database, or identity context -> reject
        console.warn(`[SECURITY GUARDRAIL] Out-of-context query rejected: "${trimmed}"`);
        return {
            isRelevant: false,
            isMixed: false,
            directRejection: STANDARD_REJECTION
        };
    }

    /**
     * Post-processing validation on the AI response before sending it to the client.
     * Prevents code leaks, system prompt disclosure, or credential leakage.
     */
    validateOutput(reply) {
        if (!reply || typeof reply !== 'string') {
            return STANDARD_REJECTION;
        }

        // Check for code blocks in output — only block actual programming code, not markdown tables
        const hasCodeBlock = /```(?:python|javascript|js|php|html|css|sql|bash|c|cpp|java|typescript|ts|ruby|go|rust|swift)\s[\s\S]*?```/i.test(reply);
        const hasDirectCodeConstructs = /(?:def\s+[a-zA-Z_]\w*\s*\(|console\.log\(|import\s+sys|System\.out\.println|<\?php)/i.test(reply);

        if (hasCodeBlock || hasDirectCodeConstructs) {
            console.warn('[SECURITY GUARDRAIL] Output contained programming code, sanitized to rejection message.');
            return STANDARD_REJECTION;
        }

        // Check for leaked API keys or credentials
        const hasApiKey = /gsk_[a-zA-Z0-9_-]{20,}/i.test(reply);
        const hasSecret = /nnaco_secure_ai_hmac_secret/i.test(reply);

        if (hasApiKey || hasSecret) {
            console.error('[SECURITY CRITICAL] Output attempted to leak API key or secret token! Blocked.');
            return STANDARD_REJECTION;
        }

        return reply;
    }
}

module.exports = new AiGuardrailService();
