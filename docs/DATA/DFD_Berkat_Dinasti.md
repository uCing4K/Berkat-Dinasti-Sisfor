# 📊 DATA FLOW DIAGRAM (DFD)
## Sistem Informasi Manajemen Pesanan Roti — *Berkat Dinasti*
### Mata Kuliah: Sistem Informasi | Kelompok 5

---

> **Sumber Data:**
> - Hasil wawancara mitra: *Dynasty Food & Beverage* (29 Agustus 2026)
> - Anggota Kelompok 5: Aditya Widyatamaka Tri Utomo, Chasbiah Azzahra, Dzauq Bachrul 'Ulum, Elshifa Viorissa
> - Lokasi Mitra: Desa Songokerto, Kota Batu — operasional Malang Raya

---

## PENDAHULUAN

### Latar Belakang

**Dynasty Food & Beverage** adalah UMKM produksi dan penjualan roti berbasis *pre-order* yang berlokasi di Desa Songokerto, Kota Batu. Usaha ini melayani pelanggan retail (acara hajatan/syukuran) maupun reseller di wilayah Malang Raya (Kota Malang, Kabupaten Malang, dan Kota Batu).

**Kondisi saat ini (As-Is):**
- Penerimaan order melalui WhatsApp dan tatap muka (offline)
- Pencatatan pesanan dilakukan **manual** di buku nota
- Tidak ada database pelanggan yang tersistem
- Invoice berupa nota tulis tangan
- Pelacakan pembayaran menggunakan buku kas manual
- Tidak ada rekap laporan penjualan rutin

**Tujuan Sistem (To-Be):**
- Digitalisasi pencatatan pesanan dan data pelanggan
- Otomatisasi pembuatan invoice/struk digital (format `INV-YYYYMMDD-XXXX`)
- Manajemen piutang dan tracking pembayaran (Lunas / Hutang)
- Pelaporan penjualan harian dan bulanan yang akurat

### Entitas Eksternal Sistem

| Entitas | Peran dalam Sistem |
|---------|-------------------|
| **Pelanggan** (Retail & Reseller) | Sumber pesanan dan penerima produk/invoice |
| **Admin / Pemilik** | Mengelola seluruh operasional sistem |
| **Staff** | Membantu input pesanan dan pencatatan pembayaran |

### Proses Utama Sistem

| No | Proses | Deskripsi |
|----|--------|-----------|
| 1 | **Manajemen Katalog Produk** | Pengelolaan data produk, kategori, varian kemasan, dan harga |
| 2 | **Manajemen Pelanggan** | Pendataan pelanggan, klasifikasi (retail/reseller), dan riwayat transaksi |
| 3 | **Manajemen Pesanan** | Input pesanan, auto-generate invoice, kalender produksi, dan tracking status |
| 4 | **Manajemen Pembayaran** | Pencatatan pembayaran dan pelacakan status piutang (Lunas/Hutang) |
| 5 | **Manajemen Pengiriman** | Penjadwalan tanggal & waktu pengiriman per pesanan |
| 6 | **Pelaporan Keuangan** | Rekap penjualan, piutang, pengeluaran, dan laporan periodik |
| 7 | **Manajemen Pengguna** | Autentikasi login dan pengelolaan hak akses (admin/staff) |

---

## BAGIAN 1 — DFD LEVEL 0: CONTEXT DIAGRAM

> **Definisi:** Context diagram menggambarkan sistem secara keseluruhan sebagai satu proses tunggal beserta entitas eksternal yang berinteraksi dengannya.

```
                     +==============================+
                     |                              |
  +--------------+   |                              |   +--------------+
  |              |-->|  Info Pesanan, Data Diri,    |<--|    ADMIN /   |
  |  PELANGGAN   |   |  Konfirmasi Pembayaran       |-->|   PEMILIK    |
  | (Retail &    |<--|                              |   |              |
  |  Reseller)   |   |   SISTEM INFORMASI           |   +--------------+
  +--------------+   |   MANAJEMEN PESANAN ROTI     |
                     |   "BERKAT DINASTI"           |   +--------------+
                     |   (Dynasty Food & Beverage)  |<--|              |
                     |                              |-->|    STAFF     |
                     |   - Pengiriman GRATIS        |   |              |
                     |     untuk Malang Raya        |   +--------------+
                     |   - Status Bayar: Lunas/Hutang|
                     +==============================+
```

### Penjelasan Arus Data — DFD Level 0

