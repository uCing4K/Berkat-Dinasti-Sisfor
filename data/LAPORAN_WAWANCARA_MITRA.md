# 📄 LAPORAN HASIL WAWANCARA ANALISIS KEBUTUHAN SISTEM
## Aplikasi Manajemen Pesanan Roti "ForHomie"

---

## IDENTITAS PROYEK

| Item | Keterangan |
|------|------------|
| **Nama Aplikasi** | ForHomie - Sistem Manajemen Order & Pelanggan |
| **Nama Kelompok** | Kelompok 4 |
| **Anggota Tim** | Sipa, Adit, Bia, Dzay |
| **Mata Kuliah** | Basis Data |
| **Tanggal Wawancara** | 28 Januari 2026 |
| **Tanggal Laporan** | 4 Februari 2026 |
| **Versi Dokumen** | 1.0 |

---

## 📌 RINGKASAN EKSEKUTIF

Laporan ini merangkum hasil wawancara dan analisis kebutuhan sistem untuk pengembangan aplikasi manajemen pesanan roti "ForHomie". Wawancara dilakukan dengan mitra usaha roti yang berlokasi di wilayah Malang Raya. Sistem ini dirancang untuk mendigitalisasi proses pencatatan pesanan, manajemen pelanggan, tracking pengiriman, dan pelaporan keuangan yang saat ini masih dilakukan secara manual.

**Tujuan Utama:**
- Digitalisasi pencatatan penjualan dan data pelanggan
- Otomatisasi pembuatan invoice/struk digital
- Tracking status pesanan secara real-time
- Manajemen piutang dan pembayaran
- Pelaporan penjualan yang akurat

---

## 1️⃣ PROFIL MITRA USAHA

### 1.1 Informasi Umum Usaha

| Aspek | Detail |
|-------|--------|
| **Nama Usaha** | Usaha Roti Pre-Order (PO) |
| **Jenis Usaha** | Produksi dan Penjualan Roti |
| **Lokasi Operasional** | Malang Raya (Kota Malang, Kabupaten Malang, Kota Batu) |
| **Model Bisnis** | Pre-Order (bukan ready stock) |
| **Target Pasar** | Retail (acara hajatan/syukuran) & Reseller |
| **Skala Usaha** | UMKM |

### 1.2 Produk dan Layanan

#### 📦 Katalog Produk

| No | Nama Produk | Kategori | Kemasan | Harga | Min. Order |
|----|-------------|----------|---------|-------|------------|
| 1 | Roti Hajatan Isi 6 Rasa | Roti Hajatan | Kardus Biasa | Rp 9.000 | 1 pcs |
| 2 | Roti Hajatan Isi 6 Rasa | Roti Hajatan | Mika | Rp 10.000 | 1 pcs |
| 3 | Roti Kopi | Roti Manis | Satuan | Rp 4.000 | 50 pcs |
| 4 | Roti Bijian Besar | Roti Manis | Satuan | Rp 4.000 | 20 pcs |
| 5 | Roti Bijian Kecil | Roti Manis | Satuan | Rp 3.000 | 50 pcs |

**Catatan Produk:**
- Roti Hajatan memiliki 6 varian rasa dalam satu paket
- Masa kedaluwarsa (shelf life): 7 hari
- Produksi berdasarkan pre-order, tidak ada stok ready
- Minimum order berbeda-beda tergantung jenis produk

#### 🚚 Layanan Pengiriman

| Zona | Wilayah Cakupan | Ongkos Kirim | Keterangan |
|------|-----------------|--------------|------------|
| Zona 1 | Kota Malang | **GRATIS** | - |
| Zona 2 | Kabupaten Malang | **GRATIS** | - |
| Zona 3 | Kota Batu | **GRATIS** | - |
| Zona 4 | Luar Malang Raya | Rp 15.000+ | Sesuai jarak |

**Kapasitas Pengiriman:**
- Alat transportasi: Sepeda obrok
- Kapasitas maksimal: 150 pcs roti besar per trip
- Jadwal pengiriman: Disesuaikan dengan deadline pesanan

#### 💳 Metode Pembayaran

| Metode | Keterangan |
|--------|------------|
| **Cash** | Pembayaran tunai saat pengiriman |
| **Transfer** | Transfer bank (bisa bayar setelah barang diantar) |
| **Sistem Kredit** | Pelanggan tetap bisa bayar belakangan |

---

## 2️⃣ ANALISIS KONDISI SAAT INI (AS-IS)

### 2.1 Proses Bisnis Saat Ini

#### 📝 Alur Pemesanan Konvensional

```
┌─────────────────┐
│  Pelanggan      │
│  Order via WA   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Pemilik        │
│  Catat di Buku  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Produksi       │
│  Sesuai PO      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Pengiriman     │
│  Manual Antar   │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│  Pembayaran     │
│  Cash/Transfer  │
└─────────────────┘
```

### 2.2 Sistem Pencatatan Saat Ini

| Aspek | Metode | Media |
|-------|--------|-------|
| **Penerimaan Order** | WhatsApp Chat | HP |
| **Pencatatan Pesanan** | Manual tulis tangan | Buku nota |
| **Data Pelanggan** | Tidak tersistem | Kontak WA |
| **Invoice** | Nota tulis tangan | Kertas |
| **Jadwal Produksi** | Ingatan/catatan lepas | Buku harian |
| **Jadwal Pengiriman** | Tidak terjadwal sistematis | Manual |
| **Tracking Pembayaran** | Catatan manual | Buku kas |
| **Laporan Penjualan** | Tidak ada rekap rutin | - |

### 2.3 Permasalahan Teridentifikasi

#### 🔴 Masalah Utama

