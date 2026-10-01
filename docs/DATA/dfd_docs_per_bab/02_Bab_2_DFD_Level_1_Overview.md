> 📋 **PROMPT UNTUK GEMINI DOCS (Copy teks prompt di bawah ini lalu paste ke kotak Gemini):**
> 
> *"Ubah teks di bawah ini menjadi bab laporan akademik resmi untuk DFD LEVEL 1 (OVERVIEW DIAGRAM & DATA STORE). Susun dengan Heading Docs yang terstruktur, buat tabel Kamus Data Store (D1-D7) menjadi tabel resmi bergaris rapi dengan kolom ID, Nama Data Store, Entitas/Tabel Terlibat, dan Deskripsi Fungsi. Pada bagian diagram alur data, rapikan tata letak kotak diagram dan sertakan analisis naratif akademis tentang bagaimana arus data bergerak dari pemesanan oleh pelanggan, pemrosesan pesanan, penyimpanan data store, hingga pelaporan keuangan ke admin/pemilik."*

---

# BAB II: DFD LEVEL 1 (OVERVIEW DIAGRAM & DATA STORE)
## Sistem Informasi Manajemen Pesanan Roti — Berkat Dinasti

---

### 1. Definisi & Konsep DFD Level 1

> **Definisi:** DFD Level 1 (*Overview Diagram*) memecah proses tunggal dari DFD Level 0 menjadi 7 sub-proses utama sistem. Diagram ini secara eksplisit memetakan keterkaitan antara entitas eksternal, proses pengolahan, dan penyimpanan data (*Data Store* D1 hingga D7).

---

### 2. Diagram Alur Data Level 1 (Overview Diagram)

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

---

### 3. Kamus Data Store (Penyimpanan Data D1 — D7)

| ID | Nama Data Store | Entitas / Tabel Fisik | Fungsi & Keterangan |
|:--:|:----------------|:----------------------|:-------------------|
| **D1** | **Produk & Katalog** | `kategori`, `produk`, `varian_produk` | Menyimpan master data produk roti, kategori, varian kemasan (Kardus/Mika/Satuan), dan harga jual |
| **D2** | **Data Pelanggan** | `pelanggan` | Menyimpan data pelanggan retail & reseller (nama, alamat lengkap, kontak WA, riwayat pesanan, status piutang). **Sistem TIDAK menggunakan tabel zona**. |
| **D3** | **Pesanan** | `pesanan`, `detail_pesanan` | Menyimpan header transaksi (`INV-YYYYMMDD-XXXX`), status produksi, tanggal kirim, dan rincian item produk |
| **D4** | **Pembayaran & Tracking** | `pembayaran`, `tracking_log` | Menyimpan riwayat transaksi pembayaran (tunai/transfer, Lunas/Hutang) serta log riwayat perubahan status |
| **D5** | **Pengiriman** | `pengiriman`, `tracking_log` | Menyimpan jadwal pengiriman pesanan (tanggal & jam kirim). Bebas ongkir untuk seluruh wilayah Malang Raya |
| **D6** | **Keuangan & Kas** | `pengeluaran`, `transaksi_kas` | Menyimpan pencatatan pengeluaran operasional usaha dan mutasi arus kas masuk-keluar |
| **D7** | **Pengguna Sistem** | `pengguna` | Menyimpan kredensial akun pengguna, password terenkripsi bcrypt, serta wewenang hak akses (*Admin* & *Staff*) |