| No | Entitas | Arah Arus | Isi Data |
|----|---------|-----------|----------|
| 1 | Pelanggan -> Sistem | Masuk | Data pesanan (produk, jumlah, varian, catatan), data identitas pelanggan (nama, alamat, no. WA), konfirmasi pembayaran |
| 2 | Sistem -> Pelanggan | Keluar | Invoice digital, status pesanan (Lunas/Hutang), katalog produk, informasi harga & min. order, struk digital |
| 3 | Admin/Pemilik -> Sistem | Masuk | Data produk, harga, kategori, jadwal pengiriman, update status pesanan, data pengeluaran operasional |
| 4 | Sistem -> Admin/Pemilik | Keluar | Laporan penjualan harian/bulanan, daftar piutang, rekap pengeluaran, kalender produksi |
| 5 | Staff -> Sistem | Masuk | Data pembayaran (jumlah, metode cash/transfer), input pesanan baru, update status pengiriman |
| 6 | Sistem -> Staff | Keluar | Invoice pesanan, daftar pesanan belum lunas, jadwal pengiriman harian |

---

## BAGIAN 2 — DFD LEVEL 1: OVERVIEW DIAGRAM

> **Definisi:** DFD Level 1 memecah proses utama sistem menjadi proses-proses yang lebih detail, lengkap dengan penyimpanan data (data store) dan arus data antar proses.

```
                         PELANGGAN
                             |
           +-----------------+-----------------------+
           |                 |                       |
Info Produk|     Data Pesanan|           Konfirmasi  |
Katalog    |     + Data Diri |           Pembayaran  |
           |                 |                       |
           v                 v                       v
 +------------------+  +------------------+  +-----------------+
 |                  |  |                  |  |                 |
 |  1.0             |  |  2.0             |  |  3.0            |
 |  MANAJEMEN       |  |  MANAJEMEN       |  |  MANAJEMEN      |
 |  KATALOG PRODUK  |  |  PELANGGAN       |  |  PESANAN        |
 |                  |  |                  |  |                 |
 +--------+---------+  +---------+--------+  +--------+--------+
          |                      |                    |
          |                      |                    |
  +-------v------+      +--------v------+    +--------v-------+
  | D1           |      | D2            |    | D3             |
  | kategori     |      | pelanggan     |    | pesanan        |
  | produk       |      | (tanpa zona)  |    | detail_pesanan |
  | varian_produk|      +---------------+    +--------+-------+
  +--------------+                                    |
                                                      |
          +--------------------------------------------+
          |
          v
 +------------------------+---------------------------+
 |                        |                           |
 |  4.0                   |  5.0                      |
 |  MANAJEMEN             |  MANAJEMEN                |
 |  PEMBAYARAN            |  PENGIRIMAN               |
 |  (Lunas / Hutang)      |  (FREE - Malang Raya)     |
 |                        |                           |
 +-----------+------------+-----------+---------------+
             |                        |
    +--------v------+        +--------v------+
    | D4            |        | D5            |
    | pembayaran    |        | pengiriman    |
    | tracking_log  |        | tracking_log  |
    +--------+------+        +--------+------+
             |                        |
             +----------+-------------+
                        |
                        v
             +--------------------+
             |  6.0               |
             |  PELAPORAN         |
             |  KEUANGAN          |
             +----------+---------+
                        |
               +--------v------+
               | D6            |
               | pengeluaran   |
               | transaksi_kas |
               +---------------+
                        |
                        v
                 ADMIN / PEMILIK

 +------------------+
 |  7.0             |
 |  MANAJEMEN       |
 |  PENGGUNA        |
 +------------------+
      |        ^
      |        |
 +----v----+   |
 | D7      |---+
 | pengguna|
 +---------+
 (Admin & Staff)
```

### Daftar Data Store — DFD Level 1

| ID | Nama Data Store | Entitas/Tabel yang Terlibat | Keterangan |
|----|----------------|----------------------------|------------|
| D1 | **Produk & Katalog** | `kategori`, `produk`, `varian_produk` | Menyimpan semua data produk roti beserta varian kemasan (Kardus/Mika) dan harga |
| D2 | **Data Pelanggan** | `pelanggan` | Menyimpan data pelanggan retail & reseller (nama, alamat, no. WA, tipe, status piutang) — **tanpa tabel zona** |
| D3 | **Pesanan** | `pesanan`, `detail_pesanan` | Menyimpan header pesanan (invoice, status, tanggal kirim) dan detail item yang dipesan |
| D4 | **Pembayaran & Tracking** | `pembayaran`, `tracking_log` | Menyimpan catatan pembayaran (cash/transfer, Lunas/Hutang) dan log perubahan status |
| D5 | **Pengiriman** | `pengiriman`, `tracking_log` | Menyimpan data pengiriman (tgl_kirim, waktu_kirim, status) — pengiriman gratis Malang Raya |
| D6 | **Keuangan** | `pengeluaran`, `transaksi_kas` | Menyimpan pengeluaran operasional dan arus kas masuk-keluar |
| D7 | **Pengguna Sistem** | `pengguna` | Menyimpan akun dan hak akses (admin/staff) |

