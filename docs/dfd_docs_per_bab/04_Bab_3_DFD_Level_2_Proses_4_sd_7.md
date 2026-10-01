> 📋 **PROMPT UNTUK GEMINI DOCS (Copy teks prompt di bawah ini lalu paste ke kotak Gemini):**
> 
> *"Ubah teks DFD Level 2 (Bagian 2: Proses 4.0 - 7.0) di bawah ini menjadi format bab laporan akademik resmi Google Docs. Susun dengan Heading hierarkis yang rapi, rapikan diagram kotak alur data, buat tabel dekomposisi sub-proses dan tabel matriks hak akses Admin vs Staff menjadi tabel resmi bergaris. Tekankan logika bisnis penting: sistem pembayaran fleksibel (Lunas/Hutang), pengiriman gratis Malang Raya berkapasitas 150 pcs per trip, pelaporan arus kas, serta keamanan autentikasi bcrypt dengan sesi 30 menit."*

---

# BAB III (BAGIAN 2): DFD LEVEL 2 — DEKOMPOSISI PROSES 4.0 S/D 7.0
## Sistem Informasi Manajemen Pesanan Roti — Berkat Dinasti

---

### 4. DFD Level 2 — Proses 4.0: MANAJEMEN PEMBAYARAN

> **Deskripsi:** Menguraikan pencatatan transaksi pembayaran (Tunai, Transfer, Bayar Belakangan), penentuan status otomatis (Lunas atau Hutang), pembaharuan saldo piutang, dan pencetakan bukti struk digital yang dapat dikirim via WhatsApp.

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

#### Tabel Matriks Fungsional Sub-Proses 4.0

| Sub-Proses | Data Input | Logika & Pengolahan Sistem | Data Output | Data Store Terlibat |
|:-----------|:-----------|:---------------------------|:------------|:-------------------:|
| **4.1 Catat Pembayaran** | ID pesanan, nominal dibayar, metode bayar (tunai/transfer), bukti transfer | Merekam transaksi pembayaran masuk (mendukung pembayaran sebagian / cicil) | Data transaksi pembayaran | `pembayaran` (D4.1) |
| **4.2 Update Status Bayar Otomatis** | Akumulasi pembayaran vs Grand Total pesanan | Sistem memeriksa: jika dibayar >= total maka status = `Lunas`, jika dibayar < total maka status = `Hutang` | Status pembayaran pesanan | `pesanan.status_bayar` |
| **4.3 Update Piutang Pelanggan** | Status bayar, data transaksi | Menghitung sisa kewajiban piutang (`sisa = grand_total - total_dibayar`) dan menambahkan ke akun pelanggan | Saldo total hutang pelanggan | `pelanggan.total_hutang` (D2) |
| **4.4 Generate Struk Digital** | Data pesanan dan rincian pelunasan | Membuat lembar struk/invoice digital yang terformat rapi untuk dibagikan via tautan/WhatsApp | Struk digital pelanggan | `pesanan`, `pembayaran` |

#### Metode Pembayaran Berdasarkan Wawancara Mitra

| Metode Pembayaran | Mekanisme & Keterangan Operasional |
|:------------------|:-----------------------------------|
| **Cash (Tunai)** | Pembayaran dilakukan langsung saat barang diserahkan oleh kurir |
| **Transfer Bank** | Pembayaran via rekening bank mitra (dapat ditransfer setelah pesanan tiba) |
| **Bayar Belakangan** | Sistem penangguhan pembayaran khusus reseller/pelanggan tetap (tercatat sebagai piutang aktif) |

---

### 5. DFD Level 2 — Proses 5.0: MANAJEMEN PENGIRIMAN

> **Deskripsi:** Menguraikan penjadwalan waktu pengiriman roti dan pembaharuan status logistik kurir.  
> **Catatan Khusus:** Sistem menerapkan **GRATIS ONGKIR** di seluruh Malang Raya tanpa zonasi tarif. Armada utama berupa sepeda motor dengan bronjong/obrok berkapasitas 150 pcs roti besar per perjalanan.

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

#### Tabel Matriks Fungsional Sub-Proses 5.0

| Sub-Proses | Data Input | Logika & Pengolahan Sistem | Data Output | Data Store Terlibat |
|:-----------|:-----------|:---------------------------|:------------|:-------------------:|
| **5.1 Buat Jadwal Pengiriman** | ID pesanan, tanggal kirim, estimasi jam kirim | Mengatur jadwal kirim harian; tarif ongkos kirim diset Rp 0 (Gratis Malang Raya) | Entri jadwal pengiriman baru | `pengiriman` (D5.1) |
| **5.2 Update Status Pengiriman** | ID pengiriman, status kurir terkini | Mengubah tahapan: *Menunggu* -> *Dalam Perjalanan* -> *Sampai* (atau *Gagal*); sinkron ke status pesanan | Status kirim & tracking log | `pengiriman.status`, `pesanan.status`, `tracking_log` |

