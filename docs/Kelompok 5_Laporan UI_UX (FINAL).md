# LAPORAN PERANCANGAN

## USER INTERFACE / USER EXPERIENCE

### DYNASTY FOOD & BEVERAGE (BERKAT DINASTI)

---

**Disusun Oleh:**

Aditya Widyatamaka Tri Utomo (250535623587)

Chasbiah Azzahra (250535621731)

Dzauq Bachrul 'Ulum (250535621835)

Elshifa Viorissa (250535621859)

---

**PROGRAM STUDI S1 TEKNIK INFORMATIKA**

**DEPARTEMEN TEKNIK ELEKTRO DAN INFORMATIKA**

**FAKULTAS TEKNIK**

**UNIVERSITAS NEGERI MALANG**

**2026**

---

## BAB I — PENDAHULUAN DAN DASAR PERANCANGAN UI/UX

### 1.1 Latar Belakang

Dynasty Food & Beverage merupakan UMKM produsen dan penjual roti berbasis *pre-order* di Desa Songokerto, Kota Batu. Berdasarkan laporan DFD sebelumnya, penerimaan pesanan dilakukan melalui WhatsApp dan tatap muka, sedangkan pencatatan pesanan, pembayaran, dan rekap penjualan masih banyak dilakukan secara manual. Kondisi tersebut menjadi dasar pengembangan antarmuka digital untuk membantu kegiatan internal usaha.

Setelah dilakukan wawancara lanjutan dengan mitra, ruang lingkup antarmuka dipersempit agar benar-benar sesuai dengan kebutuhan operasional. Pelanggan tidak memerlukan website sendiri. Website ditujukan untuk **admin/pemilik** sebagai alat bantu pencatatan pesanan dan pemantauan pemasukan. Fitur utama yang dibutuhkan meliputi:

- Daftar pesanan dan tambah pesanan baru
- Kalender deadline pengiriman
- Tabel jenis roti beserta harga
- Status pesanan (*Proses* dan *Selesai*)
- Informasi pemasukan
- Manajemen pelanggan (data pelanggan retail & reseller)
- Laporan keuangan (rekap penjualan dan pembayaran)
- Manajemen pengguna dan hak akses (Admin/Pemilik & Staff)
- Manajemen produk/katalog

Jadwal pembuatan atau produksi tidak menjadi fokus utama antarmuka, namun tetap terakomodasi melalui kalender pengiriman.

### 1.2 Tujuan Perancangan

- Merancang website internal admin yang **sederhana, modern, dan sesuai alur kerja** toko roti.
- Mempermudah pencatatan dan pencarian data pesanan secara digital.
- Membantu admin memantau deadline pengiriman melalui kalender visual.
- Menyederhanakan pengelolaan status pesanan menjadi alur yang jelas dan mudah dipahami.
- Menyediakan informasi jenis roti dan produk dalam bentuk tabel yang mudah dikelola.
- Memusatkan informasi pemasukan dan laporan keuangan agar mudah dipantau oleh pemilik.
- Menyediakan manajemen pelanggan untuk mendukung *Customer Relationship Management* (CRM) sederhana.
- Mengimplementasikan manajemen pengguna berbasis *Role-Based Access Control* (RBAC) untuk Admin dan Staff.

### 1.3 Ruang Lingkup UI/UX

| Komponen | Keputusan UI/UX |
| :--- | :--- |
| Pengguna utama | Admin/Pemilik toko dan Staff operasional |
| Customer website | Tidak dibuat; pemesanan tetap melalui WhatsApp/offline |
| Pesanan | Daftar pesanan (Kanban Board), detail, tambah pesanan, dan perubahan status multi-tahap |
| Deadline | Kalender tanggal pengiriman pesanan (grid bulanan) |
| Produk / Jenis Roti | Tabel katalog produk (nama, kategori, kemasan, MOQ, harga); tanpa visual produk |
| Status Pesanan | Menunggu Konfirmasi → Proses Dapur/Produksi → Siap Kirim/Diambil → Selesai |
| Produksi | Tidak menampilkan jadwal pembuatan/produksi secara terpisah |
| Keuangan | Fokus pada pemasukan, laporan penjualan, status pembayaran, dan filter periode |
| Pelanggan | Manajemen data pelanggan (VIP Member, Mitra Reseller, Pelanggan Reguler) |
| Pengaturan | Profil UMKM, POS Thermal Simulator, Manajemen User & Akses |

### 1.4 Metode Design Thinking

Perancangan menggunakan **Design Thinking Framework** yang terdiri atas lima tahap: **Empathize**, **Define**, **Ideate**, **Prototype**, dan **Testing**. Tahapan ini digunakan agar rancangan antarmuka tidak hanya mengikuti struktur sistem pada DFD, tetapi juga menyesuaikan kebutuhan nyata pengguna dan menciptakan pengalaman yang intuitif serta efisien.

---

## BAB II — EMPATHIZE: USER RESEARCH DAN USER NEEDS

### 2.1 Tujuan Empathize

Tahap empathize bertujuan memahami secara mendalam aktivitas, kebutuhan, dan kendala admin/pemilik dalam mengelola pesanan roti. Data kebutuhan diperoleh dari:

1. **Laporan DFD** yang telah dibuat sebelumnya (termasuk 7 proses utama dan 13 tabel database)
2. **Wawancara lanjutan** langsung dengan mitra pada tanggal 20 September 2026
3. **Observasi lapangan** terhadap alur kerja harian toko roti

### 2.2 Ringkasan Kondisi Pengguna (*As-Is Condition*)

| Aspek | Temuan |
| :--- | :--- |
| **Cara menerima pesanan** | Pesanan diterima melalui WhatsApp dan tatap muka/offline. Tidak ada sistem terpusat untuk mengelola pesanan masuk. |
| **Pencatatan pesanan** | Pencatatan dilakukan secara manual dalam buku catatan/nota tulis tangan. Data harus dicari kembali secara manual ketika dibutuhkan. |
| **Database pelanggan** | Tidak ada database pelanggan yang dikelola secara digital. Informasi pelanggan tersebar di berbagai catatan. |
| **Invoice & Struk** | Invoice berupa nota tulis tangan. Tidak ada format standar atau auto-generate nomor invoice. |
| **Pengguna sistem** | Website hanya diperlukan untuk admin/pemilik dan staff, bukan customer. Customer tetap memesan melalui WhatsApp/tatap muka. |
| **Deadline pengiriman** | Admin perlu mengetahui tanggal pengiriman pesanan dengan cepat. Sering terjadi risiko deadline terlewat karena catatan manual. |
| **Status pesanan** | Tidak ada sistem tracking status pesanan yang terpusat. Admin harus mengingat/mengecek manual. |
| **Produk** | Jenis roti cukup disajikan dalam tabel; gambar produk tidak diperlukan untuk operasional internal. Terdapat berbagai varian kemasan (Kardus, Mika, Satuan) dan MOQ. |
| **Produksi** | Jadwal pembuatan tidak perlu ditampilkan secara khusus. |
| **Keuangan** | Pelacakan pembayaran menggunakan buku kas manual. Tidak ada rekap laporan penjualan rutin. Pemilik ingin fokus pada informasi pemasukan. |
| **Pengiriman** | Gratis ongkir seluruh Kota Malang dan Kota Batu. Armada motor obrok kapasitas 150 pcs roti besar per trip. |
| **Akses sistem** | Belum ada pembagian akses antara pemilik dan staff. Seluruh pengelolaan dilakukan oleh satu orang. |