---

## BAGIAN 3 — DFD LEVEL 2: DETAIL SEMUA PROSES

---

### DFD Level 2 — Proses 1.0: MANAJEMEN KATALOG PRODUK

> Memecah proses pengelolaan katalog produk menjadi sub-proses yang lebih rinci.

```
ADMIN / PEMILIK
      |
      | Data Kategori (nama, deskripsi)
      v
+------------------------+
| 1.1                    |
| KELOLA KATEGORI PRODUK |-----> D1.1 [kategori]
|                        |<-----
+------------------------+
      |
      | Data Kategori Valid
      v
+------------------------+
| 1.2                    |
| KELOLA DATA PRODUK     |-----> D1.2 [produk]
| (Tambah/Edit/Hapus,    |<-----
|  foto, shelf_life)     |
+------------------------+
      |
      | id_produk + Data Varian
      v
+------------------------+
| 1.3                    |
| KELOLA VARIAN & HARGA  |-----> D1.3 [varian_produk]
| (kemasan: Kardus/Mika, |<-----
|  harga, min_order)     |
+------------------------+
      |
      | Status Produk
      v
+------------------------+
| 1.4                    |
| KELOLA STATUS          |-----> D1.2 [produk.status]
| KETERSEDIAAN           |
| (Tersedia/Tidak Ada)   |
+------------------------+
      |
      | Katalog Produk Aktif
      v
  PELANGGAN (Lihat Katalog Online)
```

**Penjelasan Sub-Proses 1.0:**

| Sub-Proses | Input | Proses | Output | Data Store |
|------------|-------|--------|--------|------------|
| **1.1 Kelola Kategori** | Nama kategori, deskripsi | Tambah/edit/hapus kategori produk | Data kategori tersimpan | `kategori` |
| **1.2 Kelola Data Produk** | Nama produk, foto, deskripsi, shelf_life, id_kategori | CRUD data produk (Roti Hajatan, Roti Kopi, Roti Bijian) | Data produk tersimpan | `produk` |
| **1.3 Kelola Varian & Harga** | id_produk, nama varian (Kardus/Mika), harga, min_order | Tambah/edit varian kemasan dan harga per produk | Varian tersimpan | `varian_produk` |
| **1.4 Kelola Status Ketersediaan** | id_produk, status | Update status tersedia/tidak_tersedia per produk | Status diperbarui | `produk.status` |

**Data Produk Aktual (dari Wawancara Mitra — 29 Agustus 2026):**

| Kategori | Produk | Varian Kemasan | Harga | Min. Order |
|----------|--------|----------------|-------|------------|
| Roti Hajatan | Roti Hajatan Isi 6 Rasa | Kardus Biasa | Rp 9.000 | 1 pcs |
| Roti Hajatan | Roti Hajatan Isi 6 Rasa | Mika | Rp 10.000 | 1 pcs |
| Roti Manis | Roti Kopi | Satuan | Rp 4.000 | 50 pcs |
| Roti Manis | Roti Bijian Besar | Satuan | Rp 4.000 | 20 pcs |
| Roti Manis | Roti Bijian Kecil | Satuan | Rp 3.000 | 50 pcs |

---

### DFD Level 2 — Proses 2.0: MANAJEMEN PELANGGAN

> Memecah proses pengelolaan data pelanggan.
> **CATATAN PENTING:** Sistem TIDAK menggunakan zona pengiriman. Ongkir otomatis GRATIS untuk seluruh Malang Raya.

