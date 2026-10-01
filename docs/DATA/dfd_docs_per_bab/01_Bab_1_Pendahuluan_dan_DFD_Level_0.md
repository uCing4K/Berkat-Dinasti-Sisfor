> 📋 **PROMPT UNTUK GEMINI DOCS (Copy teks prompt di bawah ini lalu paste ke kotak Gemini):**
> 
> *"Ubah isi bab di bawah ini menjadi format laporan akademik resmi untuk bab PENDAHULUAN & DFD LEVEL 0 (CONTEXT DIAGRAM). Format judul dan sub-judul menggunakan Heading Google Docs yang rapi, tabel entitas dan proses dibuat dengan format tabel bergaris yang jelas, dan untuk diagram alur kotak ASCII rapikan posisinya serta sertakan narasi penjelasan arus data masuk (input) dan arus data keluar (output) secara akademis. Pastikan catatan bahwa sistem ini GRATIS ongkir untuk seluruh Malang Raya dan tidak menggunakan zona pengiriman tetap tertulis jelas."*

---

# BAB I: PENDAHULUAN & DFD LEVEL 0 (CONTEXT DIAGRAM)
## Sistem Informasi Manajemen Pesanan Roti — Berkat Dinasti
**Mata Kuliah:** Sistem Informasi | **Kelompok 5**  
**Mitra UMKM:** Dynasty Food & Beverage (Desa Songokerto, Kota Batu — operasional Malang Raya)  
**Penyusun:** Aditya Widyatamaka Tri Utomo, Chasbiah Azzahra, Dzauq Bachrul 'Ulum, Elshifa Viorissa  

---

### 1. Latar Belakang & Analisis Kondisi Sistem

**Dynasty Food & Beverage** adalah UMKM produksi dan penjualan roti berbasis *pre-order* yang berlokasi di Desa Songokerto, Kota Batu. Usaha ini melayani pelanggan retail (acara hajatan/syukuran) maupun reseller di wilayah Malang Raya (Kota Malang, Kabupaten Malang, dan Kota Batu).

**Kondisi Saat Ini (As-Is):**
- Penerimaan order melalui WhatsApp dan tatap muka (offline)
- Pencatatan pesanan dilakukan secara **manual** di buku nota
- Tidak ada database pelanggan yang tersistem
- Invoice berupa nota tulis tangan
- Pelacakan pembayaran menggunakan buku kas manual
- Tidak ada rekap laporan penjualan rutin

**Tujuan Sistem Baru (To-Be):**
- Digitalisasi pencatatan pesanan dan sentralisasi data pelanggan
- Otomatisasi pembuatan invoice/struk digital (format `INV-YYYYMMDD-XXXX`)
- Manajemen piutang dan tracking pembayaran (Lunas / Hutang)
- Pelaporan penjualan harian dan bulanan yang akurat secara real-time

---

### 2. Entitas Eksternal Sistem

| Entitas Eksternal | Peran & Batasan dalam Sistem |
|-------------------|-----------------------------|
| **Pelanggan** (Retail & Reseller) | Sumber pesanan, penerima produk, invoice, dan bukti pembayaran |
| **Admin / Pemilik** | Mengelola seluruh operasional, katalog produk, keuangan, dan master data |
| **Staff** | Membantu input pesanan harian, pencatatan pembayaran kas/transfer, dan update jadwal pengiriman |

---

### 3. Proses Utama Sistem

| No | Proses Utama | Deskripsi Fungsional |
|:--:|:-------------|:---------------------|
| 1 | **Manajemen Katalog Produk** | Pengelolaan data produk, kategori, varian kemasan, dan penetapan harga |
| 2 | **Manajemen Pelanggan** | Pendataan pelanggan, klasifikasi (retail/reseller), dan riwayat transaksi |
| 3 | **Manajemen Pesanan** | Input pesanan, auto-generate invoice, kalender produksi, dan tracking status |
| 4 | **Manajemen Pembayaran** | Pencatatan pembayaran dan pelacakan status piutang (Lunas/Hutang) |
| 5 | **Manajemen Pengiriman** | Penjadwalan tanggal & waktu pengiriman per pesanan (Gratis Malang Raya) |
| 6 | **Pelaporan Keuangan** | Rekap penjualan, piutang, pengeluaran operasional, dan arus kas periodik |
| 7 | **Manajemen Pengguna** | Autentikasi login dan pengelolaan hak akses berbasis role (admin/staff) |

---

### 4. DFD Level 0: Context Diagram

> **Definisi:** Context diagram menggambarkan sistem secara keseluruhan sebagai satu proses tunggal terpadu yang berinteraksi langsung dengan entitas eksternal.

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

---

### 5. Rincian Arus Data Masuk & Keluar (DFD Level 0)

| No | Arah Aliran Arus Data | Tipe Arus | Deskripsi Rincian Data |
|:--:|:----------------------|:---------:|:-----------------------|
| 1 | **Pelanggan -> Sistem** | Masuk | Data pesanan (produk, jumlah, varian, catatan), identitas pemesan (nama, alamat, no. WA), bukti/konfirmasi pembayaran |
| 2 | **Sistem -> Pelanggan** | Keluar | Invoice digital (`INV-YYYYMMDD-XXXX`), informasi status pesanan, katalog produk & harga, struk digital |
| 3 | **Admin/Pemilik -> Sistem** | Masuk | Master data produk, varian harga, jadwal pengiriman, update status pesanan, pencatatan pengeluaran operasional |
| 4 | **Sistem -> Admin/Pemilik** | Keluar | Rekap penjualan harian/bulanan, laporan piutang pelanggan, rekap biaya operasional, tampilan kalender produksi |
| 5 | **Staff -> Sistem** | Masuk | Input pesanan baru, catatan pembayaran (cash/transfer), update status pengiriman pesanan |
| 6 | **Sistem -> Staff** | Keluar | Invoice pesanan siap kirim, daftar tagihan/pesanan belum lunas, jadwal kirim harian |