| No | Masalah | Dampak | Tingkat Urgensi |
|----|---------|--------|-----------------|
| 1 | **Pencatatan manual di buku/nota** | Data tidak terorganisir, sulit dicari kembali, rawan hilang | ⚠️⚠️⚠️ TINGGI |
| 2 | **Order hanya via WhatsApp** | Tidak ada rekap otomatis, chat campur aduk, sulit tracking | ⚠️⚠️⚠️ TINGGI |
| 3 | **Tidak ada database pelanggan** | Sulit follow-up pelanggan tetap, tidak bisa analisis pola order | ⚠️⚠️ SEDANG |
| 4 | **Tidak ada sistem invoice profesional** | Tampak kurang profesional, sulit tracking piutang | ⚠️⚠️⚠️ TINGGI |
| 5 | **Pengiriman tidak terjadwal** | Tidak efisien, bisa bentrok jadwal, boros waktu | ⚠️⚠️ SEDANG |
| 6 | **Sulit tracking pembayaran** | Pelanggan yang belum bayar sering terlupa, piutang menumpuk | ⚠️⚠️⚠️ TINGGI |
| 7 | **Tidak ada laporan penjualan** | Tidak tahu omzet pasti, sulit evaluasi performa usaha | ⚠️⚠️ SEDANG |

#### 📊 Pain Points Detail

**1. Manajemen Pesanan**
- Order masuk via WhatsApp tidak tercatat sistematis
- Rawan salah catat atau lupa pesanan
- Sulit melacak status pesanan (pending, proses, selesai)
- Tidak ada notifikasi otomatis untuk deadline

**2. Manajemen Pelanggan**
- Data pelanggan tersebar di kontak WhatsApp
- Tidak ada histori pembelian pelanggan
- Tidak tahu pelanggan mana yang sering order (loyalitas)
- Alamat pengiriman sering harus ditanya ulang

**3. Manajemen Keuangan**
- Pelanggan yang belum bayar lunas sering terlupa
- Tidak ada sistem peringatan untuk piutang
- Rekap pendapatan harian/bulanan tidak akurat
- Sulit membedakan pelanggan retail vs reseller

**4. Operasional**
- Jadwal produksi tidak terorganisir
- Rute pengiriman tidak efisien
- Tidak ada tracking real-time untuk pelanggan
- Kapasitas produksi sulit dikontrol

---

## 3️⃣ ANALISIS KEBUTUHAN SISTEM (TO-BE)

### 3.1 Kebutuhan Fungsional

#### 🎯 Modul 1: Katalog Produk

| ID | Kebutuhan | Deskripsi | Prioritas |
|----|-----------|-----------|-----------|
| F1.1 | Katalog Online | Halaman publik yang menampilkan daftar produk dengan foto, deskripsi, dan harga | ⭐⭐⭐ Wajib |
| F1.2 | Varian Kemasan | Sistem harus bisa menampilkan harga berbeda untuk kemasan berbeda (kardus/mika) | ⭐⭐⭐ Wajib |
| F1.3 | Kategori Produk | Pengelompokan produk berdasarkan kategori (Roti Hajatan, Roti Manis) | ⭐⭐⭐ Wajib |
| F1.4 | Minimum Order | Informasi batas minimum pemesanan per produk | ⭐⭐⭐ Wajib |
| F1.5 | Upload Foto Produk | Admin bisa mengelola gambar produk | ⭐⭐ Penting |
| F1.6 | Status Ketersediaan | Tandai produk tersedia/tidak tersedia | ⭐⭐ Penting |

#### 👥 Modul 2: Manajemen Pelanggan

| ID | Kebutuhan | Deskripsi | Prioritas |
|----|-----------|-----------|-----------|
| F2.1 | Database Pelanggan | Sistem menyimpan data: nama, alamat lengkap, nomor WhatsApp | ⭐⭐⭐ Wajib |
| F2.2 | Klasifikasi Pelanggan | Membedakan pelanggan retail dan reseller | ⭐⭐⭐ Wajib |
| F2.3 | Zona Pengiriman | Mengelompokkan pelanggan berdasarkan zona/wilayah | ⭐⭐⭐ Wajib |
| F2.4 | Riwayat Pesanan | Melihat semua pesanan yang pernah dilakukan pelanggan | ⭐⭐⭐ Wajib |
| F2.5 | Status Pembayaran | Tracking pelanggan yang lunas atau masih punya hutang | ⭐⭐⭐ Wajib |
| F2.6 | Total Transaksi | Akumulasi total belanja pelanggan sepanjang waktu | ⭐⭐ Penting |

#### 📝 Modul 3: Manajemen Pesanan

| ID | Kebutuhan | Deskripsi | Prioritas |
|----|-----------|-----------|-----------|
| F3.1 | Input Pesanan Baru | Form untuk input pesanan dengan detail produk dan jumlah | ⭐⭐⭐ Wajib |
| F3.2 | Status Pesanan | Tracking status: Pending → Proses → Kirim → Selesai → Batal | ⭐⭐⭐ Wajib |
| F3.3 | Auto Generate Invoice | Nomor invoice otomatis dengan format INV-YYYYMMDD-XXXX | ⭐⭐⭐ Wajib |
| F3.4 | Kalender Produksi | Tampilan kalender untuk jadwal dan deadline pengiriman | ⭐⭐⭐ Wajib |
| F3.5 | Struk Digital | Invoice/struk dalam format digital yang bisa di-share | ⭐⭐⭐ Wajib |
| F3.6 | Detail Pesanan | Sistem mencatat detail: produk, varian, qty, harga, subtotal | ⭐⭐⭐ Wajib |
| F3.7 | Catatan Pesanan | Field untuk catatan khusus dari pelanggan atau admin | ⭐⭐ Penting |
| F3.8 | Tracking Customer | Halaman publik untuk pelanggan cek status pesanan sendiri | ⭐⭐⭐ Wajib |

#### 🚚 Modul 4: Distribusi & Pengiriman