```
ADMIN / PEMILIK
      |
      | Data Pelanggan (Nama, Alamat Lengkap,
      | No. WA, Tipe: Retail/Reseller)
      v
+------------------------+
| 2.1                    |
| REGISTRASI & INPUT     |-----> D2 [pelanggan]
| DATA PELANGGAN         |<-----
+------------------------+
      |
      | id_pelanggan + Tipe
      v
+------------------------+
| 2.2                    |
| KLASIFIKASI PELANGGAN  |-----> D2 [pelanggan.tipe]
| (Retail / Reseller)    |<-----
+------------------------+
      |
      | Data Pelanggan + Riwayat
      v
+------------------------+
| 2.3                    |<----- D3 [pesanan]
| LIHAT RIWAYAT PESANAN  |
| PELANGGAN              |-----> Output ke Admin
+------------------------+
      |
      | Data Piutang
      v
+------------------------+
| 2.4                    |<----- D4 [pembayaran]
| TRACKING PIUTANG       |
| (Status: Lunas/Hutang, |-----> D2 [pelanggan.total_hutang]
|  total_transaksi)      |-----> D2 [pelanggan.total_transaksi]
+------------------------+
      |
      v
ADMIN / PEMILIK (Daftar Piutang per Pelanggan)
```

**Penjelasan Sub-Proses 2.0:**

| Sub-Proses | Input | Proses | Output | Data Store |
|------------|-------|--------|--------|------------|
| **2.1 Registrasi Pelanggan** | Nama, alamat lengkap, no. WA, tipe (retail/reseller) | Input/edit data pelanggan baru tanpa penetapan zona | Data pelanggan tersimpan | `pelanggan` |
| **2.2 Klasifikasi Pelanggan** | Tipe pelanggan | Tentukan apakah pelanggan retail atau reseller | Pelanggan terklasifikasi | `pelanggan.tipe` |
| **2.3 Riwayat Pesanan** | id_pelanggan | Ambil semua pesanan pelanggan dari data store | Daftar histori pesanan | `pesanan` |
| **2.4 Tracking Piutang** | id_pelanggan, data pembayaran | Hitung total transaksi dan total hutang pelanggan; update status Lunas/Hutang | total_hutang & total_transaksi diperbarui | `pelanggan`, `pembayaran` |

> **CATATAN:** Sistem **TIDAK menggunakan tabel zona**. Ongkos kirim bersifat **GRATIS** untuk seluruh wilayah operasional Malang Raya. Data alamat pelanggan disimpan langsung di tabel `pelanggan` tanpa relasi ke tabel zona.

---

### DFD Level 2 — Proses 3.0: MANAJEMEN PESANAN

> Memecah proses pencatatan dan pengelolaan pesanan dari pelanggan.

```
ADMIN / STAFF
      |
      | Info Pesanan (Pelanggan, Produk, Qty,
      | Catatan Khusus, Tanggal Kirim)
      v
+------------------------+
| 3.1                    |<----- D2 [pelanggan]
| INPUT PESANAN BARU     |<----- D1.3 [varian_produk]
|                        |-----> D3.1 [pesanan]
+------------------------+
      |
      | Data Pesanan Baru
      v
+------------------------+
| 3.2                    |
| GENERATE INVOICE       |-----> D3.1 [pesanan.no_invoice]
| OTOMATIS               |
| (INV-YYYYMMDD-XXXX)    |-----> PELANGGAN (Invoice Digital)
+------------------------+
      |
      | id_pesanan + Item Detail
      v
+------------------------+
| 3.3                    |<----- D1.3 [varian_produk.harga]
| INPUT DETAIL ITEM      |
| PESANAN                |-----> D3.2 [detail_pesanan]
| (produk, varian, qty,  |
|  harga_satuan,         |-----> D3.1 [pesanan.total_harga]
|  subtotal otomatis)    |
+------------------------+
      |
      | Status Pesanan
      v
+------------------------+
| 3.4                    |
| KELOLA STATUS PESANAN  |-----> D3.1 [pesanan.status]
| Pending -> Proses      |
| -> Kirim -> Selesai    |-----> D4 [tracking_log]
| (atau Batal)           |
|                        |-----> PELANGGAN (Notif Status)
+------------------------+
      |
      | Jadwal Produksi
      v
+------------------------+
| 3.5                    |<----- D3.1 [pesanan.tgl_kirim]
| KALENDER PRODUKSI &    |
| JADWAL PENGIRIMAN      |-----> Output ke Admin (Tampilan Kalender)
+------------------------+
```

**Penjelasan Sub-Proses 3.0:**