---

### 6. DFD Level 2 — Proses 6.0: PELAPORAN KEUANGAN

> **Deskripsi:** Menguraikan proses konsolidasi data pesanan, pembayaran, piutang, dan biaya operasional untuk menghasilkan laporan keuangan periodik serta fitur ekspor dokumen.

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

#### Tabel Matriks Fungsional Sub-Proses 6.0

| Sub-Proses | Data Input | Logika & Pengolahan Sistem | Data Output | Data Store Terlibat |
|:-----------|:-----------|:---------------------------|:------------|:-------------------:|
| **6.1 Rekap Penjualan** | Data pesanan & pembayaran, filter rentang tanggal | Menghitung omset bruto, jumlah transaksi, dan kuantitas produk terjual per hari/bulan | Laporan penjualan berkala | `pesanan`, `pembayaran` |
| **6.2 Laporan Piutang** | Data pelanggan, saldo hutang, riwayat tagihan | Mengelompokkan pelanggan dengan kewajiban bayar tertunda beserta usia piutang | Daftar piutang aktif | `pelanggan`, `pembayaran` |
| **6.3 Rekap Pengeluaran** | Data belanja operasional per kategori | Mengakumulasi pengeluaran modal kerja (bahan baku, kemasan, bahan bakar, dsb.) | Rekap pengeluaran usaha | `pengeluaran` (D6.1) |
| **6.4 Laporan Arus Kas** | Mutasi penerimaan tunai/transfer & biaya keluar | Menyusun posisi kas riil masuk dan keluar (*cash flow statement*) | Laporan arus kas | `transaksi_kas` (D6.2) |
| **6.5 Export Laporan** | Parameter jenis laporan yang dipilih | Mengonversi data tabel analitis menjadi berkas portabel (*PDF / Excel*) | Unduhan file laporan | Semua Data Store |

---

### 7. DFD Level 2 — Proses 7.0: MANAJEMEN PENGGUNA

> **Deskripsi:** Mengatur siklus manajemen akun pengguna internal (Admin dan Staff), enkripsi kredensial, autentikasi sesi dengan batas waktu 30 menit, dan pembatasan wewenang fitur (*Role-Based Access Control*).

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

#### Tabel Matriks Fungsional Sub-Proses 7.0

| Sub-Proses | Data Input | Logika & Pengolahan Sistem | Data Output | Data Store Terlibat |
|:-----------|:-----------|:---------------------------|:------------|:-------------------:|
| **7.1 Kelola Data Pengguna** | Nama lengkap, username, password, role, status | Pembuatan akun baru dengan *hashing* password aman (algoritma bcrypt) | Akun pengguna terdaftar | `pengguna` (D7) |
| **7.2 Autentikasi & Login** | Username dan password inputan | Komparasi kredensial hash; pembentukan token sesi kerja (*timeout 30 menit*) | Akses login diberikan / ditolak | `pengguna` |
| **7.3 Kontrol Hak Akses** | Identitas role pengguna (*admin / staff*) | Melakukan otorisasi hak menu navigasi dan proteksi endpoint sistem | Tampilan interface sesuai wewenang | `pengguna.role` |

#### Tabel Matriks Hak Akses Berdasarkan Role

| Modul / Fitur Sistem | Hak Akses Admin (Pemilik) | Hak Akses Staff |
|:---------------------|:--------------------------:|:---------------:|
| Master Produk & Katalog Harga | **Full Access** (CRUD) | Akses Ditolak |
| Master Data Pelanggan | **Full Access** (CRUD) | View Only (Lihat Data) |
| Input & Manajemen Pesanan | **Full Access** (CRUD) | **Full Access** (Input & Edit) |
| Pencatatan Kas & Pembayaran | **Full Access** (CRUD) | **Full Access** (Input Transaksi) |
| Manajemen Jadwal Pengiriman | **Full Access** (CRUD) | Input Jadwal & Update Status |
| Laporan Penjualan & Arus Kas | **Full Access** (Lihat & Ekspor) | Terbatas (Rekap Kas Harian Saja) |
| Pengaturan Akun & Pengguna | **Full Access** (Kelola User) | Akses Ditolak |