| ID | Kebutuhan | Deskripsi | Prioritas |
|----|-----------|-----------|-----------|
| F4.1 | Jadwal Pengiriman | Input tanggal dan waktu pengiriman untuk setiap pesanan | ⭐⭐⭐ Wajib |
| F4.2 | Zona Wilayah | Mapping area pelanggan untuk pengiriman terstruktur | ⭐⭐⭐ Wajib |
| F4.3 | Ongkos Kirim | Sistem menghitung ongkir otomatis (gratis untuk Malang Raya & Batu) | ⭐⭐⭐ Wajib |
| F4.4 | Kapasitas Angkut | Indikator kapasitas maksimal 150pcs per trip | ⭐⭐ Penting |
| F4.5 | Tracking Status | Update status pengiriman real-time | ⭐⭐⭐ Wajib |
| F4.6 | Grouping Pengiriman | Mengelompokkan pesanan berdasarkan wilayah untuk efisiensi rute | ⭐ Opsional |

#### 💰 Modul 5: Laporan Keuangan

| ID | Kebutuhan | Deskripsi | Prioritas |
|----|-----------|-----------|-----------|
| F5.1 | Rekap Penjualan Harian | Laporan total penjualan per hari | ⭐⭐⭐ Wajib |
| F5.2 | Rekap Penjualan Bulanan | Laporan total penjualan per bulan | ⭐⭐⭐ Wajib |
| F5.3 | Daftar Piutang | List pelanggan yang masih punya hutang | ⭐⭐⭐ Wajib |
| F5.4 | Metode Pembayaran | Filter laporan berdasarkan cash atau transfer | ⭐⭐⭐ Wajib |
| F5.5 | Rekap Pengeluaran | Pencatatan biaya operasional | ⭐⭐ Penting |
| F5.6 | Rekap Pendapatan | Perhitungan profit/margin | ⭐⭐ Penting |
| F5.7 | Export Laporan | Export ke PDF atau Excel | ⭐ Opsional |

#### 🔐 Modul 6: Manajemen Pengguna

| ID | Kebutuhan | Deskripsi | Prioritas |
|----|-----------|-----------|-----------|
| F6.1 | Login System | Autentikasi untuk admin | ⭐⭐⭐ Wajib |
| F6.2 | Role Management | Pembedaan hak akses (admin/staff) | ⭐⭐ Penting |
| F6.3 | Profil Pengguna | Manajemen data pengguna sistem | ⭐⭐ Penting |

### 3.2 Kebutuhan Non-Fungsional

#### 📱 Performance & Usability

| ID | Kebutuhan | Deskripsi | Standar |
|----|-----------|-----------|---------|
| NF1.1 | Responsive Design | Aplikasi harus bisa diakses optimal di mobile dan desktop | Mobile-first |
| NF1.2 | Progressive Web App (PWA) | Aplikasi bisa di-install di smartphone seperti aplikasi native | PWA Standard |
| NF1.3 | Loading Time | Halaman harus loading maksimal 3 detik | < 3 detik |
| NF1.4 | User Interface | Desain sederhana, mudah dipahami (user-friendly) | Bootstrap/Tailwind |
| NF1.5 | Offline Capability | Fitur dasar bisa diakses saat offline (PWA) | Service Worker |

#### 🔒 Security

| ID | Kebutuhan | Deskripsi | Standar |
|----|-----------|-----------|---------|
| NF2.1 | Authentication | Login dengan username dan password terenkripsi | Password Hash |
| NF2.2 | Authorization | Hak akses berdasarkan role | Role-based |
| NF2.3 | SQL Injection Prevention | Menggunakan Prepared Statement | PDO/MySQLi |
| NF2.4 | Session Management | Session timeout setelah idle | 30 menit |
| NF2.5 | Data Backup | Backup database rutin | Harian |

#### ⚡ Scalability & Reliability

| ID | Kebutuhan | Deskripsi | Standar |
|----|-----------|-----------|---------|
| NF3.1 | Database Normalization | Database ternormalisasi hingga 3NF | 3NF |
| NF3.2 | Concurrent Users | Sistem bisa handle minimal 10 user bersamaan | 10+ users |
| NF3.3 | Data Integrity | Referential integrity dengan foreign key | MySQL Constraints |
| NF3.4 | Error Handling | Pesan error yang jelas dan user-friendly | Try-catch |

### 3.3 Alur Proses Bisnis Baru (TO-BE)

#### 📊 Alur Pemesanan dengan Sistem

```
┌─────────────────────────────────────────────────────────────┐
│                         PELANGGAN                            │
│                    (Melalui WhatsApp)                        │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                      ADMIN/PEMILIK                           │
│                                                              │
│  1. Input pesanan ke sistem                                 │
│  2. Sistem generate invoice otomatis (INV-YYYYMMDD-XXXX)   │
│  3. Sistem simpan data pelanggan & detail order             │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                    JADWAL PRODUKSI                           │
│                                                              │
│  • Tampil di kalender otomatis                              │
│  • Status: PENDING → PROSES                                 │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                       PRODUKSI                               │
│                                                              │
│  • Admin update status: PROSES                              │
│  • Sistem tracking log perubahan status                     │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                      PENGIRIMAN                              │
│                                                              │
│  • Admin update status: KIRIM                               │
│  • Input jadwal & zona pengiriman                           │
│  • Pelanggan bisa tracking via halaman publik              │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                      PEMBAYARAN                              │
│                                                              │
│  • Admin input pembayaran (Cash/Transfer)                   │
│  • Sistem update status bayar: BELUM BAYAR/DP/LUNAS        │
│  • Sistem hitung otomatis piutang                           │
└──────────────────────────┬──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                       SELESAI                                │
│                                                              │
│  • Status pesanan: SELESAI                                  │
│  • Data masuk ke laporan penjualan                          │
│  • Struk digital bisa di-share ke pelanggan                │
└─────────────────────────────────────────────────────────────┘
```

---

## 4️⃣ DESAIN SISTEM

### 4.1 Teknologi yang Digunakan