### 2.3 User Needs

Berdasarkan hasil empathize, admin/pemilik membutuhkan sistem yang:

1. **Sederhana dan intuitif** — Tidak memiliki terlalu banyak menu dan mengutamakan informasi yang digunakan setiap hari.
2. **Cepat dalam input data** — Admin perlu dapat menambahkan pesanan dengan cepat, termasuk lookup data pelanggan dan validasi MOQ secara real-time.
3. **Terpusat** — Melihat daftar pesanan, deadline pengiriman, dan status pesanan dalam satu tempat (dashboard).
4. **Informatif** — Menampilkan ringkasan KPI (total pesanan, pending, pemasukan, pelanggan baru) secara sekilas.
5. **Mendukung multi-user** — Memiliki pembagian hak akses antara Admin/Pemilik dan Staff.
6. **Visual dan mudah dipahami** — Menggunakan kalender visual untuk deadline dan Kanban board untuk tracking status pesanan.
7. **Terintegrasi keuangan** — Menyediakan rekap pemasukan dengan filter periode, grafik tren penjualan, dan status pembayaran.

### 2.4 Prioritas Kebutuhan

| Prioritas | Kebutuhan | Alasan |
| :--- | :--- | :--- |
| **Tinggi** | Dashboard ringkasan (KPI Cards) | Memberikan gambaran cepat kondisi bisnis hari ini. |
| **Tinggi** | Tambah dan daftar pesanan (Kanban Board) | Merupakan aktivitas utama admin sehari-hari. |
| **Tinggi** | Kalender deadline pengiriman | Mengurangi risiko deadline terlewat. |
| **Tinggi** | Status pesanan multi-tahap | Memudahkan pemantauan progres pesanan dari awal hingga selesai. |
| **Tinggi** | Pemasukan dan laporan keuangan | Menjadi fokus informasi keuangan mitra. |
| **Sedang** | Manajemen produk/katalog | Membantu input pesanan dan pengelolaan harga serta varian kemasan. |
| **Sedang** | Manajemen pelanggan (CRM) | Mendukung klasifikasi pelanggan (VIP, Reseller, Reguler) dan tracking piutang. |
| **Sedang** | Manajemen pengguna & hak akses | Keamanan dan pembagian wewenang antara Admin dan Staff. |
| **Sedang** | Pengaturan profil UMKM | Konfigurasi identitas toko dan integrasi WhatsApp Business. |
| **Rendah** | POS Thermal Simulator | Fitur pendukung untuk preview struk thermal 80mm dengan QRIS. |
| **Tidak diprioritaskan** | Jadwal produksi dan website customer | Tidak dibutuhkan berdasarkan wawancara lanjutan. |

---

## BAB III — DEFINE: PERUMUSAN MASALAH PENGGUNA

### 3.1 Affinity Diagram

Temuan wawancara dan observasi dikelompokkan berdasarkan kesamaan tema agar permasalahan utama lebih mudah ditentukan:

| Kelompok Tema | Temuan Utama |
| :--- | :--- |
| **Pesanan** | Daftar pesanan, tambah pesanan dengan validasi MOQ, detail pesanan, pencarian data, Kanban board untuk tracking alur. |
| **Deadline & Pengiriman** | Kalender grid bulanan untuk melihat tanggal pengiriman, jadwal harian, dan rute kurir. |
| **Status** | Status dibuat multi-tahap: Menunggu Konfirmasi → Proses Dapur → Siap Kirim → Selesai. |
| **Produk & Katalog** | Jenis roti disajikan dalam tabel tanpa gambar. Termasuk kategori, kemasan, MOQ, dan harga. |
| **Pelanggan** | Data pelanggan dengan klasifikasi (VIP Member, Mitra Reseller, Reguler), riwayat pesanan, dan integrasi WhatsApp. |
| **Keuangan** | Informasi pemasukan harus mudah ditemukan. Rekap penjualan dengan grafik tren dan donut chart status pembayaran. Filter periode dan export laporan. |
| **Pengaturan & Akses** | Profil UMKM, manajemen user multi-role, preview struk thermal. |
| **Batasan** | Tidak ada website customer, tidak ada jadwal pembuatan/produksi terpisah. |

### 3.2 User Persona

#### Persona 1: Pemilik / Admin Utama

| Elemen Persona | Deskripsi |
| :--- | :--- |
| **Nama** | Pemilik Dynasty Food & Beverage |
| **Peran** | Admin Utama — Mengelola seluruh operasional, katalog produk, keuangan, dan master data. |
| **Usia** | 30-45 tahun |
| **Latar Belakang** | Pengusaha UMKM roti di Kota Batu. Mengelola bisnis pre-order roti untuk acara hajatan/syukuran dan reseller. |
| **Tujuan** | Mencatat pesanan secara rapi dan digital, mengetahui deadline, memantau status pesanan, mengelola pelanggan, dan melihat pemasukan secara real-time. |
| **Kebutuhan** | Dashboard yang informatif, website internal yang sederhana dan cepat dipahami, laporan keuangan yang dapat di-export, dan kontrol penuh atas sistem. |
| **Frustrasi** | Pencatatan manual membuat pencarian data, pemantauan deadline, dan rekap pemasukan kurang praktis. Risiko data hilang atau tertukar. |
| **Kebiasaan Teknologi** | Menggunakan WhatsApp sehari-hari untuk komunikasi bisnis. Familiar dengan smartphone, namun preferensi operasional melalui laptop/PC. |
| **Hak Akses** | Full Access (CRUD) ke seluruh modul: Produk, Pelanggan, Pesanan, Pembayaran, Pengiriman, Laporan, dan Pengaturan Pengguna. |

#### Persona 2: Staff Operasional

| Elemen Persona | Deskripsi |
| :--- | :--- |
| **Nama** | Staff Operasional (contoh: Kasir / Staf Produksi) |
| **Peran** | Membantu input pesanan harian, pencatatan pembayaran, dan update jadwal pengiriman. |
| **Usia** | 20-35 tahun |
| **Tujuan** | Memasukkan pesanan baru dengan cepat, mencatat pembayaran masuk, dan memperbarui status pengiriman. |
| **Kebutuhan** | Antarmuka yang fokus pada tugas input dan update status. Tidak memerlukan akses ke fitur konfigurasi atau laporan lengkap. |
| **Frustrasi** | Harus menunggu instruksi pemilik untuk memverifikasi data. Tidak ada sistem standar untuk input pesanan. |
| **Hak Akses** | Terbatas — Full Access ke Input Pesanan dan Pembayaran, View Only ke Data Pelanggan, Input & Update Status Pengiriman, Terbatas pada Rekap Kas Harian untuk Laporan. Tidak memiliki akses ke Pengaturan Akun dan Master Produk. |

### 3.3 Pain Points

