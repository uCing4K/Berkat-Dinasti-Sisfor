> 📋 **PROMPT UNTUK GEMINI DOCS (Copy teks prompt di bawah ini lalu paste ke kotak Gemini):**
> 
> *"Ubah teks DFD Level 2 (Bagian 1: Proses 1.0 - 3.0) di bawah ini menjadi bab laporan akademik yang mendalam dan terstruktur rapi. Format setiap sub-proses dengan heading hierarkis, buat tabel dekomposisi proses (Sub-Proses, Input, Proses, Output, Data Store) menjadi tabel rapi bergaris, rapikan diagram alur kotak DFD Level 2, dan sertakan narasi penjelasan logika bisnis (seperti penentuan varian kemasan kardus/mika, klasifikasi pelanggan retail/reseller tanpa zona, dan otomatisasi nomor invoice INV-YYYYMMDD-XXXX)."*

---

# BAB III (BAGIAN 1): DFD LEVEL 2 — DEKOMPOSISI PROSES 1.0 S/D 3.0
## Sistem Informasi Manajemen Pesanan Roti — Berkat Dinasti

---

### 1. DFD Level 2 — Proses 1.0: MANAJEMEN KATALOG PRODUK

> **Deskripsi:** Memecah proses pengelolaan katalog produk menjadi 4 sub-proses terperinci untuk mengelola kategori, master produk roti, varian kemasan, dan status ketersediaan.

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

#### Tabel Matriks Fungsional Sub-Proses 1.0

| Sub-Proses | Data Input | Logika & Pengolahan Sistem | Data Output | Data Store Terlibat |
|:-----------|:-----------|:---------------------------|:------------|:-------------------:|
| **1.1 Kelola Kategori** | Nama kategori, deskripsi | Validasi dan simpan kategori produk baru/edit | Data kategori tersimpan | `kategori` (D1.1) |
| **1.2 Kelola Data Produk** | Nama produk, foto, deskripsi, masa simpan (*shelf_life*), ID kategori | Operasi CRUD master data produk roti | Data master produk tersimpan | `produk` (D1.2) |
| **1.3 Kelola Varian & Harga** | ID produk, varian kemasan (Kardus/Mika/Satuan), harga, min. order | Pengaturan variasi harga berdasarkan jenis kemasan | Varian produk tersimpan | `varian_produk` (D1.3) |
| **1.4 Kelola Status Ketersediaan** | ID produk, status ketersediaan | Update switch ketersediaan (*Tersedia* / *Habis*) | Status produk terbarui di katalog | `produk.status` |

#### Data Master Produk Aktual Mitra (Hasil Wawancara 29 Agustus 2026)

| Kategori | Nama Produk | Varian Kemasan | Harga Satuan | Minimum Order |
|:---------|:------------|:---------------|:------------:|:-------------:|
| Roti Hajatan | Roti Hajatan Isi 6 Rasa | Kardus Biasa | Rp 9.000 | 1 pcs |
| Roti Hajatan | Roti Hajatan Isi 6 Rasa | Mika Premium | Rp 10.000 | 1 pcs |
| Roti Manis | Roti Kopi | Satuan | Rp 4.000 | 50 pcs |
| Roti Manis | Roti Bijian Besar | Satuan | Rp 4.000 | 20 pcs |
| Roti Manis | Roti Bijian Kecil | Satuan | Rp 3.000 | 50 pcs |

---

### 2. DFD Level 2 — Proses 2.0: MANAJEMEN PELANGGAN

> **Deskripsi:** Menguraikan proses pendaftaran pelanggan, segmentasi pelanggan, tracking riwayat pesanan, dan pencatatan saldo piutang.  
> **Catatan Khusus:** Sistem **TIDAK menggunakan zona pengiriman**. Ongkos kirim otomatis **GRATIS** untuk seluruh area Malang Raya.

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

#### Tabel Matriks Fungsional Sub-Proses 2.0

| Sub-Proses | Data Input | Logika & Pengolahan Sistem | Data Output | Data Store Terlibat |
|:-----------|:-----------|:---------------------------|:------------|:-------------------:|
| **2.1 Registrasi Pelanggan** | Nama lengkap, alamat pengiriman, nomor WhatsApp, tipe | Menyimpan data identitas pelanggan baru tanpa penetapan zona | Data profil pelanggan | `pelanggan` (D2) |
| **2.2 Klasifikasi Pelanggan** | Tipe pelanggan | Menetapkan klasifikasi akun: *Retail* (acara syukuran) atau *Reseller* (warung/toko) | Label status tipe pelanggan | `pelanggan.tipe` |
| **2.3 Riwayat Pesanan** | ID pelanggan | Agregasi dan penarikan seluruh data transaksi masa lalu pelanggan | Log histori pesanan | `pesanan` (D3) |
| **2.4 Tracking Piutang** | ID pelanggan, catatan pembayaran | Perhitungan saldo akumulasi pembayaran vs nilai pesanan; sinkronisasi status Lunas/Hutang | Saldo piutang & status tagihan | `pelanggan`, `pembayaran` (D4) |

---

### 3. DFD Level 2 — Proses 3.0: MANAJEMEN PESANAN

> **Deskripsi:** Menguraikan alur penerimaan pesanan pre-order roti, auto-generate kode invoice, input rincian item, perubahan siklus status, dan integrasi jadwal kalender produksi.

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

#### Tabel Matriks Fungsional Sub-Proses 3.0

| Sub-Proses | Data Input | Logika & Pengolahan Sistem | Data Output | Data Store Terlibat |
|:-----------|:-----------|:---------------------------|:------------|:-------------------:|
| **3.1 Input Pesanan Baru** | ID pelanggan, daftar varian, kuantitas, catatan khusus, tanggal kirim | Merekam data pesanan baru berstatus awal *Pending* | Rekaman pesanan baru | `pesanan` (D3.1) |
| **3.2 Generate Invoice Otomatis** | ID pesanan, tanggal transaksi | Generator otomatis penomoran unik dengan format standar: `INV-YYYYMMDD-XXXX` | Nomor invoice & file invoice digital | `pesanan.no_invoice` |
| **3.3 Input Detail Item** | ID pesanan, ID varian produk, kuantitas | Menghitung otomatis: `subtotal = kuantitas x harga_satuan`, lalu mengakumulasi total harga | Rincian detail item & akumulasi grand total | `detail_pesanan` (D3.2), `pesanan` |
| **3.4 Kelola Status Pesanan** | ID pesanan, status alur baru | Memperbarui tahapan status secara sekuensial dan merekam jejak waktu perubahan ke tracking log | Status pesanan terkini & notifikasi | `pesanan.status`, `tracking_log` (D4) |
| **3.5 Kalender Produksi** | Tanggal pengiriman pesanan | Memetakan seluruh antrean pesanan aktif ke dalam kalender kerja dapur produksi | Visualisasi jadwal produksi harian | `pesanan` |

#### Diagram Siklus Hidup Status Pesanan

```
[PENDING] --------> [PROSES] --------> [KIRIM] --------> [SELESAI]
                          |
                          +-------------------------> [BATAL]
```
