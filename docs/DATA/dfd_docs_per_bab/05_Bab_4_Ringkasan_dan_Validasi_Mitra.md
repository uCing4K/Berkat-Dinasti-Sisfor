> 📋 **PROMPT UNTUK GEMINI DOCS (Copy teks prompt di bawah ini lalu paste ke kotak Gemini):**
> 
> *"Ubah teks penutup di bawah ini menjadi format bab laporan resmi Google Docs untuk RINGKASAN ARUS DATA, TABEL REFERENSI DATA STORE LENGKAP (T1-T13), DAN VALIDASI DFD TERHADAP KEBUTUHAN MITRA UMKM. Format judul dengan Heading resmi, rapikan diagram ringkasan alur utama, buat seluruh tabel (keterlibatan entitas, tabel database T1-T13, dan tabel validasi 14 poin kebutuhan mitra) menjadi tabel akademik bergaris rapi. Berikan paragraf kesimpulan penutup di akhir yang menegaskan bahwa seluruh rancangan DFD ini telah 100% memvalidasi kebutuhan operasional mitra UMKM Dynasty Food & Beverage."*

---

# BAB IV: RINGKASAN SISTEM, TABEL REFERENSI & VALIDASI MITRA
## Sistem Informasi Manajemen Pesanan Roti — Berkat Dinasti

---

### 1. Ringkasan Arus Data Utama (Global Summary)

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

---

### 2. Matriks Keterlibatan Entitas dalam Setiap Proses

| ID Proses | Nama Modul / Proses | Admin / Pemilik | Staff Operasional | Pelanggan | Data Store Utama Terkait |
|:---------:|:--------------------|:---------------:|:-----------------:|:---------:|:-------------------------|
| **1.0** | Manajemen Katalog Produk | Akses Penuh (CRUD) | Tidak Memiliki Akses | Akses Publik (Katalog) | `kategori`, `produk`, `varian_produk` (D1) |
| **2.0** | Manajemen Pelanggan | Akses Penuh (CRUD) | Lihat Data (Read-Only) | Tidak Memiliki Akses | `pelanggan` (D2) |
| **3.0** | Manajemen Pesanan | Akses Penuh (CRUD) | Akses Penuh (Input Order) | Menerima Status & Invoice | `pesanan`, `detail_pesanan` (D3) |
| **4.0** | Manajemen Pembayaran | Akses Penuh (CRUD) | Akses Penuh (Input Kas) | Menerima Struk Digital | `pembayaran`, `tracking_log` (D4) |
| **5.0** | Manajemen Pengiriman | Akses Penuh (CRUD) | Atur Jam & Update Status | Menerima Info Pengiriman | `pengiriman`, `tracking_log` (D5) |
| **6.0** | Pelaporan Keuangan | Akses Penuh (Lihat & Ekspor)| Terbatas (Kas Harian) | Tidak Memiliki Akses | Seluruh Data Store (D1 — D6) |
| **7.0** | Manajemen Pengguna | Akses Penuh (Kelola Akun) | Tidak Memiliki Akses | Tidak Memiliki Akses | `pengguna` (D7) |

---

### 3. Tabel Referensi Data Store Lengkap (T1 — T13)

| Kode Tabel | Nama Tabel Database | Isi & Entitas Data yang Disimpan | Terhubung ke Proses |
|:----------:|:--------------------|:---------------------------------|:-------------------:|
| **T1** | `kategori` | Klasifikasi kategori produk roti (Roti Hajatan, Roti Manis) | 1.0 |
| **T2** | `produk` | Master data produk roti (nama produk, foto, shelf life, status) | 1.0, 3.0 |
| **T3** | `varian_produk` | Varian kemasan & harga (Kardus, Mika, Satuan) beserta minimum order | 1.0, 3.0, 4.0 |
| **T4** | `pelanggan` | Profil pelanggan retail & reseller — *tanpa relasi zona* | 2.0, 3.0, 4.0 |
| **T5** | `pesanan` | Header transaksi pesanan (nomor invoice, tanggal kirim, total) | 3.0, 4.0, 5.0, 6.0 |
| **T6** | `detail_pesanan` | Rincian item produk yang dipesan (kuantitas, harga, subtotal) | 3.0, 6.0 |
| **T7** | `pembayaran` | Transaksi pembayaran kas/transfer dan status (Lunas / Hutang) | 4.0, 6.0 |
| **T8** | `pengiriman` | Jadwal & pelacakan kurir per pesanan — *gratis Malang Raya* | 5.0, 6.0 |
| **T9** | `tracking_log` | Catatan audit trail histori perubahan status pesanan & pengiriman | 3.0, 4.0, 5.0 |
| **T10** | `pengguna` | Kredensial akun sistem internal dan role akses (Admin & Staff) | 7.0 |
| **T11** | `pengeluaran` | Pencatatan biaya operasional harian (bahan baku, gaji, operasional) | 6.0 |
| **T12** | `transaksi_kas` | Arus kas masuk dan kas keluar usaha | 6.0 |
| **T13** | `setting` | Parameter dinamis konfigurasi sistem | Seluruh Proses |