1. **Data pesanan manual sulit dicari kembali** — Pencarian data dari buku catatan membutuhkan waktu lama, terutama saat volume pesanan tinggi.
2. **Deadline pengiriman berpotensi terlewat** — Mengandalkan ingatan atau catatan manual rawan kesalahan.
3. **Status pesanan tidak terpantau secara terpusat** — Tidak ada visualisasi alur progres pesanan dari masuk hingga selesai.
4. **Pemasukan perlu direkap secara manual** — Menghitung ulang catatan transaksi satu per satu untuk mengetahui total pemasukan.
5. **Tidak ada database pelanggan terstruktur** — Informasi pelanggan tersebar dan tidak terklasifikasi (retail vs reseller).
6. **Fitur yang terlalu kompleks memperlambat** — Admin membutuhkan antarmuka sederhana, bukan sistem enterprise yang berat.
7. **Tidak ada pembagian akses** — Semua pengelolaan dilakukan satu orang, padahal ada staff yang bisa membantu.
8. **Tidak ada format invoice standar** — Nota tulis tangan tidak profesional dan rawan kesalahan.

### 3.4 5 Whys Analysis

| Tahap | Pertanyaan dan Jawaban |
| :--- | :--- |
| **Why 1** | **Mengapa admin kesulitan mengelola pesanan?** — Karena pencatatan masih dilakukan secara manual di buku catatan. |
| **Why 2** | **Mengapa pencatatan manual menjadi masalah?** — Karena data harus dicari kembali satu per satu saat admin membutuhkan detail pesanan, deadline, atau rekap keuangan. |
| **Why 3** | **Mengapa pencarian data membutuhkan waktu?** — Karena informasi pesanan, pelanggan, dan keuangan belum tersedia secara terpusat dalam satu sistem digital. |
| **Why 4** | **Mengapa informasi belum terpusat?** — Karena belum ada antarmuka admin yang menyatukan pesanan, deadline, status, data pelanggan, dan pemasukan dalam satu platform. |
| **Why 5** | **Mengapa antarmuka tersebut diperlukan?** — Agar seluruh aktivitas rutin operasional (input pesanan, cek deadline, update status, rekap pemasukan) dapat dilakukan lebih cepat, akurat, dan tidak bergantung pada catatan manual yang rentan hilang atau salah. |

**Root Cause:** Belum adanya sistem informasi internal yang sederhana dan modern untuk memusatkan data pesanan, deadline, status, pelanggan, dan pemasukan sesuai alur kerja admin/pemilik.

### 3.5 Problem Statement

Admin dan pemilik Dynasty Food & Beverage membutuhkan **sistem berbasis web yang sederhana, modern, dan terintegrasi** untuk:
- Mencatat dan memantau pesanan secara digital (dari penerimaan hingga selesai)
- Mengelola deadline pengiriman melalui visualisasi kalender
- Mengelola data pelanggan dengan klasifikasi dan riwayat transaksi
- Mengelola katalog produk, varian kemasan, dan harga
- Memantau pemasukan dan status pembayaran secara real-time
- Membagi akses antara Admin/Pemilik dan Staff operasional

Karena proses pencatatan manual saat ini membuat pengelolaan informasi pesanan dan keuangan menjadi kurang praktis, rawan kesalahan, dan menghambat pertumbuhan bisnis.

### 3.6 How Might We

> **Bagaimana kita dapat merancang website admin yang sederhana, modern, dan informatif** agar pemilik dan staff toko roti dapat:
> - Mencatat pesanan dengan cepat (termasuk lookup pelanggan dan validasi MOQ)
> - Memantau deadline pengiriman melalui kalender visual
> - Mengelola status pesanan dengan alur yang jelas (Kanban board)
> - Mengelola data pelanggan dan produk secara terpusat
> - Melihat pemasukan, laporan keuangan, dan tren penjualan
> - **dengan lebih cepat, terorganisir, dan minim kesalahan?**

---

## BAB IV — IDEATE: PERANCANGAN SOLUSI DAN ALUR

### 4.1 Solusi dari How Might We

Solusi yang diusulkan adalah **Sistem Informasi Berkat Dinasti (SISFOR - BERKAT DINASTI)** — sebuah web dashboard admin yang berfungsi sebagai pusat informasi internal dengan:

1. **Dashboard Beranda** — Menampilkan KPI Cards (Total Pesanan Hari Ini, Pesanan Pending, Total Pemasukan Bulan Ini + persentase target, Pelanggan Baru), tabel pesanan yang perlu tindakan, kitchen widget (batch siap kirim/dalam proses/menunggu dapur), inventory alert (stok kritis), dan quick actions.
2. **Manajemen Pesanan (Kanban Board)** — 4 swimlane status: *Menunggu Konfirmasi* → *Proses Dapur/Produksi* → *Siap Kirim/Diambil* → *Selesai*. Dilengkapi modal tambah pesanan dengan customer lookup dan validasi MOQ real-time.
3. **Kalender Pengiriman** — Grid kalender bulanan menampilkan jadwal produksi harian dan rute pengiriman kurir.
4. **Manajemen Produk** — Tabel katalog produk dengan filter kategori (Roti Manis, Cake & Tart, Pastry, Donat), status, dan kemampuan export. Modal tambah/edit produk.
5. **Manajemen Pelanggan (CRM)** — Daftar pelanggan dengan klasifikasi (VIP Member, Mitra Reseller, Pelanggan Reguler), panel detail dengan integrasi WhatsApp, dan sub-tabs riwayat pesanan & info kontak.
6. **Laporan & Keuangan** — Filter periode bulanan/date range, cards ringkasan (Total Pemasukan, Total Pesanan, Completion Rate), bar chart tren penjualan, donut chart status pembayaran, dan fitur export Excel/cetak laporan.
7. **Pengaturan** — Profil UMKM (info toko, logo, WhatsApp Business), thermal receipt simulator 80mm (preview struk POS dengan QRIS Mandiri), dan manajemen user/akses (RBAC: Owner vs Staff/Kasir).

### 4.2 Daftar Fitur Prioritas

| Fitur | Fungsi UI/UX | Proses DFD Terkait |
| :--- | :--- | :--- |
| **Dashboard / Beranda** | Menampilkan ringkasan KPI, pesanan perlu tindakan, kitchen widget, inventory alert, dan quick actions. | Proses 3.0, 4.0, 6.0 |
| **Tambah Pesanan** | CTA utama `+ Tambah Pesanan Baru` dengan modal form, customer lookup, pilih produk, validasi MOQ. | Proses 3.0 (Sub 3.1, 3.2, 3.3) |
| **Daftar Pesanan (Kanban Board)** | Menampilkan pesanan dalam 4 swimlane status dengan drag & detail card. Header ringkasan (Total Menunggu, Selesai, Omset). | Proses 3.0 (Sub 3.4) |
| **Kalender Pengiriman** | Grid bulanan menampilkan tanggal pengiriman, jumlah pesanan per hari, dan jadwal kurir. | Proses 5.0 (Sub 5.1) |
| **Manajemen Produk** | Tabel master produk (Nama, Kategori, Kemasan, MOQ, Harga). KPI ketersediaan produk. Modal tambah/edit. | Proses 1.0 |
| **Manajemen Pelanggan** | Daftar pelanggan dengan klasifikasi, detail panel, tombol WhatsApp & Order Baru, riwayat pesanan. | Proses 2.0 |
| **Laporan & Keuangan** | Filter periode, cards ringkasan, grafik tren penjualan, donut status pembayaran, export Excel/cetak. | Proses 6.0 |
| **Status Pesanan** | Perubahan status sekuensial melalui Kanban board (Menunggu → Proses → Siap Kirim → Selesai). | Proses 3.0 (Sub 3.4) |
| **Pengaturan Profil** | Info toko, logo uploader, status WhatsApp Business. | Proses 7.0 |
| **Manajemen User** | Daftar pengguna sistem, role (Admin Utama / Kasir / Staf Produksi), status aktif/nonaktif, perbandingan hak akses. | Proses 7.0 |
| **POS Thermal Simulator** | Preview struk receipt 80mm dengan QRIS Mandiri. | Proses 4.0 (Sub 4.4) |