| Komponen | Teknologi | Versi | Justifikasi |
|----------|-----------|-------|-------------|
| **Backend** | PHP | 8.x | • Stabil dan mature<br>• Banyak dokumentasi<br>• Familiar bagi developer<br>• Kompatibel dengan hosting umum |
| **Frontend** | HTML5, CSS3, JavaScript | Latest | • Standard web development<br>• Support PWA<br>• Responsive design |
| **CSS Framework** | Bootstrap / Tailwind CSS | 5.x / 3.x | • Komponen siap pakai<br>• Responsive grid system<br>• Dokumentasi lengkap |
| **Database** | MySQL | 8.0 | • Relational database<br>• Support foreign key<br>• Performa stabil<br>• Banyak tools management |
| **PWA** | Service Worker + Manifest | - | • Installable app<br>• Offline capability<br>• Push notification support |
| **Template Engine** | Blade (Laravel) / Native PHP | - | • Clean code structure<br>• Easy maintenance |
| **Server** | Self-hosted | - | • Sudah tersedia infrastruktur<br>• Kontrol penuh |

### 4.2 Arsitektur Aplikasi

```
┌────────────────────────────────────────────────────────────────┐
│                         CLIENT LAYER                            │
├────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────┐    ┌──────────────┐    ┌─────────────────┐  │
│  │   Mobile     │    │   Desktop    │    │  Customer Page  │  │
│  │   (PWA)      │    │   Browser    │    │  (Public Track) │  │
│  └──────┬───────┘    └──────┬───────┘    └────────┬────────┘  │
│         │                   │                     │            │
│         └───────────────────┼─────────────────────┘            │
│                             │                                  │
└─────────────────────────────┼──────────────────────────────────┘
                              │
                              │ HTTPS
                              │
┌─────────────────────────────▼──────────────────────────────────┐
│                      APPLICATION LAYER                          │
├────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌────────────────────────────────────────────────────────┐    │
│  │                   PHP APPLICATION                       │    │
│  ├────────────────────────────────────────────────────────┤    │
│  │                                                         │    │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │    │
│  │  │ Controllers  │  │   Models     │  │   Helpers    │ │    │
│  │  │  (Logic)     │  │  (Database)  │  │  (Utilities) │ │    │
│  │  └──────────────┘  └──────────────┘  └──────────────┘ │    │
│  │                                                         │    │
│  │  ┌─────────────────────────────────────────────────┐   │    │
│  │  │              Views (Templates)                   │   │    │
│  │  │  • Dashboard Admin                               │   │    │
│  │  │  • Manajemen Produk                              │   │    │
│  │  │  • Manajemen Pesanan                             │   │    │
│  │  │  • Laporan                                       │   │    │
│  │  │  • Tracking Customer (Public)                    │   │    │
│  │  └─────────────────────────────────────────────────┘   │    │
│  └───────────────────────────┬────────────────────────────┘    │
│                              │                                  │
└──────────────────────────────┼──────────────────────────────────┘
                               │
┌──────────────────────────────▼──────────────────────────────────┐
│                        DATABASE LAYER                            │
├────────────────────────────────────────────────────────────────┤
│                                                                 │
│                    ┌────────────────────┐                       │
│                    │   MySQL Database   │                       │
│                    │   (forhomie_db)    │                       │
│                    │                    │                       │
│                    │  ┌──────────────┐  │                       │
│                    │  │  12 Tables   │  │                       │
│                    │  │  Relational  │  │                       │
│                    │  │  Normalized  │  │                       │
│                    │  └──────────────┘  │                       │
│                    └────────────────────┘                       │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### 4.3 Entity Relationship Diagram (ERD)

#### 📊 Entitas dan Relasi Utama

**Entitas:**
1. **kategori** - Kategori produk
2. **produk** - Data produk
3. **varian_produk** - Varian kemasan & harga
4. **zona** - Zona pengiriman
5. **pelanggan** - Data pelanggan
6. **pesanan** - Header pesanan
7. **detail_pesanan** - Detail item pesanan
8. **pembayaran** - Transaksi pembayaran
9. **pengiriman** - Data pengiriman
10. **tracking_log** - Log tracking status
11. **pengguna** - User admin/staff
12. **pengeluaran** - Biaya operasional

**Relasi:**

| Tabel Parent | Kardinalitas | Tabel Child | Keterangan |
|--------------|--------------|-------------|------------|
| kategori | 1 : N | produk | 1 kategori punya banyak produk |
| produk | 1 : N | varian_produk | 1 produk punya banyak varian |
| zona | 1 : N | pelanggan | 1 zona punya banyak pelanggan |
| pelanggan | 1 : N | pesanan | 1 pelanggan bisa banyak pesanan |
| pesanan | 1 : N | detail_pesanan | 1 pesanan punya banyak item |
| varian_produk | 1 : N | detail_pesanan | 1 varian bisa di banyak pesanan |
| pesanan | 1 : N | pembayaran | 1 pesanan bisa cicil (banyak pembayaran) |
| pesanan | 1 : 1 | pengiriman | 1 pesanan punya 1 pengiriman |
| pesanan | 1 : N | tracking_log | 1 pesanan punya banyak log |

### 4.4 Struktur Database

#### 📋 Daftar Tabel

**TABEL 1: `kategori`**
- Menyimpan kategori produk (Roti Hajatan, Roti Manis, dll)

| Field | Type | Key | Keterangan |
|-------|------|-----|------------|
| id_kategori | INT | PK | Auto increment |
| nama_kategori | VARCHAR(100) | | Nama kategori |
| deskripsi | TEXT | | Deskripsi kategori |
| created_at | TIMESTAMP | | Waktu dibuat |
| updated_at | TIMESTAMP | | Waktu update |

---

**TABEL 2: `produk`**
- Menyimpan data produk utama

| Field | Type | Key | Keterangan |
|-------|------|-----|------------|
| id_produk | INT | PK | Auto increment |
| id_kategori | INT | FK | Relasi ke kategori |
| nama_produk | VARCHAR(150) | | Nama produk |
| deskripsi | TEXT | | Deskripsi produk |
| gambar | VARCHAR(255) | | Path file gambar |
| shelf_life | INT | | Masa kedaluwarsa (hari) |
| status | ENUM | | tersedia/tidak_tersedia |
| created_at | TIMESTAMP | | Waktu dibuat |
| updated_at | TIMESTAMP | | Waktu update |

---

**TABEL 3: `varian_produk`**
- Menyimpan varian kemasan dan harga

| Field | Type | Key | Keterangan |
|-------|------|-----|------------|
| id_varian | INT | PK | Auto increment |
| id_produk | INT | FK | Relasi ke produk |
| nama_varian | VARCHAR(100) | | Kardus/Mika/Satuan |
| harga | DECIMAL(10,2) | | Harga per unit |
| min_order | INT | | Minimum order |
| stok | INT | | Stok saat ini |
| created_at | TIMESTAMP | | Waktu dibuat |
| updated_at | TIMESTAMP | | Waktu update |

---

**TABEL 4: `zona`**
- Menyimpan zona pengiriman

| Field | Type | Key | Keterangan |
|-------|------|-----|------------|
| id_zona | INT | PK | Auto increment |
| nama_zona | VARCHAR(100) | | Nama zona |
| deskripsi | TEXT | | Deskripsi wilayah |
| ongkir | DECIMAL(10,2) | | Ongkos kirim |
| created_at | TIMESTAMP | | Waktu dibuat |

---

**TABEL 5: `pelanggan`**
- Menyimpan data pelanggan

| Field | Type | Key | Keterangan |
|-------|------|-----|------------|
| id_pelanggan | INT | PK | Auto increment |
| id_zona | INT | FK | Relasi ke zona |
| nama | VARCHAR(150) | | Nama pelanggan |
| alamat | TEXT | | Alamat lengkap |
| no_wa | VARCHAR(20) | | Nomor WhatsApp |
| tipe | ENUM | | retail/reseller |
| total_transaksi | DECIMAL(15,2) | | Akumulasi belanja |
| total_hutang | DECIMAL(15,2) | | Piutang saat ini |
| created_at | TIMESTAMP | | Waktu dibuat |
| updated_at | TIMESTAMP | | Waktu update |

---

**TABEL 6: `pesanan`**
- Menyimpan header pesanan

| Field | Type | Key | Keterangan |
|-------|------|-----|------------|
| id_pesanan | INT | PK | Auto increment |
| id_pelanggan | INT | FK | Relasi ke pelanggan |
| no_invoice | VARCHAR(50) | UNIQUE | INV-YYYYMMDD-XXXX |
| tgl_pesan | DATE | | Tanggal order |
| tgl_kirim | DATE | | Jadwal pengiriman |
| waktu_kirim | TIME | | Jam pengiriman |
| total_harga | DECIMAL(15,2) | | Total item |
| ongkir | DECIMAL(10,2) | | Ongkos kirim |
| grand_total | DECIMAL(15,2) | | Total + ongkir |
| status | ENUM | | pending/proses/kirim/selesai/batal |
| status_bayar | ENUM | | belum_bayar/dp/lunas |
| metode_bayar | ENUM | | cash/transfer |
| catatan | TEXT | | Catatan khusus |
| created_at | TIMESTAMP | | Waktu dibuat |
| updated_at | TIMESTAMP | | Waktu update |

**Trigger:** Auto-generate nomor invoice dengan format `INV-YYYYMMDD-XXXX`

---

**TABEL 7: `detail_pesanan`**
- Menyimpan detail item dalam pesanan (Junction Table)

| Field | Type | Key | Keterangan |
|-------|------|-----|------------|
| id_detail | INT | PK | Auto increment |
| id_pesanan | INT | FK | Relasi ke pesanan |
| id_varian | INT | FK | Relasi ke varian_produk |
| qty | INT | | Jumlah pesan |
| harga_satuan | DECIMAL(10,2) | | Harga saat order |
| subtotal | DECIMAL(15,2) | | qty × harga_satuan |
| created_at | TIMESTAMP | | Waktu dibuat |

**Trigger:** 
- Auto calculate subtotal
- Auto update total_harga di tabel pesanan

---

**TABEL 8: `pembayaran`**
- Menyimpan transaksi pembayaran

| Field | Type | Key | Keterangan |
|-------|------|-----|------------|
| id_pembayaran | INT | PK | Auto increment |
| id_pesanan | INT | FK | Relasi ke pesanan |
| jumlah | DECIMAL(15,2) | | Jumlah dibayar |
| tgl_bayar | DATETIME | | Waktu pembayaran |
| metode | ENUM | | cash/transfer |
| bukti | VARCHAR(255) | | File bukti transfer |
| keterangan | TEXT | | Catatan pembayaran |
| created_at | TIMESTAMP | | Waktu dibuat |

**Trigger:** Auto-update status_bayar di tabel pesanan (belum_bayar/dp/lunas)

---

**TABEL 9: `pengiriman`**
- Menyimpan data pengiriman

| Field | Type | Key | Keterangan |
|-------|------|-----|------------|
| id_pengiriman | INT | PK | Auto increment |
| id_pesanan | INT | FK | Relasi ke pesanan |
| tgl_kirim | DATETIME | | Waktu kirim |
| status | ENUM | | dalam_perjalanan/terkirim |
| driver | VARCHAR(100) | | Nama driver |
| catatan | TEXT | | Catatan pengiriman |
| updated_at | TIMESTAMP | | Waktu update |

---

**TABEL 10: `tracking_log`**
- Menyimpan riwayat perubahan status pesanan

| Field | Type | Key | Keterangan |
|-------|------|-----|------------|
| id_log | INT | PK | Auto increment |
| id_pesanan | INT | FK | Relasi ke pesanan |
| status | VARCHAR(50) | | Status baru |
| keterangan | TEXT | | Deskripsi perubahan |
| created_at | TIMESTAMP | | Waktu log |

**Trigger:** Otomatis insert saat status pesanan berubah

---

**TABEL 11: `pengguna`**
- Menyimpan data user admin/staff

| Field | Type | Key | Keterangan |
|-------|------|-----|------------|
| id_user | INT | PK | Auto increment |
| username | VARCHAR(50) | UNIQUE | Username login |
| password | VARCHAR(255) | | Password (hashed) |
| nama_lengkap | VARCHAR(150) | | Nama lengkap |
| role | ENUM | | admin/staff |
| created_at | TIMESTAMP | | Waktu dibuat |
| updated_at | TIMESTAMP | | Waktu update |

---

**TABEL 12: `pengeluaran`**
- Menyimpan biaya operasional

| Field | Type | Key | Keterangan |
|-------|------|-----|------------|
| id_pengeluaran | INT | PK | Auto increment |
| tanggal | DATE | | Tanggal pengeluaran |
| kategori | VARCHAR(100) | | Kategori biaya |
| deskripsi | TEXT | | Deskripsi detail |
| jumlah | DECIMAL(15,2) | | Nominal pengeluaran |
| created_at | TIMESTAMP | | Waktu dibuat |

---

### 4.5 Fitur Unggulan Sistem

#### ⚡ Fitur Otomasi

| No | Fitur | Deskripsi | Manfaat |
|----|-------|-----------|---------|
| 1 | **Auto-Generate Invoice** | Nomor invoice otomatis dengan format INV-YYYYMMDD-XXXX | Terstandar, tidak bentrok |
| 2 | **Auto-Calculate Total** | Sistem hitung subtotal, total, dan grand total otomatis | Cepat, akurat, tidak salah hitung |
| 3 | **Auto-Update Status Bayar** | Status pembayaran update otomatis saat ada pembayaran | Real-time, tidak lupa update |
| 4 | **Auto-Tracking Log** | Setiap perubahan status tercatat otomatis | Audit trail lengkap |
| 5 | **Auto-Calculate Piutang** | Total hutang pelanggan terhitung otomatis | Tidak ada piutang terlupa |

#### 📱 Progressive Web App (PWA)

| Fitur | Keterangan |
|-------|------------|
| **Installable** | Bisa di-install di smartphone seperti aplikasi native tanpa lewat Play Store |
| **Offline Mode** | Fitur dasar tetap bisa diakses meski tidak ada internet (Service Worker) |
| **Push Notification** | Bisa kirim notifikasi ke HP (opsional untuk fase 2) |
| **Responsive** | Tampilan otomatis menyesuaikan ukuran layar (HP, tablet, desktop) |
| **Fast Loading** | Loading lebih cepat dengan caching |

---

## 5️⃣ IMPACT & BENEFIT ANALYSIS

### 5.1 Manfaat untuk Mitra Usaha

#### 💼 Manfaat Bisnis

| No | Manfaat | Deskripsi | Impact |
|----|---------|-----------|--------|
| 1 | **Efisiensi Operasional** | Proses input dan tracking pesanan lebih cepat | ⬆️ Produktivitas +40% |
| 2 | **Profesionalitas** | Invoice digital membuat usaha tampak lebih profesional | ⬆️ Trust pelanggan |
| 3 | **Akurasi Data** | Tidak ada lagi salah catat atau data hilang | ⬇️ Human error 90% |
| 4 | **Kontrol Piutang** | Tracking hutang pelanggan lebih mudah | ⬆️ Cash flow membaik |
| 5 | **Analisis Penjualan** | Bisa analisis produk terlaris, pelanggan loyal, dll | ⬆️ Strategi bisnis lebih baik |
| 6 | **Skalabilitas** | Mudah handle lebih banyak pesanan tanpa chaos | ⬆️ Kapasitas bisnis |

#### 📊 Perbandingan Sebelum vs Sesudah

| Aspek | Sebelum (Manual) | Sesudah (Sistem) | Improvement |
|-------|------------------|------------------|-------------|
| **Input Pesanan** | 5-10 menit tulis tangan | 2-3 menit input sistem | ⬆️ 60% lebih cepat |
| **Cari Data Pesanan** | 5-15 menit cari di buku | 10 detik search | ⬆️ 90+ kali lebih cepat |
| **Buat Invoice** | Tulis manual 3-5 menit | Auto-generate 5 detik | ⬆️ 95% lebih cepat |
| **Tracking Status** | Tanya via WA | Cek sendiri di web | ⬇️ Gangguan berkurang |
| **Laporan Bulanan** | Hitung manual 2-3 jam | Klik tombol 1 menit | ⬆️ 99% lebih cepat |
| **Data Pelanggan** | Tidak tersistem | Database terstruktur | ⬆️ Manajemen lebih baik |

### 5.2 Manfaat untuk Pelanggan

| No | Manfaat | Deskripsi |
|----|---------|-----------|
| 1 | **Tracking Real-Time** | Pelanggan bisa cek status pesanan sendiri kapan saja |
| 2 | **Invoice Digital** | Struk/invoice dalam bentuk digital yang rapi dan bisa disimpan |
| 3 | **Katalog Online** | Bisa lihat menu dan harga kapan saja tanpa tanya |
| 4 | **Transparansi** | Jelas status pesanan: pending, proses, kirim, selesai |
| 5 | **Histori Pesanan** | Bisa lihat riwayat pesanan sebelumnya |

---

## 6️⃣ TIMELINE & RESOURCE PLANNING

### 6.1 Jadwal Pengembangan (8 Minggu)

| Fase | Minggu | Aktivitas | Deliverable | PIC |
|------|--------|-----------|-------------|-----|
| **Fase 1<br>Foundation** | 1-2 | • Setup environment<br>• Buat database & tabel<br>• Buat model CRUD<br>• Testing relasi | Database lengkap & berfungsi | Tim Backend |
| **Fase 2<br>Core Features** | 3-4 | • Sistem login<br>• CRUD Produk & Pelanggan<br>• Input pesanan<br>• Manajemen pembayaran | Admin panel dasar | Full Team |
| **Fase 3<br>Advanced** | 5-6 | • Tracking status<br>• Kalender produksi<br>• Sistem pengiriman<br>• Laporan keuangan<br>• Manajemen piutang | Fitur lengkap | Full Team |
| **Fase 4<br>Customer UI** | 7 | • Katalog online<br>• Halaman tracking customer<br>• Struk digital | Interface customer | Tim Frontend |
| **Fase 5<br>Finishing** | 8 | • Setup PWA<br>• Responsive testing<br>• Bug fixing<br>• Deployment | Aplikasi live | Full Team |

### 6.2 Resource Requirement

**Tim Pengembangan:**
- **Sipa** - Backend Developer & Database Designer
- **Adit** - Frontend Developer & UI/UX
- **Bia** - Full Stack Developer & Tester
- **Dzay** - Project Manager & Documentation

**Tools & Software:**
- XAMPP/Laragon (Local Server)
- Visual Studio Code (Code Editor)
- MySQL Workbench (Database Management)
- Git/GitHub (Version Control)
- Figma (UI Design - opsional)

**Hardware:**
- Laptop untuk development (4 unit)
- Hosting server (self-hosted sudah tersedia)

---

## 7️⃣ RISK ANALYSIS & MITIGATION

### 7.1 Identifikasi Risiko

| No | Risiko | Probabilitas | Impact | Tingkat Risiko | Mitigasi |
|----|--------|--------------|--------|----------------|----------|
| 1 | **Mitra tidak terbiasa dengan sistem digital** | Tinggi | Sedang | ⚠️ **MEDIUM** | Training intensif, UI harus user-friendly |
| 2 | **Data hilang karena tidak ada backup** | Rendah | Tinggi | ⚠️ **MEDIUM** | Implementasi auto-backup harian |
| 3 | **Server down di jam sibuk** | Sedang | Tinggi | ⚠️ **HIGH** | Monitoring server, error handling yang baik |
| 4 | **Timeline development terlambat** | Sedang | Sedang | ⚠️ **MEDIUM** | Prioritas fitur wajib dulu, opsional belakangan |
| 5 | **Bug di fitur perhitungan uang** | Sedang | Tinggi | ⚠️ **HIGH** | Testing ekstra untuk modul pembayaran |
| 6 | **Penolakan penggunaan sistem** | Rendah | Tinggi | ⚠️ **MEDIUM** | Proof of value, tunjukkan manfaat konkret |

### 7.2 Strategi Mitigasi

#### 📚 User Adoption
- Buat user manual sederhana dengan screenshot
- Video tutorial singkat untuk fitur utama
- Pendampingan intensif minggu pertama
- Quick support via WhatsApp

#### 🔒 Data Security
- Backup database otomatis setiap hari pukul 02.00
- Backup manual setiap minggu ke eksternal storage
- Password hashing dengan bcrypt
- Session management yang aman

#### ⚡ Performance
- Pagination untuk list data besar (max 50 item per page)
- Caching untuk query yang sering diakses
- Optimasi gambar produk (resize, compress)
- Index pada kolom yang sering di-query

---

## 8️⃣ KESIMPULAN & REKOMENDASI

### 8.1 Kesimpulan

Berdasarkan hasil wawancara dan analisis kebutuhan, dapat disimpulkan bahwa:

1. **Urgensi Sistem:**
   - Mitra usaha roti mengalami kendala signifikan dalam manajemen pesanan manual
   - Diperlukan sistem digital untuk meningkatkan efisiensi dan profesionalitas
   - Prioritas utama: tracking pesanan, manajemen piutang, dan laporan penjualan

2. **Kelayakan Implementasi:**
   - ✅ Mitra bersedia mengadopsi sistem digital
   - ✅ Infrastruktur hosting sudah tersedia
   - ✅ Tim developer memiliki skill yang sesuai
   - ✅ Timeline 8 minggu realistis untuk MVP (Minimum Viable Product)

3. **Value Proposition:**
   - Efisiensi operasional meningkat signifikan (40-60%)
   - Akurasi data meningkat (90% pengurangan human error)
   - Profesionalitas usaha meningkat (invoice digital)
   - Kontrol keuangan lebih baik (tracking piutang otomatis)

### 8.2 Rekomendasi

#### 🎯 Prioritas Pengembangan

**Must Have (Fase 1-3):**
- ✅ Database relasional lengkap
- ✅ Sistem login & autentikasi
- ✅ CRUD Produk, Pelanggan, Pesanan
- ✅ Auto-generate invoice
- ✅ Tracking status pesanan
- ✅ Manajemen pembayaran & piutang
- ✅ Laporan penjualan dasar

**Should Have (Fase 4-5):**
- ✅ Katalog online untuk customer
- ✅ Halaman tracking customer (public)
- ✅ PWA implementation
- ✅ Responsive design

**Nice to Have (Future Enhancement):**
- 📌 Rute optimasi pengiriman
- 📌 Notifikasi push
- 📌 Export laporan ke PDF/Excel
- 📌 Integrasi WhatsApp API untuk notifikasi otomatis
- 📌 Dashboard analytics yang lebih advanced

#### 💡 Saran Implementasi

1. **Pendekatan Incremental:**
   - Develop MVP dengan fitur esensial dulu
   - Deploy dan testing dengan data riil
   - Gathering feedback dari mitra
   - Iterasi perbaikan dan penambahan fitur

2. **User Training:**
   - Buat dokumentasi lengkap dengan gambar
   - Lakukan training tatap muka dengan mitra
   - Sediakan support channel (WhatsApp/email)
   - Pantau usage di minggu-minggu awal

3. **Quality Assurance:**
   - Testing menyeluruh sebelum deployment
   - Focus testing pada modul keuangan (critical)
   - User Acceptance Testing (UAT) dengan mitra
   - Bug fixing prioritas sebelum go-live

4. **Sustainability:**
   - Dokumentasi kode yang baik
   - Version control dengan Git
   - Regular backup dan maintenance
   - Monitoring performa sistem

---

## 9️⃣ LAMPIRAN

### Lampiran A: Data Produk Lengkap

| ID | Kategori | Nama Produk | Varian | Harga | Min Order | Shelf Life |
|----|----------|-------------|--------|-------|-----------|------------|
| 1 | Roti Hajatan | Roti Hajatan Isi 6 Rasa | Kardus Biasa | Rp 9.000 | 1 pcs | 7 hari |
| 2 | Roti Hajatan | Roti Hajatan Isi 6 Rasa | Mika | Rp 10.000 | 1 pcs | 7 hari |
| 3 | Roti Manis | Roti Kopi | Satuan | Rp 4.000 | 50 pcs | 7 hari |
| 4 | Roti Manis | Roti Bijian Besar | Satuan | Rp 4.000 | 20 pcs | 7 hari |
| 5 | Roti Manis | Roti Bijian Kecil | Satuan | Rp 3.000 | 50 pcs | 7 hari |

### Lampiran B: Zona Pengiriman

| ID | Zona | Wilayah | Ongkir | Keterangan |
|----|------|---------|--------|------------|
| 1 | Zona 1 | Kota Malang | Rp 0 | Gratis ongkir |
| 2 | Zona 2 | Kabupaten Malang | Rp 0 | Gratis ongkir |
| 3 | Zona 3 | Kota Batu | Rp 0 | Gratis ongkir |
| 4 | Zona 4 | Luar Malang Raya | Rp 15.000+ | Sesuai jarak |

### Lampiran C: Alur Status Pesanan

```
PENDING (Pesanan Baru Masuk)
   ↓
   ↓ Admin konfirmasi & jadwalkan produksi
   ↓