---

### 4. Validasi Kebutuhan Mitra Berdasarkan Hasil Wawancara

Hasil komparasi dan validasi antara kebutuhan operasional UMKM **Dynasty Food & Beverage** (Hasil wawancara 29 Agustus 2026) dengan spesifikasi rancangan DFD:

| No | Kebutuhan Riil dari Hasil Wawancara Mitra | Komponen DFD yang Mengakomodasi | Status Pemenuhan |
|:--:|:------------------------------------------|:--------------------------------|:----------------:|
| 1 | Digitalisasi pencatatan pesanan (dari manual buku nota) | Proses 3.0 Manajemen Pesanan | **Terpenuhi (100%)** |
| 2 | Sentralisasi database pelanggan (Retail & Reseller) | Proses 2.0 Manajemen Pelanggan | **Terpenuhi (100%)** |
| 3 | Segmentasi pelanggan tanpa pembagian zona | Sub-proses 2.2 Klasifikasi Pelanggan | **Terpenuhi (100%)** |
| 4 | Pembuatan invoice/struk otomatis (`INV-YYYYMMDD-XXXX`) | Sub-proses 3.2 & Sub-proses 4.4 | **Terpenuhi (100%)** |
| 5 | Pelacakan status tahapan pesanan (*Pending -> Proses -> Kirim -> Selesai*) | Sub-proses 3.4 Kelola Status Pesanan | **Terpenuhi (100%)** |
| 6 | Integrasi kalender produksi harian dan jadwal kirim | Sub-proses 3.5 & Sub-proses 5.1 | **Terpenuhi (100%)** |
| 7 | Manajemen piutang fleksibel (Status: *Lunas* / *Hutang*) | Sub-proses 4.2 & Sub-proses 4.3 | **Terpenuhi (100%)** |
| 8 | Kebijakan pengiriman GRATIS seluruh Malang Raya (tanpa tarif zona) | Proses 5.0 Manajemen Pengiriman | **Terpenuhi (100%)** |
| 9 | Laporan rekap penjualan berkala (harian & bulanan) | Sub-proses 6.1 Rekap Penjualan | **Terpenuhi (100%)** |
| 10 | Buku pembantu daftar piutang pelanggan aktif | Sub-proses 6.2 Laporan Piutang | **Terpenuhi (100%)** |
| 11 | Katalog digital varian produk (Kardus, Mika, Satuan) | Proses 1.0 Manajemen Katalog Produk | **Terpenuhi (100%)** |
| 12 | Keamanan akses multi-user (*Role Admin vs Staff*) | Proses 7.0 Manajemen Pengguna | **Terpenuhi (100%)** |
| 13 | Fasilitas ekspor rekap laporan ke format berkas PDF / Excel | Sub-proses 6.5 Export Laporan | **Terpenuhi (100%)** |
| 14 | Indikator kapasitas armada obrok (kapasitas 150 pcs/trip) | Sub-proses 5.1 (Fitur Pendukung) | **Terpenuhi (100%)** |

---

*Dokumen DFD ini diverifikasi dan disahkan berdasarkan instrumen wawancara lapangan: `InterviewMitra_Kelompok5.pdf` (29 Agustus 2026).*  
*Disusun oleh Kelompok 5 — Mata Kuliah Sistem Informasi — Universitas Negeri Malang (UM).*
