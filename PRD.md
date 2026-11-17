# PRODUCT REQUIREMENTS DOCUMENT (PRD)
## NNACO SA — Luxury Jewelry & Exclusive Accessories E-Commerce Platform with AI & WhatsApp Automation

| Properti Dokumen | Keterangan |
| :--- | :--- |
| **Nama Proyek** | NNACO SA (`nnaco_sa`) |
| **Versi Dokumen** | 1.0 (Production / Active) |
| **Tanggal Pembaruan** | November 2026 |
| **Status Dokumen** | Approved / Live Implementation |
| **Klasifikasi** | Internal Project Documentation |
| **Domain Bisnis** | B2C E-Commerce, Retail Perhiasan Mewah, AI Automation, WhatsApp CRM |

---

## 1. Executive Summary & Visi Produk

### 1.1 Latar Belakang
**NNACO SA** adalah platform e-commerce ritel modern yang mengkhususkan diri pada perhiasan dan aksesoris mewah (kalung, gelang, cincin, anting berbahan titanium, perak, dan material premium lainnya). Industri perhiasan menuntut standar kepercayaan tinggi, verifikasi identitas yang reliabel, kepastian stok secara real-time, serta komunikasi yang instan dan transparan kepada pelanggan.

Untuk menjawab kebutuhan tersebut, sistem NNACO SA mengintegrasikan:
1. **Storefront & Corporate Dashboard** dengan standar antarmuka elegan (*Corporate UI Design System*).
2. **Otentikasi Passwordless WhatsApp OTP** via Fonnte Gateway untuk mengurangi friksi login dan meminimalisir akun palsu.
3. **Event-Driven WhatsApp Order Notifications** untuk mengabarkan mutasi pesanan secara otomatis tanpa membebani performa server utama.
4. **AI-Powered Customer & Executive Assistant** yang terintegrasi langsung dengan basis data menggunakan mekanisme *Function Calling / Dynamic Tool Execution* dengan proteksi *Role-Based Access Control* (RBAC) dan *Row-Level Security* (RLS).

### 1.2 Visi & Tujuan Bisnis
* **Customer Retention & Trust:** Memberikan pengalaman belanja mewah yang aman dengan verifikasi WhatsApp dan transparansi riwayat pesanan.
* **Operational Efficiency:** Mengotomatiskan manajemen antrean pesanan, pemantauan stok menipis (*low stock alert*), dan notifikasi nomor resi pengiriman oleh Operator.
* **Data-Driven Decision Making:** Memberikan laporan omzet komprehensif serta asisten AI eksekutif (*Owner AI Advisor*) bagi pemilik bisnis (*Owner*) untuk menganalisis performa toko secara instan.

---

## 2. Target Pengguna & Persona (User Roles)

Sistem NNACO SA dirancang dengan arsitektur **4-Tier Role-Based Access Control (RBAC)**:

| Role | Deskripsi | Kebutuhan Utama |
| :--- | :--- | :--- |
| **Customer** | Pelanggan ritel akhir yang membeli perhiasan/aksesoris. | Eksplorasi katalog, konsultasi via AI Chat, checkout aman, pembayaran simulator/gateway, pelacakan status pesanan, dan terima notifikasi WhatsApp. |
| **Operator** | Staf operasional gudang dan pemrosesan pesanan. | Memantau antrean pesanan baru, memproses pesanan (`Diproses`, `Dikemas`, `Dikirim`), input resi ekspedisi, dan mengelola stok fisik produk. |
| **Admin** | Administrator pengelola katalog dan sistem. | Mengelola master produk & kategori, manajemen akun pengguna, inspeksi riwayat chat AI, serta konfigurasi pengaturan sistem terpusat. |
| **Owner** | Pemilik bisnis / Eksekutif pengambil keputusan. | Analisis pendapatan/omzet, statistik metode pembayaran, produk terlaris, dan konsultasi strategis dengan AI Executive Advisor. |

---

## 3. Matriks Hak Akses (RBAC Matrix)

| Modul / Fitur | Customer | Operator | Admin | Owner |
| :--- | :---: | :---: | :---: | :---: |
| **Katalog, Keranjang & Checkout** | ✅ Full | ❌ Akses Terbatas | ❌ | ❌ |
| **Profil & Riwayat Pesanan Pribadi** | ✅ Full | ❌ | ❌ | ❌ |
| **Customer AI Assistant** | ✅ Terbatas (RLS) | ❌ | ❌ | ❌ |
| **Dashboard Operasional & Antrean Pesanan** | ❌ | ✅ Full | ✅ Full | ✅ Full |
| **Pembaruan Status Pesanan & Input Resi** | ❌ | ✅ Full | ✅ Full | ✅ Full |
| **Edit Stok Cepat & Pantau Low-Stock** | ❌ | ✅ Full | ✅ Full | ✅ Full |
| **CRUD Master Produk & Kategori** | ❌ | ❌ (Hanya Stok) | ✅ Full | ✅ Full |
| **CRUD Manajemen User** | ❌ | ❌ | ✅ Full | ✅ Full |
| **Inspeksi Riwayat Chatbot AI** | ❌ | ❌ | ✅ Full | ✅ Full |
| **Pengaturan Sistem & API Keys (Settings)** | ❌ | ❌ | ✅ Full | ✅ Full |
| **System Audit Logs** | ❌ | ❌ | ✅ Full | ✅ Full |
| **Laporan Omzet Keuangan & Grafik Harian** | ❌ | ❌ | ❌ | ✅ Full |
| **Owner AI Executive Advisor** | ❌ | ❌ | ❌ | ✅ Full |