PROSES (Sedang Diproduksi)
   ↓
   ↓ Produksi selesai, siap kirim
   ↓
KIRIM (Dalam Pengiriman)
   ↓
   ↓ Barang sampai ke customer
   ↓
SELESAI (Transaksi Selesai)

   ┌─────────┐
   │  BATAL  │ ← Bisa dibatalkan kapan saja sebelum KIRIM
   └─────────┘
```

### Lampiran D: Format Invoice

```
═══════════════════════════════════════
         INVOICE DIGITAL
       Usaha Roti Pre-Order
═══════════════════════════════════════

No. Invoice   : INV-20260204-0001
Tanggal       : 4 Februari 2026
Pelanggan     : Ibu Siti
Alamat        : Jl. Veteran No. 123, Malang
No. WhatsApp  : 0812-3456-7890

───────────────────────────────────────
Item Pesanan:
───────────────────────────────────────

1. Roti Hajatan (Kardus)
   10 pcs × Rp 9.000        = Rp  90.000

2. Roti Kopi
   50 pcs × Rp 4.000        = Rp 200.000

───────────────────────────────────────
Subtotal                    : Rp 290.000
Ongkos Kirim (Zona 1)       : Rp       0
───────────────────────────────────────
TOTAL TAGIHAN               : Rp 290.000
───────────────────────────────────────