| Sub-Proses | Input | Proses | Output | Data Store |
|------------|-------|--------|--------|------------|
| **3.1 Input Pesanan Baru** | Data pelanggan, produk/varian yang dipesan, catatan, tgl_kirim | Simpan header pesanan baru dengan status awal *pending* | Pesanan tersimpan | `pesanan` |
| **3.2 Generate Invoice Otomatis** | id_pesanan, tgl_pesan | Sistem otomatis membuat nomor invoice unik format `INV-YYYYMMDD-XXXX` | No. invoice otomatis, invoice digital | `pesanan.no_invoice` |
| **3.3 Input Detail Item** | id_pesanan, id_varian, qty | Simpan detail item; sistem hitung subtotal otomatis (qty x harga_satuan), update total pesanan | Detail item tersimpan, total dihitung otomatis | `detail_pesanan`, `pesanan.total_harga` |
| **3.4 Kelola Status Pesanan** | id_pesanan, status baru | Update status: Pending -> Proses -> Kirim -> Selesai (atau Batal); setiap perubahan dicatat di tracking_log | Status terbarui, log tersimpan | `pesanan.status`, `tracking_log` |
| **3.5 Kalender Produksi** | `pesanan.tgl_kirim` | Tampilkan jadwal pesanan dalam bentuk kalender berdasarkan tanggal kirim | Tampilan kalender produksi | `pesanan` |

**Alur Status Pesanan:**

```
[PENDING] --> [PROSES] --> [KIRIM] --> [SELESAI]
                              |
                              +-------> [BATAL]
```

---

### DFD Level 2 — Proses 4.0: MANAJEMEN PEMBAYARAN

> Memecah proses pencatatan pembayaran. Status pembayaran: **Lunas** atau **Hutang**.

```
ADMIN / STAFF
      |
      | Data Pembayaran (id_pesanan, jumlah,
      | metode: cash/transfer, bukti transfer)
      v
+------------------------+
| 4.1                    |<----- D3.1 [pesanan.grand_total]
| CATAT PEMBAYARAN       |
| (Cash / Transfer)      |-----> D4.1 [pembayaran]
+------------------------+
      |
      | Total Dibayar vs Grand Total
      v
+------------------------+
| 4.2                    |<----- D4.1 [pembayaran.jumlah]
| UPDATE STATUS BAYAR    |
| OTOMATIS               |-----> D3.1 [pesanan.status_bayar]
| (Lunas / Hutang)       |
+------------------------+
      |
      | Sisa Hutang
      v
+------------------------+
| 4.3                    |<----- D3.1 [pesanan.status_bayar]
| UPDATE PIUTANG         |<----- D4.1 [pembayaran]
| PELANGGAN              |
|                        |-----> D2 [pelanggan.total_hutang]
+------------------------+
      |
      | Bukti Transaksi
      v
+------------------------+
| 4.4                    |<----- D4.1 [pembayaran]
| GENERATE STRUK /       |<----- D3.1 [pesanan]
| INVOICE DIGITAL        |
| (bisa di-share via WA) |-----> PELANGGAN (Struk Digital)
+------------------------+
      |
      | Arus Kas
      v
D6 [transaksi_kas] --> Proses 6.0 (Pelaporan)
```

**Penjelasan Sub-Proses 4.0:**

| Sub-Proses | Input | Proses | Output | Data Store |
|------------|-------|--------|--------|------------|
| **4.1 Catat Pembayaran** | id_pesanan, jumlah, metode (cash/transfer), bukti, tgl_bayar | Simpan transaksi pembayaran; mendukung skenario belum bayar penuh | Data pembayaran tersimpan | `pembayaran` |
| **4.2 Update Status Bayar Otomatis** | Total pembayaran, grand_total pesanan | Sistem bandingkan: jika penuh -> `lunas`; jika belum penuh -> `hutang` | Status bayar pesanan terbarui otomatis | `pesanan.status_bayar` |
| **4.3 Update Piutang Pelanggan** | status_bayar, data pembayaran | Hitung sisa hutang = grand_total - total_dibayar; update total_hutang pelanggan | Piutang pelanggan terbarui | `pelanggan.total_hutang` |
| **4.4 Generate Struk Digital** | Data pesanan & pembayaran | Buat invoice/struk digital yang bisa di-share via WhatsApp | Struk digital dikirim ke pelanggan | `pesanan`, `pembayaran` |

**Metode Pembayaran (dari Wawancara Mitra):**

| Metode | Keterangan |
|--------|-----------|
| Cash | Pembayaran tunai saat pengiriman |
| Transfer | Transfer bank, dapat dibayar setelah barang diantar |
| Bayar Belakangan | Pelanggan dapat bayar di kemudian hari (tercatat sebagai piutang/hutang) |

---

### DFD Level 2 — Proses 5.0: MANAJEMEN PENGIRIMAN

> Memecah proses penjadwalan dan pencatatan status pengiriman.
> **TIDAK ada sistem zona** — pengiriman GRATIS untuk seluruh Malang Raya.