### 4.3 Site Map

- **BERKAT DINASTI — Sistem Informasi**
  - **🔐 LOGIN**
    - Form Login (Username + Password)
    - Toggle Visibility Password
    - "Ingat Saya" Checkbox
    - "Lupa Password?" → Reset Password
  - **🏠 BERANDA (Dashboard)**
    - KPI Cards
      - Total Pesanan Hari Ini (misal: 8 order)
      - Pesanan Pending (misal: 3)
      - Total Pemasukan Bulan Ini (misal: Rp 4.250.000 / 85% target)
      - Pelanggan Baru (misal: 5 member)
    - Tabel "Pesanan Perlu Tindakan"
      - List pesanan dengan deadline & status pembayaran
    - Kitchen Widget
      - Batch siap kirim
      - Dalam proses
      - Menunggu dapur
    - Inventory Alert (misal: Stok tepung terigu kritis)
    - Quick Actions
      - Unduh Rekap Harian
      - \+ Tambah Pesanan Baru
  - **📦 PRODUK (Manajemen Katalog)**
    - KPI Cards (24 varian, 21 aktif, 5 kemasan, 98.4% ketersediaan)
    - Filter: Kategori | Status | Export
    - Tabel Produk (Nama, Kategori, Kemasan, MOQ, Harga)
    - Modal Tambah/Edit Produk
  - **📋 PESANAN (Kanban Board)**
    - Header Ringkasan (Total Menunggu, Selesai, Omset)
    - Swimlane 1: Menunggu Konfirmasi
    - Swimlane 2: Proses Dapur / Produksi
    - Swimlane 3: Siap Kirim / Diambil
    - Swimlane 4: Selesai
    - Modal Tambah Pesanan (Customer Lookup + MOQ Validation)
  - **📅 KALENDER PENGIRIMAN**
    - Grid Kalender Bulanan
    - Jadwal Produksi Harian
    - Rute Pengiriman Kurir
    - Highlight Tanggal Aktif + Jumlah Pesanan
  - **👥 PELANGGAN (CRM)**
    - Customer List (VIP Member, Mitra Reseller, Reguler)
    - Detail Panel
      - Tombol WhatsApp
      - Tombol Order Baru
      - Ringkasan Transaksi
    - Sub-tabs
      - Riwayat Pesanan
      - Info Kontak
  - **📊 LAPORAN & KEUANGAN**
    - Filter: Periode Bulanan | Date Range
    - Actions: Unduh Excel | Cetak Laporan
    - Cards Ringkasan
      - Total Pemasukan (+ persentase pertumbuhan)
      - Total Pesanan (misal: 48)
      - Completion Rate (misal: 87.5%)
    - Grafik
      - Bar Chart: Tren Penjualan Bulanan
      - Donut Chart: Status Pembayaran (Lunas / DP-Hutang / Belum Bayar)
  - **⚙️ PENGATURAN**
    - Profil UMKM
      - Info Toko & Logo Uploader
      - WhatsApp Business Status
    - POS Thermal Simulator
      - Preview Struk 80mm dengan QRIS Mandiri
    - Manajemen User & Akses
      - Daftar Pengguna (Role, Status Aktif/Nonaktif)
      - Perbandingan Hak Akses (Owner vs Staff/Kasir)

### 4.4 User Flow Utama — Tambah Pesanan

1. **Login**
2. Masuk ke **Dashboard (Beranda)**
3. Klik **"+ Tambah Pesanan Baru"** (Quick Action / CTA)
4. **Modal Form Pesanan** muncul:
   - Lakukan **Customer Lookup** (cari/pilih pelanggan)
   - Pilih produk/varian roti
   - Masukkan jumlah (sistem validasi MOQ secara real-time)
   - Tentukan tanggal pengiriman
   - Masukkan info pembayaran
   - Klik **"Simpan"**
5. Pesanan masuk ke Kanban Board swimlane **"Menunggu Konfirmasi"**
6. Admin mengkonfirmasi, pesanan pindah ke **"Proses Dapur"**
7. Selesai produksi, pesanan pindah ke **"Siap Kirim/Diambil"**
8. Pesanan terkirim, pesanan pindah ke **"Selesai"**

### 4.5 User Flow — Cek Deadline Pengiriman

1. Masuk ke **Dashboard (Beranda)**
2. Klik menu **"Kalender"** di sidebar
3. Tampil grid kalender bulanan (misal: September 2025)
4. Tanggal aktif ter-highlight dengan jumlah pesanan
5. Klik **tanggal tertentu**
6. Sistem menampilkan daftar pesanan pada tanggal tersebut
7. Admin membuka **detail pesanan**
8. Admin dapat kembali ke daftar atau **memperbarui status**

### 4.6 User Flow — Cek Pemasukan & Laporan

1. Masuk ke **Dashboard (Beranda)**
2. Klik menu **"Laporan"** di sidebar
3. Tampil halaman **Laporan & Keuangan**
4. Filter periode (bulanan / date range)
5. Sistem menampilkan:
   - **Cards**: Total Pemasukan (+pertumbuhan), Total Pesanan, Completion Rate
   - **Bar Chart**: Tren penjualan per bulan
   - **Donut Chart**: Status pembayaran (75% Lunas, 16.7% DP/Hutang, 8.3% Belum Bayar)
6. Admin dapat klik **"Unduh Excel"** atau **"Cetak Laporan"**
7. Admin dapat membuka detail transaksi/pesanan terkait

### 4.7 User Flow — Kelola Pelanggan

1. Masuk ke **Dashboard (Beranda)**
2. Klik menu **"Pelanggan"** di sidebar
3. Tampil **daftar pelanggan** (VIP, Reseller, Reguler)
4. Klik salah satu pelanggan
5. **Detail Panel** muncul, menampilkan:
   - Info pelanggan, ringkasan transaksi
   - Tombol **"WhatsApp"** untuk kontak langsung
   - Tombol **"Order Baru"** untuk membuat pesanan
   - Tab **"Riwayat Pesanan"** dan **"Info Kontak"**

### 4.8 Wireflow

