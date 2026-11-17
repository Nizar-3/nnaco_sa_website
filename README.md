# NNACO SA — Luxury Jewelry & Exclusive Accessories E-Commerce Platform
> **Sistem Informasi E-Commerce Retail Perhiasan & Aksesoris Mewah Berbasis AI Assistant dan Otomasi WhatsApp Gateway**  
> Tugas Akhir / Ujian Akhir Semester (UAS) Mata Kuliah **Rekayasa Perangkat Lunak (RPL)**

---

## 👥 Identitas Anggota Kelompok

**Kelompok 2 (Sumber City) — Kelas 1C-2025**

| No | Nama Lengkap | Nomor Induk Mahasiswa (NIM) | Kelas | Peran / Kontribusi |
| :-: | :--- | :-: | :-: | :--- |
| 1 | **Nizar Firmansyah** | **41257147** | 1C-2025 | Project Lead, Backend Core MVC & Integrasi AI |
| 2 | **Syamsul Hadi** | **41257163** | 1C-2025 | Database Designer & Concurrency Lock System |
| 3 | **Sulistina Juliyanti** | **41257161** | 1C-2025 | Frontend UI/UX, Corporate Dashboard & Theme |
| 4 | **Siti Nur Fadillah** | **41257241** | 1C-2025 | Authentication, WhatsApp OTP & Notification Gateway |
| 5 | **Talita Nabila** | **41257242** | 1C-2025 | Testing, Documentation & System Quality Assurance |

---

## 🎯 Tema / Topik Proyek

**"Pengembangan Platform E-Commerce Retail Perhiasan Mewah dan Aksesoris Eksklusif Berbasis Kecerdasan Buatan (AI Chatbot & Business Advisor) Serta Otomasi Gateway WhatsApp Terintegrasi."**

---

## 📖 Deskripsi Aplikasi

### 1. Penjelasan Singkat
**NNACO SA** (`nnaco_sa`) adalah platform web e-commerce ritel *Business-to-Consumer* (B2C) yang mengkhususkan diri pada penjualan produk perhiasan mewah (seperti kalung titanium anti-karat, cincin perak murni, gelang eksklusif, dan anting mutiara elegan). Aplikasi ini dibangun dengan standar arsitektur bersih berorientasi objek (**OOP**) menggunakan pola desain **Model-View-Controller (MVC)** dalam PHP Native, dipadukan dengan desain antarmuka modern **Corporate UI Dashboard & Luxury Storefront**.

### 2. Tujuan Pembuatan Aplikasi
1. Membangun platform transaksi digital yang aman, elegan, dan terpercaya bagi konsumen perhiasan mewah.
2. Mengotomatiskan alur kerja operasional gudang dan pemrosesan pesanan menggunakan notifikasi WhatsApp instan.
3. Mengurangi friksi pendaftaran dan lupa kata sandi dengan menghadirkan metode *passwordless login* menggunakan **WhatsApp OTP**.
4. Memberikan asistensi cerdas berbasis AI bagi pelanggan (asisten katalog & belanja) serta pemilik bisnis (penasihat eksekutif keuangan dan strategi stok).

### 3. Permasalahan yang Ingin Diselesaikan
* **Pencegahan Overselling / Kehabisan Stok Fisik:** Menghindari penjualan ganda saat pesanan masuk secara bersamaan melalui mekanisme *Database Transaction Row-Level Locking* (`SELECT ... FOR UPDATE`).
* **Keamanan Akun & Akun Palsu:** Mengeliminasi pendaftaran bot dan lupa sandi melalui verifikasi nomor aktif WhatsApp via Fonnte API dengan hash Bcrypt dan masa kadaluarsa 5 menit.
* **Keterlambatan Pelacakan Pesanan:** Pelanggan tidak perlu membuka web secara manual setiap saat; sistem mengirim notifikasi otomatis ke WhatsApp pelanggan ketika pesanan diproses, dikemas, dikirim beserta nomor resi ekspedisi, hingga selesai.
* **Pengambilan Keputusan Bisnis yang Lambat:** Pemilik toko (*Owner*) dapat mengajukan pertanyaan strategis bisnis langsung kepada AI (*askAI*) untuk membaca omzet, performa produk, dan prioritas restock tanpa perlu menyusun laporan manual.

---

## 🚀 Fitur Utama Aplikasi

Sistem NNACO SA membagi hak akses ke dalam **4 Tingkatan Peran Pengguna (Role-Based Access Control / RBAC)**:

### A. Fitur Pelanggan (Customer / Storefront)
* **Katalog & Filter Produk:** Pencarian kata kunci (*keyword search*), pemfilteran kategori (Kalung, Cincin, Gelang, Anting), rentang harga, serta pagination responsif.
* **Detail Produk & Jaminan Keaslian:** Galeri produk mewah, status stok real-time, ulasan pembeli, serta deskripsi spesifikasi bahan perhiasan.
* **Keranjang Belanja (Shopping Cart):** Penambahan item belanja, manipulasi kuantitas instan, dan kalkulasi subtotal otomatis.
* **Checkout & Concurrency Guard:** Transaksi checkout aman yang otomatis memotong stok barang dengan proteksi *row-locking* database.
* **Simulasi Pembayaran (Payment Gateway Simulator):** Pilihan pembayaran Tunai (In-Store/COD), QRIS, Virtual Account, dan Transfer Bank dengan sinkronisasi status otomatis (`PAID`, `PENDING`, `FAILED`).
* **Riwayat & Pelacakan Pesanan:** Memantau siklus hidup pesanan (`Menunggu Pembayaran` ➔ `Diproses` ➔ `Dikemas` ➔ `Dikirim` ➔ `Selesai`).
* **Profil Pengguna:** Manajemen data pribadi, nomor WhatsApp, serta pembaruan password.

### B. Fitur Operasional Gudang (Operator)
* **Dashboard Antrean Pesanan:** Pemantauan real-time pesanan yang baru masuk dan pesanan yang sedang diproses.
* **Pemrosesan Pengiriman & Input Resi:** Mengubah status pesanan menjadi `Dikemas` dan `Dikirim`, serta menginputkan nomor resi ekspedisi yang otomatis diteruskan ke WhatsApp pembeli.
* **Manajemen Stok Cepat (Low Stock Alert):** Peringatan otomatis untuk perhiasan dengan stok menipis ($\le 5\text{ pcs}$), pengeditan kuantitas fisik, dan penyesuaian status ketersediaan (*Available*, *Out of Stock*, *Hidden*).

### C. Fitur Administrator Sistem (Admin)
* **Master Produk & Kategori:** Operasi CRUD (*Create, Read, Update, Delete*) katalog perhiasan lengkap dengan pengunggahan gambar dan manajemen kategori.
* **Manajemen Pengguna (User Management):** Pengelolaan seluruh akun (Customer, Operator, Admin, Owner) beserta pengubahan status akun (*Active*, *Inactive*, *Banned*).
* **Monitoring & Riwayat Chatbot AI:** Inspeksi riwayat percakapan pelanggan dengan asisten AI untuk memantau sentimen, keluhan, dan tren pertanyaan.
* **Pusat Pengaturan Sistem (System Settings):**
  * Konfigurasi Provider AI (Groq, Gemini, Mock) beserta pemilihan model, temperature, dan batas token.
  * Konfigurasi Fonnte WhatsApp OTP (Token, masa berlaku, batas percobaan).
  * Pengaturan sakelar *event* notifikasi WhatsApp dan kustomisasi template pesan.
  * Manajemen tema antarmuka (*Corporate Light/Dark Mode*).
* **System Audit Logs:** Perekaman jejak audit (*audit trail*) otomatis setiap kali terjadi perubahan konfigurasi sistem untuk mencegah manipulasi.

### D. Fitur Eksekutif Pemilik Toko (Owner)
* **Dashboard Eksekutif Omzet:** Analisis pendapatan riil, rasio keberhasilan transaksi, volume pesanan, dan rata-rata nilai belanja.
* **Laporan Penjualan & Grafik Tren:** Grafik penjualan harian interaktif (berbasis Chart.js) dengan rentang filter tanggal fleksibel.
* **Konsultan Bisnis AI (Owner AI Advisor):** Fitur interaktif untuk menganalisis data keuangan dan performa operasional toko secara mendalam.

---

## 🤖 Fitur Kecerdasan Buatan (Artificial Intelligence)

### 1. Fungsi AI dalam Sistem
Sistem NNACO SA mengimplementasikan dua agen kecerdasan buatan dengan peran berbeda:
1. **Customer AI Assistant (`/assistant`):** Bertindak sebagai konsultan perhiasan pribadi yang siap melayani pelanggan 24/7 untuk rekomendasi kado/aksesoris, penjelasan keaslian bahan, pengecekan stok, serta status pesanan milik akun pelanggan.
2. **Owner AI Executive Advisor (`/owner` - `askAI`):** Bertindak sebagai analis bisnis eksekutif (*C-Level Business Advisor*) yang mengolah agregasi data omzet, produk terlaris, dan transaksi macet untuk memberikan rekomendasi taktis bisnis perhiasan.