```
ADMIN / STAFF
      |
      | Jadwal Kirim (Tanggal & Waktu)
      v
+------------------------+
| 5.1                    |<----- D3.1 [pesanan.tgl_kirim]
| BUAT JADWAL PENGIRIMAN |<----- D3.1 [pesanan.waktu_kirim]
| (Input tanggal & waktu |
|  per pesanan)          |-----> D5.1 [pengiriman]
| Ongkir: GRATIS         |
| Malang Raya            |
+------------------------+
      |
      | Data Pengiriman Terjadwal
      v
+------------------------+
| 5.2                    |<----- D5.1 [pengiriman]
| UPDATE STATUS          |<----- D3.1 [pesanan]
| PENGIRIMAN             |
| (menunggu ->           |-----> D5.1 [pengiriman.status]
|  dalam_perjalanan ->   |
|  sampai / gagal)       |-----> D4 [tracking_log]
|                        |
|                        |-----> D3.1 [pesanan.status]
+------------------------+
      |
      v
PELANGGAN (Status Pengiriman)
```

**Penjelasan Sub-Proses 5.0:**

| Sub-Proses | Input | Proses | Output | Data Store |
|------------|-------|--------|--------|------------|
| **5.1 Buat Jadwal Pengiriman** | id_pesanan, tgl_kirim, waktu_kirim | Input jadwal pengiriman (tanggal & waktu) per pesanan; ongkir otomatis GRATIS untuk Malang Raya | Data pengiriman terjadwal tersimpan | `pengiriman` |
| **5.2 Update Status Pengiriman** | id_pengiriman, status baru | Update status: menunggu -> dalam_perjalanan -> sampai (atau gagal); sinkronisasi ke status pesanan | Status terbarui, log tercatat | `pengiriman.status`, `tracking_log`, `pesanan.status` |

**Informasi Pengiriman (dari Wawancara Mitra — 29 Agustus 2026):**
- Ongkos kirim: **GRATIS** untuk seluruh Malang Raya (Kota Malang, Kabupaten Malang, Kota Batu)
- **TIDAK ada sistem zona pengiriman** dalam desain sistem ini
- Kendaraan: Sepeda obrok
- Kapasitas maks: **150 pcs roti besar** per trip *(fitur indikator bersifat opsional)*

---

### DFD Level 2 — Proses 6.0: PELAPORAN KEUANGAN

> Memecah proses rekap dan pelaporan data keuangan usaha.

```
D3.1 [pesanan]
D4.1 [pembayaran]       ADMIN / PEMILIK
D6.1 [pengeluaran]           |
      |                      | Filter (Tanggal, Metode, Kategori)
      +----------+-----------+
                 |
                 v
        +--------------------+
        | 6.1                |
        | REKAP PENJUALAN    |-----> Laporan Penjualan Harian
        | HARIAN & BULANAN   |-----> Laporan Penjualan Bulanan
        +--------------------+
                 |
        +--------------------+
        | 6.2                |<----- D2 [pelanggan.total_hutang]
        | LAPORAN PIUTANG    |<----- D4.1 [pembayaran]
        | PELANGGAN          |
        | (Status: Lunas /   |-----> Daftar Piutang per Pelanggan
        |  Hutang)           |
        +--------------------+
                 |
        +--------------------+
        | 6.3                |
        | REKAP PENGELUARAN  |<----- D6.1 [pengeluaran]
        | OPERASIONAL        |
        |                    |-----> Rekap Biaya Operasional
        +--------------------+
                 |
        +--------------------+
        | 6.4                |<----- D6.2 [transaksi_kas]
        | LAPORAN ARUS KAS   |
        | (Masuk & Keluar)   |-----> Rekap Kas Harian/Bulanan
        +--------------------+
                 |
        +--------------------+
        | 6.5                |
        | EXPORT LAPORAN     |-----> File PDF / Excel
        | (PDF / Excel)      |
        +--------------------+
                 |
                 v
          ADMIN / PEMILIK
```

**Penjelasan Sub-Proses 6.0:**

| Sub-Proses | Input | Proses | Output | Data Store |
|------------|-------|--------|--------|------------|
| **6.1 Rekap Penjualan** | Data pesanan & pembayaran, filter tanggal | Agregasi total penjualan per hari/bulan, filter per metode pembayaran | Laporan penjualan harian & bulanan | `pesanan`, `pembayaran` |
| **6.2 Laporan Piutang** | Data pelanggan, total_hutang, data pembayaran | Buat daftar pelanggan yang masih memiliki hutang beserta nominal piutang | Daftar piutang per pelanggan | `pelanggan`, `pembayaran` |
| **6.3 Rekap Pengeluaran** | Data pengeluaran per kategori | Agregasi biaya operasional (bahan baku, operasional, gaji, transportasi, lainnya) per periode | Rekap pengeluaran | `pengeluaran` |
| **6.4 Laporan Arus Kas** | Transaksi kas masuk & keluar | Tampilkan saldo kas dan pergerakan kas harian/bulanan | Rekap kas | `transaksi_kas` |
| **6.5 Export Laporan** | Semua data laporan | Konversi dan unduh laporan ke format PDF/Excel | File PDF/Excel terunduh | Semua data store |