| Halaman Awal | Aksi Pengguna | Halaman / Komponen Tujuan |
| :--- | :--- | :--- |
| Login | Login berhasil | Dashboard (Beranda) |
| Dashboard | Klik `+ Tambah Pesanan Baru` | Modal Form Tambah Pesanan |
| Modal Tambah Pesanan | Simpan pesanan | Kanban Board (swimlane "Menunggu Konfirmasi") |
| Dashboard / Pesanan | Klik salah satu card pesanan | Detail Pesanan |
| Dashboard | Klik menu Kalender di sidebar | Kalender Pengiriman (Grid Bulanan) |
| Kalender | Klik tanggal dengan pesanan aktif | Detail Pesanan pada tanggal tersebut |
| Dashboard | Klik menu Produk di sidebar | Tabel Manajemen Produk |
| Produk | Klik `+ Tambah Produk` | Modal Form Tambah Produk |
| Dashboard | Klik menu Pelanggan di sidebar | Daftar Pelanggan (CRM) |
| Pelanggan | Klik salah satu pelanggan | Detail Panel Pelanggan |
| Dashboard | Klik menu Laporan di sidebar | Halaman Laporan & Keuangan |
| Laporan | Klik `Unduh Excel` / `Cetak Laporan` | Download / Print |
| Dashboard | Klik menu Pengaturan di sidebar | Halaman Pengaturan |
| Pengaturan | Klik tab Manajemen User | Daftar Pengguna & Hak Akses |

---

## BAB V — PROTOTYPE: LOW-FIDELITY DAN HIGH-FIDELITY

### 5.1 Prinsip Prototype

Prototype dikembangkan dalam dua tingkat ketelitian:

1. **Low-Fidelity (Wireframe)** — Digunakan untuk memvalidasi susunan informasi, navigasi, tata letak elemen, dan prioritas tombol/CTA tanpa fokus pada visual akhir. Dibuat dalam bentuk wireframe hitam-putih dengan placeholder konten.

2. **High-Fidelity (Figma)** — Mengembangkan struktur wireframe menjadi tampilan yang mendekati produk akhir dengan:
   - Design system yang konsisten (typography, color palette, spacing, icons)
   - Komponen interaktif (buttons, modals, tables, calendar, charts)
   - State management (hover, active, disabled, loading)
   - Prototype flow yang dapat diklik dan dinavigasi

### 5.2 Design System — Identitas Visual

| Elemen Desain | Spesifikasi |
| :--- | :--- |
| **Tema** | Warm Bakery / Culinary — Hangat dan profesional |
| **Warna Primer** | Warm Amber/Orange `#F08223` — Mencerminkan identitas bakery |
| **Sidebar** | Deep Chocolate Charcoal `#2B231F` — Kontras premium dengan sidebar gelap |
| **Background** | Soft Off-White `#F8F9FA` — Bersih dan nyaman untuk operasional |
| **Status Hijau** | Lunas / Aktif / Selesai |
| **Status Merah** | Belum Bayar / Overdue / Stok Kritis |
| **Status Kuning** | Proses / Pending / DP/Hutang |
| **Navigasi** | Persistent left sidebar — Konsisten dan mudah diakses |
| **Layout** | Sidebar (fixed) + Content area (scrollable) |

### 5.3 Rancangan Low-Fidelity (Wireframe) per Halaman

#### A. Login & Authentication

**Wireframe Login:**
- Header: Logo Berkat Dinasti + tagline "*Kelola Pesanan Rotimu dengan Mudah*"
- Form: Input username + input password (toggle visibility)
- Checkbox: "Ingat Saya"
- Link: "Lupa Password?"
- Button: "Masuk"

**Wireframe Reset Password:**
- Form recovery (input email/username)
- Button: "Kirim Link Reset"

#### B. Dashboard / Beranda

**Wireframe Dashboard:**
- **Header Row:** 4 KPI Cards horizontal
  - Card 1: Total Pesanan Hari Ini (icon + angka + label)
  - Card 2: Pesanan Pending (icon + angka + label)
  - Card 3: Total Pemasukan Bulan Ini (icon + nominal + progress bar target)
  - Card 4: Pelanggan Baru (icon + angka + label)
- **Middle Row:** Tabel "Pesanan Perlu Tindakan" (kolom: No Invoice, Pelanggan, Deadline, Status Pembayaran, Aksi)
- **Bottom Row:** Kitchen Widget (3 section: Siap Kirim, Dalam Proses, Menunggu Dapur) + Inventory Alert (warning stok kritis)
- **Quick Actions:** Button `Unduh Rekap Harian` + Button `+ Tambah Pesanan Baru`

#### C. Manajemen Produk

**Wireframe Produk:**
- **KPI Row:** 4 mini-cards (Total Varian, Aktif, Jenis Kemasan, Ketersediaan %)
- **Filter Bar:** Dropdown Kategori + Dropdown Status + Button Export
- **Tabel Produk:** Kolom (Nama Produk, Kategori, Kemasan, MOQ, Harga, Aksi)
- **Modal Tambah Produk:** Form (Nama, Kategori, Harga, Tipe Kemasan, Isi/Kemasan, MOQ, Status Toggle, Deskripsi, Button Simpan/Batal)

#### D. Manajemen Pesanan (Kanban Board)

**Wireframe Kanban:**
- **Header:** Summary cards (Total Menunggu, Total Selesai, Omset)
- **4 Kolom Swimlane:**
  1. *Menunggu Konfirmasi* — Card pesanan dengan info singkat
  2. *Proses Dapur / Produksi* — Card pesanan
  3. *Siap Kirim / Diambil* — Card pesanan
  4. *Selesai* — Card pesanan
- **Setiap Card:** Nama pelanggan, jenis roti, jumlah, tanggal kirim, badge status
- **Button:** `+ Tambah Pesanan` (membuka modal)

#### E. Kalender Pengiriman

**Wireframe Kalender:**
- **Header:** Navigasi bulan (prev | Bulan Tahun | next)
- **Grid:** 7 kolom (Senin-Minggu) x 5-6 baris
- **Setiap Cell:** Tanggal + badge jumlah pesanan (jika ada)
- **Sidebar:** Daftar pesanan pada tanggal terpilih

#### F. Manajemen Pelanggan (CRM)

**Wireframe Pelanggan:**
- **List Panel (kiri):** Daftar pelanggan dengan avatar, nama, tipe (VIP/Reseller/Reguler), badge
- **Detail Panel (kanan):**
  - Info pelanggan (Nama, Alamat, No. WA, Tipe)
  - Button `WhatsApp` + Button `Order Baru`
  - Tab 1: Riwayat Pesanan (tabel: Tanggal, Invoice, Total, Status)
  - Tab 2: Info Kontak

#### G. Laporan & Keuangan

**Wireframe Laporan:**
- **Filter Bar:** Dropdown Periode + Date Range Picker + Button `Unduh Excel` + Button `Cetak Laporan`
- **Cards Row:** Total Pemasukan (+ % growth) | Total Pesanan | Completion Rate
- **Chart Area:**
  - Bar Chart: Tren Penjualan per Bulan (6-12 bulan)
  - Donut Chart: Status Pembayaran (Lunas / DP-Hutang / Belum Bayar) dengan persentase

#### H. Pengaturan

**Wireframe Pengaturan:**
- **Tab 1 - Profil UMKM:** Form (Nama Toko, Alamat, Telepon, Logo Upload, Status WhatsApp Business)
- **Tab 2 - POS Thermal Simulator:** Preview struk receipt 80mm (header toko, daftar item, total, QRIS Mandiri)
- **Tab 3 - Manajemen User:** Tabel pengguna (Nama, Role, Status), tombol Tambah User, dan tabel perbandingan hak akses Owner vs Staff/Kasir