Jadwal Kirim  : 5 Februari 2026, 08:00
Status        : PENDING
Pembayaran    : BELUM BAYAR

───────────────────────────────────────
Catatan:
- Pembayaran bisa cash/transfer
- Terima kasih atas kepercayaan Anda

Tracking: forhomie.com/track/INV202602040001
═══════════════════════════════════════
```

### Lampiran E: User Roles & Permissions

| Fitur | Admin | Staff | Customer (Public) |
|-------|-------|-------|-------------------|
| Login Dashboard | ✅ | ✅ | ❌ |
| CRUD Produk | ✅ | ❌ | ❌ |
| CRUD Pelanggan | ✅ | ✅ | ❌ |
| Input Pesanan | ✅ | ✅ | ❌ |
| Update Status Pesanan | ✅ | ✅ | ❌ |
| Input Pembayaran | ✅ | ✅ | ❌ |
| Lihat Laporan | ✅ | ❌ | ❌ |
| Manajemen User | ✅ | ❌ | ❌ |
| Lihat Katalog | ✅ | ✅ | ✅ |
| Tracking Pesanan | ✅ | ✅ | ✅ (miliknya saja) |

---

## 📞 KONTAK TIM PENGEMBANG

**Kelompok 4 - Basis Data**

| Nama | Role | Kontak |
|------|------|--------|
| Sipa | Backend Developer | - |
| Adit | Frontend Developer | - |
| Bia | Full Stack Developer | - |
| Dzay | Project Manager | - |

**Repository Project:** [GitHub/ForHomie] (akan diupdate)

---

## 📝 PERSETUJUAN DOKUMEN

| Pihak | Nama | Jabatan | Tanda Tangan | Tanggal |
|-------|------|---------|--------------|---------|
| **Mitra Usaha** | _____________ | Pemilik Usaha | _____________ | ________ |
| **Tim Developer** | Dzay | Project Manager | _____________ | 4 Feb 2026 |

---

## 📚 REFERENSI

1. Connolly, T., & Begg, C. (2014). *Database Systems: A Practical Approach to Design, Implementation, and Management*. Pearson Education.

2. Hoffer, J. A., Ramesh, V., & Topi, H. (2016). *Modern Database Management*. Pearson.

3. Elmasri, R., & Navathe, S. B. (2015). *Fundamentals of Database Systems*. Pearson.

4. Laravel Documentation. (2026). Retrieved from https://laravel.com/docs

5. MySQL Documentation. (2026). Retrieved from https://dev.mysql.com/doc/

6. Progressive Web Apps. (2026). Retrieved from https://web.dev/progressive-web-apps/

---

**DOKUMEN INI ADALAH HASIL ANALISIS KEBUTUHAN SISTEM BERDASARKAN WAWANCARA DENGAN MITRA USAHA ROTI DAN AKAN MENJADI ACUAN UTAMA DALAM PENGEMBANGAN APLIKASI "FORHOMIE"**

---

*Dokumen ini dibuat dengan penuh tanggung jawab dan komitmen untuk menghasilkan sistem yang berkualitas dan bermanfaat bagi mitra usaha.*

**Malang, 4 Februari 2026**

---

**END OF DOCUMENT**