### 2. Teknologi & Model AI yang Digunakan
* **Groq Cloud AI API:** Menggunakan model Large Language Model (LLM) berkecepatan tinggi seperti **`llama-3.3-70b-versatile`** dan **`mixtral-8x7b-32768`**.
* **Google Gemini API:** Menggunakan model mutakhir **`gemini-1.5-flash`** untuk pemrosesan konteks bisnis kompleks dan bernalar logis.
* **Mock AI Service:** Mekanisme fallback bawaan agar sistem tetap dapat beroperasi dan merespons query dasar saat koneksi internet atau kuota API habis.

### 3. Arsitektur & Cara Integrasi AI
Aplikasi menggunakan **Dual-Architecture Integration**:
* **Node.js Express Microservice (`groq-chat-server`):** Layanan microservice modular mandiri yang berjalan di *port 3001* untuk menangani streaming chat, intent recognition, query database, dan validasi guardrail.
* **Native PHP Fallback AI Service:** Core layer PHP (`AIDatabaseService.php`, `GeminiAIService.php`, `GroqAIService.php`) yang mampu memproses interaksi AI secara mandiri tanpa dependensi Node.js.
* **Database Function Calling (Tool Calling):** AI tidak berhalusinasi data, melainkan membaca data riil melalui alat query database terisolasi (misal: `searchProducts`, `checkProductStock`, `getMyOrderStatus`, `getRestockPriorityProducts`, `getBusinessContextForAI`).
* **Role-Based Access Control (RBAC) & Row-Level Security (RLS) Guardrail:**
  Melalui `AIRolePermissionService.php`, AI dijaga secara ketat:
  * Customer **DILARANG KERAS** mengakses data omzet toko, pesanan pelanggan lain, token API, atau konfigurasi sistem.
  * Operator hanya dapat mengakses antrean operasional dan stok inventaris.
  * Owner diberikan izin penuh untuk analisis keuangan dan konteks bisnis.

---

## 📺 Link Demonstrasi Video YouTube

Tonton demonstrasi lengkap fitur aplikasi, alur transaksi, notifikasi WhatsApp, dan interaksi AI pada link berikut:

🔗 **Link Video YouTube:** [https://youtu.be/lmuB5ZxZcx0?si=iSx8f5fN-ECmyYZU](https://youtu.be/lmuB5ZxZcx0?si=iSx8f5fN-ECmyYZU)

---

## 🛠️ Panduan Instalasi & Menjalankan Aplikasi

### 1. Persyaratan Perangkat & Software (Prerequisites)
* **Sistem Operasi:** Windows 10/11, macOS, atau Linux.
* **Web Server & Database:** **XAMPP** (Apache $\ge 2.4$, MySQL / MariaDB $\ge 10.4$, **PHP $\ge 8.0$**).
* **Ekstensi PHP Aktif:** `pdo_mysql`, `curl`, `mbstring`, `openssl`, `gd`, `session`, `json`.
* **Runtime Node.js (Opsional untuk AI Server):** Node.js $\ge 18.x$ dan npm.
* **Koneksi Internet:** Diperlukan untuk pengujian WhatsApp Fonnte Gateway dan Cloud AI (Groq / Gemini).

---

### 2. Langkah-Langkah Instalasi

#### Langkah 1: Kloning atau Pindahkan Direktori Project
Pastikan folder project berada di dalam root web server lokal (untuk XAMPP Windows):
```text
C:\xampp\htdocs\nnaco_sa
```
*(Atau sesuaikan dengan path drive instalasi XAMPP Anda).*

#### Langkah 2: Konfigurasi Database MySQL
1. Buka aplikasi **XAMPP Control Panel** dan nyalakan modul **Apache** dan **MySQL**.
2. Akses **phpMyAdmin** melalui browser di `http://localhost/phpmyadmin`.
3. Buat database baru bernama:
   ```sql
   CREATE DATABASE `nnaco_sa_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
4. Impor berkas struktur tabel dari folder `database/` secara berurutan:
   * **`database/schema.sql`** (Tabel pengguna, produk, kategori, order, payment, review, ai_conversations).
   * **`database/migrate_otp.sql`** (Tabel `otp_requests` untuk WhatsApp OTP).
   * **`database/migrate_settings.sql`** (Tabel `system_settings` dan `system_audit_logs`).
5. *(Opsional)* Isi data sampel produk dan pengguna menggunakan seeder:
   * Jalankan via terminal: `php seeder.php` dan `php seeder_users.php`.

#### Langkah 3: Konfigurasi File Environment (`.env`)
1. Salin berkas **`.env.example`** menjadi **`.env`** di root folder project:
   ```bash
   cp .env.example .env
   ```
2. Buka file **`.env`** dan sesuaikan konfigurasi token:
   ```env
   # Fonnte WhatsApp Gateway
   FONNTE_TOKEN=your_fonnte_token_here
   FONNTE_API_URL=https://api.fonnte.com/send

   # AI Provider: 'groq', 'gemini', atau 'mock'
   AI_PROVIDER=groq
   GROQ_API_KEY=your_groq_api_key_here
   GROQ_MODEL=llama-3.3-70b-versatile
   GEMINI_API_KEY=your_gemini_api_key_here
   ```
   > *Catatan: Jika tidak memiliki API key, sistem secara otomatis akan menggunakan `mock` sehingga aplikasi tetap dapat berjalan normal.*

#### Langkah 4: Menjalankan Microservice AI (Opsional)
Jika ingin menggunakan Node.js Modular AI Server untuk Groq:
1. Buka terminal di folder `groq-chat-server/`:
   ```bash
   cd groq-chat-server
   npm install
   node server.js
   ```
   *(Atau klik ganda berkas `start_ai_server.bat` di root project).*
2. Server AI akan aktif di `http://localhost:3001`.

---

### 3. Cara Mengakses Aplikasi

Buka browser (Google Chrome / Mozilla Firefox / Microsoft Edge) dan kunjungi URL berikut:

| Halaman | URL Akses |
| :--- | :--- |
| **Storefront Utama** | `http://localhost/nnaco_sa/public` |
| **Katalog Produk** | `http://localhost/nnaco_sa/public/product` |
| **Asisten AI Chatbot** | `http://localhost/nnaco_sa/public/assistant` |
| **Login Pengguna** | `http://localhost/nnaco_sa/public/auth/login` |
| **Login WhatsApp OTP** | `http://localhost/nnaco_sa/public/auth/loginOtp` |

---

### 4. Akun Pengguna Bawaan (Default Login)

Untuk keperluan pengujian sistem dan evaluasi UAS, dapat menggunakan akun seeder berikut:

| Peran (Role) | Email Login | Password | Akses Halaman |
| :--- | :--- | :--- | :--- |
| **Customer** | `customer@nnaco.com` | `password123` | Storefront, Cart, Checkout, Riwayat Pesanan |
| **Operator** | `operator@nnaco.com` | `password123` | Dashboard Operator, Antrean Pesanan, Stok Gudang |
| **Admin** | `admin@nnaco.com` | `password123` | Master Produk, User Management, Pengaturan Sistem |
| **Owner** | `owner@nnaco.com` | `password123` | Dashboard Eksekutif, Laporan Omzet, askAI Advisor |

---

## 📁 Struktur Direktori Project

```text
nnaco_sa/
├── app/                        # Direktori Core MVC PHP
│   ├── config/config.php       # Konfigurasi database & environment loader
│   ├── controllers/            # Controller aplikasi (Auth, Order, Admin, Owner, dll)
│   ├── core/                   # MVC Framework engine (App, Controller, Database, Middleware, CSRF)
│   ├── models/                 # Model database (UserModel, ProductModel, OrderModel, dll)
│   ├── services/               # Lapisan logika layanan (Fonnte, AI Services, Payment, Setting)
│   └── views/                  # Antarmuka tampilan (Storefront, Dashboards, Auth, Templates)
├── database/                   # Skema DDL SQL dan berkas migrasi database
├── groq-chat-server/           # Node.js Modular AI Express Microservice (Port 3001)
├── public/                     # Dokumen root web (index.php, css, js, images)
│   ├── css/                    # Stylesheet (Corporate UI, theme.css, bootstrap)
│   └── js/                     # Client-side scripts (theme-manager.js, AJAX tables)
├── tests/                      # Berkas pengujian unit & integrasi sistem
├── .env.example                # Template konfigurasi environment yang aman
├── .gitignore                  # Berkas proteksi file rahasia agar tidak terunggah ke Git
├── README.md                   # Dokumentasi resmi proyek UAS Rekayasa Perangkat Lunak
└── start_ai_server.bat         # Script otomatis menjalankan AI Server di Windows
```

---

## 🔒 Kebijakan Keamanan & Privasi Data
* **Keamanan Kunci Rahasia:** File `.env` yang berisi token API dan kredensial database **tidak pernah di-commit ke repositori Git**.
* **Proteksi CSRF:** Seluruh request mutasi data (`POST`, `PUT`, `DELETE`) dilindungi oleh token CSRF unik per sesi browser.
* **Pencegahan SQL Injection:** Seluruh interaksi database menggunakan PDO Prepared Statements dengan parameter binding ketat.
* **Pencegahan XSS:** Seluruh output data dinamis disanitasi menggunakan `htmlspecialchars()`.

---

## 📄 Lisensi & Hak Cipta
Project ini dikembangkan sebagai karya akademik untuk pemenuhan tugas **Ujian Akhir Semester (UAS) Rekayasa Perangkat Lunak (RPL)** tahun akademik 2025/2026.  
Hak cipta kode sumber dan desain perhiasan dimiliki oleh **Kelompok 2 (Sumber City)**.