### 5.4 Arah High-Fidelity

Desain High-Fidelity dikembangkan menggunakan **Figma** dengan spesifikasi sebagai berikut:

#### Prinsip Desain Visual:

| Aspek | Implementasi High-Fidelity |
| :--- | :--- |
| **Typography** | Font modern dan mudah dibaca (Inter / Roboto). Hierarki heading jelas. |
| **Color Palette** | Warm amber/orange sebagai aksen utama, dark chocolate sidebar, off-white background. Semantic colors untuk status. |
| **Spacing** | Grid system 8px. Consistent padding dan margin. |
| **Komponen** | Button (primary, secondary, ghost), Input fields, Dropdowns, Modals, Tables, Cards, Badges, Toast notifications. |
| **Icons** | Icon set konsisten (Lucide/Heroicons style). Sidebar icons dengan label. |
| **Charts** | Bar chart dengan gradient colors. Donut chart dengan legend. |
| **States** | Default, Hover, Active, Disabled, Error, Loading states. |
| **Responsiveness** | Desktop-first design (target utama laptop/PC admin). |

#### Daftar Screen High-Fidelity (25 Frame pada Figma):

| No. | Nama Screen | Deskripsi |
| :--- | :--- | :--- |
| 1 | Cover / Splash Screen | Branding "Berkat Dinasti" dengan tagline |
| 2 | Login Screen | Form login (username, password, toggle visibility, ingat saya, lupa password) |
| 3 | Reset Password | Form recovery password |
| 4 | Dashboard — Beranda | KPI cards, tabel pesanan perlu tindakan, kitchen widget, inventory alert |
| 5 | Dashboard — Quick Actions | CTA Unduh Rekap Harian dan + Tambah Pesanan Baru |
| 6 | Produk — Daftar Katalog | KPI ketersediaan, filter kategori, tabel produk |
| 7 | Produk — Modal Tambah | Form tambah produk baru (nama, kategori, harga, kemasan, MOQ, status) |
| 8 | Pesanan — Kanban Board | 4 swimlane status, header ringkasan, pesanan cards |
| 9 | Pesanan — Modal Tambah | Form tambah pesanan (customer lookup, pilih produk, MOQ validation) |
| 10 | Pesanan — Detail | Detail lengkap pesanan individual |
| 11 | Kalender — Grid Bulanan | Kalender September 2025, tanggal aktif dengan badge pesanan |
| 12 | Kalender — Detail Harian | Jadwal produksi harian dan rute pengiriman kurir |
| 13 | Pelanggan — Daftar | List pelanggan (VIP, Reseller, Reguler) |
| 14 | Pelanggan — Detail Panel | Info pelanggan, WhatsApp button, ringkasan transaksi |
| 15 | Pelanggan — Riwayat Pesanan | Tab riwayat pesanan pelanggan |
| 16 | Pelanggan — Info Kontak | Tab info kontak pelanggan |
| 17 | Laporan — Overview | Filter periode, cards ringkasan, charts |
| 18 | Laporan — Bar Chart | Tren penjualan bulanan |
| 19 | Laporan — Donut Chart | Status pembayaran (Lunas 75%, DP 16.7%, Belum Bayar 8.3%) |
| 20 | Pengaturan — Profil UMKM | Info toko, logo uploader, WhatsApp Business status |
| 21 | Pengaturan — Thermal Receipt | Preview struk POS 80mm dengan QRIS Mandiri |
| 22 | Pengaturan — Manajemen User | Daftar pengguna (Admin Utama, Kasir, Staf Produksi) |
| 23 | Pengaturan — Role Permissions | Perbandingan hak akses Owner vs Staff/Kasir |
| 24 | State — MOQ Validation Warning | Warning real-time saat jumlah pesanan di bawah MOQ |
| 25 | State — Inventory Alert | Alert stok bahan baku kritis (misal: tepung terigu 12 kg, min 15 kg) |

#### Link Prototype Figma:

> **[SISFOR - BERKAT DINASTI — High Fidelity Prototype](https://www.figma.com/proto/nyxxCopYBjWGphWFkbYawy/SISFOR---BERKAT-DINASTI?node-id=60-3969&p=f&viewport=666%2C65%2C0.09&t=6UeotSVE8pX1QdB7-1&scaling=min-zoom&content-scaling=fixed&page-id=0%3A1)**

Prototype ini dapat diklik dan dinavigasi untuk menguji alur pengguna secara interaktif.

---

## BAB VI — TESTING: USER FEEDBACK

### 6.1 Tujuan Testing

Testing dilakukan untuk mengetahui apakah admin/pemilik dan staff dapat menggunakan prototype High-Fidelity sesuai kebutuhan nyata operasional toko roti. Pengujian berfokus pada:

- **Findability** — Kemudahan menemukan fitur dan informasi
- **Understandability** — Kemampuan memahami informasi yang ditampilkan
- **Efficiency** — Kecepatan menyelesaikan aktivitas utama
- **Satisfaction** — Kepuasan pengguna terhadap tampilan dan pengalaman

### 6.2 Skenario Usability Testing

| No. | Task Pengujian | Indikator Keberhasilan |
| :--- | :--- | :--- |
| 1 | **Tambahkan satu pesanan baru** dengan tanggal pengiriman tertentu. | Pengguna dapat menemukan CTA `+ Tambah Pesanan Baru`, mengisi form (customer lookup, pilih produk, tentukan jumlah dan tanggal kirim), dan menyimpan pesanan tanpa bantuan. |
| 2 | **Cari pesanan dengan deadline paling dekat.** | Pengguna dapat menemukan informasi deadline melalui dashboard (tabel "Pesanan Perlu Tindakan") atau kalender pengiriman, dan mengidentifikasi pesanan dengan deadline terdekat. |
| 3 | **Ubah status pesanan** dari "Menunggu Konfirmasi" ke "Proses Dapur" kemudian ke "Selesai". | Pengguna memahami Kanban board, dapat memindahkan/mengubah status pesanan melalui card, dan perubahan tersimpan dengan benar. |
| 4 | **Cari total pemasukan pada periode tertentu.** | Pengguna menemukan halaman Laporan & Keuangan melalui sidebar, menggunakan filter periode, dan membaca data ringkasan (cards, bar chart, donut chart) dengan benar. |
| 5 | **Cari harga dan MOQ salah satu jenis roti.** | Pengguna menemukan halaman Produk melalui sidebar, menggunakan filter kategori, dan menemukan informasi harga serta MOQ pada tabel produk. |
| 6 | **Cari data pelanggan tertentu dan lihat riwayat pesanannya.** | Pengguna menemukan halaman Pelanggan, mencari pelanggan di daftar, membuka detail panel, dan mengakses tab riwayat pesanan. |
| 7 | **Unduh rekap laporan penjualan dalam format Excel.** | Pengguna menemukan fitur "Unduh Excel" pada halaman Laporan, mengklik tombol, dan memahami proses download. |

### 6.3 Form User Feedback

| No. | Task | Hasil | Feedback User | Perbaikan |
| :--- | :--- | :--- | :--- | :--- |
| 1 | Tambah pesanan baru | Berhasil / Belum diuji | *(Diisi setelah testing mitra)* | *(Diisi berdasarkan feedback)* |
| 2 | Cari deadline terdekat | Berhasil / Belum diuji | *(Diisi setelah testing mitra)* | *(Diisi berdasarkan feedback)* |
| 3 | Ubah status pesanan | Berhasil / Belum diuji | *(Diisi setelah testing mitra)* | *(Diisi berdasarkan feedback)* |
| 4 | Cari total pemasukan | Berhasil / Belum diuji | *(Diisi setelah testing mitra)* | *(Diisi berdasarkan feedback)* |
| 5 | Cari harga jenis roti | Berhasil / Belum diuji | *(Diisi setelah testing mitra)* | *(Diisi berdasarkan feedback)* |
| 6 | Cari data pelanggan | Berhasil / Belum diuji | *(Diisi setelah testing mitra)* | *(Diisi berdasarkan feedback)* |
| 7 | Unduh laporan Excel | Berhasil / Belum diuji | *(Diisi setelah testing mitra)* | *(Diisi berdasarkan feedback)* |

### 6.4 Pertanyaan Setelah Testing

Pertanyaan berikut digunakan untuk mengumpulkan feedback kualitatif dari mitra setelah sesi testing:

1. **Secara keseluruhan**, apakah tampilan website ini mudah dipahami dan digunakan? (Skala 1-5)
2. **Fitur mana** yang paling berguna dan sering Anda gunakan dalam operasional sehari-hari?
3. **Apakah ada fitur** yang membingungkan atau sulit ditemukan?
4. **Apakah informasi di Dashboard** sudah cukup membantu untuk mengetahui kondisi bisnis hari ini?
5. **Apakah Kanban Board** (papan status pesanan) sudah sesuai dengan alur kerja pesanan Anda?
6. **Apakah Kalender Pengiriman** sudah membantu dalam memantau deadline?
7. **Apakah Laporan Keuangan** sudah memberikan informasi yang Anda butuhkan?
8. **Apakah ada fitur tambahan** yang Anda harapkan namun belum tersedia?
9. **Apakah pembagian hak akses** (Admin vs Staff) sudah sesuai dengan kebutuhan?
10. **Saran perbaikan** apa yang ingin Anda sampaikan untuk pengembangan selanjutnya?

---

## BAB VII — VALIDASI UI/UX TERHADAP DFD DAN KEBUTUHAN MITRA

### 7.1 Pemetaan DFD ke UI/UX

Rancangan UI/UX tidak menampilkan seluruh proses DFD sebagai menu yang berdiri sendiri. DFD menggambarkan aliran dan penyimpanan data sistem secara lengkap (7 proses utama, 13 tabel database), sedangkan UI/UX menampilkan fungsi yang perlu berinteraksi langsung dengan admin dalam satu antarmuka yang terintegrasi. Beberapa proses DFD direpresentasikan sebagai sub-fitur atau logika backend, bukan menu utama.

| Kebutuhan UI/UX | Sumber Data / Proses DFD Terkait | Implementasi pada Antarmuka High-Fidelity |
| :--- | :--- | :--- |
| **Dashboard / Beranda** | Proses 3.0, 4.0, 6.0; D3, D4, D6 | KPI Cards (Total Pesanan, Pending, Pemasukan, Pelanggan Baru), Tabel Pesanan Perlu Tindakan, Kitchen Widget, Inventory Alert, Quick Actions |
| **Manajemen Produk** | Proses 1.0 (Manajemen Katalog Produk); D1 (kategori, produk, varian_produk) | Halaman Produk: KPI ketersediaan, filter kategori, tabel produk, modal tambah/edit produk |
| **Manajemen Pesanan** | Proses 3.0 (Sub 3.1-3.5); D3 (pesanan, detail_pesanan) | Kanban Board 4 swimlane, modal tambah pesanan dengan customer lookup & MOQ validation, detail pesanan |
| **Kalender Pengiriman** | Proses 5.0 (Sub 5.1); tanggal kirim pada D3, D5 | Grid kalender bulanan, jadwal harian, rute kurir, highlight tanggal aktif |
| **Manajemen Pelanggan** | Proses 2.0 (Sub 2.1-2.4); D2 (pelanggan) | Daftar pelanggan (VIP/Reseller/Reguler), detail panel, WhatsApp button, riwayat pesanan, info kontak |
| **Laporan & Keuangan** | Proses 6.0 (Sub 6.1-6.5); D3, D4, D6 | Filter periode, cards ringkasan, bar chart tren penjualan, donut chart status pembayaran, export Excel/cetak |
| **Status Pesanan** | Proses 3.0 (Sub 3.4 Kelola Status Pesanan) | Kanban board swimlane (Menunggu → Proses → Siap Kirim → Selesai) |
| **Invoice & Struk** | Proses 3.0 (Sub 3.2 Generate Invoice), 4.0 (Sub 4.4 Generate Struk) | Auto-generate INV-YYYYMMDD-XXXX, POS Thermal Simulator 80mm dengan QRIS |
| **Pembayaran** | Proses 4.0 (Sub 4.1-4.3); D4 (pembayaran, tracking_log) | Status pembayaran pada pesanan, donut chart status (Lunas/DP/Hutang/Belum Bayar), tracking piutang |
| **Manajemen Pengguna** | Proses 7.0 (Sub 7.1-7.3); D7 (pengguna) | Halaman Pengaturan: Manajemen User, daftar pengguna, role, status, perbandingan hak akses |
| **Profil UMKM** | Parameter konfigurasi sistem | Halaman Pengaturan: Profil, info toko, logo, WhatsApp Business |
| **Customer (Pelanggan Akhir)** | Entitas pelanggan tetap ada pada aliran bisnis | **Tidak dibuatkan website customer** — pemesanan tetap via WhatsApp/offline |
| **Jadwal Produksi** | Sebelumnya ada pada kalender produksi (Sub 3.5) | **Tidak ditampilkan terpisah** — terintegrasi dalam kalender pengiriman |

### 7.2 Validasi Kebutuhan Mitra (14 Poin Wawancara)

Evaluasi dan komparasi antara kebutuhan operasional riil UMKM Dynasty Food & Beverage (berdasarkan hasil wawancara tanggal 20 September 2026) dengan implementasi pada desain High-Fidelity:

| No. | Kebutuhan Riil dari Hasil Wawancara | Komponen UI/UX yang Mengakomodasi | Status |
| :---: | :--- | :--- | :---: |
| 1 | Digitalisasi pencatatan pesanan (dari manual buku nota) | Kanban Board Pesanan + Modal Tambah Pesanan | **Terpenuhi (100%)** |
| 2 | Sentralisasi database pelanggan (Retail & Reseller) | Halaman Pelanggan (CRM) dengan klasifikasi VIP/Reseller/Reguler | **Terpenuhi (100%)** |
| 3 | Segmentasi pelanggan tanpa pembagian zona | Detail Panel Pelanggan dengan badge tipe | **Terpenuhi (100%)** |
| 4 | Pembuatan invoice/struk otomatis (INV-YYYYMMDD-XXXX) | Auto-generate invoice + POS Thermal Simulator 80mm | **Terpenuhi (100%)** |
| 5 | Pelacakan status tahapan pesanan | Kanban Board 4 swimlane (Menunggu → Proses → Siap Kirim → Selesai) | **Terpenuhi (100%)** |
| 6 | Integrasi kalender produksi harian dan jadwal kirim | Kalender Pengiriman (Grid Bulanan + Jadwal Harian + Rute Kurir) | **Terpenuhi (100%)** |
| 7 | Manajemen piutang fleksibel (Lunas / Hutang) | Donut Chart Status Pembayaran + Tracking piutang pada detail pelanggan | **Terpenuhi (100%)** |
| 8 | Kebijakan pengiriman GRATIS seluruh Malang Raya | Kalender Pengiriman (tanpa komponen ongkir/zona) | **Terpenuhi (100%)** |
| 9 | Laporan rekap penjualan berkala (harian & bulanan) | Halaman Laporan: Filter periode + Bar Chart tren penjualan + Export Excel | **Terpenuhi (100%)** |
| 10 | Buku pembantu daftar piutang pelanggan aktif | Halaman Pelanggan → Riwayat Pesanan + Dashboard → Pesanan Perlu Tindakan | **Terpenuhi (100%)** |
| 11 | Katalog digital varian produk (Kardus, Mika, Satuan) | Halaman Produk: Tabel dengan kolom Kemasan, MOQ, Harga + Filter Kategori | **Terpenuhi (100%)** |
| 12 | Keamanan akses multi-user (Admin vs Staff) | Pengaturan → Manajemen User & Akses (RBAC: Owner vs Staff/Kasir) | **Terpenuhi (100%)** |
| 13 | Fasilitas ekspor rekap laporan ke PDF / Excel | Halaman Laporan: Button Unduh Excel + Button Cetak Laporan | **Terpenuhi (100%)** |
| 14 | Indikator kapasitas armada obrok (150 pcs/trip) | Kalender Pengiriman → Jadwal harian dengan info rute/kapasitas | **Terpenuhi (100%)** |

### 7.3 Perubahan Kebutuhan Setelah Wawancara Lanjutan

| Rancangan DFD Sebelumnya | Keputusan UI/UX Terbaru (High-Fidelity) | Alasan |
| :--- | :--- | :--- |
| Katalog dapat diakses pelanggan | Tidak ada website customer | Customer tetap memesan melalui WhatsApp/offline sesuai kebiasaan. |
| Kalender produksi terpisah (Sub 3.5) | Kalender deadline pengiriman (terintegrasi jadwal harian) | Admin membutuhkan tanggal deadline pengiriman, bukan jadwal pembuatan secara terpisah. |
| Status pesanan: Pending → Proses → Kirim → Selesai/Batal | Status diperkaya: Menunggu Konfirmasi → Proses Dapur → Siap Kirim → Selesai | Lebih detail dan sesuai alur kerja bakery (ada tahap konfirmasi dan proses dapur). |
| Produk dapat memiliki foto | Jenis roti berupa tabel katalog (tanpa foto) | Gambar tidak diperlukan untuk operasional internal admin. |
| Pelaporan keuangan luas (6 sub-proses) | Fokus pada pemasukan, tren penjualan, dan status pembayaran | Kebutuhan UI utama adalah pemantauan pemasukan dan status pembayaran. |
| Dashboard sederhana (ringkasan saja) | Dashboard diperkaya (KPI Cards, Kitchen Widget, Inventory Alert, Quick Actions) | Memberikan gambaran komprehensif kondisi bisnis dalam satu pandangan. |
| Pelanggan hanya data profil | CRM mini (klasifikasi, WhatsApp integration, riwayat transaksi) | Mendukung relationship management dan re-order pelanggan. |

### 7.4 Kesimpulan Validasi

Rancangan UI/UX High-Fidelity tetap **konsisten dengan data inti pada DFD**, terutama:
- **D1** (Produk & Katalog) → Halaman Manajemen Produk
- **D2** (Data Pelanggan) → Halaman Manajemen Pelanggan (CRM)
- **D3** (Pesanan & Detail) → Kanban Board Pesanan
- **D4** (Pembayaran & Tracking) → Status Pembayaran, Donut Chart, Invoice
- **D5** (Pengiriman) → Kalender Pengiriman
- **D6** (Keuangan & Kas) → Halaman Laporan & Keuangan
- **D7** (Pengguna Sistem) → Halaman Pengaturan → Manajemen User

Namun, bentuk antarmuka **disederhanakan dan diperkaya** berdasarkan wawancara lanjutan:
- Penyederhanaan bukan menghapus struktur data DFD, melainkan menentukan **informasi mana yang ditampilkan** kepada admin agar sistem lebih sesuai dengan aktivitas pengguna.
- Penambahan fitur seperti Kitchen Widget, Inventory Alert, dan CRM mini bertujuan memberikan **nilai tambah** tanpa menambah kompleksitas navigasi.
- Seluruh **14 poin kebutuhan mitra** dari hasil wawancara **terpenuhi 100%** pada desain High-Fidelity.

---

## BAB VIII — KESIMPULAN

Perancangan UI/UX Dynasty Food & Beverage (Berkat Dinasti) menggunakan **Design Thinking Framework** untuk menerjemahkan kebutuhan bisnis dari DFD dan hasil wawancara menjadi antarmuka yang **sederhana, modern, dan informatif**. Pengguna utama adalah admin/pemilik dan staff operasional, sedangkan pelanggan tetap melakukan pemesanan melalui WhatsApp atau secara offline.

Melalui tahap **Empathize** dan **Define**, ditemukan bahwa masalah utama bukan kurangnya banyak fitur, melainkan **belum adanya tempat terpusat** yang mudah digunakan untuk mengelola informasi penting (pesanan, deadline, status, pelanggan, dan pemasukan). Analisis 5 Whys mengungkapkan *root cause* berupa ketiadaan sistem informasi internal yang modern.

Tahap **Ideate** menghasilkan solusi berupa **SISFOR - BERKAT DINASTI** dengan sitemap, user flow, dan wireflow yang berfokus pada aktivitas admin. Fitur utama meliputi Dashboard dengan KPI Cards, Kanban Board pesanan, Kalender Pengiriman, Manajemen Produk & Pelanggan, Laporan Keuangan, dan Manajemen User berbasis RBAC.

Tahap **Prototype** diwujudkan dalam:
- **Low-Fidelity (Wireframe)** — Validasi susunan informasi dan navigasi
- **High-Fidelity (Figma)** — 25 frame desain interaktif dengan design system yang konsisten (warm bakery theme, color palette, typography, component library)

Tahap **Testing** menyiapkan 7 skenario usability testing beserta form feedback dan pertanyaan kualitatif untuk validasi langsung dengan mitra. Hasil testing akan digunakan sebagai dasar iterasi desain pada tahap pengembangan selanjutnya.

**Validasi terhadap DFD** menunjukkan bahwa seluruh 7 proses utama dan 13 tabel database terakomodasi pada antarmuka, dengan penyesuaian bentuk tampilan sesuai kebutuhan nyata pengguna. Seluruh **14 poin kebutuhan mitra** dari hasil wawancara **terpenuhi 100%** pada desain High-Fidelity.

Dokumen ini diverifikasi dan disahkan berdasarkan instrumen wawancara lapangan pada tanggal 20 September 2026 oleh Kelompok 5, Program Studi S1 Teknik Informatika, Universitas Negeri Malang.