---

### DFD Level 2 — Proses 7.0: MANAJEMEN PENGGUNA

> Memecah proses autentikasi dan pengelolaan akses sistem. Role: **Admin** dan **Staff**.

```
ADMIN / PEMILIK
      |
      | Data Pengguna (username, password,
      | nama lengkap, role: admin/staff, status)
      v
+------------------------+
| 7.1                    |
| KELOLA DATA PENGGUNA   |-----> D7 [pengguna]
| (Admin / Staff)        |
+------------------------+
      |
      | Hak Akses per Role
      v
+------------------------+
| 7.2                    |<----- D7 [pengguna.role]
| AUTENTIKASI & LOGIN    |
| SISTEM                 |<----- Input: Username + Password
|                        |-----> Output: Session / Akses Ditolak
|  Session timeout: 30   |
|  menit                 |
+------------------------+
      |
      | Role-based Access
      v
+------------------------+
| 7.3                    |<----- D7 [pengguna.role]
| KONTROL HAK AKSES      |
| (Role-based)           |-----> Admin: Akses Penuh
|                        |-----> Staff: Pesanan & Pembayaran
+------------------------+
      |
      v
Semua Pengguna (Admin & Staff)
```

**Penjelasan Sub-Proses 7.0:**

| Sub-Proses | Input | Proses | Output | Data Store |
|------------|-------|--------|--------|------------|
| **7.1 Kelola Data Pengguna** | Nama lengkap, username, password, role (admin/staff), status | CRUD akun pengguna sistem; password di-hash (bcrypt) sebelum disimpan | Akun pengguna tersimpan | `pengguna` |
| **7.2 Autentikasi & Login** | Username, password | Verifikasi kredensial dengan password_hash; buat session; session timeout 30 menit | Session aktif / akses ditolak | `pengguna` |
| **7.3 Kontrol Hak Akses** | Role pengguna | Tentukan halaman/fitur yang bisa diakses berdasarkan role | Menu & fitur sesuai role | `pengguna.role` |

**Tabel Hak Akses per Role:**

| Fitur / Modul | Admin | Staff |
|---------------|-------|-------|
| Manajemen Produk & Katalog | Full | Tidak |
| Manajemen Pelanggan | Full | View |
| Input Pesanan Baru | Full | Full |
| Manajemen Pembayaran | Full | Full |
| Manajemen Pengiriman | Full | Input Jadwal & Update Status |
| Laporan Keuangan | Full | Terbatas |
| Manajemen Pengguna | Full | Tidak |

---

## BAGIAN 4 — RINGKASAN DAN VALIDASI

### Arus Data Utama (Summary)

```
                  +-----------------------------------------------+
                  |          SISTEM BERKAT DINASTI                 |
                  |                                                 |
PELANGGAN --------+---> [3.0 Pesanan] --> D3 -> [4.0 Bayar] -> D4 |
    ^             |         |                        |             |
    |             |         v                        v             |
    |             |  [5.0 Pengiriman]->D5    [6.0 Laporan]-->ADMIN |
    |             |  (FREE-Malang Raya)                           |
    +-------------+---------+ (Status, Invoice, Struk Digital)    |
                  |                                                 |
ADMIN   ----------+---> [1.0 Produk] --> D1                        |
                  |  --> [2.0 Pelanggan] --> D2 (tanpa zona)       |
                  |  --> [7.0 Pengguna] --> D7                     |
                  |                                                 |
STAFF   ----------+---> [3.0 Pesanan] --> D3                       |
                  |  --> [4.0 Pembayaran] --> D4                   |
                  |  --> [5.0 Pengiriman] --> D5                   |
                  +-----------------------------------------------+
```

### Daftar Proses & Keterlibatan Entitas

