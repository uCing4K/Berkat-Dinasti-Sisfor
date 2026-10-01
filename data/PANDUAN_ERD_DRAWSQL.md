# 📘 PANDUAN LENGKAP: Membuat ERD di DrawSQL.app
## ForHomie - Sistem Manajemen Pesanan Roti
**Versi:** 1.0  
**Tanggal:** 14 Februari 2026  
**Untuk:** Teman-teman tim yang belum paham

---

## 📑 DAFTAR ISI

1. [Setup Awal](#setup-awal)
2. [TABEL 1: KATEGORI](#tabel-1-kategori)
3. [TABEL 2: PRODUK](#tabel-2-produk)
4. [TABEL 3: VARIAN_PRODUK](#tabel-3-varian_produk)
5. [TABEL 4: ZONA](#tabel-4-zona)
6. [TABEL 5: PELANGGAN](#tabel-5-pelanggan)
7. [TABEL 6: PESANAN](#tabel-6-pesanan)
8. [TABEL 7: DETAIL_PESANAN](#tabel-7-detail_pesanan)
9. [TABEL 8: PEMBAYARAN](#tabel-8-pembayaran)
10. [TABEL 10: PENGIRIMAN](#tabel-9-pengiriman)
11. [TABEL 11: TRACKING_LOG](#tabel-10-tracking_log)
12. [TABEL 12: PENGGUNA](#tabel-11-pengguna)
13. [TABEL 13: PENGELUARAN](#tabel-12-pengeluaran)
14. [Troubleshooting](#troubleshooting)

---

## SETUP AWAL

### Langkah Persiapan

1. **Buka website DrawSQL**
   - URL: https://drawsql.app/
   - Bisa menggunakan akun Google atau manual login

2. **Buat Diagram Baru**
   - Klik tombol "Create a new diagram" atau tombol "+" 
   - Pilih database type: **MySQL** (sesuai PLANNING.md ForHomie)
   - Berikan nama: `forhomie` atau `ForHomie Database`

3. **Persiapan Workspace**
   - Pastikan network stabil
   - Jangan tutup tab sebelum export hasil
   - Disarankan use Ctrl+S berkala untuk save

---

## TABEL 1: KATEGORI

**Deskripsi:** Menyimpan kategori produk (Roti Hajatan, Roti Manis, dll)

### Langkah Pembuatan

#### Step 1: Tambah Tabel Baru
```
Klik tombol: "Add Table" atau "Insert Table"
Nama tabel: kategori
```

#### Step 2: Tambahkan Field - ID (Primary Key)

```
Field Name: id
Data Type: int
Constraints:
  ✓ Primary Key (klik icon kunci)
  ✓ Auto increment (opsional tapi recommended)
  ✓ Not Null (N column akan ter-check)
```

**Screenshot tips:**
- Klik icon kunci ⚿ untuk set sebagai PRIMARY KEY
- Check otomatis akan "Not Null"
- Auto increment akan otomatis aktif untuk int PK

#### Step 3: Tambahkan Field - nama_kategori

```
Field Name: nama_kategori
Data Type: varchar
Varchar Length: 100
Constraints:
  ✓ Not Null (check N column)
```

**Penjelasan:**
- `varchar(100)` = teks maksimal 100 karakter
- Contoh: "Roti Hajatan" (12 karakter) ✓
- Not Null = wajib diisi, tidak boleh kosong

#### Step 4: Tambahkan Field - deskripsi

```
Field Name: deskripsi
Data Type: text
Constraints:
  ☐ Not Null (biarkan kosong = opsional)
```

**Penjelasan:**
- `text` = teks panjang, bisa ribuan karakter
- Contoh: "Roti untuk acara hajatan/syukuran"
- Tidak perlu Not Null = opsional untuk diisi

#### Step 5: Tambahkan Field - created_at

```
Field Name: created_at
Data Type: timestamp
Default Value: CURRENT_TIMESTAMP
Constraints:
  ☐ Not Null (opsional)
```

**Penjelasan:**
- `timestamp` = tipe data tanggal + waktu
- `CURRENT_TIMESTAMP` = otomatis isi waktu saat data dibuat
- Contoh nilai: 2026-02-14 10:30:45

**Cara set default value:**
1. Klik field `created_at`
2. Lihat panel kanan → "Default"
3. Ketik atau pilih: `CURRENT_TIMESTAMP`

#### Step 6: Tambahkan Field - updated_at

```
Field Name: updated_at
Data Type: timestamp
Default Value: CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
Constraints:
  ☐ Not Null (opsional)
```

**Penjelasan:**
- Sama seperti `created_at` tapi UPDATE otomatis setiap kali data diubah
- Contoh: saat kategori diedit, `updated_at` akan update otomatis

### Hasil Akhir Tabel KATEGORI

```
kategori
├── id (int, PK, AUTO INCREMENT) ← Kunci utama 🔑
├── nama_kategori (varchar 100, NOT NULL)
├── deskripsi (text)
├── created_at (timestamp, DEFAULT: CURRENT_TIMESTAMP)
└── updated_at (timestamp, DEFAULT: CURRENT_TIMESTAMP ON UPDATE)
```

✅ **Tabel KATEGORI selesai!**

---

## TABEL 2: PRODUK

**Deskripsi:** Menyimpan data produk utama (Roti Hajatan, Roti Kopi, Roti Bijian)

### Langkah Pembuatan

#### Step 1: Tambah Tabel Baru
```
Klik: "Add Table"
Nama: produk
```

#### Step 2: Tambahkan Field - id_produk (Primary Key)

```
Field Name: id_produk
Data Type: int
Constraints:
  ✓ Primary Key
  ✓ Auto increment
  ✓ Not Null
```

**Catatan:** Sama seperti tabel kategori, ini adalah primary key unik untuk setiap produk.

#### Step 3: Tambahkan Field - id_kategori (Foreign Key ⭐)

```
Field Name: id_kategori
Data Type: int
Constraints:
  ✓ Not Null
```

**PENTING - JANGAN LUPA:**
- Field ini akan menjadi FOREIGN KEY (relasi ke tabel kategori)
- Kita akan membuat relasi SETELAH semua field ditambah
- Tidak perlu set sebagai PK, hanya NOT NULL

#### Step 4: Tambahkan Field - nama_produk

```
Field Name: nama_produk
Data Type: varchar
Varchar Length: 150
Constraints:
  ✓ Not Null
```

**Penjelasan:**
- Contoh: "Roti Hajatan Isi 6 Rasa" (26 karakter)
- 150 karakter = cukup panjang untuk judul produk

#### Step 5: Tambahkan Field - deskripsi

```
Field Name: deskripsi
Data Type: text
Constraints:
  ☐ Not Null (opsional)
```

**Penjelasan:**
- Deskripsi detail produk
- Contoh: "Paket roti hajatan berisi 6 varian rasa"

#### Step 6: Tambahkan Field - gambar

```
Field Name: gambar
Data Type: varchar
Varchar Length: 255
Constraints:
  ☐ Not Null (opsional)
```

**Penjelasan:**
- Menyimpan path/nama file gambar produk
- Contoh: "uploads/produk/roti-hajatan.jpg"
- 255 karakter = standar untuk path file

#### Step 7: Tambahkan Field - shelf_life

```
Field Name: shelf_life
Data Type: int
Default Value: 7
Constraints:
  ☐ Not Null (opsional)
```

**Penjelasan:**
- Masa kedaluwarsa dalam hari
- Contoh: 7 hari, 14 hari, dll
- Default 7 = jika tidak diisi otomatis 7 hari

#### Step 8: Tambahkan Field - status

```
Field Name: status
Data Type: varchar
Varchar Length: 20
Default Value: tersedia
Constraints:
  ☐ Not Null (opsional)
```

**Penjelasan:**
- Nilai yang diperbolehkan: "tersedia" atau "tidak_tersedia"
- Default: "tersedia" = produk tersedia saat dibuat
- Varchar 20 = cukup untuk kedua nilai

#### Step 9: Tambahkan Field - created_at & updated_at

```
Field Name: created_at
Data Type: timestamp
Default: CURRENT_TIMESTAMP

Field Name: updated_at
Data Type: timestamp
Default: CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

**Sama seperti tabel kategori**

### Hasil Akhir Tabel PRODUK

```
produk
├── id_produk (int, PK, AUTO INCREMENT) 🔑
├── id_kategori (int, NOT NULL) ← FK ke kategori
├── nama_produk (varchar 150, NOT NULL)
├── deskripsi (text)
├── gambar (varchar 255)
├── shelf_life (int, DEFAULT: 7)
├── status (varchar 20, DEFAULT: 'tersedia')
├── created_at (timestamp, DEFAULT: CURRENT_TIMESTAMP)
└── updated_at (timestamp, DEFAULT: CURRENT_TIMESTAMP ON UPDATE)
```

✅ **Tabel PRODUK selesai!**

### Buat Relasi KATEGORI → PRODUK

**Langkah:**
1. **Lihat field `id_kategori` di tabel PRODUK**
2. **Click & drag titik biru** di sebelah `id_kategori`
3. **Tarik ke tabel KATEGORI**, lepas di field `id`
4. **Garis biru akan terbentuk** menghubungkan kedua tabel
5. **Klik garis relasi** → pilih **`1:N`** (One-to-Many)
   - 1 kategori = banyak produk ✓

**Hasil:** Garis biru terbentuk antara kategori.id dan produk.id_kategori

---

## TABEL 3: VARIAN_PRODUK

**Deskripsi:** Menyimpan varian kemasan & harga produk

**Contoh Data:**
- Roti Hajatan → Kardus Biasa (Rp 9.000), Mika (Rp 10.000)
- Roti Kopi → Satuan (Rp 4.000)

### Langkah Pembuatan

#### Step 1: Tambah Tabel Baru
```
Klik: "Add Table"
Nama: varian_produk
```

#### Step 2: Field - id_varian (PK)
```
id_varian
Type: int
Constraints: PK, AUTO INCREMENT
```

#### Step 3: Field - id_produk (FK)
```
id_produk
Type: int
Constraints: NOT NULL
```

#### Step 4: Field - nama_varian
```
nama_varian
Type: varchar(100)
Constraints: NOT NULL
Note: Contoh: "Kardus Biasa", "Mika", "Satuan"
```

#### Step 5: Field - harga
```
harga
Type: decimal
Precision: 10
Scale: 2
Constraints: NOT NULL
Note: Contoh nilai: 9000.00, 10000.00, 4000.00
       decimal(10,2) = 10 digit, 2 di belakang koma
```

**Penjelasan DECIMAL(10,2):**
- 10 = total digit yang bisa disimpan (termasuk di belakang koma)
- 2 = digit di belakang koma (untuk rupiah)
- Bisa simpan: 12345678.90 (maksimal)
- Cocok untuk harga karena presisi rupiah

#### Step 6: Field - min_order
```
min_order
Type: int
Default: 1
Constraints: opsional
Note: Minimum pembelian produk ini
```

#### Step 7: Field - stok
```
stok
Type: int
Default: 0
Constraints: opsional
Note: Stok saat ini
```

#### Step 8: Field - created_at & updated_at
```
Sama seperti tabel sebelumnya
```

### Hasil Akhir Tabel VARIAN_PRODUK

```
varian_produk
├── id_varian (int, PK, AUTO INCREMENT) 🔑
├── id_produk (int, NOT NULL) ← FK ke produk
├── nama_varian (varchar 100, NOT NULL)
├── harga (decimal 10.2, NOT NULL)
├── min_order (int, DEFAULT: 1)
├── stok (int, DEFAULT: 0)
├── created_at (timestamp)
└── updated_at (timestamp)
```

✅ **Tabel VARIAN_PRODUK selesai!**

### Buat Relasi PRODUK → VARIAN_PRODUK

**Langkah:**
1. Drag dari `id_produk` di tabel VARIAN_PRODUK
2. Lepas di `id_produk` di tabel PRODUK
3. Pilih **`1:N`** (1 produk = banyak varian)

---

## TABEL 4: ZONA

**Deskripsi:** Menyimpan zona pengiriman (Malang, Batu, Luar Malang Raya)

### Langkah Pembuatan

#### Step 1: Tambah Tabel
```
Klik: "Add Table"
Nama: zona
```

#### Step 2: Field - id_zona (PK)
```
id_zona
Type: int
Constraints: PK, AUTO INCREMENT
```

#### Step 3: Field - nama_zona
```
nama_zona
Type: varchar(100)
Constraints: NOT NULL
Note: Contoh: "Kota Malang", "Kabupaten Malang", "Kota Batu"
```

#### Step 4: Field - deskripsi
```
deskripsi
Type: text
Constraints: opsional
Note: Deskripsi wilayah/area
```

#### Step 5: Field - ongkir
```
ongkir
Type: decimal
Precision: 10
Scale: 2
Default: 0.00
Note: Ongkos kirim untuk zona ini
Contoh: Zona Malang = 0, Luar Malang = 15000
```

#### Step 6: Field - created_at
```
created_at
Type: timestamp
Default: CURRENT_TIMESTAMP
```

### Hasil Akhir Tabel ZONA

```
zona
├── id_zona (int, PK, AUTO INCREMENT) 🔑
├── nama_zona (varchar 100, NOT NULL)
├── deskripsi (text)
├── ongkir (decimal 10.2, DEFAULT: 0.00)
└── created_at (timestamp)
```

✅ **Tabel ZONA selesai!**

---

## TABEL 5: PELANGGAN

**Deskripsi:** Menyimpan data pelanggan (retail & reseller)

### Langkah Pembuatan

#### Step 1: Tambah Tabel
```
Nama: pelanggan
```

#### Step 2: Field - id_pelanggan (PK)
```
id_pelanggan
Type: int
Constraints: PK, AUTO INCREMENT
```

#### Step 3: Field - id_zona (FK)
```
id_zona
Type: int
Constraints: opsional (NOT NULL tidak check, boleh kosong)
Note: Foreign Key ke zona
```

#### Step 4: Field - nama
```
nama
Type: varchar(150)
Constraints: NOT NULL
Note: Nama pelanggan
```

#### Step 5: Field - alamat
```
alamat
Type: text
Constraints: NOT NULL
Note: Alamat lengkap pelanggan
```

#### Step 6: Field - no_wa
```
no_wa
Type: varchar(20)
Constraints: NOT NULL
Note: Nomor WhatsApp
Contoh: "081234567890"
```

#### Step 7: Field - tipe
```
tipe
Type: varchar(20)
Default: retail
Note: retail / reseller
```

#### Step 8: Field - total_transaksi
```
total_transaksi
Type: decimal(15,2)
Default: 0.00
Note: Akumulasi total belanja sepanjang waktu
```

#### Step 9: Field - total_hutang
```
total_hutang
Type: decimal(15,2)
Default: 0.00
Note: Total piutang yang belum lunas
```

#### Step 10: Field - created_at & updated_at
```
Sama seperti tabel lainnya
```

### Hasil Akhir Tabel PELANGGAN

```
pelanggan
├── id_pelanggan (int, PK, AUTO INCREMENT) 🔑
├── id_zona (int) ← FK ke zona
├── nama (varchar 150, NOT NULL)
├── alamat (text, NOT NULL)
├── no_wa (varchar 20, NOT NULL)
├── tipe (varchar 20, DEFAULT: 'retail')
├── total_transaksi (decimal 15.2, DEFAULT: 0.00)
├── total_hutang (decimal 15.2, DEFAULT: 0.00)
├── created_at (timestamp)
└── updated_at (timestamp)
```

✅ **Tabel PELANGGAN selesai!**

### Buat Relasi ZONA → PELANGGAN

**Langkah:**
1. Drag dari `id_zona` di tabel PELANGGAN
2. Lepas di `id_zona` di tabel ZONA
3. Pilih **`1:N`** (1 zona = banyak pelanggan)

---

## TABEL 6: PESANAN

**Deskripsi:** Menyimpan data pesanan/order dari pelanggan

**Status Flow:** Pending → Proses → Kirim → Selesai (atau Batal)

### Langkah Pembuatan

#### Step 1: Tambah Tabel
```
Nama: pesanan
```

#### Step 2: Field - id_pesanan (PK)
```
id_pesanan
Type: int
Constraints: PK, AUTO INCREMENT
```

#### Step 3: Field - id_pelanggan (FK)
```
id_pelanggan
Type: int
Constraints: NOT NULL
```

#### Step 4: Field - no_invoice (UNIQUE)
```
no_invoice
Type: varchar(50)
Constraints: NOT NULL, UNIQUE
Note: Format: INV-YYYYMMDD-XXXX (auto-generate)
Contoh: INV-20260214-0001
```

**Penjelasan UNIQUE:**
- Setiap invoice harus unik (tidak boleh duplikat)
- Di DrawSQL, cari icon khusus untuk set UNIQUE
- Biasanya ada checkbox atau icon yang berbeda dari PK

#### Step 5: Field - tgl_pesan
```
tgl_pesan
Type: date (bukan datetime, hanya tanggal)
Constraints: NOT NULL
Note: Tanggal order dibuat
Contoh: 2026-02-14
```

#### Step 6: Field - tgl_kirim
```
tgl_kirim
Type: date
Constraints: opsional
Note: Jadwal/deadline pengiriman
```

#### Step 7: Field - waktu_kirim
```
waktu_kirim
Type: time
Constraints: opsional
Note: Jam pengiriman
Contoh: 10:30:00
```

#### Step 8: Field - total_harga
```
total_harga
Type: decimal(15,2)
Default: 0.00
Note: Total harga item (sebelum ongkir)
```

#### Step 9: Field - ongkir
```
ongkir
Type: decimal(10,2)
Default: 0.00
Note: Ongkos kirim
```

#### Step 10: Field - grand_total
```
grand_total
Type: decimal(15,2)
Default: 0.00
Note: Total akhir (total_harga + ongkir)
```

#### Step 11: Field - status
```
status
Type: varchar(20)
Default: pending
Note: pending / proses / kirim / selesai / batal
```

#### Step 12: Field - status_bayar
```
status_bayar
Type: varchar(20)
Default: belum_bayar
Note: belum_bayar / dp (down payment) / lunas
```

#### Step 13: Field - metode_bayar
```
metode_bayar
Type: varchar(20)
Default: cash
Note: cash / transfer
```

#### Step 14: Field - catatan
```
catatan
Type: text
Constraints: opsional
Note: Catatan khusus pesanan dari pelanggan
```

#### Step 15: Field - created_at & updated_at
```
Sama seperti tabel lainnya
```

### Hasil Akhir Tabel PESANAN

```
pesanan
├── id_pesanan (int, PK, AUTO INCREMENT) 🔑
├── id_pelanggan (int, NOT NULL) ← FK ke pelanggan
├── no_invoice (varchar 50, NOT NULL, UNIQUE)
├── tgl_pesan (date, NOT NULL)
├── tgl_kirim (date)
├── waktu_kirim (time)
├── total_harga (decimal 15.2, DEFAULT: 0.00)
├── ongkir (decimal 10.2, DEFAULT: 0.00)
├── grand_total (decimal 15.2, DEFAULT: 0.00)
├── status (varchar 20, DEFAULT: 'pending')
├── status_bayar (varchar 20, DEFAULT: 'belum_bayar')
├── metode_bayar (varchar 20, DEFAULT: 'cash')
├── catatan (text)
├── created_at (timestamp)
└── updated_at (timestamp)
```

✅ **Tabel PESANAN selesai!**

### Buat Relasi PELANGGAN → PESANAN

**Langkah:**
1. Drag dari `id_pelanggan` di tabel PESANAN
2. Lepas di `id_pelanggan` di tabel PELANGGAN
3. Pilih **`1:N`** (1 pelanggan = banyak pesanan)

---

## TABEL 7: DETAIL_PESANAN

**Deskripsi:** Detail item dalam pesanan (Junction Table Many-to-Many)

**Contoh:**
```
Pesanan INV-20260214-0001:
- Item 1: Roti Hajatan (Kardus) → 10 pcs @ Rp 9.000 = Rp 90.000
- Item 2: Roti Kopi → 50 pcs @ Rp 4.000 = Rp 200.000
Total: Rp 290.000
```

### Langkah Pembuatan

#### Step 1: Tambah Tabel
```
Nama: detail_pesanan
```

#### Step 2: Field - id_detail (PK)
```
id_detail
Type: int
Constraints: PK, AUTO INCREMENT
```

#### Step 3: Field - id_pesanan (FK)
```
id_pesanan
Type: int
Constraints: NOT NULL
Note: FK ke tabel pesanan
```

#### Step 4: Field - id_varian (FK)
```
id_varian
Type: int
Constraints: NOT NULL
Note: FK ke tabel varian_produk
```

#### Step 5: Field - qty
```
qty
Type: int
Constraints: NOT NULL
Note: Jumlah yang dipesan
Contoh: 10, 50, 100
```

#### Step 6: Field - harga_satuan
```
harga_satuan
Type: decimal(10,2)
Constraints: NOT NULL
Note: Harga per unit saat order (snapshot/history)
Penting: Disimpan supaya jika harga berubah, pesanan lama tetap tercatat harga lama
```

#### Step 7: Field - subtotal
```
subtotal
Type: decimal(15,2)
Constraints: NOT NULL
Note: qty × harga_satuan
Contoh: 10 × 9000 = 90000
```

#### Step 8: Field - created_at
```
created_at
Type: timestamp
Default: CURRENT_TIMESTAMP
```

### Hasil Akhir Tabel DETAIL_PESANAN

```
detail_pesanan
├── id_detail (int, PK, AUTO INCREMENT) 🔑
├── id_pesanan (int, NOT NULL) ← FK ke pesanan
├── id_varian (int, NOT NULL) ← FK ke varian_produk
├── qty (int, NOT NULL)
├── harga_satuan (decimal 10.2, NOT NULL)
├── subtotal (decimal 15.2, NOT NULL)
└── created_at (timestamp)
```

✅ **Tabel DETAIL_PESANAN selesai!**

### Buat 2 Relasi untuk DETAIL_PESANAN

**Relasi 1: PESANAN → DETAIL_PESANAN**
1. Drag dari `id_pesanan` di tabel DETAIL_PESANAN
2. Lepas di `id_pesanan` di tabel PESANAN
3. Pilih **`1:N`** (1 pesanan = banyak detail item)

**Relasi 2: VARIAN_PRODUK → DETAIL_PESANAN**
1. Drag dari `id_varian` di tabel DETAIL_PESANAN
2. Lepas di `id_varian` di tabel VARIAN_PRODUK
3. Pilih **`1:N`** (1 varian = bisa di banyak pesanan)

---

## TABEL 8: PEMBAYARAN

**Deskripsi:** Menyimpan transaksi pembayaran (bisa cicilan)

**Contoh:**
```
Pesanan Rp 290.000
- Pembayaran 1 (DP): Rp 100.000 (status: dp)
- Pembayaran 2 (Lunas): Rp 190.000 (status: lunas)
```

### Langkah Pembuatan

#### Step 1: Tambah Tabel
```
Nama: pembayaran
```

#### Step 2: Field - id_pembayaran (PK)
```
id_pembayaran
Type: int
Constraints: PK, AUTO INCREMENT
```

#### Step 3: Field - id_pesanan (FK)
```
id_pesanan
Type: int
Constraints: NOT NULL
Note: FK ke pesanan (boleh multple pembayaran per pesanan ~cicilan)
```

#### Step 4: Field - jumlah
```
jumlah
Type: decimal(15,2)
Constraints: NOT NULL
Note: Jumlah yang dibayarkan
```

#### Step 5: Field - tgl_bayar
```
tgl_bayar
Type: datetime (bukan hanya date, tapi datetime)
Constraints: NOT NULL
Note: Tanggal & waktu pembayaran
Contoh: 2026-02-14 14:30:00
```

#### Step 6: Field - metode
```
metode
Type: varchar(20)
Constraints: NOT NULL
Note: cash / transfer
```

#### Step 7: Field - bukti
```
bukti
Type: varchar(255)
Constraints: opsional
Note: Path file bukti transfer
Contoh: uploads/pembayaran/bukti-20260214-001.jpg
```

#### Step 8: Field - keterangan
```
keterangan
Type: text
Constraints: opsional
Note: Catatan pembayaran
Contoh: "DP untuk INV-001"
```

#### Step 9: Field - created_at
```
created_at
Type: timestamp
Default: CURRENT_TIMESTAMP
```

### Hasil Akhir Tabel PEMBAYARAN

```
pembayaran
├── id_pembayaran (int, PK, AUTO INCREMENT) 🔑
├── id_pesanan (int, NOT NULL) ← FK ke pesanan
├── jumlah (decimal 15.2, NOT NULL)
├── tgl_bayar (datetime, NOT NULL)
├── metode (varchar 20, NOT NULL)
├── bukti (varchar 255)
├── keterangan (text)
└── created_at (timestamp)
```

✅ **Tabel PEMBAYARAN selesai!**

### Buat Relasi PESANAN → PEMBAYARAN

**Langkah:**
1. Drag dari `id_pesanan` di tabel PEMBAYARAN
2. Lepas di `id_pesanan` di tabel PESANAN
3. Pilih **`1:N`** (1 pesanan = bisa banyak pembayaran/cicilan)

---

## TABEL 9: PENGIRIMAN

**Deskripsi:** Menyimpan data pengiriman (1 pesanan = 1 pengiriman)

### Langkah Pembuatan

#### Step 1: Tambah Tabel
```
Nama: pengiriman
```

#### Step 2: Field - id_pengiriman (PK)
```
id_pengiriman
Type: int
Constraints: PK, AUTO INCREMENT
```

#### Step 3: Field - id_pesanan (FK, UNIQUE) ⭐ PENTING
```
id_pesanan
Type: int
Constraints: NOT NULL, UNIQUE
Note: FK ke pesanan
UNIQUE = setiap pesanan hanya punya 1 pengiriman (ONE-TO-ONE relation)
```

**Penjelasan UNIQUE untuk ONE-TO-ONE:**
- Biasanya FK hanya NOT NULL (boleh duplicate)
- Tapi di sini tambah UNIQUE = tidak boleh duplicate
- Artinya: 1 pesanan = persis 1 pengiriman (tidak boleh lebih)

#### Step 4: Field - tgl_kirim
```
tgl_kirim
Type: datetime
Constraints: opsional
Note: Tanggal & waktu pengiriman aktual
```

#### Step 5: Field - status
```
status
Type: varchar(30)
Constraints: opsional
Note: menunggu / dalam_perjalanan / sampai / gagal
```

#### Step 6: Field - driver
```
driver
Type: varchar(100)
Constraints: opsional
Note: Nama driver/kurir
```

#### Step 7: Field - catatan
```
catatan
Type: text
Constraints: opsional
Note: Catatan pengiriman
Contoh: "Rumah belakang toko", "Paket dikirim 2x karena volume besar"
```

#### Step 8: Field - created_at & updated_at
```
Sama pattern sebelumnya
```

### Hasil Akhir Tabel PENGIRIMAN

```
pengiriman
├── id_pengiriman (int, PK, AUTO INCREMENT) 🔑
├── id_pesanan (int, NOT NULL, UNIQUE) ← FK ke pesanan (ONE-TO-ONE)
├── tgl_kirim (datetime)
├── status (varchar 30)
├── driver (varchar 100)
├── catatan (text)
├── created_at (timestamp)
└── updated_at (timestamp)
```

✅ **Tabel PENGIRIMAN selesai!**

### Buat Relasi PESANAN → PENGIRIMAN (ONE-TO-ONE)

**Langkah:**
1. Drag dari `id_pesanan` di tabel PENGIRIMAN
2. Lepas di `id_pesanan` di tabel PESANAN
3. Pilih **`1:1`** (bukan 1:N!) ← PENTING karena UNIQUE

---

## TABEL 10: TRACKING_LOG

**Deskripsi:** Menyimpan riwayat perubahan status pesanan (audit trail)

**Contoh:**
```
Pesanan INV-001:
- 2026-02-14 10:00 → PENDING (order baru)
- 2026-02-14 11:30 → PROSES (mulai produksi)
- 2026-02-15 14:00 → KIRIM (dalam perjalanan)
- 2026-02-15 16:45 → SELESAI (sampai)
```

### Langkah Pembuatan

#### Step 1: Tambah Tabel
```
Nama: tracking_log
```

#### Step 2: Field - id_log (PK)
```
id_log
Type: int
Constraints: PK, AUTO INCREMENT
```

#### Step 3: Field - id_pesanan (FK)
```
id_pesanan
Type: int
Constraints: NOT NULL
```

#### Step 4: Field - status
```
status
Type: varchar(50)
Constraints: NOT NULL
Note: Status yang dicatat
Nilai: pending / proses / kirim / selesai / batal
```

#### Step 5: Field - keterangan
```
keterangan
Type: text
Constraints: opsional
Note: Deskripsi perubahan
Contoh: "Status berubah dari Pending menjadi Proses"
```

#### Step 6: Field - created_at
```
created_at
Type: timestamp
Default: CURRENT_TIMESTAMP
Note: Waktu status change tercatat (bukan updated, tapi baru insert)
```

### Hasil Akhir Tabel TRACKING_LOG

```
tracking_log
├── id_log (int, PK, AUTO INCREMENT) 🔑
├── id_pesanan (int, NOT NULL) ← FK ke pesanan
├── status (varchar 50, NOT NULL)
├── keterangan (text)
└── created_at (timestamp)
```

✅ **Tabel TRACKING_LOG selesai!**

### Buat Relasi PESANAN → TRACKING_LOG

**Langkah:**
1. Drag dari `id_pesanan` di tabel TRACKING_LOG
2. Lepas di `id_pesanan` di tabel PESANAN
3. Pilih **`1:N`** (1 pesanan = banyak log entries)

---

## TABEL 11: PENGGUNA

**Deskripsi:** Menyimpan data user admin/staff sistem

### Langkah Pembuatan

#### Step 1: Tambah Tabel
```
Nama: pengguna
```

#### Step 2: Field - id_user (PK)
```
id_user
Type: int
Constraints: PK, AUTO INCREMENT
```

#### Step 3: Field - username
```
username
Type: varchar(50)
Constraints: NOT NULL, UNIQUE
Note: Username untuk login
Contoh: "admin", "staff01"
```

#### Step 4: Field - password
```
password
Type: varchar(255)
Constraints: NOT NULL
Note: Password (hashed dengan bcrypt, bukan plain text!)
Contoh: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og...
```

#### Step 5: Field - nama_lengkap
```
nama_lengkap
Type: varchar(150)
Constraints: opsional
Note: Nama lengkap user
```

#### Step 6: Field - role
```
role
Type: varchar(20)
Constraints: opsional
Note: admin / staff
```

#### Step 7: Field - created_at & updated_at
```
Sama pattern sebelumnya
```

### Hasil Akhir Tabel PENGGUNA

```
pengguna
├── id_user (int, PK, AUTO INCREMENT) 🔑
├── username (varchar 50, NOT NULL, UNIQUE)
├── password (varchar 255, NOT NULL)
├── nama_lengkap (varchar 150)
├── role (varchar 20)
├── created_at (timestamp)
└── updated_at (timestamp)
```

✅ **Tabel PENGGUNA selesai!**

**CATATAN:** Tabel ini TIDAK punya FK ke tabel lain. Dia standalone table.

---

## TABEL 12: PENGELUARAN

**Deskripsi:** Menyimpan data pengeluaran/biaya operasional bisnis

**Contoh:**
```
- Beli bahan baku: Rp 500.000
- Biaya listrik: Rp 100.000
- Biaya transport: Rp 200.000
```

### Langkah Pembuatan

#### Step 1: Tambah Tabel
```
Nama: pengeluaran
```

#### Step 2: Field - id_pengeluaran (PK)
```
id_pengeluaran
Type: int
Constraints: PK, AUTO INCREMENT
```

#### Step 3: Field - tanggal
```
tanggal
Type: date
Constraints: NOT NULL
Note: Tanggal pengeluaran terjadi
```

#### Step 4: Field - kategori
```
kategori
Type: varchar(100)
Constraints: opsional
Note: Kategori pengeluaran
Contoh: "Bahan Baku", "Listrik", "Transport", "Lain-lain"
```

#### Step 5: Field - deskripsi
```
deskripsi
Type: text
Constraints: opsional
Note: Deskripsi detail pengeluaran
Contoh: "Beli tepung terigu 50kg di supplier A"
```

#### Step 6: Field - jumlah
```
jumlah
Type: decimal(15,2)
Constraints: NOT NULL
Note: Nominal pengeluaran dalam rupiah
```

#### Step 7: Field - created_at
```
created_at
Type: timestamp
Default: CURRENT_TIMESTAMP
```

### Hasil Akhir Tabel PENGELUARAN

```
pengeluaran
├── id_pengeluaran (int, PK, AUTO INCREMENT) 🔑
├── tanggal (date, NOT NULL)
├── kategori (varchar 100)
├── deskripsi (text)
├── jumlah (decimal 15.2, NOT NULL)
└── created_at (timestamp)
```

✅ **Tabel PENGELUARAN selesai! (TABEL TERAKHIR)**

**CATATAN:** Tabel ini TIDAK punya FK ke tabel lain. Dia standalone table.

---

## RINGKASAN SEMUA RELASI

Setelah semua tabel dibuat, berikut SEMUA relasi yang harus ada:

```
1. kategori.id ←1:N→ produk.id_kategori
2. produk.id_produk ←1:N→ varian_produk.id_produk
3. zona.id_zona ←1:N→ pelanggan.id_zona
4. pelanggan.id_pelanggan ←1:N→ pesanan.id_pelanggan
5. pesanan.id_pesanan ←1:N→ detail_pesanan.id_pesanan
6. varian_produk.id_varian ←1:N→ detail_pesanan.id_varian
7. pesanan.id_pesanan ←1:N→ pembayaran.id_pesanan
8. pesanan.id_pesanan ←1:1→ pengiriman.id_pesanan ⭐ (UNIQUE)
9. pesanan.id_pesanan ←1:N→ tracking_log.id_pesanan
```

**Tabel TANPA relasi (Standalone):**
- pengguna
- pengeluaran

---

## TIPS & BEST PRACTICES

### ✅ DO (LAKUKAN)

1. **Gunakan naming convention konsisten**
   - PK: `id_{table_name}` (id_kategori, id_produk, dll)
   - FK: `id_{referenced_table}` (id_kategori dalam produk)
   - Timestamps: selalu `created_at`, `updated_at`

2. **Set constraints dengan benar**
   - PK = harus ada, tidak boleh NULL, tidak boleh duplikat
   - FK = harus ada, harus mengacu ke PK di tabel lain
   - NOT NULL = field wajib diisi saat insert

3. **Gunakan tipe data yang sesuai**
   - Currency/harga = DECIMAL(10,2), jangan FLOAT
   - Tanggal = DATE, Tanggal+Waktu = DATETIME/TIMESTAMP
   - Teks pendek (< 100 char) = VARCHAR, panjang = TEXT

4. **Beri default value yang logical**
   - Timestamps = CURRENT_TIMESTAMP
   - Status = nilai default yang masuk akal
   - Angka = 0 atau nilai awal yang sesuai

### ❌ DON'T (JANGAN)

1. ❌ Jangan bikin relasi circular (A ke B, B ke A)
2. ❌ Jangan gunakan ENUM karena sulit ekspor import
3. ❌ Jangan pairing PK dengan FK (redundan)
4. ❌ Jangan lupa cardinality saat membuat relasi (1:1, 1:N, etc)
5. ❌ Jangan simpan password plain text (harus hashed)

---

## TROUBLESHOOTING

### Problem 1: "Tidak bisa drag relasi"

**Solusi:**
- Pastikan kedua tabel sudah ada di canvas
- Klik dari field SOURCE (yang punya FK), drag ke field REFERENCE
- Jangan dari PK, dari FK!
- Lepas di field yang sesuai (biasanya PK di tabel target)

### Problem 2: "Relasi tidak muncul"

**Solusi:**
- Klik relasi → lihat cardinality di tengah (1:1, 1:N, N:1)
- Pastikan pilihan cardinality benar
- Coba delete relasi dan buat ulang

### Problem 3: "Field tidak bisa jadi UNIQUE"

**Solusi:**
- Di DrawSQL, UNIQUE biasanya ada di column attributes
- Klik field → cari icon atau checkbox UNIQUE
- Kalau tidak ada option, skip dulu (bisa ditambah di SQL nanti)

### Problem 4: "Lupa type data apa"

**Referensi:**
- Bilangan bulat: INT
- Teks (< 100 char): VARCHAR(n)
- Teks panjang: TEXT
- Uang/decimal: DECIMAL(10,2)
- Tanggal saja: DATE
- Tanggal + waktu: DATETIME atau TIMESTAMP

### Problem 5: "Mau export ERD jadi PNG/PDF"

**Langkah:**
- Menu (biasanya kanan atas) → "Export"
- Pilih format: PNG, PDF, SVG
- Download

### Problem 6: "Mau export jadi SQL"

**Langkah:**
- Menu → "Export"
- Pilih format: MySQL SQL / SQL Script
- Copy ke file schema.sql
- Siap import ke database

---

## AFTER ALL TABLES COMPLETE

**Checklist Final:**

- [ ] 12 Tabel dibuat
- [ ] Semua fields lengkap
- [ ] Semua PK diset
- [ ] Semua FK direlasi
- [ ] Cardinality benar (1:1, 1:N)
- [ ] Export ke PNG untuk dokumentasi
- [ ] Export ke SQL untuk implementasi

---

## NEXT STEPS

1. **Export PNG:** Untuk dokumentasi laporan
2. **Export SQL:** Untuk import ke MySQL
3. **Share ke tim:** Untuk review sebelum development
4. **Testing data:** Insert sample data untuk test relasi

---

**PANDUAN INI SELESAI!**

Terima kasih sudah membaca. Semoga teman-teman Anda bisa membuat ERD dengan mudah sekarang! 🎉

Jika ada pertanyaan, silakan refer kembali ke bagian [TROUBLESHOOTING](#troubleshooting).

**Good luck dengan ForHomie! 🚀**
