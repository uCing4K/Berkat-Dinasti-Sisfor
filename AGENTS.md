# 🤖 AGENTS.MD — Panduan Kerja & Protokol AI Assistant (Sistem Informasi Berkat Dinasti)

Dokumen ini berisi sekumpulan aturan baku, alur kerja, standar kualitas, arsitektur teknis, dan etika eksekusi yang **WAJIB** dipatuhi oleh seluruh AI Assistant / Agent dalam mengembangkan, merawat, dan memodifikasi proyek **Sistem Informasi Berkat Dinasti** (Sistem Manajemen Pesanan Khusus Bakery & Pastry — UMKM Dynasty Food & Beverage).

---

## 💬 1. Standar Komunikasi & Sapaan Respon
* **Sapaan Pembuka Wajib**: Awali setiap respon/jawaban kepada pengguna dengan kalimat:
  > **"oke mas"**
* **Gaya Komunikasi**: Lugas, solutif, santun, terstruktur, berbasis data teknis, dan langsung menjawab inti permasalahan tanpa bertele-tele.

---

## 🌿 2. Protokol Git, Percabangan & CI/CD Deployment
* **Branch Utama & Target Kerja**: Branch kerja utama repositori ini adalah `main`.
* **CI/CD Auto-Deploy via GitHub Actions**:
  * Repositori ini terintegrasi dengan **GitHub Actions** (`.github/workflows/main.yml`).
  * Setiap kode yang di-`push` ke branch `main` akan **secara otomatis langsung ter-deploy (*upload*) ke server cPanel hosting melalui FTP** ke domain live preview: **[ex5.ucing4k.my.id](https://ex5.ucing4k.my.id)**.
  * **Kredensial Testing Web (Live)**:
    * **URL**: [https://ex5.ucing4k.my.id/](https://ex5.ucing4k.my.id/)
    * **Username**: `admin`
    * **Password**: `kekuatanadmindinasti`
* **Kewajiban Verifikasi Sebelum Push**:
  * Karena push ke `main` langsung berdampak ke server live preview, AI **WAJIB memverifikasi keabsahan sintaks dan fungsionalitas kode** secara lokal sebelum melakukan commit/push.
  * Untuk penambahan fitur berskala besar atau eksperimental, buat branch terpisah (misal `feat/nama-fitur`) sebelum di-merge ke `main`.
* **Proteksi File & Direktori (FTP Exclude)**:
  * GitHub Actions mengecualikan folder `database/**`, `docs/**`, `prototypes/**`, dan `.git*` dari proses upload FTP.
  * Pastikan seluruh fungsionalitas aplikasi produksi berjalan mandiri di folder `app/`, `config/`, `public/`, `views/`, dan `index.php`.
* **Standar Pesan Commit (Conventional Commits with Scope)**:
  Gunakan format `type(scope): deskripsi jelas dan ringkas`.
  * `feat(scope):` penambahan fitur baru (misal: `feat(pesanan): implement Kanban board and delivery calendar`).
  * `fix(scope):` perbaikan bug (misal: `fix(beranda): move logoutModal outside script block`).
  * `refactor(scope):` restrukturisasi kode tanpa mengubah fungsionalitas.
  * `docs(scope):` pembaruan dokumentasi (misal: `docs(architecture): update DFD Level 1`).
  * `style(scope):` penyesuaian visual/formatting tanpa mengubah logika bisnis.
  * **Scope yang Tersedia**: `(beranda)`, `(produk)`, `(pesanan)`, `(pelanggan)`, `(kalender)`, `(laporan)`, `(pengaturan)`, `(auth)`, `(ui)`, `(db)`.

---

## 🏗️ 3. Arsitektur Sistem & Tech Stack Spesifik
* **Backend**:
  * **PHP Native** terstruktur dengan pola MVC mandiri:
    * `app/Controllers/`: Penanganan request dan logika alur kerja.
    * `app/Models/`: Interaksi data query ke database MySQL.
    * `app/Core/`: Router dan modul inti aplikasi.
    * `app/Helpers/`: Fungsi utilitas pendukung (format rupiah, tanggal, invoice generator).
* **Frontend**:
  * **PHP Views**: Template antarmuka di `views/pages/`, `views/partials/`, dan `views/layouts/`.
  * **Tailwind CSS**: Dijalankan via CDN dengan utility classes standar.
  * **Vanilla JavaScript**: Hindari dependensi framework JavaScript berat yang dapat memicu konflik rendering di lingkungan FTP hosting.
  * **Typography**: Wajib menggunakan font **Inter** (`docs/UI UX/Inter/`).
* **Database**:
  * **MySQL** dengan 13 tabel fisik (T1 s.d. T13) dan 7 kelompok Data Store (D1 s.d. D7).
  * Skema baseline tersimpan di `database/berkat_dinasti_final.sql`.
* **Environment Detection (`config/config.php`)**:
  * Aplikasi memiliki konfigurasi cerdas otomatis:
    * **Lokal (`localhost` / `127.0.0.1`)**: DB `berkat_dinasti`, user `root`, pass kosong, base URL `http://localhost/Berkat-Dinasti/public/`.
    * **Server Produksi (cPanel)**: DB `rodd1157_berkat_dinasti_db`, user `rodd1157_berkat_dinasti`, base URL `https://ex5.ucing4k.my.id/`.
  * **DILARANG KERAS** mengubah atau merusak mekanisme deteksi host otomatis pada `config/config.php`.

---

## 🎨 4. Desain Antarmuka & Aturan Bisnis (UI/UX Compliance)
* **Kepatuhan Figma Pixel-Perfect**:
  * Desain antarmuka merujuk pada **Figma Interactive Prototype Berkat Dinasti**.
  * Gunakan token node Figma yang tersedia (`figma_node.json`, `figma_node_clean.json`) atau MCP Figma Developer (`.agents/mcp_config.json`) untuk memastikan layout, padding, dan warna 1:1 sesuai desain.
* **Karakteristik & Batasan Antarmuka Berkat Dinasti**:
  1. **Khusus Internal Admin**: Aplikasi ini dirancang murni untuk Admin dan Staf UMKM (TIDAK ADA portal customer / e-commerce publik).
  2. **Katalog Produk Cepat**: Tabel master produk hanya menampilkan Nama Produk, Kategori, Varian Kemasan, dan Harga — **tanpa foto produk**, guna mempercepat *page load* operasional admin.
  3. **Tombol CTA Kontras & Ergonomis**: Tombol utama seperti `+ Tambah Pesanan Baru` dan modal `Simpan` harus berukuran besar dan kontras tinggi sesuai rekomendasi *usability testing*.
  4. **Kanban Swimlanes**: Pengelolaan status pesanan menggunakan 4 swimlane utama: `Menunggu Konfirmasi` ➔ `Proses Dapur` ➔ `Siap Kirim` ➔ `Selesai`.
  5. **Kalender Deadline Pengiriman**: Menampilkan visualisasi pesanan berdasarkan tanggal kirim untuk mencegah keterlambatan produksi.
  6. **Ketentuan Pengiriman & Armada**: Pengiriman di area Kota Malang & Kota Batu bersifat **Bebas Ongkir (Rp 0)**. Armada kurir obrok motor memiliki kapasitas maksimal **150 pcs roti per perjalanan (trip)**.

---

## 💳 5. Standar Data Transaksi, Invoice & Database
* **Format Penomoran Invoice Otomatis**:
  * Setiap pesanan baru wajib menggunakan penomoran otomatis dengan format standar:
    $$\text{INV-} \mathbf{YYYYMMDD} \text{-} \mathbf{XXXX}$$
    *Contoh:* `INV-20261026-0001` (Tanggal 26 Oktober 2026, nomor urut 1).
* **Perhitungan Pembayaran & Akumulasi Piutang**:
  * Status pembayaran terbagi menjadi **Lunas** ($\ge \text{Grand Total}$) dan **Hutang / DP** ($< \text{Grand Total}$).
  * Selisih pembayaran (sisa piutang) secara otomatis diakumulasikan ke field `total_hutang` pada tabel `pelanggan`.
* **Protokol Perubahan Skema Database**:
  * Setiap penambahan/modifikasi tabel atau kolom **WAJIB** dibuatkan file migrasi SQL terpisah di folder `database/` (misal: `database/migration_update.sql`).
  * Sinkronkan perubahan ke file baseline `database/berkat_dinasti_final.sql`.
  * Dilarang menjalankan query `DROP` atau destruktif tanpa instruksi eksplisit dan skrip rollback.

---

## 📚 6. Dokumentasi Terpusat di `docs/` (Obsidian Vault Knowledge Base)
Segala keputusan perancangan, perubahan arsitektur, atau penambahan fitur **WAJIB dicatat dan diselaraskan** dengan struktur folder dokumentasi Obsidian:
* `docs/00_Berkat_Dinasti_Index.md` : Home / Map of Content (MOC) utama.
* `docs/01-Architecture/` : Arsitektur DFD Level 0, 1, 2, Kamus Data Store (T1–T13), Matriks RBAC (Admin vs Staf).
* `docs/02-Features/` : Site Map, spesifikasi 7 modul utama, alur user flow, dan aturan armada.
* `docs/03-UI-UX-Guidelines/` : Panduan visual, typography Inter, hasil usability testing, dan link Figma.
* `docs/04-Changelogs/` : Catatan pembaruan, riwayat rilis, dan log migrasi.
* `docs/DATA/` : Dokumen riset, draf HKI, materi RPL, dan naskah referensi.

---

## 🔍 7. Verifikasi, Pengujian & Quality Assurance (QA) Mandiri
* **Lingkungan Live Preview & Kredensial Testing**:
  * **URL Live Server**: [https://ex5.ucing4k.my.id/](https://ex5.ucing4k.my.id/)
  * **Akun Testing Admin**:
    * **Username**: `admin`
    * **Password**: `kekuatanadmindinasti`
  * **Tujuan Pengujian**: Digunakan oleh AI Assistant / Developer untuk memverifikasi fungsionalitas fitur di server live, validasi otentikasi login, pemeriksaan antarmuka secara visual (*live smoke testing*), serta inspeksi alur transaksi pesanan.
* **Pengecekan Sintaks PHP Bebas Error**:
  * Pastikan file PHP yang dimodifikasi tidak memiliki *syntax error* (`php -l`) sebelum commit. Error sintaks pada PHP akan menyebabkan HTTP 500 error langsung di live server cPanel.
* **Pengecekan Konsol & Script JavaScript**:
  * Pastikan tidak ada syntax error pada Vanilla JS, fungsi modal ID yang tidak cocok, atau event listener yang duplikat.
* **Pengecekan Integrasi MVC**:
  * Pastikan Controller, Model, dan View terhubung secara konsisten, parameter query aman (Prepared Statements), dan data binding sesuai dengan skema database.
* **Kompatibilitas URL**:
  * Selalu gunakan konstanta `BASE_URL` dari `config/config.php` atau helper URL agar routing aset gambar, link CSS/JS, dan AJAX request tetap valid baik di localhost maupun cPanel hosting.

---

## ❓ 8. Prinsip Bebas Spekulasi (No Guesswork & Clarification)
* **Dilarang Menebak-nebak**: Jika instruksi User ambigu, tidak lengkap, atau memiliki risiko arsitektur/database tinggi, AI **DILARANG KERAS** mengambil asumsi sepihak.
* **Format Pengajuan Pertanyaan Wajib**:
  1. Penjelasan bagian yang ambigu atau membutuhkan konfirmasi.
  2. Minimal 2 (dua) opsi alternatif solusi beserta kelebihan dan kekurangannya.
  3. Rekomendasi solusi terbaik menurut AI beserta alasan teknisnya.

---

## 🛡️ 9. Keamanan, Integritas Kredensial & Clean Code
1. **Keamanan Kredensial**:
   * Dilarang melakukan hardcode password/token rahasia pada kode publik.
   * Pertahankan enkripsi password pengguna menggunakan algoritma hash modern (`password_hash` / `bcrypt`).
2. **Sanitasi Input & SQL Injection Prevention**:
   * Seluruh query database yang menerima input pengguna wajib menggunakan prepared statements (`PDO` / parameter binding).
3. **Kebersihan Kode (Clean Code)**:
   * Hapus fungsi debug sementara (`var_dump()`, `print_r()`, `console.log()`) sebelum melakukan commit/push ke branch `main`.
   * Pertahankan komentar dokumentasi fungsi dan ikuti struktur penamaan yang konsisten.

---

## 🧪 10. Metodologi Kerja: Bertahap & Atomic Tasks
* **Eksekusi Bertahap (Atomic Execution)**:
  * Pecah instruksi kompleks menjadi langkah-langkah kecil yang logis dan selesaikan secara berurutan.
  * Uji setiap modul secara independen sebelum beralih ke modul berikutnya.
* **Prinsip Minimal Regresi**:
  * Saat menambahkan fitur baru, pastikan modul lama (seperti Beranda, Produk, Pesanan, Kalender, Pelanggan, Laporan, Pengaturan) tidak mengalami kerusakan atau konflik style.