| Proses | Admin | Staff | Pelanggan | Data Store Utama |
|--------|-------|-------|-----------|-----------------|
| 1.0 Katalog Produk | Ya | Tidak | Baca saja | `kategori`, `produk`, `varian_produk` |
| 2.0 Manajemen Pelanggan | Ya | View | Tidak | `pelanggan` |
| 3.0 Manajemen Pesanan | Ya | Ya | Baca status | `pesanan`, `detail_pesanan` |
| 4.0 Manajemen Pembayaran | Ya | Ya | Terima struk | `pembayaran`, `tracking_log` |
| 5.0 Manajemen Pengiriman | Ya | Ya | Baca status | `pengiriman`, `tracking_log` |
| 6.0 Pelaporan Keuangan | Ya | Terbatas | Tidak | Semua data store |
| 7.0 Manajemen Pengguna | Ya | Tidak | Tidak | `pengguna` |

---

## BAGIAN 5 — TABEL REFERENSI DATA STORE LENGKAP

| ID | Tabel | Isi | Digunakan Proses |
|----|-------|-----|-----------------|
| T1 | `kategori` | Kategori produk (Roti Hajatan, Roti Manis) | 1.0 |
| T2 | `produk` | Master data produk roti (nama, foto, shelf_life, status) | 1.0, 3.0 |
| T3 | `varian_produk` | Varian kemasan & harga (Kardus/Mika/Satuan), min_order | 1.0, 3.0, 4.0 |
| T4 | `pelanggan` | Data pelanggan retail & reseller — tanpa relasi zona | 2.0, 3.0, 4.0 |
| T5 | `pesanan` | Header pesanan (invoice, status, tgl_kirim, total) | 3.0, 4.0, 5.0, 6.0 |
| T6 | `detail_pesanan` | Detail item per pesanan (qty, harga_satuan, subtotal) | 3.0, 6.0 |
| T7 | `pembayaran` | Catatan pembayaran (cash/transfer, Lunas/Hutang) | 4.0, 6.0 |
| T8 | `pengiriman` | Data pengiriman per pesanan (tgl_kirim, status) — gratis Malang Raya | 5.0, 6.0 |
| T9 | `tracking_log` | Log riwayat perubahan status pesanan | 3.0, 4.0, 5.0 |
| T10 | `pengguna` | Akun sistem (admin/staff) | 7.0 |
| T11 | `pengeluaran` | Biaya operasional (bahan baku, operasional, gaji, dll) | 6.0 |
| T12 | `transaksi_kas` | Arus kas masuk-keluar | 6.0 |
| T13 | `setting` | Konfigurasi dinamis sistem | Semua |

---

## BAGIAN 6 — VALIDASI DFD TERHADAP KEBUTUHAN MITRA

Berdasarkan hasil wawancara dengan **Dynasty Food & Beverage** (29 Agustus 2026):

| Kebutuhan dari Wawancara | DFD yang Mengakomodasi | Status |
|--------------------------|------------------------|--------|
| Digitalisasi pencatatan pesanan (dari manual/buku) | Proses 3.0 Manajemen Pesanan | Terpenuhi |
| Database pelanggan (Retail & Reseller) | Proses 2.0 Manajemen Pelanggan | Terpenuhi |
| Klasifikasi pelanggan (Retail/Reseller) — tanpa zona | Sub-proses 2.2 | Terpenuhi |
| Invoice/struk digital otomatis (INV-YYYYMMDD-XXXX) | Sub-proses 3.2 & 4.4 | Terpenuhi |
| Tracking status pesanan (Pending->Proses->Kirim->Selesai) | Sub-proses 3.4 | Terpenuhi |
| Kalender produksi & jadwal pengiriman | Sub-proses 3.5 & 5.1 | Terpenuhi |
| Status pembayaran: Lunas / Hutang | Sub-proses 4.2 & 4.3 | Terpenuhi |
| Pengiriman GRATIS Malang Raya — TIDAK ada sistem zona | Proses 5.0 (sub 5.1) | Terpenuhi |
| Laporan penjualan harian & bulanan | Sub-proses 6.1 | Terpenuhi |
| Daftar piutang pelanggan | Sub-proses 6.2 | Terpenuhi |
| Katalog produk online (harga, varian, min. order, foto) | Proses 1.0 | Terpenuhi |
| Login sistem dengan role (admin/staff) | Proses 7.0 | Terpenuhi |
| Export laporan ke PDF/Excel | Sub-proses 6.5 | Terpenuhi |
| Kapasitas angkut 150 pcs/trip (indikator opsional) | Sub-proses 5.1 (opsional) | Terpenuhi |

---

*Dokumen DFD ini dibuat dan diperbarui sesuai data dari: InterviewMitra_Kelompok5.pdf (29 Agustus 2026).*

*Kelompok 5 — Mata Kuliah Sistem Informasi — 2026*
